<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/20
 * Time: 10:17
 */
namespace app\rec\controller;
use app\rec\model\User;
use think\Controller;
use think\Request;
class Share extends Controller
{
    /**
     * @ 生成二维码
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function qr_code()
    {
        $request = Request::instance();
        $param = $request->param();

        if(!$param['my_invitation'])returnJson(0,'邀请码不能为空');

        $data = User::where('my_invitation',$param['my_invitation'])->find();
        //判断
        returnArray($data);

        $list['code'] = $this->code($param['my_invitation']);
        $list['yqm'] = $param['my_invitation'];
        return returnJson(1,'获取成功',$list);

    }


    /**
     * @param $a
     * @return string
     */
    function code($a){

        $list = 'http://qr.topscan.com/api.php?text=' . $a;

        return $list;
//        echo "<img src='" . $list . "'>";
    }

}