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
use think\Request;
use app\city\model\CitySetting;
use app\city\model\CityDecay;
use app\city\model\CityEvaluate;
use app\city\model\StoreCommission;


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
    public function city_setting(Request $request){
        if($request->isPost()){

        }
        $store_data = StoreCommission::commission_setting();
        $citySetting = CitySetting::city_setting();
        $citydecay = CityDecay::city_decay();
        $cityevalute = CityEvaluate::city_evaluate();
        
        return view("city_setting",['store_data'=>$store_data,'citySetting'=>$citySetting,'citydecay'=>$citydecay,'cityevalute'=>$cityevalute]);
    }

    /**
     * [城市等级套餐]
     * 郭杨
     */    
    public function city_rank_meal(){
        return view("city_rank_meal");
    }

    /**
     * [城市等级套餐编辑]
     * 郭杨
     */    
    public function city_rank_meal_edit(){
        return view("city_rank_meal_edit");
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
    /**
     * [城市入驻费用审核]
     * 郭杨
     */    
    public function city_price_examine(){
        return view("city_price_examine");
    }
}