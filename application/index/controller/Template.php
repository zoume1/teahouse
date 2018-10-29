<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/11
 * Time: 16:46
 */
namespace app\index\controller;

use think\Controller;
class Template extends Controller{

    /**
     * 模板商城
     * 陈绪
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function index(){

        return view("template");

    }



    /**
     * 商品详情
     * 陈绪
     */
    public function goods_show(){

        return view("goods_show");

    }



    /**
     * 商品购买
     * 陈绪
     */
    public function goods_buy(){

        return view("goods_buy");

    }

}