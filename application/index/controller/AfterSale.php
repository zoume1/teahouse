<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/22 0022
 * Time: 16:12
 * 售后
 */
namespace  app\index\controller;

use think\Controller;
use think\Request;
use think\Db;

class  AfterSale extends Controller{


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:上传的图片，注意：小程序只能一张张上传
     **************************************
     * @param Request $request
     */
    public function after_sale_upload(Request $request){
        if($request->isPost()){
            $img =$request->file("img"); //获取上传的图片
            $info = $img->move(ROOT_PATH . 'public' . DS . 'uploads');
            $images= str_replace("\\", "/", $info->getSaveName());
            //插入评价图片数据库
            $insert_data =[
                "url"=>$images,
            ];
            $images_id =Db::name("after_image")->insertGetId($insert_data);
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
     * Notes:售后图片删除（有时候返回上一次则进行删除）
     **************************************
     * @param Request $request
     */
    public function after_sale_images_del(Request $request){
        if($request->isPost()){
            $id =$request->only(["id"])["id"];//数组id
            foreach ($id as $k=>$v) {
                $data =Db::name("after_image")->where("id",$v)->value("uel");
                //删除图片
                unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $data);
                $bool =Db::name("after_image")->where("id",$v)->delete();
            }
            if($bool){
                return ajax_success("删除图片成功");
            }else{
                return ajax_error("删除失败");
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:用户申请售后
     **************************************
     * @param Request $request
     */
    public function  apply_after_sale(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];//会员id
            $order_id =$request->only(["order_id"])["order_id"];//订单编号（主键）
            $return_reason =$request->only(["return_reason"])["return_reason"];//退货原因
            $application_amount =$request->only(["application_amount"])["application_amount"];//申请的金额
            $is_return_goods =$request->only(["is_return_goods"])["is_return_goods"];//判断是否需要换货还是退货退款（1需要要进行换货，2退款退货）
            $after_image_ids =$request->only(["after_image_ids"])["after_image_ids"];//退货上传的图片id 数组形式
            //限制一下不能申请超过该单的支付原价
            $before_order_data =Db::name("order")
                ->where("id",$order_id)
                ->find();
//            if($before_order_data["refund_amount"] < $application_amount){
//                return ajax_error("申请的金额不能超过".$before_order_data["refund_amount"]."元");
//            }
            $time=date("Y-m-d",time());
            $v=explode('-',$time);
            $time_second=date("H:i:s",time());
            $vs=explode(':',$time_second);
            $sale_order_number  ="SH".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].rand(1000,9999); //订单编号
            $insert_data  =[
                "order_id"=>$order_id, //订单号
                "sale_order_number"=>$sale_order_number,//售后编号
                "is_return_goods"=>$is_return_goods,//判断是否为换货还是退货退款，1换货，2退款退货
                "operation_time"=>time(), //操作时间
                "application_amount"=>$application_amount,//申请金额
                "return_reason"=>$return_reason,//退货原因
                "status"=>1, //申请状态（1为申请中，2商家已同意，等待上传快递单信息，处理中，3收货中，4换货成功，5拒绝）
                "buy_order_number"=>$before_order_data["parts_order_number"],//原始订单号
                "member_id"=>$member_id, //会员id
            ];
            $after_sale_id =Db::name("after_sale")->insertGetId($insert_data);
            if($after_sale_id){
                if(!empty($after_image_ids)){
                    foreach ($after_image_ids as $ks=>$vs){
                        //插入评价图片数据库
                        $insert_data =[
                            "after_sale_id"=>$after_sale_id,
                        ];
                        Db::name("after_image")->where("id",$vs)->update($insert_data);
                    }
                }
                return ajax_success("申请成功，请耐心等待审核");
            }else{
                return ajax_error("请重新提交申请");
            }
        }
    }





}