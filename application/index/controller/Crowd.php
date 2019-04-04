<?php

/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/3/28
 * Time: 15:21
 */
namespace app\index\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;


class Crowd extends Controller
{
    /**
     * [众筹商品首页显示]
     * 郭杨
     */
    public function crowd_index(Request $request)
    {
        if ($request->isPost()) {
            $date_time = time();
            $crowd = db("crowd_goods")
                ->where("label",1)
                ->where("status",1)
                ->where("state",1)
                ->field("id,project_name,goods_describe,end_time,goods_show_image")
                ->select();
            if(!empty($crowd)){
                foreach($crowd as $key => $value){
                    $crowd[$key]["days"] = intval(($crowd[$key]["end_time"]-$date_time)/86400);
                }
                $count = count($crowd);
                if($count > 6){
                    $arandom = array_rand($crowd,6);
                    foreach($crowd as $key => $value){
                        if(in_array($key,$arandom)){
                            $arr[] = $value;
                        }
                    }
                    return ajax_success('传输成功', $arr);
                } else {
                    return ajax_success('传输成功', $crowd);
                }
            } else {
                return ajax_error("数据为空");
            }
        }
    }



    /**
     * [正在众筹商品]
     * 郭杨
     */
    public function crowd_now(Request $request)
    {
        if ($request->isPost()) {
            $date_time = time();
            $record = Db::name("crowd_goods")
            ->where("label",1)
            ->where("status",1)
            ->count();

            $crowd = Db::name("crowd_goods")
                ->where("label",1)
                ->where("state",1)
                ->where("end_time",">=",$date_time)
                ->field("id,project_name,end_time,goods_show_image")
                ->select();
                
            if(!empty($crowd)){
                foreach($crowd as $key => $value)
                {
                    $crowd[$key]["days"] = intval(($crowd[$key]["end_time"]-$date_time)/86400);
                    $special[$key] = db("crowd_special")
                        ->where("goods_id",$crowd[$key]["id"])
                        ->field("price,cost,collecting_money,collecting")
                        ->limit(1)
                        ->order("cost asc")
                        ->find();
                    $crowd[$key]["cost"] = $special[$key]["cost"];
                    $crowd[$key]["centum"] = intval(($special[$key]["collecting_money"]/$special[$key]["price"])*100);
                    $crowd[$key]["collecting"] = $special[$key]["collecting"];
                    
                }
                $count = count($crowd);
                $arandom = array_rand($crowd,$count);
                foreach($crowd as $key => $value){
                    if(in_array($key,$arandom)){
                        $arr[] = $value;
                    }
                }
                ajax_success('传输成功', $arr);
            } else {
                return ajax_error("数据为空");
            }
        }
    }


    /**
     * [支持众筹商品]
     * 郭杨
     */
    public function crowd_support(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only('id')['id'];
            $date_time = time();
            $crowd = Db::name("crowd_goods")
                ->where("id",$id)
                ->field("id,project_name,end_time,goods_show_image,goods_show_images,company_name,company_name1,company_time,goods_text,team,text")
                ->select();
            
            if(!empty($crowd)){
                foreach($crowd as $key => $value)
                {
                    $crowd[$key]["goods_show_images"] =  explode(",",$crowd[$key]["goods_show_images"]);
                    $crowd[$key]["days"] = intval(($crowd[$key]["end_time"]-$date_time)/86400);
                    $special[$key] = db("crowd_special")
                        ->where("goods_id",$id)
                        ->field("price,cost,collecting_money,collecting,state")
                        ->limit(1)
                        ->order("cost asc")
                        ->find();
                    $standard = db("crowd_special")
                        ->where("goods_id",$id)
                        ->field("id,name,images,cost,story,stock,limit")
                        ->order("cost asc")
                        ->select();
                    $crowd[$key]["state"] = $special[$key]["state"];
                    $crowd[$key]["cost"] = $special[$key]["cost"];
                    $crowd[$key]["centum"] = intval(($special[$key]["collecting_money"]/$special[$key]["price"])*100);
                    $crowd[$key]["collecting"] = $special[$key]["collecting"];
                    $crowd[$key]["collecting_money"] = $special[$key]["collecting_money"];
                    $crowd[$key]["standard"] = $standard;
                    
                    
                }
                
                ajax_success('传输成功', $crowd);
            } else {
                return ajax_error("数据为空");
            }
        }
    }


    /**
     * [往期众筹商品]
     * 郭杨
     */
    public function crowd_period(Request $request)
    {
        if ($request->isPost()){
            $date_time = time();
            $crowd = Db::name("crowd_goods")
            ->where("label",1)
            ->where("end_time","<=",$date_time)
            ->field("id,project_name,end_time,goods_show_image")
            ->select();

            if(!empty($crowd)){
                foreach($crowd as $key => $value)
                {
                    $crowd[$key]["days"] = intval(($crowd[$key]["end_time"]-$date_time)/86400);
                    $special[$key] = db("crowd_special")
                        ->where("goods_id",$crowd[$key]["id"])
                        ->field("price,cost,collecting_money,collecting")
                        ->limit(1)
                        ->order("cost asc")
                        ->find();
                    $crowd[$key]["cost"] = $special[$key]["cost"];
                    $crowd[$key]["centum"] = intval(($special[$key]["collecting_money"]/$special[$key]["price"])*100);
                    $crowd[$key]["collecting"] = $special[$key]["collecting"];
                    
                }
                $count = count($crowd);
                if($count > 1){
                    $arandom = array_rand($crowd,$count);
                    foreach($crowd as $key => $value){
                        if(in_array($key,$arandom)){
                            $arr[] = $value;
                        }
                    }
                } else {
                    $arr = $crowd;
                }
                ajax_success('传输成功', $arr);
            } else {
                return ajax_error("数据为空");
            }
        }
    }

}