<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/19 0019
 * Time: 11:58
 */
namespace app\index\controller;
use think\Controller;

include('../extend/SampleCode/php/wxBizMsgCrypt.php');

class WxTest extends Controller
{
    private $appid = 'wx4a653e89161abf1c';            //第三方平台应用appid
    private $appsecret = '4d88679173c2eb375b20ed57459973be';     //第三方平台应用appsecret
    private $token = 'zhihuichacang';           //第三方平台应用token（消息校验Token）
    private $encodingAesKey = 'zhihuichacangzhihuicangxuanmingkeji12345678';      //第三方平台应用Key（消息加解密Key）
    private $component_ticket= 'ticket@**xv-g';   //微信后台推送的ticket,用于获取第三方平台接口调用凭据
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:微信二维码生成
     **************************************
     * @return \think\response\View
     */
    public function index()
    {
        //微信二维码
        header("Content-type: text/html; charset=utf-8");
        ini_set('date.timezone', 'Asia/Shanghai');
        include('../extend/WxpayAll/lib/WxPay.Api.php');
        include('../extend/WxpayAll/example/WxPay.NativePay.php');
        include('../extend/WxpayAll/example/log.php');
        $notify = new \NativePay();
        $input = new \WxPayUnifiedOrder();;//统一下单
//        $config = new \WxPayConfig();//配置参数
        //$paymoney = input('post.paymoney'); //支付金额
        $paymoney = 0.01; //测试写死
        $out_trade_no = 'w' . date("YmdHis"); //商户订单号(自定义)
        $goods_name = '扫码支付'; //商品名称(自定义)
        $goods_id =12345688;
        $input->SetBody($goods_name);//设置商品或支付单简要描述
        $input->SetAttach($goods_name);//设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
        $input->SetOut_trade_no($out_trade_no);//设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
        $input->SetTotal_fee($paymoney * 100);//金额乘以100
        $input->SetTime_start(date("YmdHis")); //设置订单生成时间,格式为yyyyMMddHHmmss
        $input->SetTime_expire(date("YmdHis", time() + 600)); //设置订单失效时间
        $input->SetGoods_tag("test"); //设置商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
        $input->SetNotify_url("http://teahouse.siring.com.cn/wxpaynotifyurl"); //回调地址
        $input->SetTrade_type("NATIVE"); //交易类型(扫码)
        $input->SetProduct_id($goods_id);//设置trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
        $result = $notify->GetPayUrl($input);
        $url2 = $result["code_url"];
        //支付宝二维码
        header("Content-type:text/html;charset=utf-8");
        include EXTEND_PATH . "/lib/payment/alipay/alipay.class.php";
        $int_order_id = intval(12);
        $obj_alipay = new \alipay();
        $arr_data = array(
            "return_url" => trim("http://teahouse.siring.com.cn/index.html"),
            "notify_url" => trim("http://teahouse.siring.com.cn/"),
            "service" => "create_direct_pay_by_user",
            "payment_type" => 1, //
            "seller_email" => '717797081@qq.com',
            "out_trade_no" => time(),
            "subject" => "siring支付测试", //商品订单的名称
            "total_fee" => number_format('0.01', 2, '.', ''),
        );
        if (isset($arr_order['paymethod']) && isset($arr_order['defaultbank']) && $arr_order['paymethod'] === "bankPay" && $arr_order['defaultbank'] != "") {
            $arr_data['paymethod'] = "bankPay";
            $arr_data['defaultbank'] = $arr_order['defaultbank'];
        }
        $str_pay_html = $obj_alipay->make_form($arr_data, true);
        return view("index",["url2"=>$url2,"str_pay_html"=>$str_pay_html]);
//        return view("index",["url2"=>$url2]);
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
        $qrcode = new \QRcode();
        ob_end_clean();
        $errorCorrectionLevel = 3;//容错级别
        $matrixPointSize = 6;//生成图片大小
        return $qrcode->png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
        exit();
    
    }


    
    public  function qrcode_create($url = 'http://www.baidu.com', $size = '6', $errorlevel = '3')
    {
        include('../extend/WxpayAll/example/phpqrcode/phpqrcode.php');
        $qrcode = new \QRcode();
        ob_end_clean();
        $errorCorrectionLevel = intval($errorlevel);//容错级别
        $matrixPointSize = intval($size);//生成图片大小
        return $qrcode->png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
        exit();
    }
    /**
     * lilu
     * 微信公众平台---第三方授权（小程序）
     */
    public function receive_ticket(){
            $timeStamp  = empty($_GET['timestamp'])     ? ""    : trim($_GET['timestamp']) ;
            $nonce      = empty($_GET['nonce'])     ? ""    : trim($_GET['nonce']) ;
            $msg_sign   = empty($_GET['msg_signature']) ? ""    : trim($_GET['msg_signature']) ;
            $encryptMsg = file_get_contents('php://input');
            if(!$encryptMsg){
                $encryptMsg= $GLOBALS['HTTP_RAW_POST_DATA'];
                if(!$encryptMsg){
                    $encryptMsg = input('post.');	
                }
            }
            $pc = new \WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appid);
            $xml_tree = new \DOMDocument();
            $xml_tree->loadXML($encryptMsg);
            $array_e = $xml_tree->getElementsByTagName('Encrypt');
            $encrypt = $array_e->item(0)->nodeValue;
            $format = "<xml><AppId><![CDATA[AppId]]></AppId><Encrypt><![CDATA[%s]]></Encrypt></xml>";
            $from_xml = sprintf($format, $encrypt);
             // 第三方收到公众号平台发送的消息
             $msg = '';
            $errCode = $pc->decryptMsg ($msg_sign, $timeStamp, $nonce, $encryptMsg, $msg );
            if ($errCode == 0) {
                $pp['msg']=$msg;
                db('test')->insert($pp);
                $xml = new \DOMDocument();
                $xml->loadXML($msg);
                $array_e = $xml->getElementsByTagName('ComponentVerifyTicket');
    
                $component_verify_ticket = $array_e->item(0)->nodeValue;
                $pp2['msg']=$component_verify_ticket;
                db('test')->insert($pp2);
                $da['component_verify_ticket']=$component_verify_ticket;
                $da['token_time']=time()+7000;
                 db('wx_threeopen')->where('id',1)->update($da);
                 echo "success";
            }else{
                $pp['msg']=$errCode;
                db('test')->insert($pp);
                echo "false";
            }
        }
        /**
         * 微信公众号获取token
         */
        public function token(){
            //获取随机字符串
                $echoStr = input("echostr");
                if($echoStr){
                    // 验证接口的有效性，由于接口有效性的验证必定会传递echostr 参数
                    if($this ->checkSignature()){
                            echo $echoStr;
                            exit;
                    }
                }else{
                      $this->responseMsg();
                }
        }

        protected function checkSignature()
        {
            // 微信加密签名
                $signature = input("signature");
                $timestamp = input("timestamp");//时间戳
                $nonce =input("nonce");//随机数
                $token = "zhihuichacang";  //token值，必须和你设置的一样
                $tmpArr =array($token,$timestamp,$nonce);
                sort($tmpArr,SORT_STRING);
                $tmpStr = implode($tmpArr);
                $tmpStr =sha1($tmpStr);
              if($tmpStr == $signature){
                    return true;
                }else{
                     return false;
                }
        }
        public function responseMsg()
        {
                $postStr = file_get_contents('php://input');    
                if (!empty($postStr)){
                    libxml_disable_entity_loader(true);
                    $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                    $fromUsername = $postObj->FromUserName;
                    $toUsername = $postObj->ToUserName;
                    $keyword = trim($postObj->Content);
                    $time = time();
                    $textTpl = "<xml>
                                    <ToUserName><![CDATA[%s]]></ToUserName>
                                    <FromUserName><![CDATA[%s]]></FromUserName>
                                    <CreateTime>%s</CreateTime>
                                    <MsgType><![CDATA[%s]]></MsgType>
                                    <Content><![CDATA[%s]]></Content>
                                    <FuncFlag>0</FuncFlag>
                                </xml>";
                        if(!empty( $keyword ))
                        {
                            $msgType = "text";
                            $contentStr = "Welcome to wechat world!";
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                        }else{
                            echo "Input something...";
                        }
                }else {
                    echo "";
                    exit;
                }
            }



}