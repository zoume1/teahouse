<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25 0025
 * Time: 15:59
 */
namespace app\index\controller;


use think\Controller;
use think\Request;
use think\Db;

class Notification extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:用户提醒
     **************************************
     * @param Request $request
     */
    public function option_add(Request $request){
        if($request->isPost()){
            $order_num =$request->only("order_num")["order_num"]; //订单编号
            $timetoday = strtotime(date("Y-m-d",time()));//今天0点的时间点
            $time2 = time() + 3600*24;//今天24点的时间点，两个值之间即为今天一天内的数据
            $time_condition  = "create_time>$timetoday and create_time< $time2";
            $is_notice =Db::name("note_notification")
                ->where("order_num",$order_num)
                ->where($time_condition)
                ->find();
            if(!empty($is_notice)){
                return ajax_success("您已提醒过");
            }
            $data =Db::name("order")
                ->field("id")
                ->where("parts_order_number",$order_num)
                ->select();
            foreach ($data as $k=>$v){
                $information_data =[
                    "information"=>"用户提醒发货",
                    "create_time"=>time(),
                    "option_name"=>"用户",
                    "order_id"=>$v["id"]
                ];
                $res =  Db::name("note_notification")->insert($information_data);
            }
            if($res){
                return ajax_success("提醒成功",["status"=>1]);
            }else{
                return ajax_error("请重新操作",["status"=>0]);
            }
        }

    }




}