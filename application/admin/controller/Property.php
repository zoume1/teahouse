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
use think\Session;
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
        $store_id =  Session :: get("store_id");
        $query = 'Select FROM_UNIXTIME(tb_serial.create_time,"%Y-%m") as time ,SUM(money) as money ,SUM(talk_money) as talk_money,SUM(prime) as prime
        FROM  tb_serial  
        WHERE tb_serial.store_id = '.$store_id.'
        Group by FROM_UNIXTIME(tb_serial.create_time,"%Y-%m")';
        $data = Db::query($query);
        foreach($data as $key => $value){
            $data[$key]['gross_profit'] =  $data[$key]['money'] -  $data[$key]['prime']; //毛利
            $data[$key]['pure_profit'] =  $data[$key]['gross_profit'] -  $data[$key]['talk_money']; //纯利
        }
        $url = 'admin/Property/property_month';
        $pag_number = 20;
        $data = paging_data($data,$url,$pag_number);
        return view("property_month",['data'=>$data]);
    }




    /**
     * [日账单详细]
     * 郭杨
     */
    public function property_day_index(){
        return view("property_day_index");
    }
    
 }