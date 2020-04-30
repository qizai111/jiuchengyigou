<?php
/**
 * Created by CRMEB.
 * Copyright (c) 2017~2019 http://www.crmeb.com All rights reserved.
 * Author: liaofei <136327134@qq.com>
 * Date: 2019/3/27 21:44
 */

namespace app\models\user;

use think\facade\Cache;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * TODO 用户消费新增金额明细 model
 * Class UserBill
 * @package app\models\user
 */
class UserActivityBill extends BaseModel
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
    protected $name = 'user_activity_bill';

    use ModelTrait;

    public static function income($title, $uid, $unit, $category, $number, $balance = 0, $mark = '', $status = 1)
    {
        $add_time = time();
        $goods = $category =='egg'? "鸡蛋":"";
        $type = "sign";
        $mark = date("Y-m-d H:i:s",$add_time).":用户签到领取:".$goods.$number.$unit;
        return self::create(compact('title', 'uid', 'unit', 'category', 'number', 'balance', 'mark', 'status',  'add_time','type'));
    }
    /**
     * 补签
     * */
    public static function incomeRepair($title, $uid, $unit, $category, $number, $balance, $status = 1,$repair_time)
    {
        $add_time = $repair_time;
        $goods = $category =='egg'? "鸡蛋":"";
        $type = "sign";
        $mark = date("Y-m-d H:i:s",$add_time).":用户补签领取:".$goods.$number.$unit;
        return self::create(compact('title', 'uid', 'unit', 'category', 'number', 'balance', 'mark', 'status',  'add_time','type'));
    }

    public static function expend($title, $uid, $category, $type, $number, $link_id = 0, $balance = 0, $mark = '', $status = 1)
    {
        $pm = 0;
        $add_time = time();
        return self::create(compact('title', 'uid', 'link_id', 'category', 'type', 'number', 'balance', 'mark', 'status', 'pm', 'add_time'));
    }

    /*
     * 获取用户账单明细
     * @param int $uid 用户uid
     * @param int $page 页码
     * @param int $limit 展示多少条
     * @param int $type 展示类型
     * @return array
     * */
    public static function getUserBillList($uid, $page, $limit, $type)
    {
        if (!$limit) return [];
        $model = self::where('uid', $uid)->where('category', 'now_money')->order('add_time desc')->where('number', '<>', 0)
            ->field('FROM_UNIXTIME(add_time,"%Y-%m") as time,group_concat(id SEPARATOR ",") ids')->group('time');
        switch ((int)$type) {
            case 0:
                $model = $model->where('type', 'in', 'recharge,brokerage,pay_product,system_add,pay_product_refund,system_sub');
                break;
            case 1:
                $model = $model->where('type', 'pay_product');
                break;
            case 2:
                $model = $model->where('type', 'in', 'recharge,system_add');
                break;
            case 3:
                $model = $model->where('type', 'brokerage');
                break;
            case 4:
                $model = $model->where('type', 'extract');
                break;
        }
        if ($page) $model = $model->page((int)$page, (int)$limit);
        $list = ($list = $model->select()) ? $list->toArray() : [];
        $data = [];
        foreach ($list as $item) {
            $value['time'] = $item['time'];
            $value['list'] = self::where('id', 'in', $item['ids'])->field('FROM_UNIXTIME(add_time,"%Y-%m-%d %H:%i") as add_time,title,number,pm')->order('add_time DESC')->select();
            array_push($data, $value);
        }
        return $data;
    }

    /**
     * TODO 获取用户记录 按月查找
     * @param $uid $uid  用户编号
     * @param int $page $page 分页起始值
     * @param int $limit $limit 查询条数
     * @param string $category $category 记录类型
     * @param string $type $type 记录分类
     * @return mixed
     */
    public static function getRecordList($uid, $page = 1, $limit = 8, $category = 'now_money', $type = '')
    {
        $model = new self;
        $model = $model->field("FROM_UNIXTIME(add_time, '%Y-%m') as time");
        $model = $model->where('uid', $uid);
        if (strlen(trim($type))) $model = $model->whereIn('type', $type);
        $model = $model->where('category', $category);
        $model = $model->group("FROM_UNIXTIME(add_time, '%Y-%m')");
        $model = $model->order('time desc');
        $model = $model->page($page, $limit);
        return $model->select();
    }

    /**
     * TODO  按月份查找用户记录
     * @param $uid $uid  用户编号
     * @param int $addTime $addTime 月份
     * @param string $category $category 记录类型
     * @param string $type $type 记录分类
     * @return mixed
     */
    public static function getRecordListDraw($uid, $addTime = 0, $category = 'now_money', $type = '')
    {
        if (!$uid) [];
        $model = new self;
        $model = $model->field("title,FROM_UNIXTIME(add_time, '%Y-%m-%d %H:%i') as time,number,pm");
        $model = $model->where('uid', $uid);
        $model = $model->where("FROM_UNIXTIME(add_time, '%Y-%m')= '{$addTime}'");
        $model = $model->where('category', $category);
        if (strlen(trim($type))) $model = $model->where('type', 'in', $type);
        $model = $model->order('add_time desc');
        $list = $model->select();
        if ($list) return $list->toArray();
        else [];
    }


    /**
     * TODO 获取用户记录总和
     * @param $uid
     * @param string $category
     * @param string $type
     * @return mixed
     */
    public static function getRecordCount($uid, $category = 'now_money', $type = '', $time = '', $pm = false)
    {
        $model = new self;
        $model = $model->where('uid', $uid);
        $model = $model->where('category', $category);
        $model = $model->where('status', 1);
        if (strlen(trim($type))) $model = $model->where('type', 'in', $type);
        if ($time) $model = $model->whereTime('add_time', $time);
        if ($pm) {
            $model = $model->where('pm', 0);
        }
        return $model->sum('number');
    }

    /*
     * 记录分享次数
     * @param int $uid 用户uid
     * @param int $cd 冷却时间
     * @return Boolean
     * */
    public static function setUserShare($uid, $cd = 300)
    {
        $user = User::where('uid', $uid)->find();
        if (!$user) return self::setErrorInfo('用户不存在！');
        $cachename = 'Share_' . $uid;
        if (Cache::has($cachename)) return false;
        self::income('用户分享记录', $uid, 'share', 'share', 1, 0, 0, date('Y-m-d H:i:s', time()) . ':用户分享');
        Cache::set($cachename, 1, $cd);
        event('UserLevelAfter', [$user]);
        return true;
    }

}
