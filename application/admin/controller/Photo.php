<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/22
 * Time: 19:53
 */
namespace app\admin\controller;
use think\Controller;

class Photo extends Controller{


    /**
     * 图片库
     * 邹梅
     */
    public function index(){

        return view("photo_index");

    }



}