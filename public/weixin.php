<?php  
/** 
  * wechat php test 
  */  
  
//define your token  
define("TOKEN", "zhihuiweixin");  
$wechatObj = new wechatCallbackapiTest();  
$wechatObj->valid();
use think\Db;
class wechatCallbackapiTest  
{  
    public function valid()  
    {  
        db('admin')->insert(['account' => '13456789','status'=>5, 'sex' => 2,'role_id' => 1]);
        $echoStr = $_GET["echostr"];  
  

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
  
    public function responseMsg()  
    {  
        // //get post data, May be due to the different environments  
        // $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];  
  
        // //extract post data  
        // if (!empty($postStr)){  
                  
        //         $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);  
        //         $fromUsername = $postObj->FromUserName;  
        //         $toUsername = $postObj->ToUserName;  
        //         $keyword = trim($postObj->Content);  
        //         $time = time();  
        //         $textTpl = "<xml>  
        //                     <ToUserName><![CDATA[%s]]></ToUserName>  
        //                     <FromUserName><![CDATA[%s]]></FromUserName>  
        //                     <CreateTime>%s</CreateTime>  
        //                     <MsgType><![CDATA[%s]]></MsgType>  
        //                     <Content><![CDATA[%s]]></Content>  
        //                     <FuncFlag>0</FuncFlag>  
        //                     </xml>";               
        //         if(!empty( $keyword ))  
        //         {  
        //             $msgType = "text";  
        //             $contentStr = "Welcome to wechat world!";  
        //             $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);  
        //             echo $resultStr;  
        //         }else{  
        //             echo "Input something...";  
        //         }  
  
        // }else {  
        //     echo "";  
        //     exit;  
        // }  
        //get post data, May be due to the different environments
        $postStr = file_get_contents("php://input");
 
        if (!empty($postStr)){
 
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
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
                        db('admin')->insert(['account' => '13456789','status'=>5, 'sex' => 2,'role_id' => 1]);
 
                        // $openid = (new reIndex)->rereselect($retgzid);
                        
                        // if($openid != $fromUsername){
                        //     db('pc_user')->insert(['phone_number' => '13456789','status'=>2 ]);
 
                        //     $sharesum = (new reIndex)->reupdate($openid);
 
                        //     $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$fromUsername.'&lang=zh_CN';
                       
                        //     $re = json_decode($this->getjson($url),true);
                     
                        //     $contentStr = '你好,'.$re['nickname'].'欢迎关注皮皮郭!';    
                            
                        //     $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
                        //     $data = array();
                        //     $data = array(
                        //         "touser"  => $openid,
                        //         "msgtype" => "text",
                        //         "text" =>
                        //          array(
                        //            "content" => "已成功分享给用户".$re['nickname']."已成功分享".$sharesum.'次'
        
                        //           )
                        //     );
 
                        //     $res = $this->getjson($url,json_encode($data,JSON_UNESCAPED_UNICODE));
                           
                        //     var_dump($res);
                            
                        // }
 
                        
                    }else{
 
                        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$fromUsername.'&lang=zh_CN';
                       
                        $re = json_decode($this->getjson($url),true);
                        db('admin')->insert(['account' => '13456789','status'=>3, 'sex' => 2,'role_id' => 1]);
                        $contentStr = '你好,'.$re['nickname'].'欢迎关注皮皮郭!'; 
                      
                    }
        
 
                }else{
 
                    if($postObj->Event == 'subscribe'){
                        db('admin')->insert(['account' => '13456789','status'=>4, 'sex' => 2,'role_id' => 1]);
                       $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$fromUsername.'&lang=zh_CN';
                       
                       $re = json_decode($this->getjson($url),true);
                     
                       $contentStr = '你好,'.$re['nickname'].'欢迎关注皮皮郭!';  
                        
                    }    
 
                }
 
                   $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                  
                   echo $resultStr;
 
                }
 
          }else {
            db('admin')->insert(['account' => '13456789','status'=>5, 'sex' => 2,'role_id' => 1]);       
             exit;
          }

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
  
  
    // 获取access_token
    public function reindex(){
 
        $appid  = 'wx44dfb4be3e92aa9f';
        $secret = '54d5635902b52a24b01f50898b0de7b6';
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
       
        $res = $this->curl_post($url);
       
        return  $res;
 
    }


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
}
?>  