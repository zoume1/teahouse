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

class  Owner extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:身份证订单数据返回
     **************************************
     * @param Request $request
     */
    public function id_card_return(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $data =Db::name("member")->where("id",$member_id)->field("ID_card,member_real_name")->find();
            if(!empty($data)){
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
                "user_name"=>$user_name
            ];
            $member_id =$request->only(["member_id"])["member_id"];
            $bool =Db::name("member")->where("id",$member_id)->insert($data);
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
                "user_name"=>$user_name
            ];
            $member_id =$request->only(["member_id"])["member_id"];
            $bool =Db::name("member")->where("id",$member_id)->insert($data);
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
     * Notes:银行卡管理
     **************************************
     * @param Request $request
     */
    public function bank_bingding(Request $request){

    }



}