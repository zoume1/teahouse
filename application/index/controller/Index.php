<?php
namespace app\index\controller;

use think\Controller;
use think\Cache;

class Index extends Controller
{
    public function index()
    {
        return view("index");
    }
    public function home(){
        return view("home");
    }

    public function text(){
        return view("text");
    }

    // 茶厂
    public function tea_factory(){
        return view("teafactory");
    }
    // 茶商
    public function tea_merchant(){
        return view("teamerchant");
    }
    // 茶圈
    public function tea_moment(){
        return view("teamoment");
    }
    // 用户
    public function consumer(){
        return view("consumer");
    }
    // 智慧茶仓
    public function wisdom(){
        return view("wisdom");
    }
    // 招募合伙人
    public function partner(){
        return view("partner");
    }
    // 关于我们
    public function about(){
        return view("about");
    }

}
