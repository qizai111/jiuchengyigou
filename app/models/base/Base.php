<?php

namespace app\api\controller\base;

use app\admin\model\system\SystemAttachment;
use app\admin\model\system\SystemAttachmentCategory;
use app\models\article\Article;
use app\Request;
use crmeb\services\UtilService;

/**
 * 基地展示类
 * Class StoreOrderController
 * @package app\api\controller\admin\order
 */
class BaseController
{
    /**
     *  基地展示图片
     * @return mixed
     */
    public function baseShow($cid = 1)
    {
        $list = Article::cidByArticleList($cid,1,10,"id,title,image_input,visit,from_unixtime(add_time,'%Y-%m-%d %H:%i') as add_time,synopsis,url") ?? [];
        if(is_object($list)) $list = $list->toArray();
        $list[0]['image_input'] = $list[0]['image_input'][0];
        var_dump($list);die;
        return app('json')->successful($list);

    }
    public function artice(Request $Request)
    {
        $article = Article::getArticleOne($id);
        if ($article) return app('json')->successful($article);

    }
}