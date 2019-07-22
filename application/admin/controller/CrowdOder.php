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
        return view("crowd_order_index");
    }


}