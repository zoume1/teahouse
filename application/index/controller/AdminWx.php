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
            if($val["result_code"] == "SUCCESS"){
                file_put_contents(EXTEND_PATH."data.txt",$val);
                //进行逻辑处理
                $result =Db::name("set_meal_order")
                    ->where("order_number",$val["out_trade_no"])
                    ->update(["status"=>1]);
                if($result){
                    echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                }else{
                    return "fail";
                }
            }
        }
    }

}