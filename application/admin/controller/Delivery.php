<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/12 0012
 * Time: 15:41
 */
namespace app\admin\controller;

use think\Controller;

class Delivery extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:配送设置
     **************************************
     */
    public function delivery_index(){
        return view("delivery_index");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:上门自提添加
     **************************************
     */
    public function delivery_add(){
        return view("delivery_add");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:快递发货
     **************************************
     */
    public function delivery_goods(){
        return view("delivery_goods");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:快递发货添加按重量
     **************************************
     */
    public function delivery_goods_add_weight(){
        return view("delivery_goods_add_weight");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:快递发货添加按件
     **************************************
     */
    public function delivery_goods_add_number(){
        return view("delivery_goods_add_number");
    }



}