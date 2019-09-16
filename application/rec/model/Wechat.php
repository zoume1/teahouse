<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/12
 * Time: 14:58
 */
namespace app\rec\model;

use think\Model;
use think\Config;
class Wechat extends Model
{

    /**
     * @function 手机端网页微信登录授权（微信公众平台微信登录授权）
     */
    public function wx_accredit(){
    	
        $redirect_uri = Config::get('web_url').'rec/wx_code';
        $redirect_uri = urlencode($redirect_uri);
        //微信公众平台appid
        $appid = Config::get('wx_appid');
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';

        header('Location:'.$url);
    }
    /**
     * @function 获取openid
     */
    public function wx_code(){
        $request = Request::instance();
        $param = $request->param();
        if(empty($param['code'])){
            echo json_encode(array('code'=>0,'msg'=>'code参数为空'));exit;
        }
        
        //微信公众平台信息
        $appid = Config::get('wx_appid');
        $secret = Config::get('wx_secret');
        
        //获取token
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$param['code'].'&grant_type=authorization_code';
        $data = $this->curlGet($url);
        
        if(empty($data['access_token'])){
            echo json_encode(array('code'=>0,'msg'=>'access_token错误'));exit;
        }
        if(empty($data['openid'])){
            echo json_encode(array('code'=>0,'msg'=>'openid错误'));exit;
        }
        
        $token = $data['access_token'];
        $openid = $data['openid'];
        $Allurl = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$token.'&openid='.$openid.'&lang=zh_CN';

        $res = $this->curlGet($Allurl);

        return $res;

    }

    /**
     * @function curl以get方式连接
     */
    public function curlGet($url){
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        $data = curl_exec($curl);
        curl_close($curl);
        return json_decode($data,true);
    }
}
