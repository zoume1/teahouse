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

class StoreHouse extends Controller{
    
    /**
     * [仓库管理]
     * 郭杨
     */    
    public function store_house(){
        $store = db("store_house")->paginte(20);
        return view("store_house",["store"=>$store]);
    }

    
    /**
     * [仓库管理添加]
     * 郭杨
     */    
    public function store_house_add(Request $request){
        if($request->isPost()){
            $data = $request->param();
            $res =Db::name("store_house")->insert($data);
            
            if($res){
                $this -> success("添加成功","admin/StoreHouse/store_house");
            } else {
                $this -> success("添加失败","admin/StoreHouse/store_house");
            }
        }
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