<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/19 0019
 * Time: 11:58
 */
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Session;
use think\View;

include('../extend/SampleCode/php/wxBizMsgCrypt.php');

class WxTest extends Controller
{
    private $appid = 'wx4a653e89161abf1c';            //第三方平台应用appid
    private $appsecret = '4d88679173c2eb375b20ed57459973be';     //第三方平台应用appsecret
    private $token = 'zhihuichacang';           //第三方平台应用token（消息校验Token）
    private $encodingAesKey = 'zhihuichacangzhihuicangxuanmingkeji12345678';      //第三方平台应用Key（消息加解密Key）
    // private $component_ticket= 'ticket@@@mMQLlMnPx_y9E5HWGdfJKeKJadwSFBhcrzA8eJrMSmfIZInb_8ck42Y9eitnPWnkZXlNkgR33-P3otpQ1c00-A';   //微信后台推送的ticket,用于获取第三方平台接口调用凭据
    
    public function __construct(){
        ///获取component_ticket
        $this->component_ticket=db('wx_threeopen')->where('id',1)->value('component_verify_ticket');
    }
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
                    $encryptMsg = input('post.');	
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
                $xml = new \DOMDocument();
                $xml->loadXML($msg);
                $array_e = $xml->getElementsByTagName('ComponentVerifyTicket');
    
