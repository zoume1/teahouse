<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/18 0018
 * Time: 10:02
 */
namespace  app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Session;

class Register extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:PC注册验证码，修改手机号验证码
     **************************************
     * @param Request $request
     */
    public function PcsendMobileCode(Request $request)
    {
        //接受验证码的手机号码
        if ($request->isPost()) {
            $mobile = $request->only(['mobile'])['mobile'];
            $pattern = '/^1[3456789]\d{9}$/';
            if(preg_match($pattern,$mobile)) {
                $res =  Db::name('pc_user')
                    ->field('phone_number')
                    ->where('phone_number',$mobile)
                    ->select();
                if($res){
                    return ajax_error('此手机号已经注册',['status'=>0]);
                }
                $mobileCode = rand(100000, 999999);
                $arr = json_decode($mobile, true);
                $mobiles = strlen($arr);
                if (isset($mobiles) != 11) {
                    return ajax_error("手机号码不正确",['status'=>0]);
                }
                //存入session中
                if (strlen($mobileCode)> 0) {
                    session('mobileCodes',$mobileCode);
                    $_SESSION['mobiles'] = $mobile;
                }
                $content = "尊敬的用户，您本次验证码为{$mobileCode}，十分钟内有效";
                $url = "http://120.26.38.54:8000/interface/smssend.aspx";
                $post_data = array("account" => "chacang", "password" => "123qwe", "mobile" => "$mobile", "content" => $content);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                $output = curl_exec($ch);
                curl_close($ch);
                if ($output) {
                    return ajax_success("发送成功", $output);
                } else {
                    return ajax_error("发送失败",['status'=>0]);
                }
            }else{
                return ajax_error("请填写正确的手机号",['status'=>0]);
            }
        }
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:PC注册操作
     **************************************
     * @param Request $request
     */
    public function  doRegByPhone(Request $request){
        if($request->isPost())
        {
            $mobile = trim($_POST['mobile']);
            $is_reg =Db::name("pc_user")->where("phone_number",$mobile)->find();
            if(!empty($is_reg)){
                return ajax_error("此手机已注册，可以直接登录");
            }
            $code = trim($_POST['mobile_code']);
            $password =trim($_POST['password']);
            $confirm_password =trim($_POST['confirm_password']);
            $create_time =date('Y-m-d H:i:s');
            if($password !==$confirm_password ){
                return ajax_error('两次密码不相同');
            }
            if (strlen($mobile) != 11 || substr($mobile, 0, 1) != '1' || $code == '') {
                return ajax_error("参数不正确");
            }
            if (session('mobileCodes') != $code || $mobile != $_SESSION['mobiles']) {
                return ajax_error("验证码不正确");
            } else {
                $passwords =password_hash($password,PASSWORD_DEFAULT);
                $datas =[
                    'phone_number'=>$mobile,
                    'password'=>$passwords,
                    'create_time'=>strtotime($create_time),
                    "status"=>1,
                ];
                    $res =Db::name('pc_user')->insertGetId($datas);
                    if($res){
                        //注册成功
                        return ajax_success('注册成功',$res);
                    }else{
                        return ajax_error('请重新注册',['status'=>0]);
                    }
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺支付密码获取验证码
     **************************************
     */
//    public function StoreMobile(Request $request){
//        if($request->isPost()){
//
//        }
//    }


}