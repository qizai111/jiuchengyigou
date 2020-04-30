<?php

namespace  app\models\employer;

use crmeb\basic\BaseModel;

class  YgOrder extends BaseModel
{
    function getAddTimeAttr($value)
    {
        return date('m-d H:i:s',$value);
    }
    function uid(){
        return $this->hasOne('Users','id','uid')->bind(['avatar_url']);
    }
    function getImagesAttr($value)
    {
        if (strlen($value) >= 1) {
            $images = explode(',', $value);

            foreach ($images as $k1 => $v1) {
                $data[] = $v1;//config('apiconfig.OSS_URL').
            }
            return $data;
        }
    }
}