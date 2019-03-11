<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25 0025
 * Time: 16:24
 */
namespace  app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;

class Notification extends Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:这是处理回复
     **************************************
     * @param Request $request
     */
    public function notice_index(Request $request){
        if($request->isPost()){
            $order_id =$request->only("order_id")["order_id"];
            $datas =Db::name("note_notification")
                ->where("order_id",$order_id)
                ->order("create_time","desc")
                ->select();
            $order_type =Db::name("order")->where("id",$order_id)->value("order_type");
            $courier_number =Db::name("order")->where("id",$order_id)->value("courier_number");
            $express_name =Db::name("order")->where("id",$order_id)->value("express_name");
            $data =[
                "datas"=>$datas,
                "order_type"=>$order_type,
                "express_name"=>$express_name,
                "courier_number"=>$courier_number
            ];
            if(!empty($data)){
                return ajax_success("数据返回成功",$data);
            }else{
                return ajax_error("没有数据",["status"=>0]);
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:卖家备注
     **************************************
     */
    public function option_add_notice(Request $request){
        if($request->isPost()){
            $order_id =$request->only("order_id")["order_id"];
            $information =$request->only("information")["information"];
            $information_data =[
                "information"=>$information,
                "create_time"=>time(),
                "option_name"=>"商家",
                "order_id"=>$order_id,
            ];
           $res  = Db::name("note_notification")->insert($information_data);
            if($res){
                return ajax_success("备注成功",$information_data);
            }else{
                return ajax_error("备注失败",["status"=>0]);
            }
        }
    }


}