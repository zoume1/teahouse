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
use EasyWeChat\Payment\Order as WechatOrder;
use app\rec\model\WechatPay as WeiPay;
use think\Db;

class WechatPay extends Controller{

    public function get_pay($id)
    {
        //phpinfo();die;
        // 查询订单信息
        $url = 'https://www.zhihuichacang.com/rec/app_notice';
        $order = db('set_meal_order') -> getById($id);

        $pay =1;//先测试1分钱
        if(!$order)returnJson(0,'当前订单不存在');
        if($order['status'] != -1)returnJson(0,'当前订单状态异常');
        $wechatpay = new WeiPay();
        $res = $wechatpay->pay($order['goods_name'],$order['store_name'],$order['order_number'],$pay,$url,$order['openid']);

        return  $res; exit();
    }



    /**
     * 店铺支付成功微信回调
     * @throws \EasyWeChat\Core\Exceptions\FaultException
     */
    public function app_notice(){

        //初始化微信sdk

        $options = [
            'debug'  => true,
            // 前面的appid什么的也得保留哦
            'app_id' => 'wx7a8782e472a6c34a',
            // payment
            'payment' => [
                'merchant_id'        => '1484093452',
                'key'                => 'zhihuichacang123456zhihuichacang',
                'notify_url'         => 'https://www.zhihuichacang.com/rec/app_notice',       // 你也可以在下单时单独设置来想覆盖它
            ],
            'log' => [
                'level' => 'debug',
                'permission' => 0777,
                'file'  => ROOT_PATH . 'runtime/wechat/easywechat.log', // XXX: 绝对路径！！！！
            ],
        ];
        $app = new Application($options);
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