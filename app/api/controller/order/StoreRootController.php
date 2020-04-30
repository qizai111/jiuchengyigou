<?php

namespace app\api\controller\order;

use app\Request;
use app\admin\controller\order\StoreOrderPrint;

/*
*订单支付成功后调用类
*/
class StoreRootController
{
    /**
     * 新订单提醒
     * @param Request $list @用户
     *  @param Request $cont 内容
     * @return mixed
     */
    public static function  order_robot($order)
    {
        $url= config("roboot.url");
        $rname = $order['real_name'];
        $phone = $order['user_phone'];
        $address = $order['user_address'];
        $pay_time = date("Y-m-d H:i:s");
        $data = array(
            "msgtype"=>"text",
            "text"=>array(
                "content"=>"用户:$rname.已下单，请相关同事注意。\n>下单时间:$pay_time.\n>电话:$phone.\n>地址:$address.>系统:酒城易购.",
                "mentioned_list" => "@all",
            )
        );
		StoreOrderPrint::index($order['id']);//打印小票
        request_post($url, json_encode($data,'320'),'json');
    }
}
