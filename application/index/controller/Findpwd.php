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
use think\Request;
use  think\Session;

class Findpwd extends Controller{
    /**
     **************李火生*******************
     * @param Request $request
     * 用来接收密码
     * phone_number 手机号
     * code     输入的验证码
     * password     输入的密码
     **************************************
     */
    public function find_password_by_phone(Request $request){

        if($request->isPost()){
            $mobile =trim($_POST['phone_number']);
            $code =trim($_POST['code']);
            $password =trim($_POST['password']);
            $password_second =trim($_POST['password_second']);
            if($password!=$password_second){
                return ajax_error('两次密码不一致',['status'=>0]);
            }
            $data=[
                'phone_number'=>$mobile,
                'password'=>$password,
                'code'=>$code,
            ];
            if(!empty($data)){
                $res =Db::name('pc_user')->field('phone_number')->where('phone_number',$mobile)->select();
                if(empty($res)){
                    return ajax_error('此手机号不能存在，请前往注册',$mobile);
                }else{
                    if (session('mobileCode') != $code) {
                        return ajax_error("验证码不正确",$mobile);
                    }else{
                        $passwords =password_hash($password,PASSWORD_DEFAULT);
                        $password_bool =Db::name('pc_user')->where('phone_number',$mobile)->update(['password'=>$passwords]);
                        if($password_bool){
                            $user_data =Db::name('pc_user')->where('phone_number',$mobile)->find();
                            return ajax_success('密码修改成功',$user_data);
                        }else{
                            return ajax_error('密码修改失败',$password_bool);
                        }
                    }
                }
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:PC找回密码验证码，PC修改手机号验证码
     **************************************
     * @param Request $request
     */
    public function sendMobileCodeByPhone(Request $request)
    {
        //接受验证码的手机号码
        if ($request->isPost()) {
            $mobile = $_POST["mobile"];
            $is_set_mobile =Db::name('pc_user')->where('phone_number',$mobile)->find();
            if(empty($is_set_mobile)){
                return ajax_error("此手机未注册",['status'=>0]);
            }
            $mobileCode = rand(100000, 999999);
            $arr = json_decode($mobile, true);
            $mobiles = strlen($arr);
            if (isset($mobiles) != 11) {
                return ajax_error("手机号码不正确");
            }
            //存入session中
            if (strlen($mobileCode)> 0){
                session('mobileCode',$mobileCode);
                session('mobile',$mobile);
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
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:修改手机号
     **************************************
     * @param Request $request
     */
    public function sendMobileCodeByPh(Request $request)
    {
        //接受验证码的手机号码
        if ($request->isPost()) {
            $mobile = $_POST["mobile"];
            $user_id = Session::get("user");
            $is_set_mobile =Db::name('pc_user')->where("id",$user_id)->where('phone_number',$mobile)->find();
            if(!empty($is_set_mobile)){
                return ajax_error("请输入不一样的号码",['status'=>0]);
            }
            $is_set_mobiles = Db::name('pc_user')->where('phone_number',$mobile)->find();
            if(!empty($is_set_mobiles)){
                return ajax_error("此手机号已注册，请换其他手机号",['status'=>0]);
            }
            $mobileCode = rand(100000, 999999);
            $arr = json_decode($mobile, true);
            $mobiles = strlen($arr);
            if (isset($mobiles) != 11) {
                return ajax_error("手机号码不正确");
            }
            //存入session中
            if (strlen($mobileCode)> 0){
                session('mobileCode',$mobileCode);
                session('mobile',$mobile);
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
        }
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:修改密码
     **************************************
     */
    public function update_password(Request $request){
        if($request->isPost()){
            $password =$request->only(['password'])['password'];
            $passwords =password_hash(trim($password),PASSWORD_DEFAULT);
            $member =session('member');
            if(!empty(trim($password))){
                $bool =Db::name('pc_user')
                    ->where('phone_number',$member['phone_number'])
                    ->update(['password'=>$passwords]);
                if($bool){
                    return ajax_success('修改成功',['status'=>1]);
                }else{
                    return ajax_error('修改失败',['status'=>0]);
                }
            }
        }
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:PC修改手机号
     **************************************
     */
    public function  new_phone_update(Request $request){
        if($request->isPost()){
            $user_id =Session::get("user");
            $mobile = trim($_POST['mobiles']);//新手机
            $is_reg =Db::name("pc_user")->where("phone_number",$mobile)->find();
            if(!empty($is_reg)){
                return ajax_error("此手机已注册，可以直接登录");
            }
            $code = trim($_POST['mobile_code']);
            $mobile_codes = trim($_POST['mobile_codes']); //新手机号
            $password =trim($_POST['password']);
            $confirm_password =trim($_POST['confirm_password']);
            $create_time =date('Y-m-d H:i:s');
            if($password !==$confirm_password ){
                return ajax_error('两次密码不相同');
            }
            if (strlen($mobile) != 11 || substr($mobile, 0, 1) != '1' || $code == '') {
                return ajax_error("参数不正确");
            }
            //新手机号
            if (session('mobileCodes') != $mobile_codes || $mobile != $_SESSION['mobiles']) {
                return ajax_error("新手机号验证码不正确");
            } else {
                //这个是旧手机
                if(session('mobileCode') != $code){
                    return ajax_error("旧手机号验证码不正确");
                }
                $passwords =password_hash($password,PASSWORD_DEFAULT);
                $datas =[
                    'phone_number'=>$mobile,
                    'password'=>$passwords,
                ];
                $res =Db::name('pc_user')->where("id",$user_id)->update($datas);
                if($res){
                    $datas =[
                        'phone_number'=> $mobile,
                    ];
                    Session::set('member',$datas);
                    return ajax_success('修改成功',$res);
                }else{
                    return ajax_error('修改失败');
                }
            }
        }
    }
}