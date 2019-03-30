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


class StoreHouse extends Controller{
    
    /**
     * [仓库管理]
     * 郭杨
     */    
    public function store_house(){
         $store = db("store_house")->paginate(20 ,false, [
             'query' => request()->param(),
         ]);
        return view("store_house",["store"=>$store]);
    }

    
    /**
     * [仓库管理添加]
     * 郭杨
     */    
    public function store_house_add(Request $request){
        if($request->isPost()){
            $data = $request->param();
            $data["unit"] = implode(",",$data["unit"]);
            $data["cost"] = implode(",",$data["cost"]);
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
     * [仓库管理编辑]
     * 郭杨
     */    
    public function delivery_goods_update(Request $request){
        if( $request->isPost()){
            $data = $request -> param();
            $bool = db("express")->where('id', $request->only(["id"])["id"])->update($data);

            if($bool){
                $this->success("更新成功",url("admin/Delivery/delivery_goods"));
            } else {
                $this->error("更新失败", url("admin/Delivery/delivery_goods"));
            }
                  
        }
    }


    /**
     * [仓库管理编辑]
     * 郭杨
     */    
    public function store_house_edit($id){
        $house = db("store_house")->where("id",$id)->select(); 
        return view("store_house_edit",["house"=>$house]);
    }

    /**
     * [仓库管理删除]
     * 郭杨
     */
    public function store_house_delete($id){
        $bool = db("store_house")->where("id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/StoreHouse/store_house"));
        } else {
            $this->error("删除失败", url("admin/StoreHouse/store_house"));
        }

    }

    /**
     * [仓库所有单位]
     * 郭杨
     */
    public function store_house_unit(){
        $unit = db("special")->distinct(true)->field("unit")->select();
        if(!empty($unit)){
            $list = unit_list($unit);
            return ajax_success('传输成功', $list);
        } else {
            return ajax_error('数据为空');
        }      
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