<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/19 0019
 * Time: 11:58
 */
namespace app\index\controller;
use think\Controller;
class WxTest extends Controller
{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:二维码生成
     **************************************
     * @return \think\response\View
     */
    public function index()
    {
        include('../extend/WxpayAll/lib/WxPay.Api.php');
        include('../extend/WxpayAll/example/WxPay.NativePay.php');
        include('../extend/WxpayAll/lib/WxPay.Notify.php');
        include('../extend/WxpayAll/example/log.php');
        $notify = new \NativePay();
        header("Content-type: text/html; charset=utf-8");
        ini_set('date.timezone', 'Asia/Shanghai');
        $input = new \WxPayUnifiedOrder();//统一下单
//        $config = new \WxPayConfig();//配置参数
        //$paymoney = input('post.paymoney'); //支付金额
        $paymoney = 0.01; //测试写死
        $out_trade_no = 'wxpay' . date("YmdHis"); //商户订单号(自定义)
        $goods_name = '扫码支付' . $paymoney . '元'; //商品名称(自定义)
        $input->SetBody($goods_name);//设置商品或支付单简要描述
        $input->SetAttach($goods_name);//设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
        $input->SetOut_trade_no($out_trade_no);//设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
        $input->SetTotal_fee($paymoney * 100);//金额乘以100
        $input->SetTime_start(date("YmdHis")); //设置订单生成时间,格式为yyyyMMddHHmmss
        $input->SetTime_expire(date("YmdHis", time() + 600)); //设置订单失效时间
        $input->SetGoods_tag("test"); //设置商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
        $input->SetNotify_url("http://teahouse.siring.com.cn/wxpaynotifyurl"); //回调地址
        $input->SetTrade_type("NATIVE"); //交易类型(扫码)
        $input->SetProduct_id("123456789");//设置trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
        $res = $notify->GetPayUrl($input);
        $url2 = $res["code_url"];
        return view("index",["url2"=>$url2]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:微信进行扫码
     **************************************
     */
    public function qrcode()
    {
        error_reporting(E_ERROR);
        include('../extend/WxpayAll/example/phpqrcode/phpqrcode.php');
        $url = $_GET["url2"];
        \QRcode::png($url);
    }

}