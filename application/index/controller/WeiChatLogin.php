<?php

/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/10/10
 * Time: 15:21
 */

namespace app\index\Controller;
use app\index\controller\Gateway ;
use app\rec\model\User as Pc_user;
use app\admin\model\Admin;
use think\Config;
use think\Db;
use think\Request;
use think\Session;
use app\common\exception\BaseException;

const BOOLEZERO = 0;
const BOOLEONE = 1;
const BOOLETWO = 2;

$wechatObj = new wechatCallbackapiTest();  
$wechatObj->WeiChatReturnUrls(); 


class WeiChatLogin extends Gateway
{
    const API_BASE            = 'https://api.weixin.qq.com/sns/';
    protected $AuthorizeURL   = 'https://open.weixin.qq.com/connect/qrconnect';
    protected $AccessTokenURL = 'https://api.weixin.qq.com/sns/oauth2/access_token';


    /**
     * PC端微信扫码登录
     * @author: GY
     * @param $name
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function WeiChatScanCodeLogin(Request $request)
    {
        if ($request->isAjax()) {
            $name = Request::instance()->param('name');
            $weixin_code = $this->getRedirectUrl();
            if ($name == 'weixin') {
                return jsonSuccess('发送成功', ['weixin_code' => $weixin_code]);
            } else {
                return jsonError('参数有误');
            }
        }
    }




    /**
     * PC端微信扫码登录回调
     * @author: GY
     * @param $name
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function WeiChatScanCodeReturnUrl()
    {
        //检验用户是否初次登陆
        //提醒用户注册账号或者绑定已有账号
        //保存用户信息
        //初始化用户登录状态
        //将unionid存session
        //跳转到新页面
        $userinfo = $this-> userinfo();
        $rest = $this->checkUser($userinfo);
        
        switch($rest)
        {
            case BOOLEZERO:
                $this ->redirect("index/index/sign_weixin"); 
                break;
            case BOOLEONE:
                $this ->redirect("index/index/my_shop");
                break;
            case BOOLETWO:
                $this->redirect("/admin");
                break;
            default:
                break;
        }
        
    }

    /**
     * PC端微信扫码登录测试回调
     * @author: GY
     * @param $name
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function WeiChatReturnUrls()
    {   
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $echostr = $_GET["echostr"];

        if($this->checkSignature($signature,$timestamp,$nonce)){
            return $echostr;
            exit;
        }else{
            echo false;exit;
        }
       
    }

    public function checkSignature($signature,$timestamp,$nonce)
    {
        $token = "zhihuiweixin";
        $tmpArr = array($token,$timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if($tmpStr == $signature){
            return true;
        }else{
            return false;
        }
    }


    


    /**
     * 得到跳转地址
     * GY
     */
    public function getRedirectUrl()
    {
        $this->switchAccessTokenURL();
        $params = [
            'appid'         => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'response_type' => $this->config['response_type'],
            'scope'         => $this->config['scope'],
            'state'         => $this->config['state'],
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params) . '#wechat_redirect';
    }

    /**
     * 获取中转代理地址
     * GY
     */
    public function getProxyURL()
    {
        $params = [
            'appid'         => $this->config['app_id'],
            'response_type' => $this->config['response_type'],
            'scope'         => $this->config['scope'],
            'state'         => $this->config['state'],
            'return_uri'    => $this->config['callback'],
        ];
        return $this->config['proxy_url'] . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     * GY
     */
    public function openid()
    {
        $this->getToken();

        if (isset($this->token['openid'])) {
            return $this->token['openid'];
        } else {
            throw new \Exception('没有获取到微信用户ID！');
        }
    }

    /**
     * 获取格式化后的用户信息
     * GY
     */
    public function userinfo()
    {
        $rsp = $this->userinfoRaw();

        $avatar = $rsp['headimgurl'];
        if ($avatar) {
            $avatar = \preg_replace('~\/\d+$~', '/0', $avatar);
        }

        $userinfo = [
            'openid'  => $this->openid(),
            'unionid' => isset($this->token['unionid']) ? $this->token['unionid'] : '',
            'channel' => 'weixin',
            'nick'    => $rsp['nickname'],
            'gender'  => $this->getGender($rsp['sex']),
            'avatar'  => $avatar,
        ];
        return $userinfo;
    }

    /**
     * 获取原始接口返回的用户信息
     * GY
     */
    public function userinfoRaw()
    {
        $this->getToken();

        return $this->call('userinfo');
    }

    /**
     * 发起请求
     * GY
     * @param string $api
     * @param array $params
     * @param string $method
     * @return array
     */
    private function call($api, $params = [], $method = 'GET')
    {
        $method = strtoupper($method);

        $params['access_token'] = $this->token['access_token'];
        $params['openid']       = $this->openid();
        $params['lang']         = 'zh_CN';

        $data = $this->$method(self::API_BASE . $api, $params);
        return json_decode($data, true);
    }

    /**
     * 根据第三方授权页面样式切换跳转地址
     * GY
     * @return void
     */
    private function switchAccessTokenURL()
    {
        if ($this->display == 'mobile') {
            $this->AuthorizeURL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
        } else {
            //微信扫码网页登录，只支持此scope
            $this->config['scope'] = 'snsapi_login';
        }
    }

    /**
     * 默认的AccessToken请求参数
     * GY
     * @return array
     */
    protected function accessTokenParams()
    {
        $params = [
            'appid'      => $this->config['app_id'],
            'secret'     => $this->config['app_secret'],
            'grant_type' => $this->config['grant_type'],
            'code'       => isset($_REQUEST['code']) ? $_REQUEST['code'] : '',
        ];
        return $params;
    }

    /**
     * 解析access_token方法请求后的返回值
     * GY
     * @param string $token 获取access_token的方法的返回值
     */
    protected function parseToken($token)
    {
        $data = json_decode($token, true);
        if (isset($data['access_token'])) {
            return $data;
        } else {
            throw new \Exception("获取微信 ACCESS_TOKEN 出错：{$token}");
        }
    }

    /**
     * 格式化性别
     *  GY
     * @param string $gender
     * @return string
     */
    private function getGender($gender)
    {
        $return = null;
        switch ($gender) {
            case 1:
                $return = 'm';
                break;
            case 2:
                $return = 'f';
                break;
            default:
                $return = 'n';
        }
        return $return;
    }


    /**
     * 检验是否绑定
     *  GY
     * @param string $gender
     * @return string
     */
    private function checkUser($userinfo)
    {
        $one = Pc_user::detail(['unionid' => $userinfo['unionid']]);
        $two = Admin::detail(['unionid' => $userinfo['unionid'],'admin_status' =>BOOLEONE]);
        $rest = BOOLEZERO;
        if ($one) {
            $rest = BOOLEONE;
        } elseif ($two) {
            $rest = BOOLETWO;
        }
        switch ($rest) {
            case BOOLEONE:
                Session::set("user", $one["id"]);
                Session::set('member', ['phone_number' => $one['phone_number']]);
                break;
            case BOOLETWO:
                Session::set("user_id", $two["id"]);
                Session::set("user_info", [$two]);
                Session::set("store_id", $two["store_id"]);
                break;
            default:
                break;
        }

        Session::set("unionid", $userinfo['unionid']);
        Session::set("sign_status", BOOLEONE);
        return $rest;
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
    
    
        // 获取access_token
        public function reindex(){
 
            $appid  = 'wx44dfb4be3e92aa9f';
            $secret = '54d5635902b52a24b01f50898b0de7b6';
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
           
            $res = $this->curl_post($url);
           
            return  $res;
     
        }
}
