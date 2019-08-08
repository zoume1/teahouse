<?php
// include_once APP_DIR.'controllers/auth/php/wxBizMsgCrypt.php';
include('../extend/SampleCode/php/wxBizMsgCrypt.php');
class DemoController extends Controller{
public $component_access_token;
    public $weixin_account;
    public $accountM;
    public $userid;
    public $token;
    public $pubs;
    public function __construct(){
        $this->userid   = 100;
        $this->token    = 'weibao';
        $this->weixin_account['appId'] = 'wx4a653e89161abf1c';
        $this->weixin_account['appSecret'] = '4d88679173c2eb375b20ed57459973be';
        $this->weixin_account['token'] = 'zhihuichacang';
        $this->weixin_account['encodingAesKey'] = 'zhihuichacangzhihuicangxuanmingkeji12345678';
        $this->weixin_account['component_verify_ticket'] = 'ticket@@@mMQLlMnPx_y9E5HWGdfJKeKJadwSFBhcrzA8eJrMSmfIZInb_8ck42Y9eitnPWnkZXlNkgR33-P3otpQ1c00-A';
    }
	//发起授权页的体验URL
    public function actionLogin()
    {
        $get_pre_auth_code = $this->get_pre_auth_code();
        $url = urlencode('http://api.ixianguor.com/demo.php?act=list');
        echo "<a href='https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=" . $this->weixin_account['appId'] . "&pre_auth_code=$get_pre_auth_code&redirect_uri=$url'><img src='https://open.weixin.qq.com/zh_CN/htmledition/res/assets/res-design-download/icon64_appwx_logo.png'></a>";
    	exit;
    }
    public function actionList(){
    	echo 'ok';exit;
    }
	
	//公众号消息与事件接收URL

    public function receive_ticket()
    {
        $msg_signature = $_REQUEST['msg_signature'];
        if ($msg_signature) {
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $toUsername = $postObj->ToUserName;
            if ($toUsername == 'gh_3c884a361561') {
                $this->encrypt($postStr);
            }else{
				$this->encrypt($postStr);	
			}
        } else {
            return;
        }
    }
    /*
    解密
    */
    public function encrypt($encyptdata)
    {
        
        
        $encodingAesKey = $this->weixin_account['encodingAesKey'];
        $token          = $this->weixin_account['token'];
        $appId          = $this->weixin_account['appId'];
        $timeStamp      = empty($_GET['timestamp']) ? "" : trim($_GET['timestamp']);
        $nonce          = empty($_GET['nonce']) ? "" : trim($_GET['nonce']);
        $msg_sign       = empty($_GET['msg_signature']) ? "" : trim($_GET['msg_signature']);
        $pc             = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
        $xml_tree       = new DOMDocument();
        $xml_tree->loadXML($encyptdata);
        $array_e  = $xml_tree->getElementsByTagName('Encrypt');
        $encrypt  = $array_e->item(0)->nodeValue;
        $format   = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
        $from_xml = sprintf($format, $encrypt);
        //第三方收到公众号平台发送的消息
        $msg      = '';
        $errCode  = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
        if ($errCode == 0) {
            
            $msgObj = json_decode(json_encode(simplexml_load_string($msg, 'SimpleXMLElement', LIBXML_NOCDATA)));
            switch ($msgObj->MsgType) {
                case "event":
                    $this->replyEventMessage($timeStamp, $nonce, $msg_sign, $msgObj->Event, $msgObj->ToUserName, $msgObj->FromUserName);
                    break;
                case "text":
                    
                    $this->processTextMessage($timeStamp, $nonce, $msg_sign, $msgObj->Content, $msgObj->ToUserName, $msgObj->FromUserName);
                    break;
                default:
                    break;
            }
            echo "success";
            
        } else {
            // file_put_contents('/app/error/cccxmlerr.txt', json_encode($errCode) . date('Y-m-d H:i:s', time()) . "/n", FILE_APPEND);
        }
    }
    
    public function processTextMessage($timeStamp, $nonce, $msg_sign, $Content, $toUserName, $fromUserName)
    {
        
        if ('TESTCOMPONENT_MSG_TYPE_TEXT' == $Content) {
            $text = $Content . '_callback';
            $this->replyTextMessage($timeStamp, $nonce, $msg_sign, $text, $toUserName, $fromUserName);
            
        } elseif (stristr($Content, "QUERY_AUTH_CODE")) {
            $textArray = explode(':', $Content);
            $this->replyApiTextMessage($timeStamp, $nonce, $msg_sign, $textArray[1], $toUserName, $fromUserName);
        }else{
			$this->replyTextMessage($timeStamp, $nonce, $msg_sign, $Content, $toUserName, $fromUserName);
		}
        
    }
    
