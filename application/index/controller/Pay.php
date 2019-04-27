<?php

namespace app\index\controller;
use think\Controller;
use think\Request;
use  think\Db;
use think\Cache;

include('../extend/WxpayAPI/lib/WxPay.Api.php');
include('../extend/WxpayAPI/example/WxPay.NativePay.php');
include('../extend/WxpayAPI/lib/WxPay.Notify.php');
include('../extend/WxpayAPI/example/log.php');

class Pay extends  Controller{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序活动支付
     **************************************
     * @param Request $request
     */
    function index(Request $request) {
        $open_ids = $request->param("open_id");//open_id
        $activity_name = $request->param("activity_name");//名称
        $cost_moneny = $request->param("cost_moneny");//金额
        $order_numbers =$request->param("order_number");//订单编号
        //         初始化值对象
        $input = new \WxPayUnifiedOrder();
        //         文档提及的参数规范：商家名称-销售商品类目
        $input->SetBody($activity_name);
        //         订单号应该是由小程序端传给服务端的，在用户下单时即生成，demo中取值是一个生成的时间戳
//        $input->SetOut_trade_no(time().'');
        $input->SetOut_trade_no($order_numbers);
        //         费用应该是由小程序端传给服务端的，在用户下单时告知服务端应付金额，demo中取值是1，即1分钱
        $input->SetTotal_fee($cost_moneny*100);
        $return_url = config("domain.url")."notify";
        $input->SetNotify_url($return_url);//需要自己写的notify.php
        $input->SetTrade_type("JSAPI");
        //         由小程序端传给后端或者后端自己获取，写自己获取到的，
        $input->SetOpenid( $open_ids);
        //$input->SetOpenid($this->getSession()->openid);
        //         向微信统一下单，并返回order，它是一个array数组
        $order = \WxPayApi::unifiedOrder($input);
        //         json化返回给小程序端
        header("Content-Type: application/json");
        echo $this->getJsApiParameters($order);
    }

