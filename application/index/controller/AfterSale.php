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
     * Notes:售后订单信息返回
     **************************************
     * @param Request $request
     */
    public function after_sale_order_return(Request $request){
        if($request->isPost()){
            $id =$request->only(["id"])["id"];
            $data =Db::name("order")->field("parts_goods_name,goods_image,refund_amount")->where("id",$id)->find();
            if(!empty($data)){
                return ajax_success("数据返回成功",$data);
            }else{
                return ajax_error("没有该数据");
            }
        }
    }

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
//            $application_amount =$request->only(["application_amount"])["application_amount"];//申请的金额
            $is_return_goods =$request->only(["is_return_goods"])["is_return_goods"];//判断是否需要换货还是退货退款（1需要要进行换货，2退款退货）
            $after_image_ids =$request->only(["after_image_ids"])["after_image_ids"];//退货上传的图片id 数组形式
            //限制一下不能申请超过该单的支付原价
            $before_order_data =Db::name("order")
                ->where("id",$order_id)
                ->find();
//            if($before_order_data["refund_amount"] < $application_amount){
//                return ajax_error("申请的金额不能超过".$before_order_data["refund_amount"]."元");
//            }
            $normal_time =Db::name("order_setting")->find();//订单设置的时间
            $normal_future_time =strtotime("+". $normal_time['after_sale_time']." minute");
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
                "future_time"=>$normal_future_time,//未来时间
                "application_amount"=>$before_order_data["refund_amount"],//申请金额
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


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:添加物流信息
     **************************************
     * @param Request $request
     */
    public function  add_express_information(Request $request){
        $id =$request->only(["id"])["id"];
        $buy_express_company =$request->only(["buy_express_company"])["buy_express_company"]; //快递公司
        $buy_express_number =$request->only(["buy_express_number"])["buy_express_number"]; //快递单号
        if(!empty($buy_express_company) && (!empty($buy_express_number))){
            $data =[
                "buy_express_company"=>$buy_express_company,
                "buy_express_number"=>$buy_express_number,
                "status" =>3
            ];
            $bool=Db::name("after_sale")->where("id",$id)->update($data);
            if($bool){
                return ajax_success("添加快递信息成功");
            }else{
                return ajax_error("请重新添加信息");
            }
        }else{
            return ajax_error("请填写快递公司或快递单号");
        }

    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:退货信息返回
     **************************************
     * @param Request $request
     */
    public function after_sale_information_return(Request $request){
        if($request->isPost()){
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];
            $data =Db::name("after_sale")->where("id",$after_sale_id)->find();
            $data["images"] =Db::name("after_image")->where("after_sale_id",$after_sale_id)->select();
            $data["reply"] =Db::name("after_reply")->where("after_sale_id",$after_sale_id)->select();
            if(!empty($data)){
                return ajax_success("售后信息返回成功",$data);
            }else{
                return ajax_error("暂无售后信息");
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后买家回复
     **************************************
     * @param Request $request
     */
    public function buyer_replay(Request $request){
        if($request->isPost()){
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
            $content= $request->only(["content"])["content"]; //回复的内容
            $is_who =2;//谁回复（1卖家，2买家）
            $data =[
                "content" =>$content,
                "after_sale_id"=>$after_sale_id,
                "is_who"=>$is_who,
                "create_time" =>time()
            ];
            $id =Db::name("after_reply")->insertGetId($data);
            if($id >0){
                return ajax_success("回复成功");
            }else{
                return ajax_error("回复失败");
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:修改售后
     **************************************
     */
    public function  update_application(Request $request){
        if($request->isPost()){

        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:撤销售后申请
     **************************************
     */
    public function cancellation_of_application(Request $request){
        if($request->isPost()){
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
           $bool =Db::name("after_sale")->where("id", $after_sale_id)->update(["status"=>5]);
           if($bool){
               return ajax_success("撤销成功");
           }else{
               return ajax_error("撤销失败");
           }
        }

    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后商家寄还地址返回
     **************************************
     */
    public function business_address(Request $request){
        if($request->isPost()){
            $address =Db::name("about_us")->field("business_address")->find();
            if(!empty($address)){
                return ajax_success("商家收货地址返回成功",$address);
            }else{
                return ajax_error("没有设置收货地址");
            }
        }
    }

}