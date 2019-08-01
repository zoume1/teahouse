<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\paginator\driver\Bootstrap;
use think\Session;
use think\View;


class  Material extends  Controller{


    /**
     **************GY*******************
     * @param Request $request
     * Notes:视频直播
     **************************************
     * @return \think\response\View
     */
    public function direct_seeding(){
        return view("direct_seeding");
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:视频直播添加编辑设备
     **************************************
     */
    public  function  direct_seeding_add(){
        return  view("direct_seeding_add");
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:直播分类
     **************************************
     */
    public function direct_seeding_classification(){
        return view("direct_seeding_classification");
    }

    /**
     **************GY******************* 
     * @param Request $request
     * Notes:直播分类添加编辑
     **************************************
     * @return \think\response\View
     */
    public function direct_seeding_classification_add(Request $request){
        if($request->isPost()){

        }
        return view("direct_seeding_classification_add");
        
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:防伪溯源
     **************************************
     * @return \think\response\View
     */
    public function anti_fake(){
        return view("anti_fake");
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:温湿传感
     **************************************
     * @return \think\response\View
     */
    public function interaction_index(){
        return view("interaction_index");
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:温湿传感添加编辑
     **************************************
     * @return \think\response\View
     */
    public function interaction_add(){
        return view("interaction_add");
    }

    
 }