<?php

namespace app\admin\controller\order;

use app\admin\controller\AuthController;
use think\facade\Db;
/**
 * 订单打印小票类
 * Class StoreOrderPrint
 * @package app\api\controller\order
 */
class StoreOrderPrint extends AuthController
{

    /**
     * 执行打印请求
     *
     * @return string
     */
    public static function index($id)
    {
        $printdata = self::printData($id);
        $app_secret = config("ushengyun.app_secret");
        $data = array(
            'appid' => config("ushengyun.appid"),
            'timestamp' => time(),
            'deviceid' => config("ushengyun.deviceid"),
            'devicesecret' => config("ushengyun.devicesecret"),
            'printdata' =>$printdata
        );
        ksort($data);
        $str = "";
        foreach ($data as $key => $value) {
            $str .= $key . $value;
        }
        $str = $str . $app_secret;
        $sign = md5($str);
        $data['sign'] = $sign;
        $url = "https://open-api.ushengyun.com/printer/print";
       return self::send_post($url,$data);
    }
    /**
     * 编辑打印内容
     *
     * @return string
     */
    public static function printData($id){
        $order = self::orderInfo($id);
        $count = count($order);
        $date = date("Y-m-d H:i:s", $order[0]['pay_time']);
        $str = '';
        $vip_Price = 0;
        $price = 0;
        $payType = "";
        if($order[0]["pay_type"] == "weixin"){
            $payType = "微信支付";
        }
        elseif ($order[0]["pay_type"] == "yue"){
            $payType = "余额支付";
        }
        elseif ($order[0]["pay_type"] == "offline"){
            $payType = "线下支付";
        }
        if ($order[0]["is_postage"] == 1){
            $is_postage = "已包邮";
        }else{
            $is_postage = "未包邮";
        }
        for($i=0;$i<$count;$i++){
            $vip_Price += ($order[$i]["vip_truePrice"]*$order[$i]["cart_num"]);
            $price += ($order[$i]["price"]*$order[$i]["cart_num"]);
            $str .= '<TR><TD>'.$order[$i]["store_name"].$order[$i]["suk"]."  ".$order[$i]["truePrice"].'×'.$order[$i]["cart_num"].'</TD></TR>';
        }
        $vip_Price = (float)$vip_Price;
        $price = (float)$price;
        $printdata = ' <MC>4</MC><S1><C>#酒城易购</C></S1><C>*'.$order[0]["store_name"].'*</C>********************************<C>--在线支付--</C><S1>备注：'.$order[0]["mark"].'
</S1><RN>下单时间:'.$date.'<RN>订单编号:'.$order[0]["order_id"].'<RN>********************************<S1>'.$order[0]['activityGoods'].$str.'</S1>--------------其他--------------<RN>是否包邮:               '.$is_postage.'<RN>配送费:                 ￥'.$order[0]["postage"].'<RN>折扣:                   ￥'.$vip_Price.'<RN>********************************<RN>配送时间        '.$order[0]["delivery_time"].'<RN>********************************               支付方式:'.$payType.'<RN>                   原价:'.$price.'<RN>                 总价:<S1>'.$order[0]["pay_price"].'</S1><RN><S1>'.$order[0]["user_address"].'</S1><RN>用户:'.$order[0]["real_name"].'<RN>手机号:'.$order[0]["user_phone"].' <RN>';
        return $printdata;
    }
    /**
     * 获取订单详情和购物详情
     *
     * @param array $id_订单ID
     * @return array
     */
    public static function orderInfo($id)
    {
        //判断是否为秒杀拼团砍价活动商品
        $goods = Db::name("store_order")->where("id",$id)->find();
        if($goods["seckill_id"] != 0){
            $join ="store_seckill p";
            $store_name = "p.title as store_name";
            $title = "【秒杀活动商品】";
        }elseif($goods["bargain_id"] != 0){
            $join ="store_bargain p";
            $store_name = "p.store_name";
            $title = "【砍价活动商品】";
        }elseif($goods["combination_id"] != 0){
            $join ="store_combination p";
            $store_name = "p.title as store_name";
            $title = "【拼团活动商品】";
        }else{
            $join ="store_product p";
            $store_name = "p.store_name";
            $title = "【普通商品】";
        }
        $order = Db::name("store_order_cart_info")->alias('a')->field("a.cart_info,o.order_id,o.real_name,o.user_phone,o.user_address,o.pay_price,o.pay_time,o.delivery_time,o.pay_type,o.total_postage,o.mark,$store_name")
            ->join($join,"a.product_id = p.id")
            ->join("store_order o","a.oid = o.id")
            ->join("store_cart c","a.cart_id = c.id")->where("a.oid",$id)->select()->toArray();

        foreach ($order as $key=>$value){
            $cart_info = \Qiniu\json_decode($value['cart_info'],true);
            if (array_key_exists('attrInfo',$cart_info['productInfo'])){
                $order[$key]['suk'] = $cart_info['productInfo']['attrInfo']['suk'];
                $order[$key]['price'] = $cart_info['productInfo']['attrInfo']['price'];
            }else{
                $order[$key]['suk'] = '';
                $order[$key]['price'] = $cart_info['productInfo']['price'];
            }
            $order[$key]['truePrice'] = $cart_info['truePrice'];
            $order[$key]['vip_truePrice'] = $cart_info['vip_truePrice'];
            $order[$key]['cart_num'] = $cart_info['cart_num'];
            $order[$key]['postage'] = $cart_info['productInfo']['postage'];
            $order[$key]['is_postage'] = $cart_info['productInfo']['is_postage'];
            $order[$key]['activityGoods'] = $title;
        }
        return $order;
    }
    /**
     * 发送post请求
     * @param string $url 请求地址
     * @param array $post_data post键值对数据
     * @return string
     */
    public static function send_post($url,$data)
    {
        $postdata = json_encode($data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type:application/json',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    /**

     * Curl版本post 请求

     * 使用方法：

     * $post_string = "app=request&version=beta";

     * request_by_curl('http://www.jb51.net/restServer.php', $post_string);

     */

    function request_by_curl($remote_server, $post_string) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $remote_server);

        curl_setopt($ch, CURLOPT_POSTFIELDS, 'mypost=' . $post_string);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_USERAGENT, "jb51.net's CURL Example beta");

        $data = curl_exec($ch);

        curl_close($ch);

        return $data;

    }
}