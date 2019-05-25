<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2018/11/23
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\paginator\driver\Bootstrap;
use think\Session;

class  ActiveOrder extends  Controller{
    
    /**
     * [活动订单显示]
     * 郭杨
     */    
    public function index(){
        $store_id = Session::get("store_id");
        $active = db("activity_order")
                ->where("store_id","EQ",$store_id)	
                ->select();        
        foreach($active as $key => $value){
            $active[$key]['peoples'] = db("teahost")
            ->where('id',$active[$key]['teahost_id'])
            ->value("peoples");
        }
        
        $all_idents = $active ;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1)*$listRow, $listRow,true);// 数组中根据条件取出一段值，并返回
        $active = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path'     => url('admin/ActiveOrder/index'),//这里根据需要修改url
            'query'    =>  [],
            'fragment' => '',
        ]);
        $active->appends($_GET);
        $this->assign('actived', $active->render());
        return view("active_order_index",["active"=>$active]);
    }


     /**
     * [活动订单模糊搜索]
     * 郭杨
     */
    public function search(){
        $store_id = Session::get("store_id");
        $ativity_name = input('search_name');  //活动名称
        $ativity_number = input('search_key'); //用户账号
        
        if((!empty($ativity_name)) || (!empty($ativity_number)) ){
            $active = db("activity_order")->where("activity_name", "like","%" .$ativity_name ."%")->where("account", "like","%" .$ativity_number ."%")->select();
            foreach($active as $key => $value){
                if($value["pid"]){
                    $res = db("goods_type")->where("id",$value['pid'])->field("name")->find();
                    $active[$key]["names"] = $res["name"];
                }
            }

            $all_idents = $active ;//这里是需要分页的数据
            $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
            $listRow = 2;//每页2行记录
            $showdata = array_slice($all_idents, ($curPage - 1)*$listRow, $listRow,true);// 数组中根据条件取出一段值，并返回
            $active = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
                'var_page' => 'page',
                'path'     => url('admin/ActiveOrder/index'),//这里根据需要修改url
                'query'    =>  [],
                'fragment' => '',
            ]);
            $active->appends($_GET);
            if(!empty($active)){
            return view("active_order_index",["active"=>$active]);
            }
        }

    }

 }