<?php


namespace app\admin\controller\activity;

use crmeb\services\JsonService;
use think\facade\Db;
use app\admin\controller\AuthController;
/**
 * 签到活动 用户领取记录
 * Class StoreSeckill
 * @package app\admin\controller\store
 */
class ActivityReceive extends AuthController
{
    /**
     * 显示领取记录列表
     *
     * @return \think\Response
     */
    public function index(){

        return $this->fetch();
    }

    /**
     * 异步获取领取记录列表
     */

    public function receiveList(){
        $input = input('get.');
        if (isset($input['key'])) {
            switch ($input['key']) {
                case 1:
                    $where = null;
                    break;
                case 2:
                    $where[] = ['status', '>', 1];
                    break;

                case 3:
                    $where[] = ['status', '>', 4];
                    break;
                default:
                    $where = null;
                    break;
            }
        } else {
            $where = null;
        }
        $date1 = strtotime(date("Y-m")."-1");
        $date2 = strtotime(date("Y-m-t 23:59:00"));
        $count = Db::name('user_receive_bill')->count();
        $data = Db::name('user_receive_bill')->alias('r')->where($where)->page($input['page'])->order('r.id', 'desc')
            ->where("r.status",1)->whereBetween("receive_time","$date1,$date2")
            ->field("sign_sum_day,goods_number,receive_time,r.status,d.province,d.city,d.district,d.detail,d.phone,u.nickname,u.avatar,g.simple_name,g.unit")
            ->join("activity_goods g","r.gid = g.id")
            ->join("user u","r.uid = u.uid")->join("user_address d","r.address_id = d.id")
            ->limit(15)->select()->toArray();

        //  halt($data);
        return JsonService::successlayui($count, $data);
    }
}