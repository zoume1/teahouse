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
use think\Cache;

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
            $store_id = $request->only(['uniacid'])['uniacid'];
            $mobile = $request->only(['mobile'])['mobile'];
            $pattern = '/^1[3456789]\d{9}$/';
            if(preg_match($pattern,$mobile)) {
                $res =  Db::name('member')
                    ->field('member_phone_num')
                    ->where("store_id","EQ",$store_id)
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
                    Cache::set('mobileCode',$mobileCode,3600);
                    Cache::set('mobile',$mobile,3600);
//                    session('mobileCode',$mobileCode);
//                    $_SESSION['mobile'] = $mobile;
                }
                $content = "【智慧茶仓】尊敬的用户，您本次验证码为{$mobileCode}，十分钟内有效";
                $output = sendMessage($content,$mobile);
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
            $member_id = $request->only(['member_id'])['member_id'];
            $mobile =Db::name("member")->where("member_id",$member_id)->value("member_phone_num");
            $pattern = '/^1[3456789]\d{9}$/';
            if(preg_match($pattern,$mobile)) {
                $mobileCode = rand(100000, 999999);
                $arr = json_decode($mobile, true);
                $mobiles = strlen($arr);
                if (isset($mobiles) != 11) {
                    return ajax_error("手机号码不正确",['status'=>0]);
                }
                //存入session中
                if (strlen($mobileCode)> 0) {
                    Cache::set('mobileCode',$mobileCode,3600);
                    Cache::set('mobile',$mobile,3600);
//                    session('mobileCode',$mobileCode);
//                    $_SESSION['mobile'] = $mobile;
                }
                $content = "【智慧茶仓】尊敬的用户，您本次验证码为{$mobileCode}，十分钟内有效";
                $output = sendMessage($content,$mobile);
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
     * Notes:忘记支付密码（找回密码验证码）
     **************************************
     */
    public function sendMobileCodePay(Request $request){
        //接受验证码的手机号码
        if ($request->isPost()) {
            $store_id = $request->only(['uniacid'])['uniacid'];
            $member_id = $request->only(['member_id'])['member_id'];
            $mobile =  Db::name('member')
            ->field('member_phone_num')
            ->where("store_id","EQ",$store_id)
            ->where('member_id',$member_id)
            ->value('member_phone_num');
            
            if(!empty($mobile)){
                $mobileCode = rand(100000, 999999);
                $arr = json_decode($mobile, true);
                $mobiles = strlen($arr);
                //存入session中
                if (strlen($mobileCode)> 0) {
                    Cache::set('mobileCode',$mobileCode,3600);
                    Cache::set('mobile',$mobile,3600);
//                    session('mobileCode',$mobileCode);
//                    $_SESSION['mobile'] = $mobile;
                }
                $content = "【智慧茶仓】尊敬的用户，您本次验证码为{$mobileCode}，十分钟内有效";
                $output = sendMessage($content,$mobile);
                if ($output) {
                    return ajax_success("发送成功", $output);
                } else {
                    return ajax_error("发送失败",['status'=>0]);
                }
            }else{
                return ajax_error("请绑定您的手机号",['status'=>0]);
            }
        }
    }

}