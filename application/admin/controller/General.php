<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\paginator\driver\Bootstrap;

class  General extends  Controller{
    
    /**
     * [店铺概况]
     * 郭杨
     */    
    public function general_index(){     
        return view("general_index");
    }

    
    /**
     * [小程序设置]
     * 郭杨
     */    
    public function small_routine_index(){     
        return view("small_routine_index");
    }


    /**
     * [小程序装修]
     * 郭杨
     */    
    public function decoration_routine_index(){     
        return view("decoration_routine_index");
    }

    /**
     * [增值服务(增值商品显示)]
     * 郭杨
     */    
    public function added_service_index(){     
        return view("added_service_index");
    }

    /**
     * [订单套餐(显示)]
     * 郭杨
     */    
    public function order_package_index(){     
        return view("order_package_index");
    }



 }