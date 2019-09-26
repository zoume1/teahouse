<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2018/09/01
 */
namespace  app\admin\controller;
use think\Db;
use think\paginator\driver\Bootstrap;
use think\Session;
use think\Request;
use app\city\model\CitySetting;
use app\city\model\CityDecay;
use app\city\model\CityEvaluate;
use app\city\model\CityMeal;
use app\city\model\CityDetail;
use app\city\model\CityRank;
use app\city\model\StoreCommission;
use app\city\model\CityCopartner;
use app\city\model\CityOrder;


class  City extends  Controller{
    
    /**
     * [分销明细]
     * 郭杨
     */    
    public function detail_index(){
        $search = input('search');
        $data = CityDetail::city_detail($search);
        return view("detail_index",['data'=>$data]);
    }

    /**
     * [代理明细]
     * 郭杨
     */    
    public function agent_index(){
        $search = input('search');
        $data = CityDetail::city_detail($search);
        return view("agent_index",['data'=>$data]);
    }

    /**
     * [分销代理设置]
     * 郭杨
     */    
    public function city_setting(Request $request){
        if($request->isPost()){
            $data = Request::instance()->param();
            $one = StoreCommission::commission_setting_update($data);
            $two = CitySetting::city_setting_update($data);
            $three = CityDecay::city_decay_update($data);
            $four = CityEvaluate::city_evaluate_update($data);

            if( $one||$two||$three||$four )
            {
                $this->success("更新成功", url("admin/City/city_setting"));
            } else {
                $this->error("更新失败", url("admin/City/city_setting"));
            }
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
        $data = CityMeal::getList();
        return view("city_rank_meal",['data'=>$data]);
    }

    /**
     * [城市等级套餐添加]
     * 郭杨
     */    
    public function city_rank_meal_add(Request $request){
        if($request->isPost()){
            $data = Request::instance()->param();
            $rest = CityMeal::city_meal_add($data);
            if($rest){
                $this->success("添加成功", url("admin/City/city_rank_meal"));
            } else {
                $this->error("添加失败", url("admin/City/city_rank_meal"));
            }    
        }
        return view("city_rank_meal_add");
    }

    /**
     * [城市等级套餐编辑]
     * 郭杨
     */    
    public function city_rank_meal_edit($id)
    {
        $meal = CityMeal::detail($id);
        return view("city_rank_meal_edit",['meal'=>$meal]);
    }

    /**
     * [城市等级套餐编辑更新]
     * 郭杨
     */    
    public function city_rank_meal_update(Request $request){
        if($request->isPost()){
            $data = Request::instance()->param();
            $restul = CityMeal::meal_update($data);
            if($restul){
                $this->success("更新成功", url("admin/City/city_rank_meal"));
            } else {
                $this->error("更新失败", url("admin/City/city_rank_meal"));
            }
        }

    }

    /**
     * [城市等级设置]
     * 郭杨
     */    
    public function city_rank_setting(){
        $data = CityRank::getList();
        return view("city_rank_setting",['data'=>$data]);
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
        $search = input();
        $data = CityCopartner::city_copartner($search);
        return view("city_datum_verify",['data'=>$data]);
    }

    /**
     * [城市入驻资料审核编辑]
     * 郭杨
     */    
    public function city_datum_verify_edit($id){
        $user_data = CityCopartner::detail(['user_id'=>$id]);
        return view("city_datum_verify_edit",['user_data'=>$user_data]);
    }

    /**
     * [城市入驻资料更新]
     * 郭杨
     */    
    public function city_datum_verify_update(Request $request){
        if($request->isPost()){
            $data = input();
            $bool = CityCopartner::meal_update($data);
            if($bool){
                $this->success("更新成功", url("admin/City/city_datum_verify"));
            } else {
                $this->success("更新失败", url("admin/City/city_datum_verify"));
            }
        }

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
        $search = input();
        $data = CityOrder::city_order($search);
        return view("city_price_examine",['data'=>$data]);
    }

    /**
     * //订单号刷选
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public  function order_preparation($status)
    {
        $rest = CityOrder::city_order($status);
        return $rest;
    }


    /**
     * [城市等级添加]
     * 郭杨
     */    
    public function city_rank_add(Request $request){
        if($request->isAjax()){
            $data = $request->post();
            if(isset($data['name']) && !empty($data['name']))
            {
                if(CityRank::rank_find($data['name'])){
                    return $this->renderError('该城市已存在,不能重复添加!');
                }
                $rest = CityRank::rank_add($data);
                if($rest){
                    return $this->renderSuccess('添加成功',$data);
                } else {
                    return $this->renderError('添加失败');
                }
            }
            return $this->renderError('添加失败');
        }
        
    }


    /**
     * [城市等级删除]
     * 郭杨
     */    
    public function city_rank_delete(Request $request){
        if($request->isAjax()){
            $id = $request->post('id');
            $rest = CityRank::rank_delete($id);
            if($rest){
                return $this->renderSuccess('删除成功');
            } else {
                return $this->renderError('删除失败');
            }
        }
        
    }

    /**
     * [城市等级移动]
     * 郭杨
     */    
    public function city_rank_update(Request $request){
        if($request->isAjax()){
            $data = input();
            $rest = CityRank::rank_update($data);
            if($rest){
                return $this->renderSuccess('移动成功');
            } else {
                return $this->renderError('移动失败');
            }
        }
        
    }



    
}