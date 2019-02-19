<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/14 0014
 * Time: 16:32
 */
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Cache;

class  Owner extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:身份证数据返回
     **************************************
     * @param Request $request
     */
    public function id_card_return(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $data =Db::name("member")
                ->where("member_id",$member_id)
                ->field("ID_card,member_real_name")
                ->find();
            if(!empty($data["ID_card"]) || (!empty($data["ID_card"])) ){
                return ajax_success("身份证信息返回成功",$data);
            }else{
                return ajax_error("没有进行身份证绑定",["status"=>0]);
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:身份证绑定
     **************************************
     */
    public function id_card_add(Request $request){
        if($request->isPost()){
            $user_name =$request->only(["name"])["name"];
            $id_card =$request->only(["id_card"])["id_card"];
            $data =[
                "ID_card"=>$id_card,
                "member_real_name"=>$user_name
            ];
            $member_id =$request->only(["member_id"])["member_id"];
            $bool =Db::name("member")->where("member_id",$member_id)->update($data);
            if($bool){
                return ajax_success("绑定成功",["status"=>1]);
            }else{
                return ajax_success("绑定失败",["status"=>0]);
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:身份证修改
     **************************************
     * @param Request $request
     */
    public function id_card_edit(Request $request){
        if($request->isPost()){
            $user_name =$request->only(["name"])["name"];
            $id_card =$request->only(["id_card"])["id_card"];
            $data =[
                "ID_card"=>$id_card,
                "member_real_name"=>$user_name
            ];
            $member_id =$request->only(["member_id"])["member_id"];
            $bool =Db::name("member")->where("member_id",$member_id)->insert($data);
            if($bool){
                return ajax_success("修改成功",["status"=>1]);
            }else{
                return ajax_success("没有进行修改",["status"=>0]);
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:银行卡数据返回
     **************************************
     * @param Request $request
     */
    public function bank_bingding(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $data =Db::name("user_bank")->where("user_id",$member_id)->select();
            if(!empty($data)){
                return ajax_success("银行卡信息返回成功",$data);
            }else{
                return ajax_error("未绑定银行卡");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:银行卡添加
     **************************************
     * @param Request $request
     */
    public function bank_bingding_add(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $bank_name =$request->only(["bank_name"])["bank_name"];
            $account_name =$request->only(["account_name"])["account_name"];
            $bank_card =$request->only(["bank_card"])["bank_card"];
            $status =$request->only(["status"])["status"];
            $member_phone_num =Db::name("member")->where("member_id",$member_id)->value("member_phone_num");
            $code =$request->only(["code"])["code"];
            $mobileCode =Cache::get('mobileCode');
            $mobile =Cache::get('mobile');
            if($member_phone_num != $mobile){
                return ajax_error("手机号不匹配");
            }
            if($mobileCode != $code) {
                return ajax_error("验证码不正确");
            }
            $data =[
                "bank_name"=>$bank_name,
                "bank_card"=>$bank_card,
                "account_name"=>$account_name,
                "status"=>$status,
                "user_id"=>$member_id
            ];
            $res =Db::name("user_bank")->insert($data);
            if($res){
                return ajax_success("添加成功",["status"=>1]);
            }else{

                return ajax_error("请重试",["status"=>0]);
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:银行卡修改返回信息
     **************************************
     * @param Request $request
     */
    public function bank_bingding_update_return(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $id =$request->only(["id"])["id"];
            $data =Db::name("user_bank")
                ->where("user_id",$member_id)
                ->where("id",$id)
                ->find();
            if(!empty($data)){
                return ajax_success("银行卡信息返回成功",$data);
            }else{
                return ajax_error("未绑定银行卡");
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:银行卡编辑
     **************************************
     * @param Request $request
     */
    public function bank_bingding_update(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $id =$request->only(["id"])["id"];
            $bank_name =$request->only(["bank_name"])["bank_name"];
            $account_name =$request->only(["account_name"])["account_name"];
            $bank_card =$request->only(["bank_card"])["bank_card"];
            $status =$request->only(["status"])["status"];
            $member_phone_num =Db::name("member")
                ->where("member_id",$member_id)
                ->value("member_phone_num");
            $member_isset_card =Db::name("user_bank")->where("bank_card",$bank_card)->find();
            if(!empty($member_isset_card)){
                return ajax_error("此银行卡已被绑定");
            }
            $code =$request->only(["code"])["code"];
            $mobileCode =Cache::get('mobileCode');
            $mobile =Cache::get('mobile');
            if($member_phone_num != $mobile){
                return ajax_error("手机号不匹配");
            }
            if($mobileCode != $code) {
                return ajax_error("验证码不正确");
            }
            $data =[
                "bank_name"=>$bank_name,
                "bank_card "=>$bank_card,
                "account_name"=>$account_name,
                "status"=>$status,
                "user_id"=>$member_id
            ];
            $res =Db::name("user_bank")->where("id",$id)->update($data);
            if($res){
                if($status==1){
                    Db::name('user_bank')
                        ->where('user_id',$member_id)
                        ->where('id','NEQ',$id)
                        ->update(['status'=>-1]);
                }
                return ajax_success("添加成功",["status"=>1]);
            }else{
                return ajax_error("请重试",["status"=>0]);
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:银行卡默认设置
     **************************************
     * @param Request $request
     */
    public function bank_binding_status(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $status =$request->only(["status"])["status"];
            $id =$request->only(["id"])["id"];
            $bool =Db::name("user_bank")
                ->where("user_id",$member_id)
                ->where("id",$id)
                ->update(["status"=>$status]);
            if(!empty($bool)){
                Db::name("user_bank")
                    ->where("user_id",$member_id)
                    ->where("id","NEQ",$id)
                    ->update(["status"=>-1]);
                return ajax_success("修改成功",["status"=>1]);
            }else{
                return ajax_error("修改失败",["status"=>0]);
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:银行卡删除
     **************************************
     */
    public function bank_binding_del(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $id =$request->only(["id"])["id"];
            $bool =Db::name("user_bank")->where("member_id",$member_id)->where("id",$id)->delete();
            if($bool){
                return ajax_success("删除成功",["status"=>1]);
            }else{
               return ajax_error("删除失败",["status"=>0]);
            }
        }
    }



}