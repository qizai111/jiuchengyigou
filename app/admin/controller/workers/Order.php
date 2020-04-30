<?php

namespace app\admin\controller\workers;
use app\admin\controller\AuthController;
use crmeb\services\JsonService;
use think\facade\Db;
use crmeb\services\UtilService;

class Order extends AuthController
{
    public function index(){
        return $this->fetch();

    }

    public function lists(){
        $input=input('get.');
        if (isset($input['key'])){
            switch ($input['key']){
                case 1:
                    $where=null;
                    break;
                case 2:
                    $where[]=['status','>',1];
                    break;

                case 3:
                    $where[]=['status','>',4];
                    break;
                default:
                    $where=null;
                    break;
            }
        }else{
            $where=null;
        }
        $count=Db::name('yg_order')->count();

        $data=Db::name('yg_order')->where($where)->page($input['page'])->order('id','desc')
            ->withAttr('add_time',function ($value){
                return date('Y-m-d H:i:s',$value);
            })
            ->limit(15)->select();
        return JsonService::successlayui($count,$data);

    }

    /*
 * 删除订单
 * */
    public function del_order()
    {
        $ids = UtilService::postMore(['ids'])['ids'];
        if(!count($ids)) return JsonService::fail('请选择需要删除的订单');
        $res=Db::name('yg_order')->where('id','in',$ids)->update(['status'=>-3]);
        if($res)
            return JsonService::successful('删除成功');
        else
            return JsonService::fail('删除失败');
    }
}
