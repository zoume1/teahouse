<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/6
 * Time: 15:59
 */

namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Loader;

class Login extends Controller{

    /**
     * 注册首页
     */
    public function index(){
       return view("login_index");
    }


    public function wechatlogin()
    {
        $get = input('get.');
        //获取session_key
        $params['appid'] = 'wxe81efe5d23e83c7d';
        $params['secret'] = '09be44f61decf64d0c5b1992666e4891';
        $params['js_code'] = define_str_replace($get['code']);
        $params['grant_type'] = 'authorization_code';
        $http_key = httpCurl('https://api.weixin.qq.com/sns/jscode2session', $params, 'GET');
        $session_key = json_decode($http_key, true);
        if (!empty($session_key['session_key'])) {
            $appid = $params['appid'];
            $encryptedData = urldecode($get['encryptedData']);
            $iv = define_str_replace($get['iv']);
            $errCode = decryptData($appid,$session_key['session_key'],$encryptedData, $iv);
            return ajax_success('这是数据',$errCode);
//            if(!empty($errCode)){
//                return ajax_success('这是数据',$errCode);
//            }
//            dump($errCode); //打印获取的数据
        } else {
            return ajax_error('获取session_key失败',['status'=>0]);
            echo '获取session_key失败！';
        }
    }



}