    public function replyApiTextMessage($timeStamp, $nonce, $msg_sign, $query_auth_code, $toUserName, $fromUserName)
    {
        $url                    = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token='.$this->get_Access_token();
        $data                   = json_encode(array(
            'component_appid' => $this->weixin_account['appId'],
            'authorization_code' => $query_auth_code
        ));
        $getreplyApiTextMessage = json_decode($this->curl_get_post($url, $data));
        
        $text                    = $query_auth_code . '_from_api';
        $sfromUserName           = $getreplyApiTextMessage->authorization_info->authorizer_appid;
        $authorizer_access_token = $getreplyApiTextMessage->authorization_info->authorizer_access_token;
        $authorization           = $this->get_authorization($sfromUserName, 'customer_service', '1');
        if ($authorization == 'ok') {
            $this->processWechatTextMessage($text, $fromUserName, $authorizer_access_token);
        }
        
    }
    /*
     *推送客服回复信息
     */
    function processWechatTextMessage($text, $fromUserName, $authorizer_access_token)
    {
        $url  = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $authorizer_access_token;
        $data = json_encode(array(
            'touser' => $fromUserName,
            'msgtype' => 'text',
            'text' => array(
                'content' => $text
            )
        ));
        $getreplyApiTextMessage = json_decode($this->curl_get_post($url, $data));
    }
    /*
    获取授权
    location_report(地理位置上报选项) 0无上报 1进入会话时上报 2每5s上报
    voice_recognize（语音识别开关选项）0关 1开
    customer_service（客服开关选项）0关 1开
    */
    function get_authorization($authorizer_appid, $option_name, $option_value = '1')
    {
        $arraydata = array(
            'location_report',
            'voice_recognize',
            'customer_service'
        );
        if (in_array($option_name, $arraydata)) {
            $url  = 'https://api.weixin.qq.com/cgi-bin/component/api_set_authorizer_option?component_access_token=' . $this->get_Access_token();
            $data = json_encode(array(
                'component_appid' => $this->weixin_account['appId'],
                'authorizer_appid' => $authorizer_appid,
                'option_name' => $option_name,
                'option_value' => $option_value
            ));
            
            $query_authorization = json_decode($this->curl_get_post($url, $data));
            
            if ($query_authorization->errmsg == 'ok') {
                return 'ok';
            } else {
                return 'error';
            }
            
        } else {
            return 'error';
        }
        
    }
    
    public function replyEventMessage($timeStamp, $nonce, $msg_sign, $event, $toUserName, $fromUserName)
    {
        $content = $event . "from_callback";
        
        $this->replyTextMessage($timeStamp, $nonce, $msg_sign, $content, $toUserName, $fromUserName);
    }
    
    public function replyTextMessage($timeStamp, $nonce, $msg_sign, $content, $toUserName, $fromUserName)
    {
        $pc         = new WXBizMsgCrypt($this->weixin_account['token'], $this->weixin_account['encodingAesKey'], $this->weixin_account['appId']);
        $encryptMsg = '';
        $time       = time();
        $text       = "<xml><ToUserName><![CDATA[" . $fromUserName . "]]></ToUserName><FromUserName><![CDATA[" . $toUserName . "]]></FromUserName><CreateTime>" . $timeStamp . "</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[" . $content . "]]></Content></xml>";
        $errCode = $pc->encryptMsg($text, $time, $nonce, $encryptMsg);
        if ($errCode == 0) {
            exit($encryptMsg);
        } else {
            exit($errCode);
            
        }
        
    }
    //授权事件接收URL
    public function callback()
    {
        // 第三方发送消息给公众平台
        $encodingAesKey = $this->weixin_account['encodingAesKey'];
        $token          = $this->weixin_account['token'];
        $appId          = $this->weixin_account['appId'];
        $timeStamp      = empty($_GET['timestamp']) ? "" : trim($_GET['timestamp']);
        $nonce          = empty($_GET['nonce']) ? "" : trim($_GET['nonce']);
        $msg_sign       = empty($_GET['msg_signature']) ? "" : trim($_GET['msg_signature']);
        $encryptMsg     = file_get_contents('php://input');
        $pc             = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
        $xml_tree       = new DOMDocument();
        $xml_tree->loadXML($encryptMsg);
        $array_e  = $xml_tree->getElementsByTagName('Encrypt');
        $encrypt  = $array_e->item(0)->nodeValue;
        $format   = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
        $from_xml = sprintf($format, $encrypt);
        //第三方收到公众号平台发送的消息
        $msg      = '';
        $errCode  = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
        /*
         * <xml>
         * <AppId><![CDATA[wxb598c3f1c6995581]]></AppId>
		   <CreateTime>1446717978</CreateTime>
		<InfoType><![CDATA[unauthorized]]></InfoType>
		<AuthorizerAppid><![CDATA[wx570bc396a51b8ff8]]></AuthorizerAppid>
		</xml>
         * 
         */
        if ($errCode == 0) {
            $xml = new DOMDocument();
            $xml->loadXML($msg);
            $array_e = $xml->getElementsByTagName('ComponentVerifyTicket');
            $component_verify_ticket = $array_e->item(0)->nodeValue;
			if(isset($component_verify_ticket)){
			}
        } else {
            file_put_contents('/app/error/errCode.txt', json_encode($errCode) . date('Y-m-d H:i:s', time()) . "/n", FILE_APPEND);
        }
        exit('success');
    }
    
