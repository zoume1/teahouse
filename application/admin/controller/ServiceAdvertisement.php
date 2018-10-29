<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/26
 * Time: 19:17
 */
namespace  app\admin\controller;

use think\Controller;

class  ServiceAdvertisement extends  Controller{


    public function Service_business_advertising(){
        return view('Service_business_advertising');
    }
    public function Service_business_add(){
        return view('Service_business_add');
    }
    public function Service_business_edit(){
        return view('Service_business_edit');
    }


}