<?php
 
namespace app\index\controller;
 
define("TOKEN", "xieqianghui1234");
 
use think\Controller;

 
class Official extends Controller{
 
    private $appId;
    private $appSecret;
 
    public function  __construct()
    {
        $this->appId = 'wxf120ba19ce55a392';  
        $this->appSecret= '06c0107cff1e3f5fe6c2eb039ac2d0b7'; 
    }
	
	 // 获取code
    public function code(){
      
         $appid  = 'wx17aaa90e1f107245';
         $redirecturl = urlencode("http://www.zhihuichacang.com/WeiChatScanCodeReturnUrl");
         $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirecturl.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
         header('location:'.$url);
    }
 
    // 通过ticket换取二维码
    public function wechatcode($ticket)
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
        
        return $this->getjson($url);
      
    }
 
    // 二维码呈现
    public function member(){
 
     
        $code  =  $_GET['code']; 
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appId.'&secret='.$this->appSecret.'&code='.$code.'&grant_type=authorization_code';
        $res = $this->curl_post($url);
          
        // 用户openid
        $openid = $res['openid'];
        $recode = (new reIndex)->reselect($openid); 
        header("Content-Type: image/jpeg;text/html; charset=utf-8");
       
        if(!$recode){
 
            $rand = mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).mt_rand(0,9);
           
            $arr = array();
            $arr = array(
                'openid' => $openid,
                'code'   => $rand,
            );
    
            (new reIndex)->readd($arr); 
   
            $res = $this->reindex();
            $access_token = $res['access_token'];
     
            // 获取ticket
            $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
            
            $list = array();
   
            $list = array(
   
               'action_name'  => 'QR_LIMIT_SCENE',
               'action_info' =>  array(
                   'scene' => array(
                      'scene_id'  => $rand,
               ),
             ), 
             
            );
         
           // 获取ticket
           $res = $this->curl_post($url,json_encode($list),'POST');
    
           // 通过ticket换取二维码
           $res = $this->wechatcode($res['ticket']);
          
           $file = fopen(dirname(dirname(dirname(__DIR__)))."/image/".$rand."upload.jpg","w");
           fwrite($file,$res);//写入
           fclose($file);//关闭
 
           $img = "http://www.neophiledesign.com/kfgzh/image/".$rand."upload.jpg";
 
        }else{
 
           $img = "http://www.neophiledesign.com/kfgzh/image/".$recode."upload.jpg";
        }
 
        echo "<img src=$img />";
 
    }
 
    // 获取access_token
    public function reindex(){
 
        $appid  = 'wx17aaa90e1f107245';
        $secret = '8bd13c549f07c6e365f818e113dee821';
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
       
        $res = $this->curl_post($url);
       
        return  $res;
 
    }
 
   
 
    // 获取用户信息及openid
    public function retest($access_token){
 
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid=OPENID&lang=zh_CN';
       
        return $this->getJson($url);
      
    }
  
    // token验证
    public function index(){
    
        if ($this->checkSignature()==false) {
    		die('非法请求');
    	}
 
    	if (isset($_GET["echostr"])) {
    		$echostr = $_GET["echostr"];
    		echo $echostr;
    		exit();
    	} else {
 
    		$this->responseMsg();
    	}
  
    }
 
    public function responseMsg(){
       
        //get post data, May be due to the different environments
        $postStr = file_get_contents("php://input");
 
        if (!empty($postStr)){
 
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
         
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            //  $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    </xml>";     
               
            if($postObj->MsgType == 'event'){
 
                $res = $this->reindex();
 
                // 获取access_token
                $access_token = $res['access_token'];
 
                $msgType = "text";
 
                // 通过二维码进入
                if(isset($postObj->EventKey) && $postObj->EventKey != ''){
 
                    $tgzid = $postObj->EventKey;
 
                    if(substr($tgzid,8)){
 
                        $retgzid = substr($tgzid,8);
 
                        $openid = (new reIndex)->rereselect($retgzid);
                        
                        if($openid != $fromUsername){
 
                            $sharesum = (new reIndex)->reupdate($openid);
 
                            $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$fromUsername.'&lang=zh_CN';
                       
                            $re = json_decode($this->getjson($url),true);
                     
                            $contentStr = '你好,'.$re['nickname'].'欢迎关注香港葵芳!';    
                            
                            $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
                            $data = array();
                            $data = array(
                                "touser"  => $openid,
                                "msgtype" => "text",
                                "text" =>
                                 array(
                                   "content" => "已成功分享给用户".$re['nickname']."已成功分享".$sharesum.'次'
        
                                  )
                            );
 
                            $res = $this->getjson($url,json_encode($data,JSON_UNESCAPED_UNICODE));
                           
                            var_dump($res);
                            
                        }
 
                        
                    }else{
 
                        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$fromUsername.'&lang=zh_CN';
                       
                        $re = json_decode($this->getjson($url),true);
                     
                        $contentStr = '你好,'.$re['nickname'].'欢迎关注香港葵芳!'; 
                      
                    }
        
 
                }else{
 
                    if($postObj->Event == 'subscribe'){
 
                       $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$fromUsername.'&lang=zh_CN';
                       
                       $re = json_decode($this->getjson($url),true);
                     
                       $contentStr = '你好,'.$re['nickname'].'欢迎关注香港葵芳!';  
                        
                    }    
 
                }
 
                   $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                  
                   echo $resultStr;
 
                }
 
          }else {
       
             exit;
          }
 
   
    }
    
    // 
    public function curl_post($url, $data=null,$method='GET', $https=true)
    {
       // 创建一个新cURL资源 
       $ch = curl_init();   
       // 设置URL和相应的选项 
       curl_setopt($ch, CURLOPT_URL, $url);  
       //要访问的网站 //启用时会将头文件的信息作为数据流输出。
       curl_setopt($ch, CURLOPT_HEADER, false);   
       //将curl_exec()获取的信息以字符串返回，而不是直接输出。 
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     
       
       if($https){ 
          
           //FALSE 禁止 cURL 验证对等证书（peer's certificate）。 
           curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
           curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
 
           //验证主机 } 
           if($method == 'POST'){ 
               curl_setopt($ch, CURLOPT_POST, true); 
               
               //发送 POST 请求  //全部数据使用HTTP协议中的 "POST" 操作来发送。 
               curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
            }    
                // 抓取URL并把它传递给浏览器 
                $content = curl_exec($ch);   
                //关闭cURL资源，并且释放系统资源 
                curl_close($ch);   
             
                return json_decode($content,true);
 
         }
    }
 
    public function getjson($url,$data=null)
    {
        $curl = curl_init();
        
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_HEADER,false);   
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
 
        //不为空，使用post传参数，否则使用get
        if($data){
 
            curl_setopt($curl,CURLOPT_POST,1);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
 
        }
 
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        
        $output = curl_exec($curl);
        curl_close($curl);
 
        return $output;
 
    }
 
    private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
 
    
}