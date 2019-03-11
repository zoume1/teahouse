<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/7 0007
 * Time: 15:03
 */
namespace  app\index\controller;

use think\Controller;
use think\Request;
use think\Db;

class  Api extends  Controller{
    /**
     **************李火生*******************
     * 快递100接口
     **************************************
     */
    public function express_hundred(Request $request)
    {
        if ($request->isPost()) {
            $order_id =$request->only(['by_order_id'])["by_order_id"];
            if(!empty($order_id)) {
                $express =Db::name('order')
                    ->field('courier_number,express_name')
                    ->where('id',$order_id)
                    ->find();
                if(!empty($express)){
                    $express_type =$express['express_name'];
                    $express_num =$express['courier_number'];
                    if(!empty($express_num)) {
                        $codes =$express_num;
                        //参数设置
                        $post_data = array();
                        $post_data["customer"] = config("express_hundred.customer");
                        $key = config("express_hundred.key");
                        $post_data["param"] = '{"com":"'.$express_type.'","num":"' . $codes . '"}';
                        $url = 'http://poll.kuaidi100.com/poll/query.do';
                        $post_data["sign"] = md5($post_data["param"] . $key . $post_data["customer"]);
                        $post_data["sign"] = strtoupper($post_data["sign"]);
                        $o = "";
                        foreach ($post_data as $k => $v) {
                            $o .= "$k=" . urlencode($v) . "&";        //默认UTF-8编码格式
                        }
                        $post_data = substr($o, 0, -1);
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                        $result = curl_exec($ch);
                        $data = str_replace("\"", '"', $result);
                        if(!empty($data)){
                            return ajax_success("物流数据返回成功",$data);
                        }else{
                            return ajax_error("暂无物流信息");
                        }
//                        $data = json_decode($data,true);
                    }
                }


            }
        }
    }
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