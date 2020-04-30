<?php

namespace app\api\controller\employer;

use app\models\employer\YgOrder;
use crmeb\services\UtilService;
use logs\SeparateLog;
use think\facade\Db;
use redis\Redis;
use app\Request;
use alipay\AliPay;
use wechatpay\Wechat;

class Index
{
    /**
     * 用户提交需求表
     */
    public function ChoiceWorker(Request $request)
    {
        $data = input('post.');
        $payType = $data['pay_type'];
//        if ($payType=='weixin') return app('json')->fail('微信支付异常请选择其他支付方式');
        $data['uid'] = $request->uid();
        $data['order_id'] = self::makeOrderNo();
        $data['add_time'] = time();
        $data['is_urgent'] = $data['urgent'];
        $data['total_price'] = $data['price'];
        $data['address'] = $data['addressId'];
        $data['pay_price'] = $data['price'];
        unset($data['addressId']);
        unset($data['price']);
        unset($data['urgent']);
//        $data['pay_price']=0.01;
        $data['process'] = json_encode([['time' => date('Y-m-d H:s:i'), 'name' => '发布订单,等待工人接单']], true);
        $coupon_true = false;
        if (isset($data['coupon_id']) && $data['coupon_id'] != 0) {
            $coupon = Db::name('store_coupon_user')->where('id', $data['coupon_id'])->where('status', 0)->find();
            if ($coupon) {
                if ($coupon['end_time'] < time()) return app('json')->fail('优惠券已过期');
                $coupon_true = true;
            } else {
                return app('json')->fail('优惠券已使用');
            }
        }
        try {
            if ($coupon_true) {
                Db::name('store_coupon_user')->where('id', $coupon['id'])->update(['use_time' => time(), 'status' => 1]);
                $data['pay_price'] = bcsub($data['pay_price'], $coupon['coupon_price'], 2);
                $data['coupon_price'] = $coupon['coupon_price'];
            }
            $res = YgOrder::create($data);
        } catch (\Exception $e) {
            SeparateLog::logs(['yg' => $e->getMessage(), 'line' => $e->getLine(), 'time' => date('Y-m-d H:i:s', time())], 'error');
            return app('json')->fail('创建用工订单失败');

        }

        if ($res) {

            switch ($payType) {
                case 'weixin':
                    $app = Wechat::appPya();
                    $payOrder = [
                        'out_trade_no' => $res['order_id'],
                        'body' => '用工服务费',
                        'total_fee' => $data['pay_price'] * 100,
                        'trade_type' => 'APP',
                        'notify_url' => 'https://api.aiyoucai.net/api/notify/yg_wechat'
                    ];
                    $pay = $app->order->unify($payOrder);
                    $jssdk = $app->jssdk->appConfig($pay['prepay_id']);
                    if ($jssdk) {
                        return app('json')->successful(['pay' => $jssdk]);
                    } else {
                        return app('json')->fail('支付失败');

                    }
                    break;
                case 'alipay':
                    $a = ['out_trade_no' => $res['order_id'], 'product_code' => 'FAST_INSTANT_TRADE_PAY', 'total_amount' => $data['pay_price'], 'subject' => '用工服务费'];
                    $pay = new AliPay();
                    $res = $pay->payphone($a);
                    if ($res) {
                        return app('json')->successful(['pay' => $res]);
                    } else {
                        return app('json')->fail('支付失败');

                    }
                    break;
            }
        }
    }

    /**修改状态
     * @return \think\response\Json
     */
    public function editOrder(Request $request)
    {
        $uid = $request->uid();
        $input = input('post.');
        $model = new YgOrder();
        $find = $model->where('id', $input['id'])->where('uid', $uid)->find();
        if (!$find) {
            return app('json')->fail('订单不存在');

        }
        switch ($input['status']) {
            case 1:
                $status = 1;
                $arr[] = ['time' => date('Y-m-d H:s:i'), 'name' => '发布订单,等待工人接单'];
                break;
            case 3:
                $status = 3;
                $arr = json_decode($find['process'], true);
                $arr[] = ['time' => date('Y-m-d H:s:i'), 'name' => '工人已到达'];
                break;
            case 4:
                $status = 4;
                $arr = json_decode($find['process'], true);
                $arr[] = ['time' => date('Y-m-d H:s:i'), 'name' => '处理完成'];
                break;
            case 5:
                $status = 5;
                $arr = json_decode($find['process'], true);
                $arr[] = ['time' => date('Y-m-d H:s:i'), 'name' => '评价成功,感谢您的使用'];
                break;
            case -1:
                if ($find['status'] == 1) {

                    if ($find['pay_type'] == 'weixin') {
                        $byOutTradeNumber = Wechat::appPya()->refund->byOutTradeNumber($find['order_id'], $find['uid'] . '-' . time(),
                            $find['pay_price'] * 100, $find['pay_price'] * 100);

                        if ($byOutTradeNumber['return_code'] == 'SUCCESS' && $byOutTradeNumber['result_code'] == 'SUCCESS') {
                            $status = -1;
                            $arr = json_decode($find['process'], true);
                            $arr[] = ['time' => date('Y-m-d H:s:i'), 'name' => '取消订单'];
                        }
                    } elseif ($find['pay_type'] == 'alipay') {
                        $data = ['out_trade_no' => $find['order_id'], 'refund_amount' => $find['pay_price'], 'operator_id' => $find['uid']];
                        $pay = new AliPay();
                        $alires = $pay->refund($data);
                        if ($alires->alipay_trade_refund_response->code == 10000) {
                            $status = -1;
                            $arr = json_decode($find['process'], true);
                            $arr[] = ['time' => date('Y-m-d H:s:i'), 'name' => '取消订单'];
                        }
                    }
                } else {
                    return app('json')->fail('订单已被接取不能取消');
                }
                break;
            default:
                return app('json')->fail('非法参数');
        }

        Db::startTrans();
        try {
            $res = $model->where('id', $input['id'])->where('uid', $uid)->update(['status' => $status, 'process' => json_encode($arr, true)]);
            if ($status == 4) {
                $bill = ['yg_id' => $uid, 'link_id' => $find['order_id'], 'pm' => 1, 'title' => $find['pay_type'] == 'alipay' ? '支付宝' : '微信' . '支付',
                    'type' => $find['pay_type'] == 'alipay' ? 2 : 3, 'number' => $find['pay_price'], 'add_time' => time()
                ];
                Db::name('yg_users_bill')->insert($bill);
//                $this->accounts($find);
                $redis = new Redis(['index' => 1]);
                $redis->zrem('receipt', $find['yg_uid']);
            }

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return app('json')->fail($e->getMessage());

        }
        if ($res) {

            if ($status == -1 && $find['alipay']) {
                $bill = ['yg_id' => $uid, 'link_id' => $find['order_id'], 'pm' => -1, 'title' => '支付宝退款',
                    'type' => $find['pay_type'] == 2, 'number' => $find['pay_price'], 'add_time' => time()
                ];
                Db::name('yg_users_bill')->insert($bill);
                return app('json')->successful('退款成功退款金额为:' . $alires->alipay_trade_refund_response->refund_fee);
            } elseif ($status == -1 && $find['weixin']) {
                $bill = ['yg_id' => $uid, 'link_id' => $find['order_id'], 'pm' => -1, 'title' => '微信退款',
                    'type' => $find['pay_type'] == 3, 'number' => $find['pay_price'], 'add_time' => time()
                ];
                Db::name('yg_users_bill')->insert($bill);
                return app('json')->successful('退款成功退款金额为:' . $byOutTradeNumber['refund_fee'] / 100);
            }
            return app('json')->successful('提交成功');
        } else {
            return app('json')->fail('提交失败');
        }
    }

