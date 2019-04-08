<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27 0027
 * 订单
 * Time: 15:20
 */
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\captcha\Captcha;

class Login extends Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:用户登录
     **************************************
     * @return \think\response\View
     */
    public function index(){
        return view("login");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:验证码
     **************************************
     * @return \think\Response
     */
    public function captchas(){
        $captcha = new Captcha([
            'imageW'=>100,
            'imageH'=>48,
            'fontSize'=>18,
            'useNoise'=>false,
            'length'=>3,
        ]);
        return $captcha->entry();
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:登录检测并取出对应的角色
     **************************************
     * @param Request $request
     */
    public function login(Request $request){
        if(!captcha_check($request->only("yzm")["yzm"])){
            //验证失败
            $this->error("验证码有误","admin/Login/index");
            exit();
        };
        if ($request->isPost()){
            $username = $request->only("account")["account"];
            $passwd = $request->only("passwd")["passwd"];
            $userInfo = db("admin")
                ->where("account",$username)
                ->where("status","<>",1)
                ->select();
            if($username !="admin"){
                $this->success("商户请在前台登录","admin/Login/index");
            }
            if (!$userInfo) {
                $this->success("账户名不正确或管理员以被停用","admin/Login/index");
            }
            if (password_verify($passwd , $userInfo[0]["passwd"])) {
                Session("user_id", $userInfo[0]["id"]);
                unset($userInfo->user_passwd);

                Session("user_info", $userInfo);
               // $this->redirect(url("admin/index/index"));
                $this->success("登录成功","admin/Index/index");
            }else{
                $this->success("账户密码不正确","admin/Login/index");

            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:[退出]
     **************************************
     */
    public function logout(){
        $store_id =Session::get("store_id");
        Session::delete("user_id");
        Session::delete("user_info");
        Session::delete("store_id");
        if(!empty($store_id)){
            $this->redirect("index/index/sign_in");
        }else{
            $this->redirect("admin/Login/index");
        }

    }






}