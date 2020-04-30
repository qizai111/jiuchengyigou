<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/25
 */

namespace app\models\user;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 用户收货地址
 * Class UserAddress
 * @package app\models\user
 */
class UserAddress extends BaseModel
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
    protected $name = 'user_address';

    use ModelTrait;

    protected $insert = ['add_time'];

    protected $hidden = ['add_time', 'is_del', 'uid'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    /**
     * 设置默认收货地址
     * @param $id 地址id
     * @param $uid 用户uid
     * @return bool
     */
    public static function setDefaultAddress($id,$uid)
    {
        self::beginTrans();
        $res1 = self::where('uid',$uid)->update(['is_default'=>0]);
        $res2 = self::where('id',$id)->where('uid',$uid)->update(['is_default'=>1]);
        $res =$res1 !== false && $res2 !== false;
        self::checkTrans($res);
        return $res;
    }

    /**
     * 设置用户地址查询初始条件
     * @param null $model
     * @param string $prefix
     * @return \think\Model
     */
    public static function userValidAddressWhere($model=null,$prefix = '')
    {
        if($prefix) $prefix .='.';
        $model = self::getSelfModel($model);
        return $model->where("{$prefix}is_del",0);
    }

    /**
     * 获取用户收货地址并分页
     * @param $uid 用户uid
     * @param int $page 页码
     * @param int $limit 展示条数
     * @param string $field 展示字段
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserValidAddressList($uid,$page=1,$limit=8,$field = '*')
    {
        if($page) return self::userValidAddressWhere()->where('uid',$uid)->order('add_time DESC')->field($field)->page((int)$page,(int)$limit)->select()->toArray()?:[];
        else return self::userValidAddressWhere()->where('uid',$uid)->order('add_time DESC')->field($field)->select()->toArray()?:[];
    }

    /**
     * 获取用户默认收货地址
     * @param $uid 用户uid
     * @param string $field 展示字段
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserDefaultAddress($uid,$field = '*')
    {
        return self::userValidAddressWhere()->where('uid',$uid)->where('is_default',1)->field($field)->find();
    }
    /**
     * 获取用户收货地址
     * @param $uid 用户uid
     * @param string $field 展示字段
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserAddress($where,$field = 'province,city,district,detail')
    {
        $addressInfo = self::userValidAddressWhere()->where($where)->field($field)->find();
        $address = $addressInfo['province'].$addressInfo['city'].$addressInfo['district'].$addressInfo['detail'];
        return $address;
    }
    /**
     *获取拼团用户的收货位置坐标
     * @param  $address
     * @return
     */

    public static function getLocations($where){
        $locations = self::userValidAddressWhere()->where($where)->field("latitude,longitude")->find()->toArray();
        return $locations;
    }
    /**
     *获取拼团用户的收货位置坐标
     * @param  $address
     * @return
     */
    public static function getLocation($address){
        $url="http://apis.map.qq.com/ws/geocoder/v1/?address=".$address."&key=OMIBZ-D5262-Q7NU5-COLQY-YBLCQ-54BTX";
        //初始化url
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        $result = json_decode($output);
        $location = $result->result->location;
        //返回坐标信息
        return $result;
    }
    /**
     *  计算两组经纬度坐标 之间的距离
     *   params ：lat1 纬度1； lng1 经度1； lat2 纬度2； lng2 经度2； len_type （1:m or 2:km);
     *   return m or km
     */
    public static function getDistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2)
    {
        $radLat1 = $lat1 * PI ()/ 180.0;   //PI()圆周率
        $radLat2 = $lat2 * PI() / 180.0;
        $a = $radLat1 - $radLat2;
        $b = ($lng1 * PI() / 180.0) - ($lng2 * PI() / 180.0);
        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $s = $s * 6378.137;
        $s = round($s * 1000);
        if ($len_type --> 1)
        {
            $s /= 1000;
        }
        return round($s, $decimal);
    }

}