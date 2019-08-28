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
use app\index\controller\Login as Loging;

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
     * Notes:PC注册操作
     **************************************
     * @param Request $request
     */
    public function  doRegByPhone(Request $request){
        if($request->isPost())
        {
            $my_invitation = new Loging();
            $re = $my_invitation->memberCode();
            $mobile = trim($_POST['mobile']);
            $is_reg =Db::name("pc_user")->where("phone_number",$mobile)->find();
            if(!empty($is_reg)){
                return ajax_error("此手机已注册，可以直接登录");
            }
            $code = trim($_POST['mobile_code']);
            $password =trim($_POST['password']);
            $confirm_password =trim($_POST['confirm_password']);
            $invitation = trim($_POST['invitation']);            
            $create_time = date('Y-m-d H:i:s');
            

            if($password !==$confirm_password ){
                return ajax_error('两次密码不相同');
            }
            if (strlen($mobile) != 11 || substr($mobile, 0, 1) != '1' || $code == '') {
                return ajax_error("参数不正确");
            }
            if (session('mobileCodes') != $code || $mobile != $_SESSION['mobiles']){
                return ajax_error("验证码不正确");
            }

            if(!empty($invitation)){
                $number = db("pc_user")->where("phone_number",$invitation)->find();
                $share_code = db("pc_user")->where("my_invitation",$invitation)->find();

                if(empty($number) && empty($share_code)){
                    return ajax_error("分享码填写有误,请重试");
                } else {
                    //分享码正确
                    if(!empty($number)){
                        $invite_id = $number["id"];
                    } else {
                        $invite_id = $share_code["id"];
                    }
                    $passwords = password_hash($password,PASSWORD_DEFAULT);
                    $datas =[
                        'phone_number'=>$mobile,
                        'password'=>$passwords,
                        'create_time'=>strtotime($create_time),
                        'invitation'=>$invitation,
                        "status"=>1,
                        "invite_id"=> $invite_id,
                        "my_invitation"=>$my_invitation->memberCode(),
                    ];
                    
                    $res =Db::name('pc_user')->insertGetId($datas);
                    if($res){
                        //注册成功
                        return ajax_success('注册成功',$res);
                    }else{
                        return ajax_error('请重新注册',['status'=>0]);
                    }
                }
            } else {
                $passwords = password_hash($password,PASSWORD_DEFAULT);
                $datas =[
                    'phone_number'=>$mobile,
                    'password'=>$passwords,
                    'create_time'=>strtotime($create_time),
                    'invitation'=>$invitation,
                    "status"=>1,
                    "my_invitation"=>$my_invitation->memberCode(),
                ];
                
                $res = Db::name('pc_user')->insertGetId($datas);
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
    public function StoreMobile(Request $request){
        if($request->isPost()){
            $store_id =Session::get("store_id");
            $mobile =Db::name("store")->where("id",$store_id)->value("phone_number");
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
                    session('mobileCodes',$mobileCode);
                    $_SESSION['mobiles'] = $mobile;
                }
                $store_name = db('store')->where('id',$store_id)->value('store_name');
                $content = "【 $store_name 】尊敬的用户，您本次验证码为{$mobileCode}，十分钟内有效";
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


}