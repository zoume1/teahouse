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

class StoreHouse extends Controller{
    
    /**
     * [仓库管理]
     * 郭杨
     */    
    public function store_house(){
        return view("store_house");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:仓库管理添加
     **************************************
     * @return \think\response\View
     */
    public function store_house_add(){
        return view("store_house_add");
    }


    /**
     * [仓库入仓]
     * 郭杨
     */    
    public function stores_divergence(){     
        return view("stores_divergence");
    }

    /**
     * [仓库出仓]
     * 郭杨
     */
    public function stores_divergence_out(){
        return view("stores_divergence_out");
    }


    
 }