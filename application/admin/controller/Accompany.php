<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/12/23
 */
namespace  app\admin\controller;
use think\Db;
use think\paginator\driver\Bootstrap;
use think\Session;
use think\Request;


class  Accompany extends  Controller{
    
    /**
     * [送存商品页面]
     * 郭杨
     */    
    public function accompany_index(){
        return view("accompany_index");
    }

    /**
     * [送存商品添加]
     * 郭杨
     */    
    public function accompany_add(){
        return view("accompany_add");
    }

    /**
     * [送存商品详情]
     * 郭杨
     */    
    public function accompany_edit(){
        return view("accompany_edit");
    }
    
}