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
    // 智慧茶仓  源头厂家
    public function wisdom(){
        $phone_num =  $this->commons();
        return view("wisdom",["phone_num"=>$phone_num]);
    }
    // 实力商家
    public function merchant(){
        $phone_num =  $this->commons();
        return view("merchant",["phone_num"=>$phone_num]);
    }
    // 更多服务
    public function more_server(){
        $phone_num =  $this->commons();
        return view("more_server",["phone_num"=>$phone_num]);
    }
    // 自有茶园
    public function zycy(){
        $phone_num =  $this->commons();
        return view("zycy",["phone_num"=>$phone_num]);
    }
    // 自有工厂
    public function zygc(){
        $phone_num =  $this->commons();
        return view("zygc",["phone_num"=>$phone_num]);
    }
    // 自有仓库
    public function zyck(){
        $phone_num =  $this->commons();
        return view("zyck",["phone_num"=>$phone_num]);
    }
    // 万用版
    public function wyb(){
        $phone_num =  $this->commons();
        return view("wyb",["phone_num"=>$phone_num]);
    }
    // 专业版
    public function zyb(){
        $phone_num =  $this->commons();
        return view("zyb",["phone_num"=>$phone_num]);
    }
    // 高级版
    public function gjb(){
        $phone_num =  $this->commons();
        return view("gjb",["phone_num"=>$phone_num]);
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
            $data['wendu']=$input['wendu'];
            $data['shidu']=$input['shidu'];
            $data['update_time']=time();
            $re=db('instrument')->where(['instrument_number'=>'8606S86YL8295C5Y','store_id'=>$input['uniacid']])->update($data);
            return ajax_success('获取成功');
        }else{
            return ajax_error('获取失败');
        }
    }

    /**
     * gy
     * 城市合伙人忘记密码
     */
    public function city_forget(){
        return view("city_forget");
    }

    /**
     * gy
     * 合伙人后台登陆
     */
    public function city_login(){
        return view("city_login");
    }

    /**
     * gy
     * 合伙人后台退出
     */
    public function city_out(){
        return view("city_login");
    }

}
