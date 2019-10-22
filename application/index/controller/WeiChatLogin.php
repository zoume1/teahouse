<?php

namespace app\index\Controller;

use think\Controller;
use think\Config;
use think\Session;
use think\Model;
use think\Validate;
use app\common\exception\BaseException;
use anerg\OAuth2\OAuth;

class WeiChatLogin extends Controller
{
    protected $config;


    /**
     * PC端微信扫码登录
     * @author: GY
     * @param $name
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function WeiChatScanCodeLogin($name)
    {
        if (empty(input('get.'))) {
            /** 登录 */
            $result = $this->login($name);
            halt($result);
            $this->redirect($result);
        }
        /** 登录回调 */
        $this->WeiChatScanCodeReturnUrl($name);
        return $this->fetch('index');
    }



    /**
     * Description:  获取配置文件
     * @author: GY
     * @param $name
     */
    public function getConfig($name)
    {
        //可以设置代理服务器，一般用于调试国外平台
        //$this->config['proxy'] = 'http://127.0.0.1:1080';
        $this->config = Config::get($name);
        if ($name == 'weixin') {
            $this->config = $this->config['pc']; //微信pc扫码登录
        }
        $this->config['state'] = 'https://www.majiameng.com/login/weixin';
    }


    /**
     * Description:  登录链接分配，执行跳转操作
     * Author: GY
     * @param $name
     */
    public function login($name)
    {
        /** 获取配置 */
        $this->getConfig($name);
        /**
         * 如果需要微信代理登录，则需要：
         * 1.将wx_proxy.php放置在微信公众号设定的回调域名某个地址，如 http://www.abc.com/proxy/wx_proxy.php
         * 2.config中加入配置参数proxy_url，地址为 http://www.abc.com/proxy/wx_proxy.php
         * 然后获取跳转地址方法是getProxyURL，如下所示
         */
        //$this->config['proxy_url'] = 'http://www.abc.com/proxy/wx_proxy.php';
        $oauth = OAuth::$name($this->config);
        // if(Tool::isMobile() || Tool::isWeiXin()){
        //     /**
        //      * 对于微博，如果登录界面要适用于手机，则需要设定->setDisplay('mobile')
        //      * 对于微信，如果是公众号登录，则需要设定->setDisplay('mobile')，否则是WEB网站扫码登录
        //      * 其他登录渠道的这个设置没有任何影响，为了统一，可以都写上
        //      */
        //     $oauth->setDisplay('mobile');
        // }
        $oauth->setDisplay('mobile');
        return $oauth->getRedirectUrl();
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
    public function WeiChatScanCodeReturnUrl($name)
    {

        /** 获取配置 */
        $this->getConfig($name);
        /** 获取第三方用户信息 */
        $userInfo = \OAuth::$name($this->config)->userInfo();
        /**
         * 如果是App登录
         * $userInfo = OAuth::$name($this->config)->setIsApp()->userInfo();
         */
        //获取登录类型
        $userInfo['type'] = \tinymeng\OAuth2\Helper\ConstCode::getType($userInfo['channel']);
        var_dump($userInfo);
        die;
    }
}
