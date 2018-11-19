<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/26
 * Time: 19:17
 */
namespace  app\admin\controller;

use think\Controller;

class  ActiveOrder extends  Controller{
    public function index(){
        return view('active_order_index');
    }

 


}