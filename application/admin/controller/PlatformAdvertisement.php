<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/26
 * Time: 19:17
 */
namespace  app\admin\controller;

use think\Controller;

class  PlatformAdvertisement extends  Controller{


    public function platform_business_index(){
        return view('platform_business_index');
    }
    public function platform_business_add(){
        return view('platform_business_add');
    }
    public function platform_business_edit(){
        return view('platform_business_edit');
    }


}