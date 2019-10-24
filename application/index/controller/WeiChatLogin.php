<?php

/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/10/10
 * Time: 15:21
 */

namespace app\index\Controller;

use app\rec\model\User as Pc_user;
use app\admin\model\Admin;
use app\index\controller\Gateway ;
use think\Config;
use think\Request;
use think\Session;
use app\common\exception\BaseException;

const BOOLEZERO = 0;
const BOOLEONE = 1;
const BOOLETWO = 2;



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
                $this ->redirect(url("index/index/sign_in"));
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
        $two = Admin::detail(['unionid' => $userinfo['unionid']]);
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
}
