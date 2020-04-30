<?php
namespace app\admin\controller\activity;

use app\admin\controller\AuthController;
use think\facade\Db;
use app\admin\model\ump\StoreSeckill as StoreSeckillModel;
use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;
/**
 * 签到活动 商品配置类
 * Class StoreSeckill
 * @package app\admin\controller\store
 */
class ActivityGoods extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {

        $goodsId = Db::name("activity_goods")->select()->toArray();
        $countGoods = Db::name("activity_goods")->count();
        $this->assign('countGoods',$countGoods);
        $this->assign('goodsId',$goodsId);
        return $this->fetch("activity/activity_goods/index");
    }
    /**
     * 异步获取商品数据
     */
    public function get_goods_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['status',''],
            ['store_name','']
        ]);
        $goodsId = Db::name("activity_goods")->select()->toArray();
        $countGoods = Db::name("activity_goods")->count();

        return Json::successlayui(['count'=>$countGoods,'data'=>$goodsId]);
    }
}