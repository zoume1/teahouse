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
                return ajax_error("2次输入密码不一致，请重新设置");
            }
            if($code !=$mobileCodes){
                return ajax_error("验证码不正确");
            }
            $boll =Db::name("store")->where("id",$store_id)->update(["store_pay_pass"=>md5($password)]);
            if($boll){
                return ajax_success("支付密码修改成功");
            }else{
                return ajax_error("您设置的密码和原密码一致，请重新设置");
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

    /**
     **************GY*******************
     * @param Request $request
     * Notes:店铺资金管理银行卡列表
     **************************************
     * @return \think\response\View
     */
    public function store_add_bankcard(){
        //将已开户银行开发送过去
        $store_id = Session::get("store_id");
        $bank = Db::name("store_bank_icard")
                ->where("store_id",'EQ',$store_id)
                ->where("status",'EQ',1)
                ->select();
        return view("store_add_bankcard",["bank"=>$bank]);
    }


    /**
     **************GY*******************
     * @param Request $request
     * Notes:银行开添加入库
     **************************************
     */
    public function store_icard_save(Request $request){
        if($request->isPost()){
            $store_id = Session::get("store_id");
            $card = $request->param();

            $bool  = Db::name("store_bank_icard")
                ->insert($card);

            return ajax_success("银行卡添加成功",$wallet);
        }
    }







}