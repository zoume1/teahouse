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
use app\admin\model\Goods;  
use app\admin\model\Accompany as Accompanyes;


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
    public function accompany_add(Request $request){
        $store_id =  Session :: get('store_id');
        if($request -> isPost()){
            $data =  Request::instance()->param();
            $rest = Accompanyes::accompany_add($data);
            halt($data);
        }
        //送存仓储
        $store_name = Db::name("store_house")->where("store_id",$store_id)->select(); 
        //面向会员
        $scope = Db::name("member_grade")->where("store_id","EQ",$store_id)->field("member_grade_id,member_grade_name")->select();

        return view("accompany_add",['store_name'=>$store_name,'scope' =>$scope]);
    }

    /**
     * [送存商品详情]
     * 郭杨
     */    
    public function accompany_edit(){
        return view("accompany_edit");
    }

    /**
     * [商品编码搜索商品]
     */
    public function serach_accompany(Request $request){
        if($request -> isPost()){
            $goods_number = $request->only(['goods_number'])['goods_number'];
            return Goods::accompany_goods($goods_number);
        }
    }

    
}