    public function accounts($order)
    {
        if ($order['pay_type'] == 'weixin') {
        } elseif ($order['pay_type'] == 'alipay') {
            $system_pay = Db::name('system_pay')->where('category', 2)->where('uid', $order['yg_uid'])->find();
            $accounts = Db::name('yg_order_accounts')->where('oid', $order['id'])->find();
            if ($system_pay) {
                $pay = new AliPay();
                $amount = $order['pay_price'];

                $order_info = [
                    ['trans_in_type' => 'userId', 'trans_in' => $system_pay['account'], 'amount' => "$amount",
                        'desc' => '用工订单服务费' . $order['order_id']]
                ];
                $orderSettle = ['out_request_no' => time(), 'trade_no' => $accounts['trade_no'],
                    'operator_id' => 'admin', 'royalty_parameters' => $order_info];

                $result = $pay->orderSettle($orderSettle);
                $res = isset($result->alipay_trade_order_settle_response) ? $result->alipay_trade_order_settle_response : '';
                if ($res && $res->code == 10000) {
                    Db::name('yg_order_accounts')->where('id', $accounts['id'])->update(['status' => 1]);
                } else {
                    SeparateLog::logs(['yg' => $result], 'error');

                    throw new \think\Exception('确认收货失败请联系管理员');

                }
            }

        }
    }

    /**订单列表
     * @return \think\response\Json
     */
    public function order(Request $request)
    {
        list($page, $status) = UtilService::getMore([
            ['page', 1],
            ['status', 1],
        ], $request, true);
        $uid = $request->uid();
        $model = new YgOrder();
        if ($status != 10) {
            $a = $model->where('status', '=', $status)->where('uid', $uid)->order('add_time', 'desc')->page($page)->field('id,order_id,user_address,images,total_price,mark,status,add_time')->select();
        } else {
            $a = $model->where('status', '>=', 1)->where('uid', $uid)->order('add_time', 'desc')->page($page)->field('id,order_id,user_address,images,total_price,mark,status,add_time')->select();

        }
        return app('json')->successful($a->toArray());
    }

    /**
     * 订单详情
     */
    public function details($id)
    {
        $data = YgOrder::where('id', $id)->field('id,order_id,real_name,user_phone,user_address,images,total_price,mark,is_urgent,status,add_time,process,pay_type,yg_uid')->find();
        if ($data) {
            if ($data['yg_uid'] != 0) {
                $user = Db::name('yg_users')->where('id', $data['yg_uid'])->field('account,nickname')->find();
                $data['yg_user'] = $user['nickname'];
                $data['yg_phone'] = $user['account'];
            } else {
                $data['yg_user'] = '暂无';
                $data['yg_phone'] = '暂无';
            }
            unset($data['yg_uid']);
            $reply = Db::name('yg_order_reply')->where('oid', $id)->field('service_score score,service_speed speed,service_quality quality')->find();
            if ($reply) {
                $data['reply'] = $reply;
            } else {
                $data['reply'] = '';
            }
        } else {
            return app('json')->fail('当前订单不存在');
        }
        return app('json')->successful($data->toArray());
    }

    /**
     * 提交评论
     */
    public function comment(Request $request)
    {

        $input = input('post.');
        $data['uid'] = $request->uid();
        $data['add_time'] = time();
        $data['oid'] = $input['oid'];
        $data['service_score'] = $input['score'];
        $data['service_speed'] = $input['speed'];
        $data['service_quality'] = $input['quality'];
        $res = Db::name('yg_order_reply')->insert($data);

        if ($res) {
            Db::name('yg_order')->where('id', $input['oid'])->update(['status' => 5]);

            return app('json')->successful('提交成功');
        } else {
            return app('json')->fail('提交失败');
        }
    }

    public static function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2019] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;

    }
}