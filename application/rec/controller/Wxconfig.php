<?php
namespace app\rec\controller;
use think\Request;
use think\Validate;
use think\Controller;
use think\Config;
use EasyWeChat\Foundation\Application;

class Wxconfig extends Controller{
	public function index_code(){
      
       
		$options = [
		    'debug'  => true,
		    'app_id' => 'wxf120ba19ce55a392',
		    'secret' => '06c0107cff1e3f5fe6c2eb039ac2d0b7',
		    'token'  => 'token',
		
		    // 'aes_key' => null, // 可选
		
		    'log' => [
		        'level' => 'debug',
		        'file'  => '/tmp/easywechat.log', // XXX: 绝对路径！！！！
		    ],
		
		    //...
		];
		
		$app = new Application($options);
		$server = $app->server;
		$server->setMessageHandler(function ($message) {
		    switch ($message->MsgType) {
		        case 'event':
		            return '收到事件消息';
		            break;
		        case 'text':
		            return '收到111字消息';
		            break;
		        case 'image':
		            return '收到图片消息';
		            break;
		        case 'voice':
		            return '收到语音消息';
		            break;
		        case 'video':
		            return '收到视频消息';
		            break;
		        case 'location':
		            return '收到坐标消息';
		            break;
		        case 'link':
		            return '收到链接消息';
		            break;
		        // ... 其它消息
		        default:
		            return '收到其它消息';
		            break;
		    }
		
		    // ...
		});
		$response = $server->serve();

		$response->send();
    
    }
}
