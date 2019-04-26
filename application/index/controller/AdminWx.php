<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/26 0026
 * Time: 11:27
 */
namespace app\index\controller;


use think\Controller;
use think\Request;
use think\Db;


class  AdminWx extends Controller{


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:后台套餐订购订单微信扫码支付回调
     **************************************
     */
    public function set_meal_notify(Request $request){
        if($request->isPost()){
            $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
            $xml_data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $val = json_decode(json_encode($xml_data), true);
            if($val["result_code"] == "SUCCESS" && $val["return_code"] =="SUCCESS" ){
                file_put_contents(EXTEND_PATH."data.txt",$val);
                $enter_all_id =Db::name("set_meal_order")
                    ->where("order_number",$val["out_trade_no"])
                    ->value("enter_all_id");
                $year =Db::name("enter_all")->where("id",$enter_all_id)->value("year");
                //进行逻辑处理
                $data["pay_time"] =time();//支付时间
                $data["pay_type"] =1;//支付类型（1扫码支付，2汇款支付，3余额支付）
                $data["pay_status"] =1;//到账状态（1为已到账，-1未到账，2待审核）
                $data["pay_status"] =1;//订单审核状态（1审核通过，-1审核不通过,0待审核）
                $data["start_time"] =time();//开始时间
                $data["end_time"] =strtotime("+$year  year");//开始时间
                $data["explains"] ="扫码支付直接通过";//审核说明
                $data["status"] =1; //订单状态（-1为未付款，1已付款）
                $data["audit_status"] =1; //订单审核状态（1审核通过，-1审核不通过,0待审核）
                $result =Db::name("set_meal_order")
                    ->where("order_number",$val["out_trade_no"])
                    ->update($data);
                if($result){
                    echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                }else{
                    return "fail";
                }
            }
        }
    }

}