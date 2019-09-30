<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/16
 * Time: 10:54
 */
namespace app\city\controller;
use think\Request;
use think\Validate;
use think\Controller;
use think\Config;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order as WechatOrder;
use app\rec\model\WechatPay as WeiPay;
use app\city\model\CityOrder as Order;

use think\Db;

class WeiCityPay extends Controller{

    /**
     * 公众号合伙人订单微信支付
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function city_WhatChatPay(Request $request){
        if($request->isPost()){
            $wechatpay = new WeiPay();
            $url = 'https://www.zhihuichacang.com/city_meal_notify';
            $order_number = $request->only(["order_number"])["order_number"];
            $pay_money = $request->only(["pay_money"])["pay_money"];
            $order =  Order::detail(['order_number'=>$order_number]);
            if(!$order)jsonError('当前订单不存在');
            $res = $wechatpay->pay($order['city_meal_name'],$order['user_name'],$order['order_number'],$pay_money,$url,$order['openid']);
            return  $res; exit();
        }

    }





    /**
     * 店铺支付成功微信回调demo
     * @throws \EasyWeChat\Core\Exceptions\FaultException
     */
    public function app_notice(){

        //初始化微信sdk
        $wxConf = config('wechat');

        $app = new Application($wxConf);
        $response = $app->payment->handleNotify(function($notify, $successful){
            // 使用通知里的 "微信支付订单号transaction_id" 或者 "商户订单号out_trade_no"
            $rstArr = json_decode($notify,true);

            $where = array('order_number'=>$rstArr['out_trade_no']);

            $orderArr = Db::table('tb_set_meal_order')->where($where)->field('status,order_number')->find();
            if (empty($orderArr)) {
                // 如果订单不存在
                returnJson(0,'订单不存在');
            }
            if ($orderArr['status'] == 1) {
                returnJson(0,'订单已支付'); // 已经支付成功了就不再更新了
            }
            // 用户是否支付成功
            if ($successful) {
                $invoice = new Invoice();
                $invoice->ele_invoice($orderArr['order_number']);
                // 不是已经支付状态则修改为已经支付状态
                Db::table('tb_set_meal_order')->where($where)->update(array('status' => 1, "pay_time" => time()));

            }
            returnJson(1,'订单已完成'); // 返回处理完成
        });
        // 将响应输出
        return $response;
    }

}