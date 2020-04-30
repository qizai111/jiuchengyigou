<?php
namespace app\api\controller\employer;
use app\models\employer\YgCategory as CategoryModel;
use think\facade\Request;

class Category{
    public function index(){
        return app('json')->successful(CategoryModel::getAll());
    }
    public function add(){
        if (Request::isPost()){
            $data=input('post.');
            $data['mer_id']=1;
            $data['pid']=$data['mediumint'];
            unset($data['mediumint']);
            $data['add_time']=time();
            $res=CategoryModel::create($data);
            if ($res){
                return app('json')->successful('提交成功');
            }else{
                return app('json')->fail('添加失败');
            }        }
        $select=CategoryModel::getAll();
        return app('json')->successful($select);

    }
}