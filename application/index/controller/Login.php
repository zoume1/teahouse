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

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:微信小程序授权登录
     **************************************
     */
    public function wechatlogin()
    {
        $get = input('get.');
        //获取session_key
        $params['appid'] = 'wxe81efe5d23e83c7d';
        $params['secret'] = '055128687ca3e2eb2756307cd03a5544';
        $params['js_code'] = define_str_replace($get['code']);
        $params['grant_type'] = 'authorization_code';
        $http_key = httpCurl('https://api.weixin.qq.com/sns/jscode2session', $params, 'GET');
        $session_key = json_decode($http_key, true);
        if (!empty($session_key['session_key'])) {
            $appid = $params['appid'];
            $encryptedData = urldecode($get['encryptedData']);
            $iv = define_str_replace($get['iv']);
            $errCode = decryptData($appid,$session_key['session_key'],$encryptedData, $iv);
            if(!empty($errCode)){
                $is_register =Db::name('member')->where('member_openid',$errCode['openId'])->find();
                if(empty($is_register)){
                    $data['member_openid'] =$errCode['openId'];
                    $data['member_head_img'] =$errCode['avatarUrl'];
                    $data['member_name'] =$errCode['nickName'];
                    $data['member_create_time'] =time();
                    $data['member_grade_create_time'] =time();
                    $data['member_grade_id']=1;
                    $data['member_status']=1;
                    $grade_name =Db::name('member_grade')->field('member_grade_name')->where('member_grade_id',1)->find();
                    $data['member_grade_name'] =$grade_name['member_grade_name'];
                    $bool =Db::name('member')->insertGetId($data);
                    if($bool){
                        session('member_openid',$errCode['openId']);
                        return ajax_success('返回数据成功',$errCode);
                    }else{
                        return ajax_success('返回数据失败',['status'=>0]);
                    }
                }else{
                    session('member_openid',$errCode['openId']);
                    return ajax_error('该用户已经注册过，请不要重复注册');
                }
            }else{
                return ajax_error('没有数据',['status'=>0]);
            }
//            dump($errCode); //打印获取的数据
        } else {
            return ajax_error('获取session_key失败',['status'=>0]);
        }
    }



}