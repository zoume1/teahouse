<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/6
 * Time: 15:59
 */

namespace app\index\controller;
use think\Controller;

class Login extends Controller{

    /**
     * 注册首页
     */
    public function index(){
       return view("login_index");
    }

}