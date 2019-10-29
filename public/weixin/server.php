<?php

include('/www/wwwroot/teahouse/vendor/autoload.php');  // 引入 composer 入口文件
use EasyWeChat\Foundation\Application;

class wechatCallbackapiTest {
    public function responseMsg()
    {
        $options = [
            'debug'  => true,
            'app_id' => 'wx7a8782e472a6c34a',
            'secret' => 'ae3dce2528dc43edd49e571cb95b9c25',
            'token'  => 'weixin',
        
            // 'aes_key' => null, // 可选
        
            'log' => [
                'level' => 'debug',
                'file'  => '/tmp/easywechat.log', // XXX: 绝对路径！！！！
            ],
        
            //...
        ];
        $app = new Application($options);
        $rest = $app->server;
        $response = $rest->serve();
        // 将响应输出
        $response->send(); // Laravel 里请使用：return $response;
    }


}
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();
?>