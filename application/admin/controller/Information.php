<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\paginator\driver\Bootstrap;

class Information extends Controller{
    
    /**
     * [数据概况]
     * 郭杨
     */    
    public function data_index(){    
        $store_id=Session::get('store_id');
        //今日订单数
        $start_time=strtotime(date("Y-m-d"));
        $end_time=strtotime(date("Y-m-d H:i:s"));
        $where['order_create_time']=array('between',array($start_time,$end_time));
        $where['store_id']=Session::get('store_id');
        $data['order_num']=db('order')->where($where)->count();     //今日总订单
        $where['status']=array('between',array(2,8));
        $data['order_money']=db('order')->where($where)->sum('order_amount');   //今日总销售额     2-8
        $data['order_money']=round($data['order_money'],2);
        //昨日订单数
        $start_time2=strtotime(date("Y-m-d "))-24*3600;
        $end_time2=strtotime(date("Y-m-d"));
        $where2['order_create_time']=array('between',array($start_time2,$end_time2));
        $where2['store_id']=Session::get('store_id');
        $data['order_num2']=db('order')->where($where2)->count();     //昨日总订单
        $where2['status']=array('between',array(2,8));
        $data['order_money2']=db('order')->where($where2)->sum('order_amount');   //昨日总销售额     2-8
        $data['order_money2']=round($data['order_money2'],2);
        //七日订单数
        $start_time3=strtotime(date("Y-m-d "))-24*3600*7;
        $end_time3=strtotime(date("Y-m-d"));
        $where2['order_create_time']=array('between',array($start_time3,$end_time3));
        $where3['store_id']=Session::get('store_id');
        $where3['status']=array('between',array(2,8));
        $data['order_money3']=db('order')->where($where3)->sum('order_amount');   //七日总销售额     2-8
        $data['order_money3']=round($data['order_money3'],2);
        //待付款订单
        $data['daifu_num']=db('order')->where(['store_id'=>$store_id,'status'=>1])->count('order_amount');   //
        //待发货订单
        $data['fahuo_num']=db('order')->where(['store_id'=>$store_id,'status'=>5])->count('order_amount');   //
        //已完成订单
        $data['yiwan_num']=db('order')->where(['store_id'=>$store_id,'status'=>8])->count('order_amount');   //

        //商品模块
        $data['shang']=db('goods')->where(['store_id'=>$store_id,'label'=>1])->count();
        $data['xia']=db('goods')->where(['store_id'=>$store_id,'label'=>0])->count();
        $data['zong']=$data['shang']+$data['xia'];
       
         


        return view("data_index",['data'=>$data]);
    }




    /**
     * [溯源分析]
     * 郭杨
     */    
    public function analytical_index(){     
        return view("analytical_index");
    }
    
 }