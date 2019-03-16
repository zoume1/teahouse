<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use app\admin\model\Good;
use app\admin\model\GoodsImages;
use think\Session;
use think\Loader;
use think\paginator\driver\Bootstrap;

class  Control extends  Controller{
    
    /**
     * [总控店铺]
     * 郭杨
     */    
    public function control_index(){ 
        $control_meale = db("enter_meal")->paginate(20,false, [
            'query' => request()->param(),
        ]);   
        return view("control_index",["control_meale"=>$control_meale]);
    }

    
    /**
     * [入驻套餐]
     * 郭杨
     */    
    public function control_meal_index(){
        $control_meal = db("enter_meal")->paginate(20,false, [
            'query' => request()->param(),
        ]);     
        return view("control_meal_index",["control_meal"=>$control_meal]);
    }


    /**
     * [添加入驻套餐]
     * 郭杨
     */    
    public function control_meal_add(Request $request){ 
        if($request -> isPost()){
            $meal = $request->param();
            $year = $meal["year"]; 
            $min_cost = $meal["cost"];
            $favourable_cost = $meal["favourable_cost"];
            foreach($min_cost as $key => $value){
                if(!$value){
                    unset($min_cost[$key]);
                }
                $cost[] = $value; 
            }           
            $min = min($min_cost);        //套餐原价最低价
            $enter = array(
                "name" => $meal["name"],
                "price" => $min,
                "sort_number" => $meal["sort_number"],
                "status" => $meal["status"],
                "cost" => implode(",",$cost),
                "favourable_cost" => implode(",",$favourable_cost),
            );
            
            $bool = db("enter_meal")->insert($enter);
            if ($bool) {
                $this->success("添加成功", url("admin/Control/control_meal_index"));
            } else {
                $this->success("添加失败", url('admin/Control/control_meal_index'));
            }
        }
            
        
        return view("control_meal_add");
    }

    /**
     * [入驻套餐首页显示]
     * 郭杨
     */
    public function control_meal_status(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("enter_meal")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Control/control_meal_index"));
                } else {
                    $this->error("修改失败", url("admin/Control/control_meal_index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("enter_meal")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Control/control_meal_index"));
                } else {
                    $this->error("修改失败", url("admin/Control/control_meal_index"));
                }
            }
        }
    }


    /**
     * [入驻套餐编辑保存]
     * 保存
     */
    public function control_meal_update(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $data = $request->param();
            $data["cost"] = implode(",",$data["cost"]);
            $data["favourable_cost"] = implode(",",$data["favourable_cost"]);

            $bool = db("enter_meal")->where("id",$id)->update($data);
            if ($bool) {
                $this->success("编辑成功", url("admin/Control/control_meal_index"));
            } else {
                $this->success("编辑失败", url('admin/Control/control_meal_index'));
            }
        }
    }



    /**
     * [入驻套餐首页显示]
     * 保存
     * 
     */
    public function control_meal_edit($id)
    {
        $meal = db("enter_meal")->where("id",$id)->find();
        $meal_edit = array(
            "id" => $meal["id"],
            "name" => $meal["name"],
            "sort_number"=> $meal["sort_number"],
            "status" => $meal["status"],
            "cost" => explode(",",$meal["cost"]),
            "favourable_cost" => explode(",",$meal["favourable_cost"])
        );
        
        return view("control_meal_edit",["meal_edit"=>$meal_edit]);
        
    }

    /**
     * [入驻订单]
     * 郭杨
     */    
    public function control_order_index(){
        $order = db("store")->paginate(20,false, [
            'query' => request()->param(),
        ]);
        // halt($order);     
        return view("control_order_index");
    }


    /**
     * [添加入驻订单]
     * 郭杨
     */    
    public function control_order_add(){     
        return view("control_order_add");
    }



    /**
     * [店铺分析]
     * 郭杨
     */    
    public function control_store_index(){     
        return view("control_store_index");
    }
    
 }