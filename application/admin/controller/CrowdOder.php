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
        $datas =Db::name("crowd_order")
            ->order("order_create_time","desc")
            ->where("store_id",'EQ',$store_id)
            ->where($where)
            ->group('parts_order_number')
            ->select();


            $url = 'admin/CrowdOder/crowd_order_index';
            $pag_number = 20;
            $data = paging_data($datas,$url,$pag_number);
        return view("crowd_order_index",["data"=>$data]);
    }


}