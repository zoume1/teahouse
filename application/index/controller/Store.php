<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/4 0004
 * Time: 16:07
 */
namespace  app\index\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Db;

class  Store extends  Controller{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:创建店铺
     **************************************
     */
    public function  store_add(Request $request){
        if($request->isPost()){
            $user_id =Session::get("user");
            $is_business =$request->only(["is_business"])["is_business"];
            $id_card =$request->only(["id_card"])["id_card"];
            $contact_name =$request->only(["contact_name"])["contact_name"];
            $address_data =$request->only(["address_data"])["address_data"];
            $address_real_data =$request->only(["address_real_data"])["address_real_data"];
            $store_introduction =$request->only(["store_introduction"])["store_introduction"];
            $business_name =$request->only(["business_name"])["business_name"];
            $licence_no =$request->only(["licence_no"])["licence_no"];
            if(empty($id_card) || empty($contact_name) || empty($address_data) ||empty($address_real_data) ||empty($store_introduction) ){
                    return ajax_error("请注意填写完所有资料");
            }
            if($is_business ==2){
                if(empty($business_name) || empty($licence_no)){
                    return ajax_error("请填写企业信息");
                }
            }
            $card_positive =$request->only(["card_positive"])["card_positive"]; //身份证正面
            if(empty($card_positive)){
                return ajax_error("请上传身份证正面图");
            }
            $card_side_file = $request->only(["card_side"])["card_side"];//身份证反面
            if(empty($card_side_file)){
                return ajax_error("请上传身份证反面图");
            }
            $card_positive_images = base64_upload_flie($card_positive);//身份证正面
            $card_side_file =base64_upload_flie($card_side_file) ; //身份证反面
            $phone_number = db("pc_user")->where("id",$user_id)->value("phone_number");//获取手机号
            $data = [
                "is_business"=>$is_business,
                "id_card"=>$id_card,
                "contact_name"=>$contact_name,
                "address_data"=>$address_data,
                "address_real_data"=>$address_real_data,
                "card_positive"=>$card_positive_images,
                "card_side"=>$card_side_file,
                "store_introduction"=>$store_introduction,
                "business_name"=>$business_name,
                "licence_no"=>$licence_no,
                "user_id"=>$user_id,
                "phone_number"=>$phone_number,
                //店铺状态(1审核通过,-1审核不通过,2审核中）
                "status"=>2
            ];
            $bool =Db::name("store")->insert($data);
            if($bool){
                return ajax_success("您的资料已提交,请耐心等待审核");
            }else{
                return ajax_error("网络错误，请重新提交");
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺信息
     **************************************
     */
    public function  store_return(Request $request){
        if($request->isPost()){
            $id =$request->only(["id"])["id"];
            $data =Db::name("store")->where("id",$id)->find();
            if(!empty($data)){
                return ajax_success("店铺数据返回成功",$data);
            }else{
                return ajax_error("没有这个店铺信息");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺重新编辑
     **************************************
     */
    public function store_edit(Request $request){
        if($request->isPost()){
            $user_id =Session::get("user");
            $id =$request->only(["id"])["id"];
            $is_business =$request->only(["is_business"])["is_business"];
            $id_card =$request->only(["id_card"])["id_card"];
            $contact_name =$request->only(["contact_name"])["contact_name"];
            $address_data =$request->only(["address_data"])["address_data"];
            $address_real_data =$request->only(["address_real_data"])["address_real_data"];
            $store_introduction =$request->only(["store_introduction"])["store_introduction"];
            $business_name =$request->only(["business_name"])["business_name"];
            $licence_no =$request->only(["licence_no"])["licence_no"];
            if(empty($id_card) || empty($contact_name) || empty($address_data) ||empty($address_real_data) ||empty($store_introduction) ){
                return ajax_error("请注意填写完所有资料");
            }
            if($is_business ==2){
                if(empty($business_name) || empty($licence_no)){
                    return ajax_error("请填写企业信息");
                }
            }
            $card_positive =$request->only(["card_positive"])["card_positive"]; //身份证正面
            $card_side = $request->only(["card_side"])["card_side"];//身份证反面
            if(!empty($card_positive_file)){
                $card_positive_images = base64_upload_flie($card_positive);//身份证正面
                $ole_positive_url =Db::name("store")->where("id",$id)->value("card_positive");
            }
            if(!empty( $card_side)){
                $card_side_file =base64_upload_flie( $card_side) ; //身份证反面
                $ole_side_url =Db::name("store")->where("id",$id)->value("card_side");
            }
            //修改图片需要把之前的图片删除
            if(!empty($card_positive_file)&& !empty($card_side_file)){
                $data = [
                    "is_business"=>$is_business,
                    "id_card"=>$id_card,
                    "contact_name"=>$contact_name,
                    "address_data"=>$address_data,
                    "address_real_data"=>$address_real_data,
                    "card_positive"=>$card_positive_images,
                    "card_side"=>$card_side_file,
                    "store_introduction"=>$store_introduction,
                    "business_name"=>$business_name,
                    "licence_no"=>$licence_no,
                    //店铺状态(1审核通过,-1审核不通过,2审核中）
                    "status"=>2
                ];
            }else if(!empty($card_positive_file) && empty($card_side_file) ){
                $data = [
                    "is_business"=>$is_business,
                    "id_card"=>$id_card,
                    "contact_name"=>$contact_name,
                    "address_data"=>$address_data,
                    "address_real_data"=>$address_real_data,
                    "card_positive"=>$card_positive_images,
                    "store_introduction"=>$store_introduction,
                    "business_name"=>$business_name,
                    "licence_no"=>$licence_no,
                    //店铺状态(1审核通过,-1审核不通过,2审核中）
                    "status"=>2
                ];
            }else if(empty($card_positive_file) && !empty($card_side_file) ){
                $data = [
                    "is_business"=>$is_business,
                    "id_card"=>$id_card,
                    "contact_name"=>$contact_name,
                    "address_data"=>$address_data,
                    "address_real_data"=>$address_real_data,
                    "card_side"=>$card_side_file,
                    "store_introduction"=>$store_introduction,
                    "business_name"=>$business_name,
                    "licence_no"=>$licence_no,
                    //店铺状态(1审核通过,-1审核不通过,2审核中）
                    "status"=>2
                ];
            }else {
                $data = [
                    "is_business"=>$is_business,
                    "id_card"=>$id_card,
                    "contact_name"=>$contact_name,
                    "address_data"=>$address_data,
                    "address_real_data"=>$address_real_data,
                    "store_introduction"=>$store_introduction,
                    "business_name"=>$business_name,
                    "licence_no"=>$licence_no,
                    //店铺状态(1审核通过,-1审核不通过,2审核中）
                    "status"=>2
                ];
            }
            $bool =Db::name("store")->where("id",$id)->where("user_id",$user_id)->update($data);
            if($bool){
                //删除图片
                    if($card_positive_images != null){
                    unlink(ROOT_PATH . 'public' . DS . 'uploads/'.$ole_positive_url);
                }
                if($card_side_file != null){
                    unlink(ROOT_PATH . 'public' . DS . 'uploads/'.$ole_side_url);
                }
                return ajax_success("您的资料已提交,请耐心等待审核");
            }else{
                return ajax_error("网络错误，请重新提交");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:所有的店铺信息返回
     **************************************
     */
    public function store_all_data(Request $request){
        if($request->isPost()){
            $user_id =Session::get("user");
            $data =Db::name("store")
                ->where("user_id",$user_id)
                ->select();
            if(!empty($data)){
                return ajax_success("所有店铺信息返回成功",$data);
            }else{
                return ajax_error("没有店铺信息");
            }
        }
    }





}
