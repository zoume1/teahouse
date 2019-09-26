<?php
/**
 * Created by PhpStorm.
 * User: FYK
 * Date: 2019/9/16
 * Time: 16:37
 */
namespace app\rec\model;
use think\Config;
use think\Model;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;

class WechatPay extends Model
{
    /**
     * @param $title
     * @param $detail
     * @param $no
     * @param $cost
     * @param $url
     * @param $openid
     * @return array|string|\think\response\Json
     */
    public function pay($title,$detail,$no,$cost,$url,$openid)
    {

        $attributes = [
            'trade_type'       => 'JSAPI', // JSAPI，NATIVE，APP
            'body'             => $title,//标题
            'detail'           => $detail,//详情
            'out_trade_no'     => $no,//订单号
            'total_fee'        => $cost, // 单位：分
            'notify_url'       => $url, // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'openid'           => $openid, // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
            // ...
        ];

        $options = [
            // 前面的appid什么的也得保留哦
            'app_id' => 'wx7a8782e472a6c34a',
            // payment
            'payment' => [
                'merchant_id'        => '1484093452',
                'key'                => 'zhihuichacang123456zhihuichacang',
                //如果没有退款这两个不需要
//                'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
//                'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！
                'notify_url'         => $url,       // 你也可以在下单时单独设置来想覆盖它
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
            return json(array('code'=>0,'msg'=>"发起支付失败"));exit();
        }
        $config = $payment->configForPayment($prepayId);
        // print_r($config);die;
        if($config){
            return $config;exit();
        }else{
            return json(array('code'=>0,'msg'=>"发起支付失败"));exit();
        }
    }

}