    /**
     **************李火生*******************
     * @param Request $request
     **************************************
     * @param $UnifiedOrderResult
     * @return string
     * @throws \WxPayException
     */
    private function getJsApiParameters($UnifiedOrderResult)
    {    //判断是否统一下单返回了prepay_id
        if(!array_key_exists("appid", $UnifiedOrderResult)
            || !array_key_exists("prepay_id", $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == "")
        {
            throw new \WxPayException("参数错误");
        }
        $jsapi = new \WxPayJsApiPay();
        $jsapi->SetAppid($UnifiedOrderResult["appid"]);
        $timeStamp = time();
        $jsapi->SetTimeStamp("$timeStamp");
        $jsapi->SetNonceStr(\WxPayApi::getNonceStr());
        $jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
        $jsapi->SetSignType("MD5");
        $jsapi->SetPaySign($jsapi->MakeSign());
        $parameters = json_encode($jsapi->GetValues());
        return $parameters;
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序普通商品订单支付
     **************************************
     * @param Request $request
     */
    function order_index(Request $request) {
        $member_id = $request->param("member_id");//open_id
        $open_ids =Db::name("member")
            ->where("member_id",$member_id)
            ->value("member_openid");
        $order_numbers =$request->param("order_number");//订单编号
        $order_datas = Db::name("order")
            ->where("parts_order_number",$order_numbers)
            ->where("member_id", $member_id)
            ->find();
        $activity_name =$order_datas["parts_goods_name"];//名称
        $cost_moneny = $order_datas["order_real_pay"];//金额
        //         初始化值对象
        $input = new \WxPayUnifiedOrder();
        //         文档提及的参数规范：商家名称-销售商品类目
        $input->SetBody($activity_name);
        //         订单号应该是由小程序端传给服务端的，在用户下单时即生成，demo中取值是一个生成的时间戳
//        $input->SetOut_trade_no(time().'');
        $input->SetOut_trade_no($order_numbers);
        //         费用应该是由小程序端传给服务端的，在用户下单时告知服务端应付金额，demo中取值是1，即1分钱
        $input->SetTotal_fee($cost_moneny*100);
        $return_url =config("domain.url")."order_notify";
        $input->SetNotify_url($return_url);//需要自己写的notify.php
        $input->SetTrade_type("JSAPI");
        //         由小程序端传给后端或者后端自己获取，写自己获取到的，
        $input->SetOpenid( $open_ids);
        //$input->SetOpenid($this->getSession()->openid);
        //         向微信统一下单，并返回order，它是一个array数组
        $order = \WxPayApi::unifiedOrder($input);
        //       json化返回给小程序端
        header("Content-Type: application/json");
        echo $this->getJsApiParameters($order);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序充值支付
     **************************************
     * @param Request $request
     */
    function  recharge_pay(Request $request){
        $member_id = $request->param("member_id");//open_id
        $open_ids =Db::name("member")
            ->where("member_id",$member_id)
            ->value("member_openid");
        $order_numbers =$request->param("recharge_order_number");//订单编号
        $order_datas = Db::name("recharge_record")
            ->where("recharge_order_number",$order_numbers)
            ->where("user_id", $member_id)
            ->find();
        $activity_name ="充值";//名称
        $cost_moneny = $order_datas["recharge_money"];//金额
        //         初始化值对象
        $input = new \WxPayUnifiedOrder();
        //         文档提及的参数规范：商家名称-销售商品类目
        $input->SetBody($activity_name);
        //         订单号应该是由小程序端传给服务端的，在用户下单时即生成，demo中取值是一个生成的时间戳
//        $input->SetOut_trade_no(time().'');
        $input->SetOut_trade_no($order_numbers);
        //         费用应该是由小程序端传给服务端的，在用户下单时告知服务端应付金额，demo中取值是1，即1分钱
        $input->SetTotal_fee($cost_moneny*100);
        $return_url = config("domain.url")."recharge_notify";
        $input->SetNotify_url($return_url);//需要自己写的notify.php
        $input->SetTrade_type("JSAPI");
        //         由小程序端传给后端或者后端自己获取，写自己获取到的，
        $input->SetOpenid( $open_ids);
        //$input->SetOpenid($this->getSession()->openid);
        //         向微信统一下单，并返回order，它是一个array数组
        $order = \WxPayApi::unifiedOrder($input);
        //       json化返回给小程序端
        header("Content-Type: application/json");
        echo $this->getJsApiParameters($order);
    }



    /**
     **************郭杨*******************
     * @param Request $request
     * Notes 众筹商品打赏支付
     **************************************
     * @param Request $request
     */
    function  reward_pay(Request $request){
        $member_id = $request->param("member_id");//open_id
        $order_numbers = $request->param("order_number");//订单编号
        $open_ids =Db::name("member")
        ->where("member_id",$member_id)
        ->value("member_openid");
        $order_datas = Db::name("reward")
            ->where("order_number",$order_numbers)
            ->where("member_id", $member_id)
            ->find();
        $activity_name ="打赏";//名称
        $cost_moneny = $order_datas["money"];//金额
        //         初始化值对象
        $input = new \WxPayUnifiedOrder();
        //         文档提及的参数规范：商家名称-销售商品类目
        $input->SetBody($activity_name);
        //         订单号应该是由小程序端传给服务端的，在用户下单时即生成，demo中取值是一个生成的时间戳
//        $input->SetOut_trade_no(time().'');
        $input->SetOut_trade_no($order_numbers);
        //         费用应该是由小程序端传给服务端的，在用户下单时告知服务端应付金额，demo中取值是1，即1分钱
        $input->SetTotal_fee($cost_moneny*100);
        $return_url = config("domain.url")."reward_notify";
        $input->SetNotify_url($return_url);//需要自己写的notify.php
        $input->SetTrade_type("JSAPI");
        //         由小程序端传给后端或者后端自己获取，写自己获取到的，
        $input->SetOpenid( $open_ids);
        //$input->SetOpenid($this->getSession()->openid);
        //         向微信统一下单，并返回order，它是一个array数组
        $order = \WxPayApi::unifiedOrder($input);
        //       json化返回给小程序端
        header("Content-Type: application/json");
        echo $this->getJsApiParameters($order);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序众筹商品订单支付
     **************************************
     * @param Request $request
     */
    function crowd_order_index(Request $request) {
        $member_id = $request->param("member_id");//open_id
        $open_ids =Db::name("member")
            ->where("member_id",$member_id)
            ->value("member_openid");
        $order_numbers =$request->param("order_number");//订单编号
        $order_datas = Db::name("crowd_order")
            ->where("parts_order_number",$order_numbers)
            ->where("member_id", $member_id)
            ->find();
        $activity_name = $order_datas["parts_goods_name"];//名称
        $cost_moneny = $order_datas["order_real_pay"];//金额
        //         初始化值对象
        $input = new \WxPayUnifiedOrder();
        //         文档提及的参数规范：商家名称-销售商品类目
        $input->SetBody($activity_name);
        //         订单号应该是由小程序端传给服务端的，在用户下单时即生成，demo中取值是一个生成的时间戳
        //        $input->SetOut_trade_no(time().'');
        $input->SetOut_trade_no($order_numbers);
        //         费用应该是由小程序端传给服务端的，在用户下单时告知服务端应付金额，demo中取值是1，即1分钱
        $input->SetTotal_fee($cost_moneny*100);
        $return_url = config("domain.url")."crowd_order_notify";
        $input->SetNotify_url($return_url);//需要自己写的notify.php
        $input->SetTrade_type("JSAPI");
        //         由小程序端传给后端或者后端自己获取，写自己获取到的，
        $input->SetOpenid( $open_ids);
        //$input->SetOpenid($this->getSession()->openid);
        //         向微信统一下单，并返回order，它是一个array数组
        $order = \WxPayApi::unifiedOrder($input);
        //       json化返回给小程序端
        header("Content-Type: application/json");
        echo $this->getJsApiParameters($order);
    }

}