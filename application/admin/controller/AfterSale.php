<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/6 0006
 * 售后
 * Time: 17:01
 */
namespace  app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;

class  AfterSale extends  Controller{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后官方回复
     **************************************
     */
    public function business_replay(Request $request){
        if($request->isPost()){
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
            $content= $request->only(["content"])["content"]; //回复的内容
            $is_who =1;//谁回复（1卖家，2买家）
            $data =[
                "content" =>$content,
                "after_sale_id"=>$after_sale_id,
                "is_who"=>$is_who,
                "create_time" =>time()
            ];
            $id =Db::name("after_reply")->insertGetId($data);
            if($id >0){
                return ajax_success("回复成功",$data);
            }else{
                return ajax_error("回复失败");
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后页面数据返回
     **************************************
     */
    public function  business_after_sale_information(Request $request){
        if($request->isPost()){
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
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
     * Notes:售后状态修改
     **************************************
     */
    public function after_sale_status(Request $request){
        if($request->isPost()){
            $status =$request->only(["status"])["status"];     //申请状态
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后记录id
            if($status ==5){   //拒绝申请
                $normal_time =Db::name("order_setting")->find();//订单设置的时间
                $normal_future_time =strtotime("+". $normal_time['after_sale_time']." minute");
                $data =[
                    "status"=>$status,
                    "handle_time"=>time(),
                    "future_time"=>$normal_future_time,
                    "who_handle"=>3 , //1、用户自己撤销 2 、中途撤销 3、商家拒绝
                ];
                //初始订单更改为已关闭
                $order_id=db('after_sale')->where('id',$after_sale_id)->value('order_id');
                $order_number=db('order')->where('id',$order_id)->value('parts_order_number');
                $re=db('order')->where('parts_order_number',$order_number)->select();
                foreach($re as $k=>$v){
                    db('order')->where('id',$v['id'])->update(['status'=>0]);
                }
            }else{      //收货中
                $data =[
                    "status"=>$status,
                    "handle_time"=>time()
                ];
            }
            $bool =Db::name("after_sale")
                ->where("id",$after_sale_id)
                ->update($data);
            if($bool){
                return ajax_success("更改成功");
            }else{
                return ajax_error("更改失败");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后状态修改带快递信息
     **************************************
     */
    public function after_sale_express_add(Request $request){
        if($request->isPost()){
            $status =$request->only(["status"])["status"];
            $sell_express_company =$request->only(["sell_express_company"])["sell_express_company"];
            $sell_express_number =$request->only(["sell_express_number"])["sell_express_number"];
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
            $data =[
                "status"=>$status,
                "sell_express_company"=>$sell_express_company,
                "sell_express_number"=>$sell_express_number
            ];
            $bool =Db::name("after_sale")->where("id",$after_sale_id)->update($data);
            //初始订单已关闭
            $order_id=db('after_sale')->where('id',$after_sale_id)->value('order_id');
            $order_number=db('order')->where('id',$order_id)->value('parts_order_number');
            $re=db('order')->where('parts_order_number',$order_number)->select();
            foreach($re as $k=>$v){
                db('order')->where('id',$v['id'])->update(['status'=>0]);
            }
            if($bool){
                return ajax_success("更改成功");
            }else{
                return ajax_error("更改失败");
            }
        }
    }
//    /**
//     **************李火生*******************
//     * @param Request $request
//     * Notes:售后状态修改带退钱操作
//     **************************************
//     */
//    public function after_sale_money_add(Request $request){
//        if($request->isPost()){
//            $status =$request->only(["status"])["status"];
//            $business_return_money =$request->only(["business_return_money"])["business_return_money"];
//            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
//            $data =[
//                "status"=>$status,
//                "business_return_money"=>$business_return_money,
//            ];
//            $bool =Db::name("after_sale")->where("id",$after_sale_id)->update($data);
//            if($bool){
//                return ajax_success("更改成功");
//            }else{
//                return ajax_error("更改失败");
//            }
//        }
//    }
/**
 * lilu
 * 退款维权---退款（余额）
 */
public function after_sale_refound(){
    //获取参数
    $input=input();
    //获取订单信息
    $order_info=db('after_sale')->where('id',$input['after_sale_id'])->find();
    //退款至会员余额
    $re=db('member')->where('member_id',$order_info['member_id'])->setInc('member_wallet',$input['business_return_money']);
    $money=db('member')->where('member_id',$order_info['member_id'])->value('member_wallet');
    //退款记录
    $map['user_id']=$order_info['member_id'];
    $map['wallet_operation']=$input['business_return_money'];
    $map['wallet_type']=1;
    $map['operation_time']=date('Y-m-d H:i:s',time());
    $map['wallet_remarks']='售后单号为'.$order_info['sale_order_number'].'退款成功';
    $map['wallet_img']='';
    $map['title']=date('Y-m-d H:i:s',time());
    $map['order_nums']=date('Y-m-d H:i:s',time());
    $map['pay_type']='小城序';
    $map['wallet_balance']=$money;
    $map['operation_linux_time']=time();
    db('wallet')->insert($map);
    //修改退款维权订单的状态
    $re2=db('after_sale')->where('id',$input['after_sale_id'])->update(['status'=>'6']);
    //修改初始订单的状态----已关闭
    $re3=db('order')->where('id',$order_info['order_id'])->update(['status'=>'0']);
   if($re && $re2 && $re3){
       return ajax_success('退款成功');
    }else{
        return ajax_error('退款失败');
   }

}


}