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
    private $appid = ' wx4a653e89161abf1c';            //第三方平台应用appid
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
    public function receive_ticket2(){
        $res = $this->component_detail();//获取第三方平台基础信息
        $last_time = $res['token_time'];//上一次component_access_token获取时间
        $component_access_token = $res['component_access_token'];//获取数据查询到的component_access_token
        $difference_time = $this->validity($last_time);//上一次获取时间与当前时间的时间差
        //判断component_access_token是否为空或者是否超过有效期
        if(empty($component_access_token) || $difference_time>7000){
            $component_access_token = $this->get_component_access_token_again();
        }
        return $component_access_token;
    }
        //获取第三方平台基础信息
    public function component_detail(){
        //获取
            $res = db('wx_threeopen')->where(array('id'=>1))->find();
            return $res;
        }
    //重新获取component_access_token
    public function get_component_access_token_again(){
        // $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
        $tok = $this->component_detail();
        $param ['component_appid'] = $tok['appid'];
        $param ['component_appsecret'] = $tok['appsecret'];
        $param ['component_verify_ticket'] = $tok['componentverifyticket'];
        $data =$this->post_data ( $url, $param );
        $token['component_access_token'] = $data ['component_access_token'];
        $token['token_time'] = time()+300;
        db('wx_threeopen') ->where(array('id'=>1))->update($token);
        return $data['component_access_token'];
    }
        //获取时间差
        public function validity($time){
            $current_time = time();
            $difference_time = $current_time - $time;
            return $difference_time;
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
            $pc = new \WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appid);
            $xml_tree = new \DOMDocument();
            $xml_tree->loadXML($encryptMsg);
            $array_e = $xml_tree->getElementsByTagName('Encrypt');
            $encrypt = $array_e->item(0)->nodeValue;
            // $format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
            $format = "";
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
                // DB::getDB()->delete("wechat_verifyticket",'uptime!=1');
                $da['component_verify_ticket']=$component_verify_ticket;
                $da['token_time']=$time()+300;
                 db('wx_threeopen')->where('id',1)->update($da);
    
                 echo "success";
            }else{
                $pp['msg']=$errCode;
                db('test')->insert($pp);
                // DB::getDB()->delete("wechat_verifyticket",'uptime!=1');
                // DB::getDB()->insert("wechat_verifyticket",array(
                //     'component_verify_ticket'    => $errCode,
                //     'uptime'                    => time()));
                echo "false";
                
            }
    
        }
        // public function _xmlToArr($xml) {
        //     $res = @simplexml_load_string ( $xml,NULL, LIBXML_NOCDATA );
        //     $res = json_decode ( json_encode ( $res), true );
        //     return $res;
        // }



}