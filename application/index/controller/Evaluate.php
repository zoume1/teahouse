<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/13 0013
 * Time: 17:47
 */
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
class  Evaluate extends  Controller {


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:评价页面数据返回
     **************************************
     * @param Request $request
     */
    public function order_evaluate_index(Request $request){
        if($request->isPost()){
            $user_id =$request->only(["member_id"])["member_id"];
            $parts_order_number =$request->only(["order_id"])["order_id"];
            $parts_status =7;
            $condition = "`member_id` = " . $user_id .  " and `id` = " . $parts_order_number. " and `status` = " . $parts_status. " and `is_del` = 1" ;
            $data =Db::name("order")
                ->where($condition)
                ->select();
            if(!empty($data)){
                return ajax_success("对应的订单信息返回成功",$data);
            }else{
                return ajax_error("没有对应的订单信息",["status"=>0]);
            }

        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:初始订单评价图片添加
     **************************************
     * @param Request $request
     */
    public function order_evaluate_images_add(Request $request){
        if($request->isPost()){
            $img =$request->file("img"); //获取上传的图片
            $info = $img->move(ROOT_PATH . 'public' . DS . 'uploads');
            $images= str_replace("\\", "/", $info->getSaveName());
            //插入评价图片数据库
            $insert_data =[
                "images"=>$images,
            ];
            $images_id =Db::name("order_evaluate_images")->insertGetId($insert_data);
            if($images_id){
                exit(json_encode(array("status"=>1,"info"=>"上传成功","data"=>["images_id"=>$images_id])));
            }else{
                return ajax_error("上传失败",["status"=>0]);
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:初始订单评价图片删除
     **************************************
     * @param Request $request
     */
    public function order_evaluate_images_del(Request $request){
        if($request->isPost()){
            $id =$request->only(["id"])["id"];//数组id
            foreach ($id as $k=>$v) {
                $data =Db::name("order_evaluate_images")->where("id",$v)->value("images");
                //删除图片
                unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $data);
                $bool =Db::name("order_evaluate_images")->where("id",$v)->delete();
            }
            if($bool){
                return ajax_success("删除图片成功",["status"=>1]);
            }else{
                return ajax_error("删除失败",["status"=>0]);
            }
        }
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:初始订单评价添加
     **************************************
     * @param Request $request
     */
    public function order_evaluate_add(Request $request){
        if($request->isPost()){
            $order_id =$request->only("id")["id"];//订单排序号（单个）
            $images_id =$request->only("images_id")["images_id"];//图片id数组
            $user_id = $request->only(["member_id"])["member_id"];//用户id
            $evaluate_content =$request->only("content")["content"];//评价内容
            $user_info =Db::name("member")->field("member_phone_num,member_name,member_id")->where("member_id",$user_id)->find();
            $create_time =time();//创建时间
                //所有的订单信息
                $order_information =  Db::name("order")
                    ->field("parts_goods_name,goods_id,parts_order_number")
                    ->where("id",$order_id)
                    ->find();
                $data =[
                    "evaluate_content"=>$evaluate_content, //评价的内容
                    "goods_id" =>$order_information["goods_id"],
                    "goods_name"=>$order_information["parts_goods_name"],
                    "user_id"=>$user_info["member_id"],
                    "status"=>-1, //状态值1代表通过，-1代表待审核
                    "order_information_number"=>$order_information["parts_order_number"],
                    "order_id"=>$order_id,
                    "create_time"=>$create_time,
                    "user_name"=> $user_info["member_name"],
                    "is_repay"=>0, //是否回复（0是否，1为是,默认为0）
                    "is_show"=>1, //是否开启1开启，-1关闭
                ];
                $bool =Db::name("order_evaluate")->insertGetId($data);
                if(!empty( $bool)){
                    Db::name("order")
                        ->where("id",$order_id)
                        ->update(["status"=>8]);
                    if(!empty($images_id)){
                        foreach ($images_id as $ks=>$vs){
                                    //插入评价图片数据库
                                    $insert_data =[
                                        "evaluate_order_id"=>$bool,
                                    ];
                                    Db::name("order_parts_evaluate_images")->where("id",$vs)->update($insert_data);
                            }
                        }
                    return ajax_success("评价成功",$bool);
                    }else{
                return ajax_error("评价失败",["status"=>0]);
            }

        }

    }


}