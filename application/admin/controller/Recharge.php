<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/25
 * Time: 11:35
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;

class Recharge extends Controller{
    /**
     **************李火生*******************
     * @return \think\response\View
     * 充值管理首页
     **************************************
     */
    public function index(){
        $recharge_data =Db::name('recharge_full_setting')->select();
        dump($recharge_data);
        return view('index',['recharge_data'=>$recharge_data]);
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 充值编辑
     **************************************
     */
    public function edit(){
        return view('edit');
    }


}