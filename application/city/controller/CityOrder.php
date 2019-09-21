<?php

namespace app\city\controller;
use think\Session;
use think\Validate;
use think\Request;
use app\city\model\CityRank;
use app\city\model\CityOrder as Order;
use app\city\model\User as UserModel;
const PAY_WX = 1;
const CITY_ZFB = 2;
const CITY_HK = 3;
/**
 * PC端城市合伙人订单
 * Class CityOrder
 * @package app\city\controller
 */
class CityOrder extends Controller
{
    /**
     * 城市合伙人订单显示
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order_index()
    {
        $order_object = new Order;
        $restul = $order_object -> order_index();
        return $restul ? jsonSuccess('订单信息返回成功',$restul) : jsonError('订单信息返回失败');
    }

    /**
     * 城市合伙人订单微信支付
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cityWhatChatPay(Request $request){
        if($request->isPost()){
            $pay_object = new Order;
            $order_number = $request->only(["order_number"])["order_number"];
            $pay_code = $pay_object->WeChatPayCode($order_number);
            return $pay_code ? jsonSuccess('微信支付码返回成功',$pay_code) : jsonError('微信支付码返回失败');
        }

    }

    /**
     * 城市合伙人订单微信支付回调
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function city_meal_notify(Request $request)
    {
        if($request->isPost()){
            $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
            $xml_data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $val = json_decode(json_encode($xml_data), true);
            if($val["result_code"] == "SUCCESS" && $val["return_code"] =="SUCCESS" ){   
                //回调成功
                //找到订单
                //更新订单状态
                $model = new Order;
                $data = [
                    'start_time' => time(),
                    'end_time' => strtotime("+1 year"),
                    'pay_status' => PAY_WX,
                    'account_status' => PAY_WX,
                ];
                $rest = $model -> allowField(true)->save($data,['order_number'=>$val['out_trade_no']]);
                if($rest){                           
                      echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                  } else {
                      return "fail";
                  }      
            }
            return "fail";     
        }

    }


    /**
     * 城市合伙人订单支付宝支付
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cityAlipayCode(Request $request){
        if($request->isPost()){
            $pay_object = new Order;
            $order_number = $request->only(["order_number"])["order_number"];
            $pay_code = $pay_object->WeChatPayCode($order_number);
            return $pay_code ? jsonSuccess('微信支付码返回成功',$pay_code) : jsonError('微信支付码返回失败');
        }

    }

    /**
     * 城市合伙人订单支付宝支付回调
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function city_meal_notify_alipay()
    {
        include EXTEND_PATH . "/lib/payment/alipay/alipay.class.php";
        $obj_alipay = new \alipay();
        if (!$obj_alipay->verify_notify())
        {
            //验证未通过
            echo "fail";
            exit();
        } else {  
            //支付状态  
            $trade_status = input('trade_status');
            //原始订单号
            $out_trade_no = input('out_trade_no');
            if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS'){     //支付成功
            //逻辑处理
            $model = new Order;
            $data = [
                'start_time' => time(),
                'end_time' => strtotime("+1 year"),
                'pay_status' => PAY_ZFB,
                'account_status' => PAY_WX,
            ];
            $rest = $model -> allowField(true)->save($data,['order_number'=>$out_trade_no]);
            if($rest)
            {
                return "success";
            } else {
                return "fail";
            } 
                return "fail"; 
            }
        }
    }
    
}