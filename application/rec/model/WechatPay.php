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
        //支付相关参数
        $attributes = [
            'trade_type'       => 'JSAPI', // JSAPI，NATIVE，APP
            'body'             => $title,//标题
            'detail'           => $detail,//详情
            'out_trade_no'     => $no,//订单号
            'total_fee'        => $cost * 100, // 单位：分
            'notify_url'       => $url, // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'openid'           => $openid, // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
            // ...
        ];
        //初始化SDK相关配置
        $options = config('wechat');

        $app = new Application($options);

        $payment = $app->payment;
        //生成订单
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
