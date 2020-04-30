<?php
namespace  app\models\employer;

use crmeb\basic\BaseModel;
use think\facade\Db;

class  YgCategory extends BaseModel{
    public static function getAll(){
        $arr=self::withAttr('img',function ($value){
            return $value;
        })->select()->toArray();
        foreach ($arr as $k=>$v){
            if ($v['status']==1){
                $arr[$k]['count']=Db::name('yg_user_data')->where('career_type',$v['id'])->count();
            }else{
                $arr[$k]['count']=0;
            }
        }
        return $arr;
    }
}