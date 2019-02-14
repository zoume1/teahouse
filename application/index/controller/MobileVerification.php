<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/14 0014
 * Time: 17:21
 */
namespace  app\index\controller;


use think\Controller;
use  think\Db;
use  think\Request;

class  MobileVerification extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:这是新绑定手机验证码验证
     **************************************
     * @param Request $request
     */
    public function sendMobileCode(Request $request)
    {
        //接受验证码的手机号码
        if ($request->isPost()) {
            $mobile = $request->only(['mobile'])['mobile'];
            $pattern = '/^1[3456789]\d{9}$/';
            if(preg_match($pattern,$mobile)) {
                $res =  Db::name('member')
                    ->field('member_phone_num')
                    ->where('member_phone_num',$mobile)
                    ->select();
                if($res){
                    return ajax_error('此手机号已经被绑定',['status'=>0]);
                }
                $mobileCode = rand(100000, 999999);
                $arr = json_decode($mobile, true);
                $mobiles = strlen($arr);
                if (isset($mobiles) != 11) {
                    return ajax_error("手机号码不正确",['status'=>0]);
                }
                //存入session中
                if (strlen($mobileCode)> 0) {
                    session('mobileCode',$mobileCode);
                    $_SESSION['mobile'] = $mobile;
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
     * Notes:这是银行卡绑定时需手机验证码验证
     **************************************
     * @param Request $request
     */
    public function sendMobileCodeBank(Request $request)
    {
        //接受验证码的手机号码
        if ($request->isPost()) {
            $mobile = $request->only(['mobile'])['mobile'];
            $pattern = '/^1[3456789]\d{9}$/';
            if(preg_match($pattern,$mobile)) {
                $res =  Db::name('member')
                    ->field('member_phone_num')
                    ->where('member_phone_num',$mobile)
                    ->select();
                if($res){
                    return ajax_error('此手机号已经被绑定',['status'=>0]);
                }
                $mobileCode = rand(100000, 999999);
                $arr = json_decode($mobile, true);
                $mobiles = strlen($arr);
                if (isset($mobiles) != 11) {
                    return ajax_error("手机号码不正确",['status'=>0]);
                }
                //存入session中
                if (strlen($mobileCode)> 0) {
                    session('mobileCode',$mobileCode);
                    $_SESSION['mobile'] = $mobile;
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

}