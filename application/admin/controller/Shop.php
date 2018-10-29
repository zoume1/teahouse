<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/22
 * Time: 19:53
 */
namespace app\admin\controller;
use think\Controller;

class Shop extends Controller{


    /**
     * 店铺列表
     * 陈绪
     */
    public function index(){

        return view("shop_index");

    }



    /**
     * 店铺基本信息
     * 陈绪
     */
    public function add(){

        return view("shop_add");

    }

}