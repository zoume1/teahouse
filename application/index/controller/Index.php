<?php
namespace app\index\controller;

use think\Controller;
use think\Cache;
use think\Session;

class Index extends Controller
{

    public function index()
    {
        $phone_num =  $this->commons();
        return view("index",["phone_num"=>$phone_num]);
    }
    public function home(){
        $phone_num =  $this->commons();
        return view("home",["phone_num"=>$phone_num]);
    }

    public function text(){
        $phone_num =  $this->commons();
        return view("text",["phone_num"=>$phone_num]);
    }

    // 茶厂
    public function tea_factory(){
        $phone_num =  $this->commons();
        return view("teafactory",["phone_num"=>$phone_num]);
    }
    // 茶商
    public function tea_merchant(){
        $phone_num =  $this->commons();
        return view("teamerchant",["phone_num"=>$phone_num]);
    }
    // 茶圈
    public function tea_moment(){
        $phone_num =  $this->commons();
        return view("teamoment",["phone_num"=>$phone_num]);
    }
    // 用户
    public function consumer(){
        $phone_num =  $this->commons();
        return view("consumer",["phone_num"=>$phone_num]);
    }
    // 智慧茶仓
    public function wisdom(){
        $phone_num =  $this->commons();
        return view("wisdom",["phone_num"=>$phone_num]);
    }
    // 招募合伙人
    public function partner(){
        $phone_num =  $this->commons();
        return view("partner",["phone_num"=>$phone_num]);
    }
    // 关于我们
    public function about(){
        $phone_num =  $this->commons();
        return view("about",["phone_num"=>$phone_num]);
    }
    // 注册
    public function sign_up(){
        return view("signup");
    }
    // 登录
    public function sign_in(){
        return view("signin");
    }
    // 忘记密码
    public function forget_pw(){
        return view("forgetpw");
    }
    // 我的店铺
    public function my_shop(){
        $phone_num =  $this->commons();
        return view("myshop",["phone_num"=>$phone_num]);
    }

    protected  function  commons(){
        $data =Session::get("member");
        if(!empty($data)){
            $phone_num =$data["phone_number"];
        }else{
          $phone_num =null;
        }
        return $phone_num;
    }
    /**
     * lilu
     * 实时获取温湿度
     * wendu
     * shidu
     * uniacid
     */
    public function get_wenshidu(){
        //获取参数
        $input=input();
        if($input){
            $re=db('instrument')->where(['instrument_number'=>'8606S86YL8295C5Y','store_id'=>$input['uniacid']])->update($input);
            return ajax_success('获取成功');
        }else{
            return ajax_error('获取失败');
        }
    }

}
