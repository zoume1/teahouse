<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/7 0007
 * Time: 17:40
 */
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
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
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
    public function NotifyProcess($data, &$msg)
    {
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
        return true;
    }

}