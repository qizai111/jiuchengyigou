<?php
namespace app\admin\controller\live;

use app\admin\controller\AuthController;
use app\admin\controller\order\StoreOrderPrint;
use app\admin\model\system\SystemUserLevel;
use crmeb\services\FormBuilder as Form;
use crmeb\services\JsonService;
use think\facade\Route as Url;
use think\Request;
use crmeb\services\CacheService;
use think\response\Json;

/*
 * 直播类
 * */
class liveBroadcast extends AuthController
{
    /**
    *获取access_token
     */
    public function access(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".sys_config('routine_appId')."&secret=".sys_config("routine_appsecret");
        $access = file_get_contents($url);
        $access_token = \Qiniu\json_decode($access)->access_token;
        $expires = \Qiniu\json_decode($access)->expires_in;
        //将access_token 写入缓存
        CacheService::set("access_token",$access_token,$expires);
        return $access_token;
    }

    /**
     * 判断token是否过期
     */
    public function isExpired(){

    }

    public function index(){

       return self::fetch();
    }
    /**
     *获取直播房间信息
     */
    public function roomInfo(){
        CacheService::clear();
        //获取房间信息的录播信息
        $res = CacheService::get("room_info");
        if(!$res){ //若缓存到期，或不存在  则调用接口获取直播间信息
            //获取access_token
            $access_token = CacheService::get("access_token");
            if(!$access_token){
                $access_token = self::access();
            }
            $url = "http://api.weixin.qq.com/wxa/business/getliveinfo?access_token=".$access_token;
            $data = array(
                'start' => '0', //其实拉取直播间，start =0 表示从第1个直播间拉取
                'limit' => '10' //每次拉取的个数上限
            );
            // TODO 因为获取直播间信息和获取录播视频每日调用限制为500次
            // TODO 将直播间信息写入缓存
            $res = StoreOrderPrint::send_post($url,$data);
            $res = \Qiniu\json_decode($res,true);
            $time = 7200; //缓存时间
            if($res['errcode'] == 0 && $res['errmsg'] == 'ok'){
                CacheService::set("room_info",$res,$time);
            }else{
                return JsonService::fail("$res->errmsg");
            }
        }
        $room_info = $res["room_info"];
      foreach ($room_info as $k=>$v){
          $room_info[$k]["start_time"] = date("Y-m-d H:i:s",$v["start_time"]);
          $room_info[$k]["end_time"] = date("Y-m-d H:i:s",$v["end_time"]);
      }
       return JsonService::successlayui($res['total'],$room_info);
    }

    /**
     *获取录播视频
     */
    public function videotape($id){
        //获取缓存录播信息
        $video = CacheService::get("live_replay");
        if(!$video){ //若缓存到期，或不存在  则调用接口获取直播间信息
            //获取access_token
            $access_token = CacheService::get("access_token");
            if(!$access_token){
                $access_token = self::access();
            }
            $url = "http://api.weixin.qq.com/wxa/business/getliveinfo?access_token=".$access_token;
            $data = array(
                "action" => "get_replay", // 获取回放
                "room_id" => $id, // 直播间   id
                "start" => 0, // 起始拉取视频，start =   0   表示从第    1   个视频片段开始拉取
                "limit" => 10 // 每次拉取的个数上限，不要设置过大，建议  100 以内
            );
            // TODO 因为获取直播间信息和获取录播视频每日调用限制为500次
            // TODO 将直播间信息写入缓存
            $video = StoreOrderPrint::send_post($url,$data);
            $video = \Qiniu\json_decode($video,true);
            $time = 7200;  //缓存时间
            if($video['errcode'] == 0 && $video['errmsg'] == 'ok'){
                CacheService::set("live_replay",$video,$time);
            }else{
                return JsonService::fail($video['errmsg']);
            }
        }
        if ($video['total'] == 0){
            return JsonService::fail("暂无回放");
        }else{
            self::assign('video',$video['live_replay']);
            return self::fetch();
        }

    }

}