<?php

namespace app\index\controller;
use think\Controller;
use think\Request;
use  think\Db;
use think\Cache;
use app\index\controller\Order as Orderset;

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
        $order_datas = Db::name('member')->where("member_openid",$open_ids)->find();
        //         初始化值对象
        $input = new \WxPayUnifiedOrder();
        $rester = new \WxPayConfig($order_datas["store_id"]);
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
        $rester = new \WxPayConfig($order_datas["store_id"]);
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
        $open_ids = Db::name("member")
            ->where("member_id",$member_id)
            ->find();
        $order_numbers = $request->param("recharge_order_number");//订单编号
        $order_datas = Db::name("recharge_record")
            ->where("recharge_order_number",$order_numbers)
            ->where("user_id", $member_id)
            ->find();
        $activity_name ="充值";//名称
        $cost_moneny = $order_datas["recharge_money"];//金额
        //         初始化值对象
        $input = new \WxPayUnifiedOrder();
        $rester = new \WxPayConfig($open_ids["store_id"]);
        //         文档提及的参数规范：商家名称-销售商品类目
        $input->SetBody($activity_name);
        //         订单号应该是由小程序端传给服务端的，在用户下单时即生成，demo中取值是一个生成的时间戳
        //        $input->SetOut_trade_no(time().'');
        $input->SetOut_trade_no($order_numbers);
        //         费用应该是由小程序端传给服务端的，在用户下单时告知服务端应付金额，demo中取值是1，即1分钱
        $input->SetTotal_fee($cost_moneny*100);
        if($order_datas['upgrade_id'] > 0){
            $return_url = config("domain.url")."member_notify";
        } else {
            $return_url = config("domain.url")."recharge_notify";      
        }
        $input->SetNotify_url($return_url);//需要自己写的notify.php
        $input->SetTrade_type("JSAPI");
        //         由小程序端传给后端或者后端自己获取，写自己获取到的，
        $input->SetOpenid( $open_ids['member_openid']);
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
        ->find();
        $order_datas = Db::name("reward")
            ->where("order_number",$order_numbers)
            ->where("member_id", $member_id)
            ->find();
        $activity_name ="打赏";//名称
        $cost_moneny = $order_datas["money"];//金额
        //         初始化值对象
        $input = new \WxPayUnifiedOrder();
        $rester = new \WxPayConfig($open_ids["store_id"]);
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
        //         由小程序端传给后端或者后端自己获取，写自己获取到的,
        $input->SetOpenid( $open_ids['member_openid']);
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
            ->find();
        $order_numbers =$request->param("order_number");//订单编号
        $order_datas = Db::name("crowd_order")
            ->where("parts_order_number",$order_numbers)
            ->where("member_id", $member_id)
            ->find();
        $activity_name = $order_datas["parts_goods_name"];//名称
        $cost_moneny = $order_datas["order_real_pay"];//金额
        //         初始化值对象
        $input = new \WxPayUnifiedOrder();
        $rester = new \WxPayConfig($open_ids["store_id"]);
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
        $input->SetOpenid( $open_ids['member_openid']);
        //$input->SetOpenid($this->getSession()->openid);
        //         向微信统一下单，并返回order，它是一个array数组
        $order = \WxPayApi::unifiedOrder($input);
        //       json化返回给小程序端
        header("Content-Type: application/json");
        echo $this->getJsApiParameters($order);
    }


    /**
     * @param int id  订单id
     * @param int member_id    账号id
     * @param int never_time   续费到期时间
     * @param int year_number  续费年数
     * @param int series_price 续费费用
     * [店铺小程序入仓定订单续费]
     * @return 成功时返回，其他抛异常
     */
    function  series_pay(Request $request){
        if ($request->isPost()) {
            $data = input();
            if(isset($data['id']) && isset($data['never_time']) && isset($data['year_number']) && isset($data['member_id']) && isset($data['series_price'])){
                $open_ids =Db::name("member")
                ->where("member_id",$data['member_id'])
                ->find();
                $time = date("Y-m-d",time());
                $v = explode('-',$time);
                $time_second = date("H:i:s",time());
                $vs = explode(':',$time_second);
                $series_parts_number ="XF".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].($data["member_id"]+1001); //订单编号

                $series_data = array(
                    'store_house_id' => $data['id'],
                    'create_time' => time(),
                    'never_time' => strtotime($data['never_time']),
                    'year_number' => $data['year_number'],
                    'series_price' => $data['series_price'],
                    'series_parts_number' => $series_parts_number,
                    'member_id' => $data['member_id']
                );

                $bool = Db::name("series_house_order")->insert($series_data);
                if($bool){
                    //         初始化值对象
                    $activity_name = "仓库订单续费";
                    $input = new \WxPayUnifiedOrder();
                    $rester = new \WxPayConfig($open_ids["store_id"]);
                    //         文档提及的参数规范：商家名称-销售商品类目
                    $input->SetBody($activity_name);
                    //         订单号应该是由小程序端传给服务端的，在用户下单时即生成，demo中取值是一个生成的时间戳
                    //        $input->SetOut_trade_no(time().'');
                    $input->SetOut_trade_no($series_parts_number);
                    //         费用应该是由小程序端传给服务端的，在用户下单时告知服务端应付金额，demo中取值是1，即1分钱
                    $input->SetTotal_fee($data['series_price']*100);
                    $return_url = config("domain.url")."series_notify";
                    $input->SetNotify_url($return_url);//需要自己写的notify.php
                    $input->SetTrade_type("JSAPI");
                    //         由小程序端传给后端或者后端自己获取，写自己获取到的,
                    $input->SetOpenid( $open_ids['member_openid']);
                    //$input->SetOpenid($this->getSession()->openid);
                    //         向微信统一下单，并返回order，它是一个array数组
                    $order = \WxPayApi::unifiedOrder($input);
                    //       json化返回给小程序端
                    header("Content-Type: application/json");
                    echo $this->getJsApiParameters($order);
                } else {
                    return ajax_error("续费失败,请稍后再试");
                }
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }
    }


        
    /**
     * @param int $id                 订单id
     * @param int member_id           账号id
     * @param int uniacid             店铺id
     * @param float house_charges     出仓费用
     * @param int order_quantity      出仓数量
     * @param int store_unit          出仓单位
     * @param int address_id          邮寄地址id
     * [店铺小程序前端订单出仓]
     * @return 成功时返回，其他抛异常
     * 小程序端出仓订单支付
     */
    public function setContinuAtion(Request $request)
    {
        if ($request->isPost()){
            $data = input();
            $new_order = new Orderset;
            if(isset($data['uniacid']) && isset($data['member_id']) && isset($data['id']) && isset($data['house_charges']) && isset($data['order_quantity']) && isset($data['address_id'])){
                $house_order = Db::name("house_order")->where("id",'EQ',$data['id'])->find();
                if(!empty($house_order)){
                    //生成定单号
                    $time = date("Y-m-d",time());
                    $v = explode('-',$time);
                    $time_second = date("H:i:s",time());
                    $vs = explode(':',$time_second);
                    $set_parts_number ="CC".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].($data["member_id"]+1001); //订单编号

                    //对应数量和单位
                    if(!empty($house_order['special_id'])){
                        $special_data = Db::name('special') -> where("id",$house_order['special_id'])->find();
                        $unit = explode(",",$special_data['unit']);
                        $num = explode(",",$special_data['num']);
                    } else {
                        $goods_data = Db::name('goods')->where("id",$house_order['goods_id'])->find();
                        $unit = explode(",",$goods_data['unit']);
                        $num = explode(",",$goods_data['num']);
                    }
                    $key = array_search($house_order['store_unit'],$unit);
                    $store_number= $new_order->unit_calculate($unit, $num,$key,$data["order_quantity"]);
                    $out_order = array(
                        'house_order_id' => $data['id'],
                        'out_order_number' => $set_parts_number,
                        'goods_name' => $house_order['parts_order_number'],
                        'user_phone_number' => $house_order['user_phone_number'],
                        'store_house_id' => $house_order['store_house_id'],
                        'order_quantity' => $data['order_quantity'],
                        'house_charges' => $data['house_charges'],
                        'status' => 0,
                        'member_id' => $house_order['member_id'],
                        'goods_id' => $house_order['goods_id'],
                        'special_id' => $house_order['special_id'],
                        'goods_money' =>  $house_order['goods_money'],
                        'pay_time' => 0,
                        'address_id' => $data['address_id'],
                        'store_number' => $store_number,
                        'store_unit' => $house_order['store_unit'],
                        'store_id' => $data['uniacid']
                    );
                    $bool = Db::name('out_house_order')->insert($out_order);
                    if($bool){
                        return ajax_success("插入成功",$out_order);
                    } else {
                    return ajax_error("出仓失败,请稍后再试");
                }
            } else {
                return ajax_error("出仓失败,请稍后再试");
            }
        } else {
            return ajax_error("参数错误");
        }              
    }
}
    

}