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
     * [订单套餐]
     * 郭杨
     */    
    public function order_package_index(){
        $order_package = db("enter_meal")->where("status",1)->field("id,name,price,favourable_price,year")->select();
        foreach($order_package as $key => $value){
            $order_package[$key]['priceList'] = db("enter_all") -> where("enter_id",$order_package[$key]['id'])->select();
        }       
        return view("order_package_index");
    }


    /**
     * [订单套餐(显示)]
     * 郭杨
     */    
    public function order_package_show(){
        $order_package = db("enter_meal")->where("status",1)->field("id,name,price,favourable_price,year")->select();
        foreach($order_package as $key => $value){
            $order_package[$key]['priceList'] = db("enter_all") -> where("enter_id",$order_package[$key]['id'])->select();
        }
        
        if(!empty($order_package)){
            return ajax_success('传输成功',$order_package);
        } else {
            return ajax_error('传输失败,请添加套餐');
        }

    }


 }