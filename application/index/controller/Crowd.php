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
            $store_id = $request->only(['uniacid'])['uniacid'];
            $crowd = db("crowd_goods")
                ->where("label",1)
                ->where("status",1)
                ->where("state",1)
                ->where("end_time",">=",$date_time)
                ->where("store_id","EQ",$store_id)	
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
            $store_id = $request->only(['uniacid'])['uniacid'];
            $member_id = $request->only('member_id')['member_id'];//会员id
            $member = db("member")->where('member_id',$member_id)->find(); //会员等级
            $member_grade_name = $member['member_grade_name']; //会员名称
            $member_grade_id = $member['member_grade_id'];
            $discount = db("member_grade")->where("member_grade_id", $member_grade_id)->value("member_consumption_discount");//会员优惠比例 
 

            $crowd = Db::name("crowd_goods")
                ->where("label",1)
                ->where("state",1)
                ->where("store_id","EQ",$store_id)	
                ->where("end_time",">=",$date_time)
                ->field("id,project_name,end_time,goods_show_image,scope,goods_member")
                ->select();
            
            if(!empty($crowd)){
                foreach($crowd as $key => $value)
                {
                    if(!empty($crowd[$key]["scope"])){
                        $crowd[$key]["scope"] = explode(",",$crowd[$key]["scope"]);
                    }
                    $crowd[$key]["days"] = intval(($crowd[$key]["end_time"]-$date_time)/86400);
                    $special[$key] = db("crowd_special")
                        ->where("goods_id",$crowd[$key]["id"])
                        ->field("price,cost,collecting_money,collecting")
                        ->limit(1)
                        ->order("cost asc")
                        ->find();
                    $crowd[$key]["cost"] = $special[$key]["cost"];

                    if($crowd[$key]["goods_member"] == 1){
                        $crowd[$key]["cost"] = $special[$key]["cost"] * $discount ;
                    }
                    
                    if(!empty($special[$key]["collecting_money"])){
                        $crowd[$key]["centum"] = intval(($special[$key]["collecting_money"]/$special[$key]["price"])*100);
                    } else {
                        $crowd[$key]["centum"] = 0;
                    }
                    //会员范围
                    $crowd[$key]["collecting"] = $special[$key]["collecting"];
                    if(!empty($crowd[$key]["scope"])){
                        if(!in_array($member_grade_name,$crowd[$key]["scope"])){ 
                            unset($crowd[$key]);
                        }
                    }
                    
                }
                $arr = array_values($crowd);           
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
            $member_id = $request->only('member_id')['member_id'];
            $store_id = $request->only(['uniacid'])['uniacid'];
            $member = db("member")->where('member_id',$member_id)->find(); //会员等级
            $member_grade_name = $member['member_grade_name']; //会员名称
            $member_grade_id = $member['member_grade_id'];
            $discount = db("member_grade")->where("member_grade_id", $member_grade_id)->value("member_consumption_discount");//会员优惠比例 
            $id = $request->only('id')['id'];
            $date_time = time();
            $crowd = Db::name("crowd_goods")
                ->where("id",$id)
                ->where("store_id","EQ",$store_id)	
                ->field("id,project_name,end_time,goods_show_image,goods_show_images,company_name,company_name1,company_time,goods_text,team,text,scope,goods_member")
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
                    
                    if($crowd[$key]["goods_member"] == 1){
                        $crowd[$key]["cost"] = $special[$key]["cost"] * $discount ;
                        foreach($standard as $m => $n){
                            $standard[$m]["cost"] = $standard[$m]["cost"]* $discount ;
                        }
                    }
                    $crowd[$key]["standard"] = $standard;
                    $crowd[$key]["centum"] = intval(($special[$key]["collecting_money"]/$special[$key]["price"])*100);
                    $crowd[$key]["collecting"] = $special[$key]["collecting"];
                    $crowd[$key]["collecting_money"] = $special[$key]["collecting_money"];
                                      
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
            $store_id = $request->only(['uniacid'])['uniacid'];
            $member_id = $request->only('member_id')['member_id'];//会员id
            $member = db("member")->where('member_id',$member_id)->find(); //会员等级
            $member_grade_name = $member['member_grade_name']; //会员名称
            $member_grade_id = $member['member_grade_id'];
            $discount = db("member_grade")->where("member_grade_id", $member_grade_id)->value("member_consumption_discount");//会员优惠比例
            $crowd = Db::name("crowd_goods")
            ->where("label",1)
            ->where("store_id",'EQ',$store_id)
            ->where("end_time","<=",$date_time)
            ->field("id,project_name,end_time,goods_show_image")
            ->select();

            if(!empty($crowd)){
                foreach($crowd as $key => $value)
                {
                    if(!empty($crowd[$key]["scope"])){
                        $crowd[$key]["scope"] = explode(",",$crowd[$key]["scope"]);
                    }
                    $crowd[$key]["days"] = intval(($crowd[$key]["end_time"]-$date_time)/86400);
                    $special[$key] = db("crowd_special")
                        ->where("goods_id",$crowd[$key]["id"])
                        ->field("price,cost,collecting_money,collecting")
                        ->limit(1)
                        ->order("cost asc")
                        ->find();
                    $crowd[$key]["cost"] = $special[$key]["cost"];
                    if($crowd[$key]["goods_member"] == 1){
                        $crowd[$key]["cost"] = $special[$key]["cost"] * $discount ;
                    }
                    $crowd[$key]["centum"] = intval(($special[$key]["collecting_money"]/$special[$key]["price"])*100);
                    $crowd[$key]["collecting"] = $special[$key]["collecting"];
                    
                    if(!empty($crowd[$key]["scope"])){
                        if(!in_array($member_grade_name,$crowd[$key]["scope"])){ 
                            unset($crowd[$key]);
                        }
                    }
                }
                $crowd = array_values($crowd);
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


   /**
     * [众筹商品打赏生成订单]
     * 郭杨
     */
    public function crowd_reward(Request $request)
    {
        if($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $member_id = $request->only('member_id')['member_id'];
            $money = $request->only('money')['money'];
            $id = $request->only('id')['id'];
            $crowd = db("crowd_special")->where("id",$id)->find();       
            $user_information = db("member")->where("member_id",$member_id)->find();
            $create_time = time();
            $time = date("Y-m-d",time());
            $v = explode('-',$time);
            $time_second = date("H:i:s",time());
            $vs = explode(':',$time_second);
            $order_number ="DS".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].($member_id+1001); //订单编号

            $data = array(
                "money"=>$money,
                "special_id"=>$id,
                "member_id"=>$member_id,
                "user_name"=>$user_information["member_name"],
                "create_time"=>$create_time,
                "order_number"=>$order_number,
                "crowd_name"=>$crowd["name"],
                "status" => 1,
                "store_id"=>$store_id
            );

            $rest_id = db("reward")->insertGetid($data);
            if($rest_id){
                $order_datas = db("reward")
                            ->field("money,order_number,crowd_name")
                            ->where('id',$rest_id)
                            ->where('member_id',$member_id)
                            ->find();
                return ajax_success('下单成功',$order_datas);
            } else {
                return ajax('失败',['status'=>0]);
            }
        }
    }


    

}