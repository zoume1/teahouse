<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/25/025
 * Time: 14:13
 */

namespace app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use think\paginator\driver\Bootstrap;
use think\Session;

class CrowdOder extends Controller{


    /**
     * [众筹商品订单显示]
     * GY
     */
    public function crowd_order_index()
    {
        $store_id = Session::get("store_id");
        $where['status']= array('between',array(0,8));
        $data =Db::name("crowd_order")
            ->order("order_create_time","desc")
            ->where("store_id",'EQ',$store_id)
            ->where($where)
            ->group('parts_order_number')
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
            $data2=[];
            foreach($data as $k=>$v){
                //获取相同订单的数据
                $list = db('crowd_order')->where('parts_order_number',$v['parts_order_number'])->select();
                $order=[];
                foreach($list as $k2 =>$v2){
                    $order[$k2]['goods_image']=$v2['goods_image'];
                    $order[$k2]['parts_goods_name']=$v2['parts_goods_name'];
                    $order[$k2]['order_quantity']=$v2['order_quantity'];
                }
                $num = count($order);
                $data2[$k]=$v;
                $data2[$k]['detail']=$order;
                $data2[$k]['num']=$num;
            }
        return view("crowd_order_index",["data"=>$data2]);
    }


}