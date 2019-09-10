<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2018/09/01
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\paginator\driver\Bootstrap;
use think\Session;


class  City extends  Controller{
    
    /**
     * [分销代理明细]
     * 郭杨
     */    
    public function detail_index(){
        return view("detail_index");
    }

    /**
     * [分销代理设置]
     * 郭杨
     */    
    public function city_setting(){
        return view("city_setting");
    }

    /**
     * [城市等级套餐]
     * 郭杨
     */    
    public function city_rank_meal(){
        return view("city_rank_meal");
    }

    /**
     * [城市等级设置]
     * 郭杨
     */    
    public function city_rank_setting(){
        return view("city_rank_setting");
    }

    /**
     * [城市等级设置编辑]
     * 郭杨
     */    
    public function city_rank_setting_edit(){
        return view("city_rank_setting_edit");
    }

    /**
     * [城市入驻资料审核]
     * 郭杨
     */    
    public function city_datum_verify(){
        return view("city_datum_verify");
    }

    /**
     * [城市入驻资料审核编辑]
     * 郭杨
     */    
    public function city_datum_verify_edit(){
        return view("city_datum_verify_edit");
    }


    
    /**
     * [城市入驻费用审核编辑]
     * 郭杨
     */    
    public function city_price_examine_update(){
        return view("city_price_examine_update");
    }
}