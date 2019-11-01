<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use app\index\model\Serial;
use think\paginator\driver\Bootstrap;

class  Property extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:资产对账单日汇总
     **************************************
     * @return \think\response\View
     */
    public function property_day(){
        $search = input();
        $data = Serial::index($search);    
        return view("property_day",['data' => $data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:资产对账单月汇总
     **************************************
     * @return \think\response\View
     */
    public function property_month(){
        return view("property_month");
    }




    /**
     * [日账单详细]
     * 郭杨
     */
    public function property_day_index(){
        return view("property_day_index");
    }
    
 }