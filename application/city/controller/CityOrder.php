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
            $pay_money = $request->only(["pay_money"])["pay_money"];
            $pay_code = $pay_object->WeChatPayCode($order_number,$pay_money);
            return $pay_code ? jsonSuccess('微信支付码返回成功',$pay_code) : jsonError('微信支付码返回失败');
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
            $pay_money = $request->only(["pay_money"])["pay_money"];
            $pay_code = $pay_object->AlipayCode($order_number,$pay_money);
            return $pay_code ? jsonSuccess('支付页面返回成功',$pay_code) : jsonError('支付页面返回失败');
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
            $pay_money = $request->only(["order_price"])["order_price"];
            $remittance_account = $request->only(['remittance_account'])['remittance_account'];
            $rest = new Picture;
            $id_image = $rest->upload_picture('payment_document');
            if($id_image){
                $data = [
                    'order_price'=>$pay_money,
                    'payment_document'=>$id_image,
                    'pay_status' => CITY_HK,
                    'remittance_account'=>$remittance_account,
                    'judge_status' => CITY_ZFB
                ];
                $model = new Order;
                $order_status = $model -> allowField(true)->save($data,['order_number'=>$order_number]);
                $order_rest = $model->detail(['order_number'=>$order_number]);
                    if($order_status){
                        $user = new UserModel;
                        $update_status = $user -> allowField(true)->save(['judge_status'=>CITY_ZFB],['user_id'=>$order_rest['city_user_id']]);
                        if($update_status){
                            return jsonSuccess("上传汇款凭证成功,我们将在2-3个工作日内进行审核,请您耐心等待");
                        } else {  
                            return jsonError("上传汇款凭证失败");
                        }
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