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
use app\rec\model\WechatPay as WeiPay;

//微信支付
class WechatPay extends Controller{

    public function get_pay()
    {
    	 //phpinfo();die;
    	$id = 972;
        // 查询订单信息
        $url = 'http://www.zhihuichacang.com/rec/app_notice';
        $openid = 'oYb9gwLrKCi2IxzBQ-GQrM5MSRfM';
        $order = db('set_meal_order') -> getById($id);
        // if(!$order)returnJson(0,'当前订单不存在');
        // if($order['status'] != -1)returnJson(0,'当前订单状态异常');
        $wechatpay = new WeiPay();
        $res = $wechatpay->pay($order['goods_name'],$order['store_name'],$order['order_number'],$order['amount_money'],$url,$openid);

        return  $res; exit();
    }

    public function app_notice2(){
        //初始化微信sdk
        $options = [
        	
            // 前面的appid什么的也得保留哦
            'app_id' => 'wxf120ba19ce55a392',
            // payment
            'payment' => [
                'merchant_id'        => '1441082002',
                'key'                => 'zhihuichacang123456zhihuichacang',
                // 'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
                // 'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！
                'notify_url'         => 'http://www.zhihuichacang.com/rec/app_notice',       // 你也可以在下单时单独设置来想覆盖它
            ],
           
        ];
		
        $app = new Application($options);
		
        $payment = $app->payment;
		// print_r($payment);die;
        $order = new Order($attributes);
		// print_r($order);die;
        $result = $payment->prepare($order); // 这里的order是上面一步得来的。 这个prepare()帮你计算了校验码，帮你获取了prepareId.省心。
        $prepayId = null;
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $prepayId = $result->prepay_id; // 这个很重要。有了这个才能调用支付。
        } else {
            return json(array('code'=>0,'msg'=>"发起支付失败"));exit();
        }
        $config = $payment->configForPayment($prepayId);
        if($config){
            $config['status'] = 0;
            return  json(($config));exit();
        }else{
            return  json(array('code'=>0,'msg'=>"发起支付失败"));exit();
        }

    }


    //微信回调
    public function app_notice(){
        //初始化微信sdk
        $options = [
            // 前面的appid什么的也得保留哦
            'app_id' => 'wx7a8782e472a6c34a',
            // payment
            'payment' => [
                'merchant_id'        => '1484093452',
                'key'                => 'zhihuichacang123456zhihuichacang',
//                'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
//                'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！
                'notify_url'         => 'http://www.zhihuichacang.com/rec/app_notice',       // 你也可以在下单时单独设置来想覆盖它
            ],
        ];
        $app = new Application($options);
        $response = $app->payment->handleNotify(function($notify, $successful){
            // 使用通知里的 "微信支付订单号transaction_id" 或者 "商户订单号out_trade_no"
            $where = array('order_number'=>$notify->out_trade_no);

            $orderArr = db('tb_set_meal_order')->where($where)->field('status')->find();
            if (empty($orderArr)) { // 如果订单不存在
                returnJson(0,'订单不存在');
//                return '订单不存在';
            }
            if ($orderArr['status'] == 1) {
                return true; // 已经支付成功了就不再更新了
            }
            // 用户是否支付成功
            if ($successful) {
                // 不是已经支付状态则修改为已经支付状态
                db('tb_set_meal_order')->where($where)->update(array('status' => 1, "pay_time" => time()));

            }
            return true; // 返回处理完成
        });
        // 将响应输出
        $response->send();
    }

}