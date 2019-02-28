<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
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
        return view("property_day");
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



    
 }