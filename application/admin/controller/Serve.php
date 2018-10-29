<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/22
 * Time: 10:34
 */
namespace app\admin\controller;
use think\Controller;

class Serve extends Controller{


    /**
     * 服务商品管理
     * 陈绪
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function index(){

        return view("serve_index");

    }



    /**
     * 服务商品添加
     * 陈绪
     */
    public function add(){

        return view("serve_add");

    }

}