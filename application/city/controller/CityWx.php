<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/27
 * Time: 16:17
 */
namespace app\city\controller;
use app\rec\model\Wechat as WechatAll;
use think\Request;
use think\Validate;
use think\Controller;
use think\Config;
//微信授权登录 获取个人信息
class CityWx extends Controller{
    //https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxf120ba19ce55a392&redirect_uri=xxx&response_type=code&scope=xxx&state=STATE#wechat_redirect
    //微信公众平台信息（appid/secret）
    protected $sj_appid = 'wxf120ba19ce55a392';
    protected $sj_secret = '06c0107cff1e3f5fe6c2eb039ac2d0b7';

    //手机端跳转首页
    protected $app_index = 'app/wechat/user/hhr-index.html';
    //手机端跳转绑定账号页面
    protected $app_wx = 'app/wechat/user/hhr-login.html';

    //手机端跳转支付页面
    protected $app_wxpay = 'app/wechat/user/hhr-wxpay.html';

    /**
     * @function 手机端网页微信登录授权（微信公众平台微信登录授权）
     */
    public function city_accredit(){

        $redirect_uri = Config::get('web_url').'city/city_wx_code';
        $redirect_uri = urlencode($redirect_uri);
        //微信公众平台appid
        $appid = $this->sj_appid;

        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';

        header('Location:'.$url);

    }
    /**
     * 用户登录直接跳转
     * @function 获取openid
     */
    public function city_wx_code(){
        $request = Request::instance();
        $param = $request->param();
        if(empty($param['code'])){
            return returnJson(0,'code参数为空');exit;
        }
        //微信信息调用model方法
        $code = new \app\rec\model\Wechat();
        $res = $code->WxOpenid($param['code']);
        $openid_name = db('city_copartner')->where(array('openid'=> $res['openid']))->field('user_id,phone_number,judge_status')->find();
        if($openid_name){
            //更新用户信息
            db('city_copartner')->where(array('openid'=> $res['openid']))
                ->update([
                    'weixin_head'=>$res['headimgurl'],
                    'update_time'=>time()
                ]);
            if($openid_name['judge_status'] === 0){
                //跳转支付页面
                $url = Config::get('web_url').$this->app_wxpay.'?openid='.$res['openid'];
                header('Location:'.$url);
            }else{
                //跳转首页
                $url = Config::get('web_url').$this->app_index.'?user_id='.$openid_name['user_id'];
                header('Location:'.$url);
            }
        }else{
                //跳转绑定账号页面
                $url = Config::get('web_url').$this->app_wx.'?openid='.$res['openid'];
                header('Location:'.$url);
    
        }

    }

}