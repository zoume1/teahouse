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

class ThreeTest extends Controller
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
                 exit('success');
            }else{
                //错误代码日志
                $pp['msg']=$errCode;
                db('test')->insert($pp);
                echo "false";
            }
        }
        /**
         * lilu
         * 微信第三方授权后。获取回调信息
         */
        public function callback2(){
            $encodingAesKey = $this->encodingAesKey;
            $token = $this->token;
            $appId = $this->appid;
            $timeStamp = empty ($_GET ['timestamp'] ) ? "" : trim ( $_GET ['timestamp'] );
            $nonce = empty ( $_GET['nonce'] ) ? "" : trim ( $_GET ['nonce'] );
            $msg_sign = empty ($_GET ['msg_signature'] ) ? "" : trim ( $_GET ['msg_signature'] );
            $pc = new \WXBizMsgCrypt ( $token, $encodingAesKey, $appId );
            //获取到微信推送过来post数据（xml格式）
            $postArr =file_get_contents("php://input");
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            $msg = '';
            $errCode =$pc->decryptMsg($msg_sign, $timeStamp, $nonce, $postArr,$msg);
            if ($errCode == 0) {
                //处理消息类型，并设置回复类型和内容
                   $postObj=simplexml_load_string($msg,'SimpleXMLElement',LIBXML_NOCDATA);
                   
                   //判断该数据包是否是订阅（用户关注）的事件推送
                   if(strtolower($postObj->MsgType) == 'event'){
                            //第三方平台全网发布检测发送事件消息测试
                            $toUsername= $postObj ->ToUserName;
                            if($toUsername== 'gh_3c884a361561'){
                                     $event= $postObj ->Event;
                                     $content= $event.'from_callback';
                                     $this->responseText($postObj,$content);
                            }
                            //如果是关注subscribe事件
                            if(strtolower($postObj->Event== 'subscribe')){
                                     $public_name=strval($postObj->ToUserName);
                                     $map['public_name']=$public_name;
                                    //  $cont=M('Subscribe')->where($map)->find();
                                     $cont='saomiaoshijain';
                                     //回复用户消息
                                     $content=$cont['content'];
                                     $this->responseText($postObj,$content);
                            }
                   }
                   //第三方平台全网发布检测普通文本消息测试
                   if(strtolower($postObj->MsgType) == 'text' &&trim($postObj->Content)=='TESTCOMPONENT_MSG_TYPE_TEXT'){
                            $toUsername= $postObj ->ToUserName;
                            if($toUsername== 'gh_3c884a361561'){
                                     $content= 'TESTCOMPONENT_MSG_TYPE_TEXT_callback';
                                     $this->responseText($postObj,$content);
                            }
                   }
                   //第三方平台全网发布检测返回api文本消息测试
                   if(strpos ($postObj->Content, 'QUERY_AUTH_CODE' ) !== false){
                            $query_auth_code= str_replace ( 'QUERY_AUTH_CODE:', '', $postObj->Content);
                            // $wechat= A('Wechat/Wechat');
                            $info= $this->getAuthInfo($query_auth_code);
                            $access_info=$info['authorization_info'] ['authorizer_access_token'];
                            $param['touser'] = $postObj ->FromUserName;
                            $param['msgtype'] = 'text';
                            $param['text'] ['content'] = $query_auth_code . '_from_api';
                            $url='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_info;
                            $res = $this->https_post ( $url,$param );
                   }
                   //用户发送某一图文关键字的时候，回复图文消息
                   if(strtolower($postObj->MsgType) == 'text' && trim($postObj->Content)=='图文'){
                            //这一步可从数据库中查询得到
                            $arr=array(
                                     array(
                                               'title'=>'test1',
                                               'description'=>'test1',
                                      'picUrl'=>'http://mmbiz.qpic.cn/mmbiz/mLiaE7fSUysSbbqzicX2LVsLL1HsXMRV0m6uicfiaSX9Aic43BA5vnpFOBMWAoEuaVDicoOX4HzGT8OT5QK6DRs14VkQ/0',
                                               'url'=>'https://mp.weixin.qq.com/s?__biz=MjM5NzY4MDc0MA==&tempkey=mKI6U0rlJZofvceyQdxTPAYtneMxKyhWy52ytbUZfOJzFEHMDqmYTQLQWrkrSRky&appmsgid=10000002&itemidx=1&sign=99baf31f45e2357af575c63b5b303b6a#wechat_redirect',
                                     ),
                                     array(
                                               'title'=>'test2',
                                               'description'=>'test2',
                                               'picUrl'=>'http://mmbiz.qpic.cn/mmbiz_jpg/mLiaE7fSUysTFDEZQTOvXleYwYqFN1JeLwM66Zg7dHjK3aHQxdVtwGTJgzuKJRuZCBHljIvVLkvZ2CADJ6paJYQ/0?wx_fmt=jpeg',
                                               'url'=>'https://mp.weixin.qq.com/s?__biz=MjM5NzY4MDc0MA==&tempkey=mKI6U0rlJZofvceyQdxTPDXw5wcPw4rpHzkwOv4U7kDY1V%2BUUirAB0C9oEEsX5HQB8Uv1Ut2zj3buNkRPh6KNYWVyTaxebMkb8IcD9FjNbpcqY0mdRbCxRnbIjtmNBd37cKXm3Egbo1KWdkSEy5NZg%3D%3D&chksm=315123030626aa15c3e454afbd931ec3458149b13370999b16bc72b876326977e7d68b406a8c#rd',
                                     )
                            );
                            $this->responseNews($postObj,$arr);
                   }else{
                            //当微信用户发送关键字，公众号回复对应内容
                            $public_name=strval($postObj->ToUserName);
                            $keyword=strval(trim($postObj ->Content));
                            $log['public_name']=$public_name;
                            $log['keyword']=array('like','%'.$keyword.'%');
                            // $con=M('Keyword')->where($log)->select();
                            $con[0]='微信用户发送关键字';
                            foreach($conas as $vo => $k){
                                     $conn=$con[$vo]['content'];
                            }
                            if($conn){
                                     $content=$conn;
                            }else{
                                     $lg['public_name']=$public_name;
                                     $lg['keyword']='';
                                    //  $con=M('Keyword')->where($lg)->select();
                                    $con[0]='微信用户发送关键字123';
                                     foreach($conas as $vo => $k){
                                     $conn=$con[$vo]['content'];
                            }
                            $content=$conn;
                            }
                            $this->responseText($postObj,$content);
                   }
            }
            $pp['msg']='postobj111';
                   db('test')->insert($pp);
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
        $p['msg']=$ret2.'设置域名';
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
     * 发送消息-客服消息
     */
    public function sendMessages($post_data = array(), $access_token = '')
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        httpRequest($url, 'POST', json_encode($post_data, JSON_UNESCAPED_UNICODE));
    }   
 
    
   /**
    * lilu 
    * 回复文本信息
    */
    public function responseText($postObj,$content){
        $template ="<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                </xml>";
        $fromUser = $postObj ->ToUserName;
        $toUser   = $postObj -> FromUserName;
        $time     = time();
        $msgType  = 'text';
        $res =sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
        $encodingAesKey = $this->encodingAesKey;
        $token =$this->token;
        $appId = $this->appid;
        $pc = new \WXBizMsgCrypt ($token, $encodingAesKey, $appId );
        $encryptMsg = '';
        $errCode =$pc->encryptMsg($res,$_GET ['timestamp'], $_GET ['nonce'], $encryptMsg);
        if($errCode ==0){
                $res = $encryptMsg;
        }
        echo $res;
    }
    /**
     * lilu
     * 回复图文消息
     */
    function responseNews($postObj,$arr){
        $toUser     = $postObj -> FromUserName;
        $fromUser   = $postObj -> ToUserName;
        $template  ="<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <ArticleCount>".count($arr)."</ArticleCount>
                <Articles>";
        foreach($arr as $k=>$v){
                $template.="<item>
                <Title><![CDATA[".$v['title']."]]></Title>
                <Description><![CDATA[".$v['description']."]]></Description>
                <PicUrl><![CDATA[".$v['picUrl']."]]></PicUrl>
                <Url><![CDATA[".$v['url']."]]></Url>
                </item>";
        }
        $template.="</Articles>
                </xml>";
        $time     = time();
        $msgType  = 'news';
        $res =sprintf($template,$toUser,$fromUser,$time,$msgType);
        $encodingAesKey = $this->encodingAesKey;
        $token =$this->token;
        $appId = $this->appid;
        $pc = new \WXBizMsgCrypt ($token, $encodingAesKey, $appId );
        $encryptMsg = '';
        $errCode =$pc->encryptMsg($res,$_GET ['timestamp'], $_GET ['nonce'], $encryptMsg);
        if($errCode ==0){
                $res = $encryptMsg;
        }
        echo $res;
            }

            /**
             * lilu
             * 消息接收
             * 
             * 
             * 
             * 
             */
            //公众号消息与事件接收URL

    public function callback()
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

          
            

}