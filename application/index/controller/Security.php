<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/6
 * Time: 15:59
 */

namespace app\index\controller;
use think\Controller;

class Security extends Controller{

    /**
     * 安全中心
     * 陈绪
     */
    public function index(){
       return view("security_index");
    }

}