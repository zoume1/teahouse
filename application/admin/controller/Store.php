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
     * Notes:检测是否进行了支付密码设置，没有设置则前往设置
     **************************************
     */
    public function store_isset_password(Request $request){
        if($request->isPost()){
            $store_id =Session::get("store_id");
            $is_set =Db::name("store")
                ->where("id",$store_id)
                ->value("store_pay_pass");
            if(empty($is_set)){
               return ajax_error("没有设置支付密码，请前往设置");
            }else{
                return ajax_success("已检测到支付密码");
            }

        }
    }


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
     * Notes:店铺余额返回
     **************************************
     */
    public function store_wallet_return(Request $request){
        if($request->isPost()){
            $store_id =Session::get("store_id");
            $id  = Db::name("store")
                ->where("id",$store_id)
                ->value("id");
            if(!$id){
                return ajax_error("店铺信息有误");
            }
            $wallet  = Db::name("store")
                ->where("id",$store_id)
                ->value("store_wallet");
            return ajax_success("余额返回成功",$wallet);
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺的钱包充值页面
     **************************************
     */
    public function store_wallet_add(){
        return view("store_wallet_add");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺的提现页面
     **************************************
     */
    public function store_wallet_reduce(){
        return view("store_wallet_reduce");
    }





}