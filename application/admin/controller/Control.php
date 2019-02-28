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

class  Control extends  Controller{
    
    /**
     * [总控店铺]
     * 郭杨
     */    
    public function control_index(){     
        return view("control_index");
    }

    
    /**
     * [入驻套餐]
     * 郭杨
     */    
    public function control_meal_index(){     
        return view("control_meal_index");
    }


    /**
     * [添加入驻套餐]
     * 郭杨
     */    
    public function control_meal_add(){     
        return view("control_meal_add");
    }


    /**
     * [入驻订单]
     * 郭杨
     */    
    public function control_order_index(){     
        return view("control_order_index");
    }


    /**
     * [添加入驻订单]
     * 郭杨
     */    
    public function control_order_add(){     
        return view("control_order_add");
    }



    /**
     * [店铺分析]
     * 郭杨
     */    
    public function control_store_index(){     
        return view("control_store_index");
    }
    
 }