<?php

namespace app\city\controller;
use think\Session;
use think\Validate;
use think\Request;
use app\city\model\CityRank;
use app\city\model\User as UserModel;
use app\city\model\CityDetail;
use app\city\model\CityCopartner;
use app\city\model\CityOrder;


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
        $user = Session::get('User');
        $number = CityCopartner::get_number($user);
        $data = CityDetail::city_store_detail();
        return view("city_tenant_detail",['data'=>$data,'number'=>$number]);
        
    }
        
    /**
     * [登陆后-城市累计商户明细搜索]
     * 郭杨
     */    
    public function logCityTenantDetail_search(){
        $search = input();
        $user = Session::get('User');
        $number = CityCopartner::get_number($user);
        $data = CityDetail::city_store_search($search);
        return view("city_tenant_detail",['data'=>$data,'number'=>$number]);
        
    }
    

    /**
     * [我邀请的商户明细]
     * 郭杨
     */    
    public function myInviteStore(){
        $user = Session::get('User');
        $number = CityCopartner::get_number($user);
        $data = CityDetail::city_store_detail_one();
        return view("my_invite_store",['data'=>$data,'number'=>$number]);
    }

    /**
     * [我邀请的商户明细搜索]
     * 郭杨
     */    
    public function myInviteStore_search(){
        $search = input();
        $user = Session::get('User');
        $number = CityCopartner::get_number($user);
        $data = CityDetail::city_store_search($search);
        return view("my_invite_store",['data'=>$data,'number'=>$number]);
    }

    /**
     * [合伙人入驻订单]
     * 郭杨
     */    
    public function copartner_order_index(){
        $user = Session::get('User');
        $data = CityOrder::detail(['city_user_id'=>$user['user_id'],'account_status'=>1]);
        return view("copartner_order_index",['data'=>$data]);
    }

    /**
     * [合伙人系统固定页面]
     * 郭杨
     */    
    public function city_server_index(){
        $user = Session::get('User');
        $user_data = UserModel::detail(['user_id'=> $user['user_id']]);
        $store_count_money = CityDetail::city_store_commission($user['user_id']);
        $create_time = $user_data['create_time'];
        $number = CityCopartner::get_number($user);
        $number ? $status = 0 : $status = 1;
        $rest_data = [
            'phone_number' => $user_data['phone_number'], //账号
            'city_address'=> $user_data['city_address'],  //代理城市
            'end_time'=>strtotime("$create_time+1year"),           //有效期
            'city_store_number'=>$user_data['city_store_number'],//城市累计商户总数
            'invitation_store_number'=>$user_data['invitation_store_number'], //我邀请的商户总数
            'store_count_money'=>$store_count_money, //城市累计商户获得佣金
            'commission_count'=> $user_data['reach_commission'], //我邀请的累计获得佣金
            'commission' => $user_data['commission'], //保底佣金总额
            'reach_commission' => $user_data['reach_commission'],//达标佣金总额
            'lock_status' => $status //能否提现
        ];
        return jsonSuccess('发送成功',$rest_data);
    }


    /**
     **************郭杨*******************
     * @param Request $request
     * Notes:[退出]
     **************************************
     */
    public function city_out_log(){
        Session::delete("User");
        $this->redirect("index/index/city_out");
    }
}