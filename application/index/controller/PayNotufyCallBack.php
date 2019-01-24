<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/7 0007
 * Time: 17:40
 */

include('../extend/WxpayAPI/lib/WxPay.Api.php');
include('../extend/WxpayAPI/lib/WxPay.Notify.php');
class  PayNotufyCallBack extends WxPayNotify{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:查询订单
     **************************************
     * @param $transaction_id
     * @return bool
     */
    public function Queryorder($transaction_id)
    {
        $input = new \WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = \WxPayApi::orderQuery($input);
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:重写回调处理函数
     **************************************
     * @param array $data
     * @param string $msg
     * @return bool
     */
    public function notify()
    {
        $data  =$this->request->param();
        $notfiyOutput = array();
        if (!array_key_exists("transaction_id", $data)) {
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if (!$this->Queryorder($data["transaction_id"])) {
            $msg = "订单查询失败";
            return false;
        }
        file_put_contents(EXTEND_PATH."data.txt",$data["out_trade_no"]);
        $res =   Db::name("activity_order")
            ->where("parts_order_number",$data["out_trade_no"])
            ->update(["status"=>1]);
        if($res){
            return true;
        }else{
            return false;
        }
    }
}