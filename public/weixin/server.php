<?php

include('/www/wwwroot/teahouse/vendor/autoload.php');  // ���� composer ����ļ�
use EasyWeChat\Foundation\Application;

class wechatCallbackapiTest {
    public function responseMsg()
    {
        $options = [
            'debug'  => true,
            'app_id' => 'wx7a8782e472a6c34a',
            'secret' => 'ae3dce2528dc43edd49e571cb95b9c25',
            'token'  => 'weixin',
        
            // 'aes_key' => null, // ��ѡ
        
            'log' => [
                'level' => 'debug',
                'file'  => '/tmp/easywechat.log', // XXX: ����·����������
            ],
        
            //...
        ];
        $app = new Application($options);
        $rest = $app->server;
        $response = $rest->serve();
        // ����Ӧ���
        $response->send(); // Laravel ����ʹ�ã�return $response;
    }


}
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();
?>