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
                ->order("sort_number desc")
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
                ->order("sort_number desc")
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
                    
                    $crowd[$key]["collecting_money"] =  db("crowd_special")->where("goods_id",$crowd[$key]["id"])->sum("collecting_money"); //已筹款金额
                    $crowd[$key]["collecting_money"] = sprintf("%.2f", $crowd[$key]["collecting_money"]);
                    $crowd[$key]["collecting"] =  db("crowd_special")->where("goods_id",$crowd[$key]["id"])->sum("collecting"); //已筹款人数
                    if($crowd[$key]["collecting_money"] > 0){
                        $crowd[$key]["centum"] = intval(($crowd[$key]["collecting_money"]/$special[$key]["price"])*100);
                    } else {
                        $crowd[$key]["centum"] = 0;
                    }
                    if($crowd[$key]["goods_member"] == 1){
                        $crowd[$key]["cost"] = sprintf("%.2f",$special[$key]["cost"] * $discount) ;
                    } else {
                        $crowd[$key]["cost"] = sprintf("%.2f",$special[$key]["cost"]); //显示价格
                    }
                    

                    //会员范围
                    $crowd[$key]["collecting"] = db("crowd_special")->where("goods_id",$crowd[$key]["id"])->sum("collecting");//众筹人数
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
                ->field("id,project_name,end_time,goods_sign,goods_show_image,goods_show_images,company_name,company_name1,company_time,goods_text,team,text,scope,goods_member")
                ->select();
            
            if(!empty($crowd)){
                foreach($crowd as $key => $value)
                {
                    $crowd[$key]["goods_show_images"] =  explode(",",$crowd[$key]["goods_show_images"]);
                    $crowd[$key]["days"] = intval(($crowd[$key]["end_time"]-$date_time)/86400);
                    if(!empty($crowd[$key]['goods_sign'])){
                        $crowd[$key]["goods_sign"] = json_decode($crowd[$key]["goods_sign"],true);
                    }
                    $special[$key] = db("crowd_special")
                        ->where("goods_id",$id)
                        ->field("price,cost,collecting_money,collecting,state,stock")
                        ->limit(1)
                        ->order("cost asc")
                        ->find();
                    $standard = db("crowd_special")
                        ->where("goods_id",$id)
                        ->field("id,name,images,cost,story,stock,limit")
                        ->order("cost asc")                                                                                                                                                                                                                                                                                                                                                                                                                                                          
                        ->select();
                    $crowd[$key]["state"] = $special[$key]["state"];
                    
                    if($crowd[$key]["goods_member"] == 1){
                        $crowd[$key]["cost"] = sprintf("%.2f",$special[$key]["cost"] * $discount);
                        foreach($standard as $m => $n){
                            $standard[$m]["cost"] = sprintf("%.2f",$standard[$m]["cost"]* $discount);
                        }
                    } else {
                        $crowd[$key]["cost"] = sprintf("%.2f",$special[$key]["cost"]);
                    }
                    $crowd[$key]["standard"] = $standard;
                    $crowd[$key]["centum"] = intval(($special[$key]["collecting_money"])/($special[$key]["cost"]*$special[$key]["stock"])*100);  //百分比
                    $crowd[$key]["collecting"] = $special[$key]["collecting"];
                    $crowd[$key]["collecting_money"] = sprintf("%.2f",$special[$key]["collecting_money"]);
                                      
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
            $re = __DIR__;
            halt($re);
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
                    
                    if($crowd[$key]["goods_member"] == 1){
                        $crowd[$key]["cost"] = sprintf("%.2f",$special[$key]["cost"] * $discount);
                    } else {
                        $crowd[$key]["cost"] = sprintf("%.2f",$special[$key]["cost"]);
                    }
                    $crowd[$key]["centum"] = intval(($special[$key]["collecting_money"]/$special[$key]["price"])*100);
                    $crowd[$key]["collecting"] = sprintf("%.2f",$special[$key]["collecting"]);
                    
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
            // $money = $request->only('money')['money'];
            $money = 0.01;
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
             //获取用户余额
            $balance=db('member')->where('member_id',$member_id)->field('member_wallet,member_recharge_money')->find();
            $bb=$balance['member_wallet']+$balance['member_recharge_money'];
            $money=round($bb,2);
            $rest_id = db("reward")->insertGetid($data);
            if($rest_id){
                $order_datas = db("reward")
                            ->field("money,order_number,crowd_name")
                            ->where('id',$rest_id)
                            ->where('member_id',$member_id)
                            ->find();
                $order_datas['order_type']=0; 
                $order_datas['coupon_type']=2;  
                $order_datas['balance']=$money;
                return ajax_success('下单成功',$order_datas);
            } else {
                return ajax('失败',['status'=>0]);
            }
        }
    }


    /**
     **************郭杨*******************
     * @param Request $request
     * Notes:众筹商品运费
     **************************************
     */
    public function getaAnsporTation(Request $request){
        if($request->isPost()){
            $goods_id = $request->only("goods_id")["goods_id"];//商品id
            $are = $request->only("are")["are"];//地区
            $standard = $request->only("goods_standard_id")["goods_standard_id"];//规格id
            $res = array();
            if(!empty($goods_id)){
                foreach($goods_id as $key => $value){
                    $goods = db("crowd_goods")->where("id",$value)->find();
                    $goods["monomer"] = db("crowd_special")->where("id",$standard[$key])->value("offer");
                    $data["goods_id"] = $value;
                    if($goods['goods_franking'] != 0){
                        $data["collect"] = $goods["goods_franking"]; //统一邮费
                        $data["markup"] = 0; //统一邮费
                    }else{
                        $templet_name = explode(",",$goods["templet_name"]);
                        $templet_id = explode(",",$goods["templet_id"]);
                        $monomer = $goods["monomer"];
                        $tempid = array_search($monomer,$templet_name);
                        $express_id = $templet_id[$tempid];
                        $rest = db("express")->where("id",$express_id)->find();
                        if(!empty($rest)){
                            $are_block = explode(",",$rest["are"]);
                            if(in_array($are,$are_block)){
                                $data["collect"] = $rest["price"];//首费
                                $data["markup"] = $rest["markup"];//续费
                            } else {
                                $data["collect"] = $rest["price_two"];//首费
                                $data["markup"] = $rest["markup_two"];//续费
                            }
                        } else {
                            return ajax_error("没有运费模板");
                        }
                    }
                    array_push($res,$data);                    
                }
                return ajax_success("返回成功",$res);
            } else {
                return ajax_error("没有运费模板");
            }
        }

    }


    

}