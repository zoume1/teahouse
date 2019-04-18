<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/18 0018
 * Time: 15:13
 */
namespace  app\admin\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Db;

class Store extends  Controller{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺的支付密码修改
     **************************************
     */
    public function  store_update_password(){
        if($this->request->isPost()){
            $store_id =Session::get("store_id");
            $password =$this->request->only(["password"])["password"];
            $pass =$this->request->only(["pass"])["pass"];//第二次密码
            $code = $this->request->only(["code"])["code"];
            $mobileCodes =Session::get("mobileCodes");//验证码
            if($password !=$pass){
                return ajax_error("两次密码不一致");
            }
            if($code !=$mobileCodes){
                return ajax_error("验证码不正确");
            }
            $boll =Db::name("store")->where("id",$store_id)->update(["store_pay_pass"=>md5($password)]);
            if($boll){
                return ajax_success("支付密码修改成功");
            }else{
                return ajax_error("支付密码修改失败");
            }
        }

    }




    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺的钱包充值
     **************************************
     */
    public function store_wallet_add(){

    }




}