    public function get_authorizer_access_token()
    {
        $url                     = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=' . $this->get_Access_token();
        $data                    = '{"component_appid":"' . $this->weixin_account['appId'] . '","authorizer_appid":"' . $this->weixin_account['authorizer_appid'] . '","authorizer_refresh_token":"' . $this->weixin_account['authorizer_refresh_token'] . '"}';
        $authorization_code_data = json_decode($this->curl_get_post($url, $data));
        print_r($authorization_code_data);
    }
	//使用授权码换取公众号的授权信息第一次
    public function get_authorization_code($auth_code)
    {
        if ($auth_code) {
            $get_Access_token                 = $this->get_Access_token();
            $url                              = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=' . $get_Access_token;
            $data                             = array(
                "component_appid" => $this->weixin_account['appId'],
                "authorization_code" => $auth_code
            );
            $authorization_code_data          = json_decode($this->curl_get_post($url, json_encode($data)));
            $authorizer_appid                 = $authorization_code_data->authorization_info->authorizer_appid;
            $authorizer_access_token          = $authorization_code_data->authorization_info->authorizer_access_token;
            $authorizer_refresh_token         = $authorization_code_data->authorization_info->authorizer_refresh_token;
            $data['appid']                    = $authorizer_appid;
            $data['accessToken']              = $authorizer_access_token;
            $data['authorizer_refresh_token'] = $authorizer_refresh_token;
            $data['authorizer_time']          = time() + 7200;
            return $authorization_code_data;
        } else {
            return false;
        }
    }
    //获取授权方的账户信息（第一次）
    public function authorizer_info()
    {
        $url                 = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=' . $this->get_Access_token();
        $data                = json_encode(array(
            'component_appid' => $this->weixin_account['appId'],
            'authorizer_appid' => $this->weixin_account['authorizer_appid']
        ));
        $authorizer_info     = json_decode($this->curl_get_post($url, $data));
        $data['name']        = $authorizer_info->authorizer_info->nick_name;  //公众号名称
        $data['original_id'] = $authorizer_info->authorizer_info->user_name;  //原始id 发送接收事件必不可少的
        $data['headpic']     = $authorizer_info->authorizer_info->qrcode_url; //公众号二维码
        return $authorizer_info;
    }
    public function get_Access_token()
    {
            $url                     = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
            $data_string             = '{"component_appid":"' . $this->weixin_account["appId"] . '" ,"component_appsecret": "' . $this->weixin_account["appSecret"] . '", "component_verify_ticket": "' . $this->weixin_account["component_verify_ticket"] . '" }';
            
            $getAccessToken          = json_decode($this->curl_get_post($url, $data_string));
            
            $component_access_token  = $getAccessToken->component_access_token;
            $data['accessToken']     = $component_access_token;
            $data['accessTokenTime'] = time() + 7100;
            
        return $component_access_token;
    }
    
    public function get_pre_auth_code()
    {
            $get_Access_token = $this->get_Access_token();
            $url              = "https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=$get_Access_token";
            $data_string = '{"component_appid":"' . $this->weixin_account["appId"] . '"}';
            $get_pre_auth_code_data  = json_decode($this->curl_get_post($url, $data_string));
            $get_pre_auth_code       = $get_pre_auth_code_data->pre_auth_code;
            $data['preAuthCode']     = $get_pre_auth_code;
            $data['preAuthCodeTime'] = time() + 1100;
        return $get_pre_auth_code;
    }
    
    private function curl_get_post($url, $data = '', $request = 'GET')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);        
        
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Errno' . curl_error($curl);
        }
        curl_close($curl);
        return $tmpInfo;
    }
}
// $act = isset($_GET['act']) ? $_GET['act'] : 'index';
// $selftest = new Activity();
// if($act == 'index'){
// 	$selftest->login();
// }else if($act == 'acceptc'){
// 	$selftest->acceptc();
// }else if($act == 'test'){
// 	$selftest->test();
// }else if($act == 'list'){
// 	echo '授权完毕展示页面';
// }
?>    