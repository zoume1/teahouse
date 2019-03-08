<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/8 0008
 * Time: 9:41
 */
namespace  app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;

include('../extend/WxpayAPI/lib/WxPay.Api.php');
include('../extend/WxpayAPI/example/WxPay.NativePay.php');
include('../extend/WxpayAPI/lib/WxPay.Notify.php');
include('../extend/WxpayAPI/example/log.php');
class  Api extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:初始订单退款
     **************************************
     */
    public function order_refund(Request $request){
        $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];
        $business_return_money =$request->only(["business_return_money"])["business_return_money"];
        $data =Db::name("after_sale")
            ->where("id",$after_sale_id)
            ->find();
        $map = array(
            'id'=>$data["order_id"]
        );
        $refund_amount =Db::name("order")
            ->field("refund_amount,parts_order_number,order_real_pay")
            ->where($map)
            ->find();
        if(!$refund_amount){
            return ajax_error("未找到该订单信息");
        }
        if($business_return_money>$refund_amount["refund_amount"]){
            return ajax_error("所退款金额大于支付的金钱");
        }
        $out_trade_no=$refund_amount["parts_order_number"];
        $total_fee=$refund_amount["order_real_pay"] *100;
        $refund_fee= $refund_amount["refund_amount"] *100;
        $input = new \WxPayRefund();
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($refund_fee);
        $input->SetOut_refund_no(\WxPayConfig::MCHID.date("YmdHis"));
        $input->SetOp_user_id(\WxPayConfig::MCHID);
       $result =\WxPayApi::refund($input);
        file_put_contents(EXTEND_PATH."refund.txt",$result);
        if ($result['result_code'] == 'SUCCESS' && $result['return_code'] == 'SUCCESS') {
            $result['code'] = 1;
            $result['data'] =  $result['transaction_id'];
          return ajax_success("支付成功",$result);
        }
        else {
            $result['code'] = 0;
            $result['msg'] =  $result['err_code'];
          return ajax_error("支付失败",$result);
        }

    }
}