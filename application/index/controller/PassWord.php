<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/15 0015
 * Time: 14:21
 */
namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Request;

class PassWord extends  Controller{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:支付密码添加编辑
     **************************************
     */
    public function pay_password_add(Request $request){
        if($request->isPost()){
            $password =$request->only(["password"])["password"];
            $password_repeat =$request->only(["password_repeat"])["password_repeat"];
            $member_id =$request->only(["member_id"])["member_id"];
            if($password ==$password_repeat){
                $passwords =password_hash($password,PASSWORD_DEFAULT);
                $bool =Db::name("member")
                    ->where("member_id",$member_id)
                    ->update(["pay_password"=>$passwords]);
                if($bool){
                    return ajax_success("成功",["status"=>1]);
                }else{
                    return ajax_error("失败",["status"=>0]);
                }
            }else {
                return ajax_error("两次密码不一致",["status"=>0]);
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:支付密码是否设置
     **************************************
     * @param Request $request
     */
    public function pay_password_return(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $is_set =Db::name("member")->where("member_id",$member_id)->value("pay_password");
            if(!empty($is_set)){
                return ajax_success("已经设置支付密码",["status"=>1]);
            }else{
                return ajax_error("未设置支付密码",["status"=>0]);
            }
        }
    }
}