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
                $enter_all_data=Db::name("set_meal_order")
                    ->where("order_number",$val["out_trade_no"])
                    ->find();
                $year =Db::name("enter_all")->where("id",$enter_all_data['enter_all_id'])->value("year");
                //进行逻辑处理
                //1、先判断是否上一单是否到期和是否存在
                //2、判断如果是升级过来的话需要进行删除之前已付款的订单
                $is_set_order =Db::name("set_meal_order")
                    ->where("store_id",$enter_all_data["store_id"])
                    ->where("audit_status",1)
                    ->find();
                if($is_set_order){
                    //这是套餐升级的情况
                    $data["pay_time"] =time();//支付时间
                    $data["pay_type"] =1;//支付类型（1扫码支付,2汇款支付，3余额支付）
                    $data["pay_status"] =1;//到账状态（1为已到账，-1未到账，2待审核）
                    $data["start_time"] =time();//开始时间
                    $data["end_time"] =strtotime("+$year  year");//开始时间
                    $data["explains"] ="微信扫码支付直接通过";//审核说明
                    $data["status"] =1; //订单状态（-1为未付款，1已付款）
                    $data["audit_status"] =1; //订单审核状态（1审核通过，-1审核不通过,0待审核）
                    $res =Db::name("set_meal_order")
                        ->where("order_number",$val["out_trade_no"])
                        ->update($data);
                    if($res){
                        //把之前的套餐订单删掉
                       $result = Db::name("set_meal_order")->where("order_number",$is_set_order["order_number"])->delete();
                        if($result){
                            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                        }else{
                            return "fail";
                        }
                    }else{
                        $result =0;
                        if($result){
                            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                        }else{
                            return "fail";
                        }
                    }
                }else{
                    //这是新加入套餐的情况
                    $data["pay_time"] =time();//支付时间
                    $data["pay_type"] =1;//支付类型（1扫码支付，2汇款支付，3余额支付）
                    $data["pay_status"] =1;//到账状态（1为已到账，-1未到账，2待审核）
                    $data["start_time"] =time();//开始时间
                    $data["end_time"] =strtotime("+$year  year");//开始时间
                    $data["explains"] ="微信扫码支付直接通过";//审核说明
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

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:后台套餐订购订单支付宝扫码支付回调
     **************************************
     */
    public function set_meal_notify_alipay()
    {
        include EXTEND_PATH . "/lib/payment/alipay/alipay.class.php";
        $obj_alipay = new \alipay();
        if (!$obj_alipay->verify_notify()) {
            //验证未通过
            echo "fail";
            exit();
        } else {
            //这里可以做一下你自己的订单逻辑处理
            $pay_time = time();
            $data['pay_time'] = $pay_time;
            //原始订单号
            $out_trade_no = input('out_trade_no');
            //支付宝交易号
            $trade_no = input('trade_no');
            //交易状态
            $trade_status = input('trade_status');
            if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                $condition['order_number'] = $out_trade_no;
                $enter_all_data = Db::name("set_meal_order")
                    ->where($condition)
                    ->find();
                $year = Db::name("enter_all")->where("id", $enter_all_data['enter_all_id'])->value("year");
                //进行逻辑处理
                //1、先判断是否上一单是否到期和是否存在
                //2、判断如果是升级过来的话需要进行删除之前已付款的订单
                $is_set_order = Db::name("set_meal_order")
                    ->where("store_id", $enter_all_data["store_id"])
                    ->where("audit_status", 1)
                    ->find();
                if ($is_set_order) {
                    //这是套餐升级的情况
                    $data["pay_time"] = time();//支付时间
                    $data["pay_type"] = 1;//支付类型（1扫码支付,2汇款支付，3余额支付）
                    $data["pay_status"] = 1;//到账状态（1为已到账，-1未到账，2待审核）
                    $data["start_time"] = time();//开始时间
                    $data["end_time"] = strtotime("+$year  year");//开始时间
                    $data["explains"] = "支付宝扫码支付直接通过";//审核说明
                    $data["status"] = 1; //订单状态（-1为未付款，1已付款）
                    $data["audit_status"] = 1; //订单审核状态（1审核通过，-1审核不通过,0待审核）
                    $res = Db::name("set_meal_order")
                        ->where($condition)
                        ->update($data);
                    if ($res) {
                        //把之前的套餐订单删掉
                        $result = Db::name("set_meal_order")
                            ->where("order_number", $is_set_order["order_number"])
                            ->delete();
                        if ($result) {
                            return "success";
                        } else {
                            return "fail";
                        }
                    } else {
                        $result = 0;
                        if ($result) {
                            return "success";
                        } else {
                            return "fail";
                        }
                    }
                } else {
                    //这是新加入套餐的情况
                    $data["pay_time"] = time();//支付时间
                    $data["pay_type"] = 1;//支付类型（1扫码支付，2汇款支付，3余额支付）
                    $data["pay_status"] = 1;//到账状态（1为已到账，-1未到账，2待审核）
                    $data["start_time"] = time();//开始时间
                    $data["end_time"] = strtotime("+$year  year");//开始时间
                    $data["explains"] = "支付宝扫码支付直接通过";//审核说明
                    $data["status"] = 1; //订单状态（-1为未付款，1已付款）
                    $data["audit_status"] = 1; //订单审核状态（1审核通过，-1审核不通过,0待审核）
                    $result = Db::name("set_meal_order")
                        ->where($condition)
                        ->update($data);
                    if ($result) {
                        return "success";
                    } else {
                        return "fail";
                    }
                }
            }

        }
    }




}