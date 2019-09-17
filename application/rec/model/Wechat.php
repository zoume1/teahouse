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

}
