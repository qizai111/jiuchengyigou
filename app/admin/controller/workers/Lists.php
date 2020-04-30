<?php


namespace app\admin\controller\workers;
use app\admin\controller\AuthController;
use app\admin\model\order\StoreOrder as StoreOrderModel;
use app\admin\model\user\User;
use crmeb\services\JsonService;
use think\facade\Db;
use crmeb\services\UtilService;

class Lists extends AuthController
{
    public function worker()
    {
        return $this->fetch();
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function worker_list()
    {
        $input = input('get.');
        if (isset($input['key'])) {
            switch ($input['key']) {
                case 1:
                    $where = null;
                    break;
                case 2:
                    $where[] = ['status', '>', 1];
                    break;

                case 3:
                    $where[] = ['status', '>', 4];
                    break;
                default:
                    $where = null;
                    break;
            }
        } else {
            $where = null;
        }
        $count = Db::name('yg_users')->count();
        $data = Db::name('yg_users')->alias("a")->join("yg_user_data b","a.id=b.user_id")->where($where)->page($input['page'])->order('id', 'asc')
            ->limit(15)->select()->toArray();
        foreach ($data as &$key) {

            $key['avatar_url'] = config('oss.YG_URL') . $key['avatar_url'];
        };
        return JsonService::successlayui($count, $data);
    }

    //审核页面
    public function to_examine($uid = '')
    {

        $to_examine = Db::name("yg_user_data")->alias('a')->
        join('yg_users b', 'a.user_id= b.id')->field('a.*,b.account')->where("user_id=" . "$uid")->select()->toArray();
        if (!$uid || !($to_examine)) {
            return $this->failed('工人信息不存在!');
        } else {
            $to_examine = $to_examine[0];
            $card = explode(',', $to_examine['crad_img']);
            $to_examine['card_z'] = config('oss.YG_URL') . $card[0];
            $to_examine['card_f'] = config('oss.YG_URL') . $card[1];
            $to_examine['certificate'] = config('oss.YG_URL') . $to_examine['certificate'];
        }
        $this->assign(compact('uid', 'to_examine'));
        return $this->fetch();
    }

    public function examine()
    {
        if (!$_POST || empty($_POST)) {
            return $this->failed('哎呀...亲...您访问的页面出现错误');
        } else{
            $flag = $_POST['flag'];
            $uid = $_POST["uid"];
            $res = Db::name('yg_user_data')->where('user_id', "$uid")->update(['status' => "$flag"]);
            if ($res) {
                if ($flag == 2) {
                    return JsonService::successful("工人认证审核通过！",1);
                } elseif ($flag == 3) {
                    return JsonService::successful("认证信息已驳回！",1);
                }
            }else {
                return JsonService::fail("操作失败，请稍后重试...",0);
            }
        }
    }
}
