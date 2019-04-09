<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/9 0009
 * Time: 16:11
 */
namespace  app\admin\controller;

use think\Controller;
use think\Session;

class  Base extends  Controller{
    public  $account;
    public function _initialize()
    {
        //判断是否是店铺进来的
        if(!$this->isset_session()){
            $this->success("超级管理员没有该功能","admin/Home/index");
        }
        return $this->account;
    }
    //是否存在store_id
    public function isset_session(){
        $store_id =Session::get('store_id');
        if(!$store_id){
             return false;
        }
        return $store_id;
    }

}