                $component_verify_ticket = $array_e->item(0)->nodeValue;
                $da['component_verify_ticket']=$component_verify_ticket;
                $da['token_time']=time()+7000;
                 db('wx_threeopen')->where('id',1)->update($da);
                 return "success";
            }else{
                //错误代码日志
                $pp['msg']=$errCode;
                db('test')->insert($pp);
                echo "false";
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
        /**
         * lilu
         * 微信第三方授权后。获取回调信息
         */
        public function callback(){
        // 每个授权小程序的appid，在第三方平台的消息与事件接收URL中设置了 $APPID$ 
        $authorizer_appid = input('param.appid/s'); 
        // 每个授权小程序传来的加密消息
        $postStr = file_get_contents("php://input");
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
 
            $toUserName = trim($postObj->ToUserName);
            $encrypt = trim($postObj->Encrypt);
 
            $format = "<xml><ToUserName><![CDATA[{$toUserName}]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
            $from_xml = sprintf($format, $encrypt);
 
            $inputs = array(
                'encrypt_type' => '',
                'timestamp' => '',
                'nonce' => '',
                'msg_signature' => '',
                'signature' => ''
            );
            foreach ($inputs as $key => $value) {
                $tmp = $_REQUEST[$key];
                if (!empty($tmp)){
                    $inputs[$key] = $tmp;
                }
            }
 
            // 第三方收到公众号平台发送的消息
            $msg = '';
            $timeStamp = $inputs['timestamp'];
            $msg_sign = $inputs['msg_signature'];
            $nonce = $inputs['nonce'];
            $token = $this->token;
            $encodingAesKey = $this->encodingAesKey;
            $appid = $this->appid;
            // vendor('minicrypto.wxBizMsgCrypt');
            $pc = new \WXBizMsgCrypt($token, $encodingAesKey, $appid);
            $errCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
            if ($errCode == 0) {
                $msgObj = simplexml_load_string($msg, 'SimpleXMLElement', LIBXML_NOCDATA);
                $content = trim($msgObj->Content);
 
                //第三方平台全网发布检测普通文本消息测试 
                if (strtolower($msgObj->MsgType) == 'text' && $content == 'TESTCOMPONENT_MSG_TYPE_TEXT') {
                    $toUsername = trim($msgObj->ToUserName);
                    if ($toUsername == 'gh_3c884a361561') { 
                        $content = 'TESTCOMPONENT_MSG_TYPE_TEXT_callback'; 
                        echo $this->responseText($msgObj, $content);
                    }
                }
                //第三方平台全网发布检测返回api文本消息测试 
                if (strpos($content, 'QUERY_AUTH_CODE') !== false) { 
                    $toUsername = trim($msgObj->ToUserName);
                    if ($toUsername == 'gh_3c884a361561') { 
                        $query_auth_code = str_replace('QUERY_AUTH_CODE:', '', $content);
                        $params = $this->dedeLogic->api_query_auth($query_auth_code);
                        $authorizer_access_token = $params['authorization_info']['authorizer_access_token']; 
                        $content = "{$query_auth_code}_from_api"; 
                        $this->sendServiceText($msgObj, $content, $authorizer_access_token);
                    }
                }
            }
        }
            //获取回调的信息
            $data2=input();
            $auth_code=$data2['auth_code'];     //授权码
            //根据授权码，获取用户信息
            $auth_info=$this->getAuthInfo($auth_code);
            //获取授权方的基本信息 
            $public_info= $this->getPublicInfo ( $auth_info ['authorization_info']['authorizer_appid'] );
            $data['wename'] = $public_info ['authorizer_info'] ['nick_name'];   //小程序名称
            // $data['wechat'] = $public_info ['authorizer_info'] ['alias'];       //别名
            //转换帐号类型 
            if($public_info ['authorizer_info'] ['service_type_info'] ['id'] == 2) { // 服务号 
            $data['service_type_info'] = 2; 
            }else { // 订阅号 
            $data['service_type_info'] = 0; 
            } 
            if($public_info ['authorizer_info'] ['verify_type_info'] ['id'] != - 1) { // 已认证 
            $data['service_type_info'] = 1; 
            } 
            $data['appid'] = $public_info ['authorization_info'] ['authorizer_appid'];   //appid
            $data['auth_time'] = time();                     //时间
            $data['authorizer_refresh_token'] = $auth_info ['authorization_info']['authorizer_refresh_token'];    //授权token
            $data['access_token'] = $auth_info ['authorization_info']['authorizer_access_token']; 
            $data['head_img'] = $public_info ['authorizer_info'] ['head_img'];     //头像
            $data['principal_name']=$public_info['authorizer_info']['principal_name'];  //公司名称 
            $data['store_id']=Session::get('store_id');//当前店铺的id
            $store_type=$public_info['authorizer_info']['MiniProgramInfo']['categories'][0]['first'].'-'.$public_info['authorizer_info']['MiniProgramInfo']['categories'][0]['second'];  //公司名称 
            $data['store_type']=$store_type;//当前店铺的经营类型
            //获取小程序的二维码
            $appsecret=Db::table('applet')->where('id',$data['store_id'])->value('appSecret');
            $head_pic=$this->getHeadpic($public_info ['authorization_info'] ['authorizer_appid'],$appsecret);   //小程序菊花码
            $data['qrcode_url'] = $head_pic;     //二维码地址
            //记录授权信息
            $res=db('miniprogram')->insert($data);
            //设置域名---修改服务器
            $set_service=$this->setServerDomain($data['access_token']);
            $set_yewu_service=$this->setBusinessDomain($data['access_token']);
            if($res){
                $this->success('授权成功',url('admin/Upload/auth_detail'));
            }else{
                $this->error('用户未授权或授权错误，请重新授权',url('admin/Upload/auth_pre'));

            }
        }
       /*

     * 设置小程序服务器地址，无需加https前缀，但域名必须可以通过https访问
     * @params string / array $domains : 域名地址。只接收一维数组。
     * */
    public  function setServerDomain($authorizer_access_token)
    {
        $domain = 'www.zhihuichacang.com';
        $url = "https://api.weixin.qq.com/wxa/modify_domain?access_token=".$authorizer_access_token;
        if(is_array($domain)) {
            $https = ''; $wss = '';
            foreach ($domain as $key => $value) {
                $https .= '"https://'.$value.'",';
                $wss .= '"wss://'.$value.'",';
            }
            $https = rtrim($https,',');
            $wss = rtrim($wss,',');
            $data = '{
                "action":"add",
                "requestdomain":['.$https.'],
                "wsrequestdomain":['.$wss.'],
                "uploaddomain":['.$https.'],
                "downloaddomain":['.$https.']
            }';
        } else {
            $data = '{
                "action":"add",
                "requestdomain":"https://'.$domain.'",
                "wsrequestdomain":"wss://'.$domain.'",
                "uploaddomain":"https://'.$domain.'",
                "downloaddomain":"https://'.$domain.'"
            }';
        }
        $ret2 = $this->https_post($url,$data);
        $ret = json_decode($ret2,true);
        $p['msg']=$ret2;
        db('test')->insert($p);
        if($ret['errcode'] == 0) {
            return true;
        } else {
            return false;
        }
    }
    /*
     * 设置小程序业务域名，无需加https前缀，但域名必须可以通过https访问
     * @params string / array $domains : 域名地址。只接收一维数组。
     * */
    public function setBusinessDomain($authorizer_access_token)

    {
        $domain = 'www.zhihuichacang.com';
        $url = "https://api.weixin.qq.com/wxa/setwebviewdomain?access_token=".$authorizer_access_token;
        if(is_array($domain)) {
            $https = '';
            foreach ($domain as $key => $value) {
                $https .= '"https://'.$value.'",';
            }
            $https = rtrim($https,',');
            $data = '{
                "action":"add",
                "webviewdomain":['.$https.']
            }';
        } else {
            $data = '{
                "action":"add",
                "webviewdomain":"https://'.$domain.'"
            }';
        }
        $ret2 = $this->https_post($url,$data);
        $ret = json_decode($ret2,true);
        $p['msg']=$ret2;
        db('test')->insert($p);
        if($ret['errcode'] == 0) {
            return true;
        } else {
            return false;
        }
    }
        /**
         * lilu
         * 获取微信公众号接口调用凭据和授权信
         */
        public function getAuthInfo($auth_code) { 
                $component_access_token = $this ->get_component_access_token(); 
                $url ="https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=".$component_access_token; 
                $param = '{
                    "component_appid":"'.$this->appid.'" ,
                    "authorization_code": "'.$auth_code.'"
                }';
                $info = json_decode($this->https_post ( $url, $param ),true);
               
                return $info; 
            }
        /*
            * 获取第三方平台access_token
            * 注意，此值应保存，代码这里没保存
            */
            private function get_component_access_token()
            {
                $url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
                $data = '{
                    "component_appid":"'.$this->appid.'" ,
                    "component_appsecret": "'.$this->appsecret.'",
                    "component_verify_ticket": "'.$this->component_ticket.'"
                }';
                $ret = json_decode($this->https_post($url,$data),true);
                if($ret['component_access_token']) {
                    return $ret['component_access_token'];
                } else {
                    return false;
                }
            }

    /*
    *  第三方平台方获取预授权码pre_auth_code
    */
    private function get_pre_auth_code()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=".$this->get_component_access_token();
        $data = '{"component_appid":"'.$this->appid.'"}';
        $ret = json_decode($this->https_post($url,$data),true);
        if($ret['pre_auth_code']) {
            return $ret['pre_auth_code'];
        } else {
            return false;
        }
    }
        /*
        * 发起POST网络提交
        * @params string $url : 网络地址
        * @params json $data ： 发送的json格式数据
        */
        private function https_post($url,$data)
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            if (!empty($data)){
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        }
        /*
            * 发起GET网络提交
            * @params string $url : 网络地址
            */
        private function https_get($url)
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); 
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); 
            curl_setopt($curl, CURLOPT_HEADER, FALSE) ; 
            curl_setopt($curl, CURLOPT_TIMEOUT,60);
            if (curl_errno($curl)) {
                return 'Errno'.curl_error($curl);
            }
            else{$result=curl_exec($curl);}
            curl_close($curl);
            return $result;
        }
        /**
         * lilu
         * 获取授权方的基本信息 
         */
        public function getPublicInfo($authorizer_appid) { 
            $component_access_token =$this->get_component_access_token(); 
            $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token='.$component_access_token; 
            $param = '{
                "component_appid":"'.$this->appid.'" ,
                "authorizer_appid": "'.$authorizer_appid.'"
            }';
            // $param ['component_appid'] = '第三方平台appid '; 
            // $param ['authorizer_appid'] =$authorizer_appid; 
            $data = $this->https_post( $url, $param ); 
            $data=json_decode($data,true);
            return $data; 
            }
            /**
             * lilu
             * 获取小程序二维码
             */
            public function getHeadpic($appid,$appsecret){
                $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
                $info = json_decode($this->https_get($url),true);     //获取access_token
                $pp['msg']=$this->https_get($url);
                db('test')->insert($pp);
                $url2 = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$info['access_token'];
                $data = '{
                    "path":"/pages/logs/logs" 
                }';
                $ret = $this->https_post($url2,$data);
                if($ret) {
                    return $ret;
                } else {
                    return false;
                }
            }

           

             /**
     * 构造函数
     * @param String $save_name 保存图片名称
     * @param String $save_dir 保存路径名称
     */
    public function check_all($save_name, $save_dir) {
        //设置保存图片名称，若未设置，则随机产生一个唯一文件名
        $this->save_name = $save_name ? $save_name : md5 ( mt_rand (), uniqid () );
        //设置保存图片路径，若未设置，则使用年/月/日格式进行目录存储
        $this->save_dir =  $save_dir ? self::ROOT_PATH .$save_dir : self::ROOT_PATH .date ( 'Y/m/d' );
        //创建文件夹
        @$this->create_dir ( $this->save_dir );
        //设置目录+图片完整路径
        $this->save_fullpath = $this->save_dir . '/' . $this->save_name;
    }
    //兼容PHP4
    public function image() {
        $this->check_all ($save_name );
    }

    public function stream2Image() {
        //二进制数据流
        $data = file_get_contents ( 'php://input' ) ? file_get_contents ( 'php://input' ) : gzuncompress ( $GLOBALS ['HTTP_RAW_POST_DATA'] );
        //数据流不为空，则进行保存操作
        if (! empty ( $data )) {
            //创建并写入数据流，然后保存文件
            if (@$fp = fopen ( $this->save_fullpath, 'w+' )) {
                fwrite ( $fp, $data );
                fclose ( $fp );
                $baseurl = "http://" . $_SERVER ["SERVER_NAME"] . ":" . $_SERVER ["SERVER_PORT"] . dirname ( $_SERVER ["SCRIPT_NAME"] ) . '/' . $this->save_name;
                if ( $this->getimageInfo ( $baseurl )) {
                    echo $baseurl;
                } else {
                    echo ( self::NOT_CORRECT_TYPE  );
                }
            } else {

            }
        } else {
            //没有接收到数据流
            echo ( self::NO_STREAM_DATA );
        }
    }
    /**
     * 创建文件夹
     * @param String $dirName 文件夹路径名
     */
    public function create_dir($dirName, $recursive = 1,$mode=0777) {
        ! is_dir ( $dirName ) && mkdir ( $dirName,$mode,$recursive );
    }
    /**
     * 获取图片信息，返回图片的宽、高、类型、大小、图片mine类型
     * @param String $imageName 图片名称
     */
    public function getimageInfo($imageName = '') {
        $imageInfo = getimagesize ( $imageName );
        if ($imageInfo !== false) {
            $imageType = strtolower ( substr ( image_type_to_extension ( $imageInfo [2] ), 1 ) );
            $imageSize = filesize ( $imageInfo );
            return $info = array ('width' => $imageInfo [0], 'height' => $imageInfo [1], 'type' => $imageType, 'size' => $imageSize, 'mine' => $imageInfo ['mine'] );
        } else {
            //不是合法的图片
            return false;
        }

    }


    /**
     * 自动回复文本
     */
    public function responseText($object = '', $content = '')
    {
        if (!isset($content) || empty($content)){
            return "";
        }
 
        $xmlTpl =   "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                    </xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
 
        return $result;
    }
 
    /**
     * 发送文本消息
     */
    public function sendServiceText($object = '', $content = '', $access_token = '')
    {
        /* 获得openId值 */
        $openid = (string)$object->FromUserName;
        $post_data = array(
            'touser'    => $openid,
            'msgtype'   => 'text',
            'text'      => array(
                            'content'   => $content
                        )
        );
        $this->sendMessages($post_data, $access_token);
    } 
    
    /**
     * 发送消息-客服消息
     */
    public function sendMessages($post_data = array(), $access_token = '')
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        httpRequest($url, 'POST', json_encode($post_data, JSON_UNESCAPED_UNICODE));
    }   
 
    /**
     * CURL请求
     * @param $url 请求url地址
     * @param $method 请求方法 get post
     * @param null $postfields post数据数组
     * @param array $headers 请求header信息
     * @param bool|false $debug  调试开启 默认false
     * @return mixed
     */
    function httpRequest($url, $method="GET", $postfields = null, $headers = array(), $debug = false) {
        $method = strtoupper($method);
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
        curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        switch ($method) {
            case "POST":
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
                }
                break;
            default:
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
                break;
        }
        $ssl = preg_match('/^https:\/\//i',$url) ? TRUE : FALSE;
        curl_setopt($ci, CURLOPT_URL, $url);
        if($ssl){
            curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
        }
        //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
        curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
        /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
        $response = curl_exec($ci);
        $requestinfo = curl_getinfo($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);
            echo "=====info===== \r\n";
            print_r($requestinfo);
            echo "=====response=====\r\n";
            print_r($response);
        }
        curl_close($ci);
        return $response;
        //return array($http_code, $response,$requestinfo);
    }

          
            

}