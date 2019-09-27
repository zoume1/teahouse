<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/12
 * Time: 14:58
 */
namespace app\rec\model;
use think\Config;
use think\Model;

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
     * 获取微信个人信息
     * @param $code
     * @return mixed
     */
    public function WxOpenid($code)
    {
        //微信公众平台信息
        $appid = Config::get('wx_appid');
        $secret = Config::get('wx_secret');

        //获取token
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
        $data = $this->curlGet($url);
        //print_r($data);die;
        if(empty($data['access_token'])){
            return returnJson(0,'access_token错误');exit;
        }
        if(empty($data['openid'])){
            return returnJson(0,'openid错误');exit;
        }
        //拿取头像相关信息
        $token = $data['access_token'];
        $openid = $data['openid'];
        $Allurl = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$token.'&openid='.$openid.'&lang=zh_CN';
        //查询数据库是否存在
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
