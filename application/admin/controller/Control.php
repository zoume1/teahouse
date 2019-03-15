<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use app\admin\model\Good;
use app\admin\model\GoodsImages;
use think\Session;
use think\Loader;
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
    public function control_meal_add(Request $request){ 
        if($request -> isPost()){
            $meal = $request->param(); 
            $min_cost = $meal["cost"];
            $favourable_cost = $meal["favourable_cost"];
        
            foreach($min_cost as $key => $value){
                if(!$value){
                    unset($min_cost[$key]);
                }
                $cost[] = $value; 
            }

            foreach($favourable_cost as $ke => $val){
                if(!$val){
                    unset($favourable_cost[$ke]);
                }
                $favourable[] = $val;
            }
            $min1 = min($min_cost);        //套餐原价
            $min2 = min($favourable_cost); //套餐优惠价
            
        }
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