<?php

namespace app\city\controller;
use think\Session;
use think\Validate;
use think\Request;
use app\city\model\CityRank;
use app\city\model\CityOrder as Order;
use app\city\controller\Picture;
use app\city\model\User as UserModel;
use app\rec\model\Invoice;
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
            $pay_code = $pay_object->AlipayCode($order_number);
            return $pay_code ? jsonSuccess('支付页面返回成功',$pay_code) : jsonError('支付页面返回失败');
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


    /**
     * 上传汇款支付凭证
     * @param User 
     * @param $data
     * @return false|int
     * @throws BaseException
     */
    public function payment_image(Request $request)
    {
        if($request->isPost()){
                //订单号
                $order_number = $request->only(["order_number"])["order_number"];
                $remittance_account = $request->only(['remittance_account'])['remittance_account'];
                $rest = new Picture;
                $id_image = $rest->upload_picture('payment_document');
                if($id_image){
                    $data = [
                        'payment_document'=>$id_image,
                        'pay_status' => CITY_HK,
                        'remittance_account'=>$remittance_account
                    ];
                    $model = new Order;
                    $order_status = $model -> allowField(true)->save($data,['order_number'=>$order_number]);
                    if($order_status){
                        return jsonSuccess("上传汇款凭证成功,我们将在2-3个工作日内进行审核,请您耐心等待");
                    } else {  
                        return jsonError("上传汇款凭证失败");
                }
            }
        }
    }

    /**
     * 开发票邮寄地址
     * @param User 
     * @param $data
     * @return false|int
     * @throws BaseException
     */
    public function mailing_address(Request $request)
    {
            if($request->isPost()){
                //订单号
                $user_id = $request->only(["user_id"])["user_id"];
                $user = UserModel::detail(['user_id'=>$user_id]);
                if($user){
                    $mailing_address = [
                        'post_address_one' => $user['post_address_one'],
                        'post_address_two' => $user['post_address_two'],
                        'post_address_three' => $user['post_address_three'],
                        'user_name' => $user['user_name'],
                        'phone_number'=>$user['phone_number'],
                        'detail'=>$user['detail']
                    ];
                    return jsonSuccess("发票邮寄地址发送成功",$mailing_address);
                } else {
                    return jsonError("发票邮寄地址发送失败");
            }
        }
    }


    /**
     * 开发票
     * @param User 
     * @param $data
     * @return false|int
     * @throws BaseException
     */
    public function cityOrderReceipt(Request $request)
    {
        if($request->isPost()){
            $data = Request::instance()->param();
            $receipt_object = new Invoice;
            $rest = $receipt_object->cityOrderReceipt($data);
            if($rest){
                   return jsonSuccess("开具发票成功");
            } else {
                    return jsonError("开具发票失败");
            }
        }
    }

}