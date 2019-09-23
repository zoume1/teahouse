<?php

namespace app\city\controller;
use think\Session;
use think\Validate;
use think\Request;
use app\city\model\CityRank;
use app\city\model\User as UserModel;

/**
 * PC端城市合伙人登录系统
 * Class Passport
 * @package app\city\controller
 */
class Citydenglu extends Controller
{
    /**
     * [登陆后-城市累计商户明细]
     * 郭杨
     */    
    public function logCityTenantDetail(){
        return view("city_tenant_detail");
    }
        
    

    /**
     * [我邀请的商户明细]
     * 郭杨
     */    
    public function myInviteStore(){
        return view("my_invite_store");
    }

        /**
     * [合伙人入驻订单]
     * 郭杨
     */    
    public function copartner_order_index(){
        return view("copartner_order_index");
    }
}