<?php
namespace app\models\user;

use app\models\user\UserActivityBill;
use crmeb\basic\BaseModel;
use crmeb\services\SystemConfigService;
use crmeb\traits\ModelTrait;
use think\facade\Db;

/**
 * TODO  用户签到模型 Model
 * Class UserSign
 * @package app\models\user
 */
class UserActivitySign extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'user_activity_sign';

    use ModelTrait;

    /**
     * 设置签到数据
     * @param $uid 用户uid
     * @param string $title 签到说明
     * @param int $number 签到获得积分
     * @param int $balance 签到前剩余积分
     * @return bool
     */
    public static function setSignData($uid,$title='',$number=0,$balance=0)
    {
        $add_time=time();
        $balance = (int)$number + $balance;
        return self::create(compact('uid','title','number','balance','add_time')) && UserActivityBill::income($title,$uid,'个','egg',$number,$balance,$title);
    }
    /**
     * 设置补签数据
     * @param $uid 用户uid
     * @param string $title 签到说明
     * @param int $number 签到获得积分
     * @param int $balance 签到前剩余积分
     * @return bool
     */
    public static function setRepairSignData($uid,$title='',$number=0,$balance=0,$repair_time)
    {
        $add_time = $repair_time;
        $balance = (int)$number + $balance;
        return self::create(compact('uid','title','number','balance','add_time')) && UserActivityBill::incomeRepair($title,$uid,'个','egg',$number,$balance,1,$repair_time);
    }
    /**
     * 分页获取用户签到数据
     * @param $uid 用户uid
     * @param $page 页码
     * @param $limit 展示条数
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSignList($uid,$page,$limit)
    {
        if(!$limit) return [];
        $billModel = UserActivityBill::where('a.category','egg')->where('a.status',1)
            ->where('a.uid',$uid)->alias('a')
            ->join("user u",'u.uid=a.uid')
            ->order('a.add_time desc')->field('FROM_UNIXTIME(a.add_time,"%Y-%m-%d") as add_time,a.title,a.number');
        if($page) $billModel = $billModel->page((int)$page,(int)$limit);
        return $billModel->select();
    }

    public static function getSignLists($uid){
        $list = self::where('uid',$uid)->select()->toArray();
        return $list;
    }
    /**
     * 获取用户累计签到次数
     * @param $uid
     * @return int
     */
    public static function getSignSumDay($uid)
    {
        return self::where('uid', $uid)->count();
    }

    /**
     * 获取用户是否签到
     * @param $uid
     * @return bool
     */
    public static function getIsSign($uid,string $type = 'today')
    {
        return self::where('uid', $uid)->whereTime('add_time',$type)->count() ? true : false;
    }

    /**
     * 获取签到配置
     * @param string $key
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSignSystemList($key='sign_egg_num')
    {
        return \crmeb\services\GroupDataService::getData($key) ? : [];
    }

    /**
     * 用户签到
     * @param $uid
     * @return bool|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function sign($uid)
    {
        $sign_list = self::getSignSystemList();
        if(!count($sign_list)) return self::setErrorInfo('请先配置签到天数');
        $user = User::where('uid',$uid)->find();
        $balance = (float)UserActivityBill::where("uid",$uid)->order("id desc")->limit(1,1)->where("status",1)->value("balance");
        $egg_num = 0;
        //获取今天的日期
        $day = date("d",time());
        foreach ($sign_list as $key => $item){
            if($key == $day){
                $egg_num = $item['sign_num'];
                break;
            }
        }

        $res1 = self::setSignData($uid,'签到领鸡蛋',(int)$egg_num,$balance);
        if($res1){
            BaseModel::checkTrans($res1);
        }
       // event('UserLevelAfter',[$user]);
        if($res1)
            return $data = array("num"=>"$egg_num","unit"=>'个',"day"=>$day);
        else
            return false;
    }
    /**
     * 用户补签
     * @param $uid
     * @return bool|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function repairSign($uid,$repair_day)
    {
        $sign_list = self::getSignSystemList();
        if(!count($sign_list)) return self::setErrorInfo('请先配置签到天数');
        $user = User::where('uid',$uid)->find();
        $balance = (float)UserActivityBill::where("uid",$uid)->order("id desc")->limit(1,1)->where("status",1)->value("balance");
        $egg_num = 0;
        $month = date("Y-m",time());
        $repair_time = strtotime($month ."-".$repair_day."-4");
        foreach ($sign_list as $key => $item){
            if($key == $repair_day){
                $egg_num = $item['sign_num'];
                break;
            }
        }
        BaseModel::beginTrans();
        $res1 = self::setRepairSignData($uid,'补签领鸡蛋',(int)$egg_num,$balance,$repair_time);
        $res2 = UserBill::expend('用户补签',$uid,"integral","repair_sign",50,0,$user['integral'],"用户使用50积分补签",1);
        $res3 = User::bcDec($uid,'integral',50,'uid');
        $res = $res1 && $res2 && $res3 !== false;
        BaseModel::checkTrans($res);
        if($res)
            return $data = array("num"=>"$egg_num","unit"=>'个');
        else
            return false;
    }
    /**
     * 用户领取记录
     * @param $uid
     * @param $address_id
     * @return bool|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function setUserReceive($uid,$address_id){
        $data = array(
            "uid" => $uid,
            "address_id" => $address_id,
            "sign_sum_day" => self::getSignSumDay($uid),
            "gid" => 1,
            "goods_number" => self::getSignSumDay($uid),
            "receive_time" => time(),
            "status" => 1
        );
        $date1 = strtotime(date("Y-m")."-1");
        $date2 = strtotime(date("Y-m-t 23:59:00"));
		$receive = Db::name("user_receive_bill")->where("uid",$uid)->where("status",1)->whereBetween("receive_time","$date1,$date2")->find();
		if($receive != null){
			 $res = Db::name("user_receive_bill")->where("id",$receive['id'])->save($data);
		}
		else{
			 $res = Db::name("user_receive_bill")->where("uid",$uid)->insert($data);
		}
       
        return $res;
    }
    /**
     * 获取签到列表按月加载
     * @param int $uid 用户uid
     * @param int $page 页码
     * @param int $limit 显示多少条
     * @return array
     * */
    public static function getSignMonthList($uid,$page=1,$limit=8)
    {
        if(!$limit) return [];
        if($page){
            $list = UserActivityBill::where('uid', $uid)
                ->where('category', 'egg')
                ->field('FROM_UNIXTIME(add_time,"%Y-%m") as time,group_concat(id SEPARATOR ",") ids')
                ->group('time')
                ->order('time DESC')
                ->page($page, $limit)
                ->select();
        }else{
            $list = UserActivityBill::where('uid', $uid)
                ->where('category', 'egg')
                ->field('FROM_UNIXTIME(add_time,"%Y-%m") as time,group_concat(id SEPARATOR ",") ids')
                ->group('time')
                ->order('time DESC')
                ->select();
        }
        $data=[];
        foreach ($list as $key=>&$item){
            $value['month'] = $item['time'];
            $value['list'] = UserActivityBill::where('id','in',$item['ids'])->field('FROM_UNIXTIME(add_time,"%Y-%m-%d") as add_time,title,number')->order('add_time DESC')->select();
            array_push($data,$value);
        }
        return $data;
    }

    public static function checkUserSigned($uid)
    {
        return UserBill::be(['uid'=>$uid,'add_time'=>['>',strtotime('today')],'category'=>'integral','type'=>'sign']);
    }

    public static function userSignedCount($uid)
    {
        return self::userSignBillWhere($uid)->count();
    }

    /**
     * @param $uid
     * @return UserBill
     */
    public static function userSignBillWhere($uid)
    {
        return UserActivityBill::where('uid', $uid)->where('category', 'egg')->where('type', 'sign');
    }

    public static function signEbApi($userInfo)
    {
        $uid = $userInfo['uid'];
        $min = SystemConfigService::get('sx_sign_min_int')?:0;
        $max = SystemConfigService::get('sx_sign_max_int')?:5;
        $integral = rand($min,$max);
        BaseModel::beginTrans();
        $res1 = UserBill::income('用户签到',$uid,'integral','sign',$integral,0,$userInfo['integral'],'签到获得'.floatval($integral).'积分');
        $res2 = User::bcInc($uid,'integral',$integral,'uid');
        $res = $res1 && $res2;
        BaseModel::checkTrans($res);
        if($res)
            return $integral;
        else
            return false;
    }
}