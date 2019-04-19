<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/19 0019
 * Time: 11:58
 */
namespace app\index\controller;
use think\Controller;
include('../extend/WxpayAll/lib/WxPay.Api.php');
include('../extend/WxpayAll/example/WxPay.NativePay.php');
include('../extend/WxpayAll/lib/WxPay.Notify.php');
include('../extend/WxpayAll/example/log.php');
class WxTest extends Controller
{
    public function index(){
        header("Content-type:text/html;charset=utf-8");
//        require EXTEND_PATH.'/wxpay/WxPay.Api.php'; //引入微信支付
        $input = new \WxPayUnifiedOrder();//统一下单
//        $config = new \WxPayConfig();//配置参数
        //$paymoney = input('post.paymoney'); //支付金额
        $paymoney = 1; //测试写死
        $out_trade_no = 'WXPAY'.date("YmdHis"); //商户订单号(自定义)
        $goods_name = '扫码支付'.$paymoney.'元'; //商品名称(自定义)
        $input->SetBody($goods_name);
        $input->SetAttach($goods_name);
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($paymoney*100);//金额乘以100
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://www.xxx.com/wxpaynotifyurl"); //回调地址
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id("123456789");//商品id
//        $result = \WxPayApi::unifiedOrder($config, $input);
        $result = \WxPayApi::unifiedOrder($input);
        halt($result);
        if($result['result_code']=='SUCCESS' && $result['return_code']=='SUCCESS') {
            $url = $result["code_url"];
            $this->assign('url',$url);
        }else{
            $this->error('参数错误');
        }
        return view("index");
    }
}
