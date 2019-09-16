<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/16
 * Time: 10:54
 */
namespace app\rec\controller;
use think\Request;
use think\Validate;
use think\Controller;
use think\Config;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;
//include __DIR__ . '/vendor/autoload.php'; // 引入 composer 入口文件


class WechatPay extends Controller{

    public function getpay()
    {

        $attributes = [
            'trade_type'       => 'JSAPI', // JSAPI，NATIVE，APP...
            'body'             => 'iPad mini 16G 白色',
            'detail'           => 'iPad mini 16G 白色',
            'out_trade_no'     => '1217752501201407033233368018',
            'total_fee'        => 1, // 单位：分
            'notify_url'       => 'http://www.zhihuichacang.com/rec/app_notice', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'openid'           => '当前用户的 openid', // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
            // ...
        ];

        $options = [
            // 前面的appid什么的也得保留哦
            'app_id' => 'wxf120ba19ce55a392',
            // payment
            'payment' => [
                'merchant_id'        => '1441082002',
                'key'                => 'zhihuichacang123456zhihuichacang',
                'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
                'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！
                'notify_url'         => 'http://www.zhihuichacang.com/rec/app_notice',       // 你也可以在下单时单独设置来想覆盖它
            ],
        ];

        $app = new Application($options);

        $payment = $app->payment;

        $order = new Order($attributes);

        $result = $payment->prepare($order); // 这里的order是上面一步得来的。 这个prepare()帮你计算了校验码，帮你获取了prepareId.省心。
        $prepayId = null;
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $prepayId = $result->prepay_id; // 这个很重要。有了这个才能调用支付。
        } else {
            return array('code'=>0,'msg'=>"发起支付失败");exit();
        }
        $config = $payment->configForPayment($prepayId);
        if($config){
            $config['status'] = 0;
            return ($config);exit();
        }else{
            return array('code'=>0,'msg'=>"发起支付失败");exit();
        }
    }

    public function app_notice(){
        echo '成功';
        //初始化微信sdk
//        $options = array(
//            'app_id' => "wx50a9b062a7759e47",
//            'secret' => "90a687567b9c7162a587e4873c92c4c9",
//            'payment' => array(
//                'merchant_id'=> '1513839901',//商户id
//                'key'=> 'T16ao205JIEE90T16ao205JIEE90T16a',//支付密匙
//                'cert_path'=> '/www/wwwroot/dpmm/Public/wxcert/apiclient_cert.pem',
//                'key_path'=> '/www/wwwroot/dpmm/Public/wxcert/apiclient_key.pem',
//                'notify_url'=>'http://www.zhihuichacang.com/rec/app_notice'
//            ),
//        );
//        $app = new Application($options);
//        $response = $app->payment->handleNotify(function($notify, $successful){
//            // 使用通知里的 "微信支付订单号transaction_id" 或者 "商户订单号out_trade_no"
//            $where = array('order_number'=>$notify->out_trade_no);
//            $field = 'id,user_id,order_number,goods_info,address_info,status,price,pay_type,create_time,update_time,cart_id';
//            $orderArr = M('order')->where($where)->field($field)->find();
//            if (empty($orderArr)) { // 如果订单不存在
//                return '订单不存在';
//            }
//            if ($orderArr['status'] == 2) {
//                return true; // 已经支付成功了就不再更新了
//            }
//            // 用户是否支付成功
//            if ($successful) {
//                // 不是已经支付状态则修改为已经支付状态
//                M('order')->where($where)->save(array('status' => 2, "update_time" => time()));
//                $goods_arr = json_decode($orderArr['goods_info'], true);
//                foreach ($goods_arr as $k => $v) {
//                    M('goods')->where(array("id" => $v['goods_id']))->setDec('number');
//                }
//                $cart_edit_list['status'] = 1;
//                $cart_edit_list['update_time'] = time();
//                M('cart')->where(array("cart_id" => array("in", $orderArr['cart_id']), "status" => 0))->save($cart_edit_list);
//            }
//            return true; // 返回处理完成
//        });
//        // 将响应输出
//        $response->send();
    }

}