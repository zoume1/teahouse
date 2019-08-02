<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/07/31
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\paginator\driver\Bootstrap;
use think\Session;
use think\View;

class  AddeOrder extends  Controller{
    
    /**
     * [增值订单数据返回]
     * 郭杨
     */    
    public function adder_place(Request $request){
        if($request->isPost()){
            $data = input();
            //商品id、规格special_id、数量order_quinity
            if(isset($data['goods_id']) && isset($data['special_id']) && isset($data['order_quantity']))
            {
                $store_id = Session::get("store_id");
                $store_data = Db::name("store")->where("id",$store_id)->find();
                $time = date("Y-m-d",time());
                $v = explode('-',$time);
                $time_second = date("H:i:s",time());
                $vs = explode(':',$time_second);
                $parts_order_number ="ZZ".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].($store_id+1001); //订单编号 

                $goods = Db::name("analyse_goods")->where("id",$data['goods_id'])->find();
                if(!empty($goods)){
                    if($data['special_id'] > 0){
                        $special = Db::name("analyse_special")->where("id",$data['special_id'])->find();
                        $price = $special['price'];
                        $goods_standard= $special['goods_standard'];
                        $stock = $special['stock'];
                    } else {
                        $price = $goods['goods_new_money'];
                        $goods_standard= 0;
                        $stock = $goods['goods_repertory'];
                    }
                    $analyse = [
                        'order_create_time' => time(),             //订单创建时间
                        'parts_order_number'=>$parts_order_number, //订单号
                        'special_id'=>$data['special_id'],         //规格id
                        'goods_id'=>$data['goods_id'],             //商品
                        'order_quantity'=>$data['order_quantity'],   //订单数量
                        'goods_money'=>$price,                     //商品单价
                        'status'=> 1,                              //支付状态
                        'goods_standard'=> $goods_standard,        //规格名称
                        'goods_type'=> $goods['goods_type'],       //商品类型 1=》物流商品  2=》虚拟商品
                        'parts_goods_name'=> $goods['goods_name'], //商品名称
                        'product_type' => $goods['product_type'],  //归属分类
                        'store_id'=>$store_id,                     //店铺id
                        'user_account_name'=>$store_data['contact_name'], //账号名字
                        'user_phone_number'=>$store_data['phone_number'], //联系方式
                        'goods_describe' => $goods['goods_describe'], //商品买点
                        'goods_image' => $goods['goods_show_image']
                    ];

                    $bool = Db::name("adder_order")->insert($analyse);
                    if($bool){
                        $restult = [
                            'order_number'=>$parts_order_number,        //订单号
                            'goods_id'=>$data['goods_id'],              //商品
                            'goods_quantity'=>$data['order_quantity'],    //订单数量
                            'amount_money'=>$price,                      //商品单价
                            'store_name'=>$store_data['store_name'],    //店铺名
                            'goods_name'=> $goods['goods_name'],        //商品名称
                            'goods_franking'=> $goods['goods_franking'],//商品统一邮费
                            'goods_type'=> $goods['goods_type'],        //商品类型 1=》物流商品  2=》虚拟商品
                            'create_time'=> time(),                     //当前时间戳 
                            'images_url'=> $goods['goods_show_image'],  //商品图片 
                            'stock' => $stock                           //库存
                        ];
                        return ajax_success("发送成功",$restult);
                    } else {
                        return ajax_error("下单失败");
                    }
                }
            } else {
                return ajax_error("参数错误");
            }

        }

    }



      /**
     **************GY*******************
     * @param Request $request
     * Notes:增值商品订购微信二维码扫码支付
     **************************************
     * @param Request $request
     */
    public function  analyse_code_pay(Request $request){
        if($request->isPost()){
            header("Content-type: text/html; charset=utf-8");
            ini_set('date.timezone', 'Asia/Shanghai');
            include('../extend/WxpayAllone/lib/WxPay.Api.php');
            include('../extend/WxpayAllone/example/WxPay.NativePay.php');
            include('../extend/WxpayAllone/example/log.php');

            $store_id = Session::get("store_id"); //店铺id
            $order_real_pay = $request->only(["order_real_pay"])["order_real_pay"];         //订单实际支付的金额(即优惠抵扣之后的价钱）
            $order_amount = $request->only(["order_amount"])["order_amount"];      //订单实际支付的金额(即优惠抵扣之后的价钱）
            $order_number = $request->only(["order_number"])["order_number"];      //订单编号
            $goods_name = $request->only(["goods_name"])["goods_name"];            //商品名称
            $order_quantity = $request->only(["order_quantity"])["order_quantity"];//商品数量
            $address_id = $request->only(["address_id"])["address_id"];            //收货地址id
            // $coupon_deductible = $request->only(["coupon_deductible"])["coupon_deductible"];     //优惠抵扣金额
            $freight = $request->only(["goods_franking"])["goods_franking"];                    //邮费

            if($address_id > 0){
                $is_address_status = Db::name("pc_store_address")->where('id',$address_id)->find(); //收货地址详细
                if (empty($is_address_status)) {
                    return ajax_error('收货地址错误',['status'=>0]);
                } else {
                    $harvest_address_city = str_replace(' ','',$is_address_status['address']);
                    $harvest_address = $harvest_address_city.$is_address_status['street']; //收货人地址
                    $harvester = $is_address_status['name'];
                    $harvester_phone_num = $is_address_status['phone'];

                    $datas = [
                        'order_real_pay' => $order_real_pay,
                        'order_amount' => $order_amount,
                        'order_quantity' => $order_quantity,
                        'harvester' => $harvester,
                        'harvest_phone_num' => $harvester_phone_num,
                        'harvester_address' => $harvest_address,
                        // 'coupon_deductible' => $coupon_deductible,
                        'freight'=>$freight
                    ];
                }
            } else {
                $datas = [
                    'order_real_pay' => $order_real_pay,
                    'order_amount' => $order_amount,
                    'order_quantity' => $order_quantity,
                    // 'coupon_deductible' => $coupon_deductible,
                    'freight'=>0
                ];
            }

            $booles = Db::name('adder_order')->where('parts_order_number',$order_number)->update($datas);
            $notify = new \NativePay();
            $input = new \WxPayUnifiedOrder();//统一下单
            $goods_id = 123456789; //商品Id
            $input->SetBody($goods_name);//设置商品或支付单简要描述
            $input->SetAttach($goods_name);//设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
            $input->SetOut_trade_no($order_number);//设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
            $input->SetTotal_fee($order_real_pay * 100);//金额乘以100
            $input->SetTime_start(date("YmdHis")); //设置订单生成时间,格式为yyyyMMddHHmmss
            $input->SetTime_expire(date("YmdHis", time() + 600)); //设置订单失效时间
            $input->SetGoods_tag("test"); //设置商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
            $input->SetNotify_url(config("domain.url")."/analyse_meal_notify"); //回调地址
            $input->SetTrade_type("NATIVE"); //交易类型(扫码)
            $input->SetProduct_id($goods_id);//设置trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
            $result = $notify->GetPayUrl($input);
            $url2 = $result["code_url"];
            if($url2){
                return ajax_success("微信二维码返回成功",["url"=>"/qrcode?url2=".$url2]);
            }else{
                return ajax_error("二维码生成失败");
            }
        }
    }


    /**
     **************GY*******************
     * @param Request $request
     * Notes:增值商品支付宝二维码支付   
     **************************************
     * @param Request $request
     */
    public function  analyse_code_alipay(Request $request){
        if($request->isPost()){
            //支付宝二维码
            $store_id = Session::get("store_id"); //店铺id
            $order_real_pay = $request->only(["order_real_pay"])["order_real_pay"];//订单实际支付的金额(即优惠抵扣之后的价钱）
            $order_amount = $request->only(["order_amount"])["order_amount"];      //订单总金额
            $order_number = $request->only(["order_number"])["order_number"];      //订单编号
            $goods_name = $request->only(["goods_name"])["goods_name"];            //商品名称
            $order_quantity = $request->only(["order_quantity"])["order_quantity"];//商品数量
            $address_id = $request->only(["address_id"])["address_id"];            //收货地址id
            // $coupon_deductible = $request->only(["coupon_deductible"])["coupon_deductible"];     //优惠抵扣金额
            $freight = $request->only(["goods_franking"])["goods_franking"];                    //邮费
    
            if($address_id > 0){
                $is_address_status = Db::name("pc_store_address")->where('id',$address_id)->find(); //收货地址详细
                if (empty($is_address_status)) {
                    return ajax_error('收货地址错误',['status'=>0]);
                } else {
                    $harvest_address_city = str_replace(' ','',$is_address_status['address']);
                    $harvest_address = $harvest_address_city.$is_address_status['street']; //收货人地址
                    $harvester = $is_address_status['name'];
                    $harvester_phone_num = $is_address_status['phone'];

                    $datas = [
                        'order_real_pay' => $order_real_pay,
                        'order_amount' => $order_amount,
                        'order_quantity' => $order_quantity,
                        'harvester' => $harvester,
                        'harvest_phone_num' => $harvester_phone_num,
                        'harvester_address' => $harvest_address,
                        // 'coupon_deductible' => $coupon_deductible,
                        'freight'=>$freight
                    ];
                }
            } else {
                $datas = [
                    'order_real_pay' => $order_real_pay,
                    'order_amount' => $order_amount,
                    'order_quantity' => $order_quantity,
                    // 'coupon_deductible' => $coupon_deductible,
                    'freight'=>0
                ];
            }

            $booles = Db::name('adder_order')->where('parts_order_number',$order_number)->update($datas);

            header("Content-type:text/html;charset=utf-8");
            include EXTEND_PATH . "/lib/payment/alipay/alipay.class.php";
            $obj_alipay = new \alipay();
            $arr_data = array(
                "return_url" => trim(config("domain.url")."admin"),
                "notify_url" => trim(config("domain.url")."/analyse_meal_notify_alipay.html"),
                "service" => "create_direct_pay_by_user", //服务参数，这个是用来区别这个接口是用的什么接口，所以绝对不能修改
                "payment_type" => 1, //支付类型，没什么可说的直接写成1，无需改动。
                "seller_email" => '717797081@qq.com', //卖家
                "out_trade_no" => $order_number, //订单编号
                "subject" => $goods_name, //商品订单的名称
                "total_fee" => number_format($order_real_pay, 2, '.', ''),
            );
            $str_pay_html = $obj_alipay->make_form($arr_data, true);
            if($str_pay_html){
                return ajax_success("二维码成功",["url"=>$str_pay_html]);
            }else{
                return ajax_error("生成二维码失败");
            }

        }
    }



        /**
     **************GY*******************
     * @param Request $request
     * Notes:增值服务余额支付
     **************************************
     * @param Request $request
     */
     public function analyse_small_pay(Request $request){
        if($request->isPost()){
            $store_id = Session::get("store_id"); //店铺id
            $password =$request->only(["password"])["password"];
            $order_real_pay = $request->only(["order_real_pay"])["order_real_pay"];//订单实际支付的金额(即优惠抵扣之后的价钱）
            $order_amount = $request->only(["order_amount"])["order_amount"];      //订单总金额
            $order_number = $request->only(["order_number"])["order_number"];      //订单编号
            $goods_name = $request->only(["goods_name"])["goods_name"];            //商品名称
            $order_quantity = $request->only(["order_quantity"])["order_quantity"];//商品数量
            $address_id = $request->only(["address_id"])["address_id"];            //收货地址id
            // $coupon_deductible = $request->only(["coupon_deductible"])["coupon_deductible"];     //优惠抵扣金额
            $freight = $request->only(["goods_franking"])["goods_franking"];       //邮费

            $store_pass = Db::name("store")
                ->where("id",$store_id)
                ->field("store_pay_pass,store_wallet")
                ->find();
            if(empty( $store_pass['store_pay_pass'])){
                exit(json_encode(array("status" => 2, "info" => "没有设置支付密码，请前往设置")));
            }
            if(md5($password) !==$store_pass["store_pay_pass"]){
                exit(json_encode(array("status" => 3, "info" => "支付密码错误,请重试")));
            }

            if($store_pass["store_wallet"] < $order_real_pay){
                exit(json_encode(array("status" => 4, "info" => "账号余额不足,请前往资金管理出充值")));
            }

            if($address_id > 0){
                $is_address_status = Db::name("pc_store_address")->where('id',$address_id)->find(); //收货地址详细
                if (empty($is_address_status)) {
                    return ajax_error('收货地址错误',['status'=>0]);
                } else {
                    $harvest_address_city = str_replace(' ','',$is_address_status['address']);
                    $harvest_address = $harvest_address_city.$is_address_status['street']; //收货人地址
                    $harvester = $is_address_status['name'];
                    $harvester_phone_num = $is_address_status['phone'];

                    $datas = [
                        'order_real_pay' => $order_real_pay,
                        'order_amount' => $order_amount,
                        'order_quantity' => $order_quantity,
                        'harvester' => $harvester,
                        'harvest_phone_num' => $harvester_phone_num,
                        'harvester_address' => $harvest_address,
                        // 'coupon_deductible' => $coupon_deductible,
                        'freight'=>$freight
                    ];
                }
            } else {
                $datas = [
                    'order_real_pay' => $order_real_pay,
                    'order_amount' => $order_amount,
                    'order_quantity' => $order_quantity,
                    // 'coupon_deductible' => $coupon_deductible,
                    'freight'=>0
                ];
            }

            $booles = Db::name('adder_order')->where('parts_order_number',$order_number)->update($datas);
            $back = [
                'pay_time' => time(),
                'status'=> 3,
                'si_pay_type'=>3,
            ];
            $rest = Db::name('adder_order')->where('parts_order_number',$order_number)->update($back);
            $store_rest = Db::name("store")
            ->where("id",$store_id)
            ->setDec('store_wallet',$order_real_pay);

            if($rest && $store_rest){
                return ajax_success("支付成功");
            } else {
                return ajax_error("支付失败");
            }
        }
    }





}