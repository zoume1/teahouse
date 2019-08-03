<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/6/28
 * Time: 15:21
 */
namespace app\index\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;


class Storehouse extends Controller
{

    public static $restel = 0;
    /**
     * @param int $uniacid
     * @param int member_id
     * [店铺小程序前端存茶数据]
     * @return 成功时返回，其他抛异常
     */
    public function getStoreData(Request $request)
    {
        if ($request->isPost()) {
            $store_id = $request->only(['uniacid'])['uniacid'];
            $member_id = $request->only(['member_id'])['member_id'];
            $time = time();
            $depot = Db::name("store_house")->where("store_id",$store_id)->select();
            halt($depot);
            foreach($depot as $kk => $va){
                $depot_name[] = $va['number'];
            }
            if(isset($member_id) && isset($store_id)){
                if(!empty($depot)){
                    foreach($depot as $key => $value){
                    $house_order[$key] = Db::table("tb_house_order")
                                        ->field("tb_house_order.id,store_unit,store_house_id,pay_time,goods_image,special_id,goods_id,end_time,goods_money,store_number,tb_goods.date,tb_store_house.number,cost,store_unit,tb_goods.goods_name,brand,goods_bottom_money,tb_wares.name,tb_store_house.unit,tb_store_house.name store_name")
                                        ->join("tb_goods","tb_house_order.goods_id = tb_goods.id",'left')  
                                        ->join("tb_store_house"," tb_store_house.id = tb_house_order.store_house_id",'left')                                      
                                        ->join("tb_wares","tb_wares.id = tb_goods.pid",'left')  
                                        ->where("tb_house_order.status",">",2)                                                                                                                                                            
                                        ->where("tb_house_order.order_quantity",">",0)                                                                                                                                                            
                                        ->where(["tb_house_order.store_id"=>$store_id, "tb_house_order.store_house_id" =>$depot[$key]['id'] ,"tb_house_order.member_id"=>$member_id])
                                        ->order("order_create_time asc")
                                        ->select();   
                    }
                    halt($house_order);
                        $count_number = count($house_order);
                        for($i = 0 ; $i < $count_number ; $i++){
                            foreach($house_order[$i] as $zt => $kl){
                                $house_order[$i][$zt]["store_number"] = explode(',', $house_order[$i][$zt]["store_number"]);
                                $house_order[$i][$zt]["unit"] = explode(',', $house_order[$i][$zt]["unit"]);
                                $house_order[$i][$zt]["cost"] = explode(',', $house_order[$i][$zt]["cost"]);
                                $rest_key = array_search($house_order[$i][$zt]["store_unit"],$house_order[$i][$zt]["unit"]);
                                $house_order[$i][$zt]["unit_price"] = $house_order[$i][$zt]["cost"][$rest_key];

                                if($time < $house_order[$i][$zt]["end_time"]){
                                    $house_order[$i][$zt]['limit_time'] = round(($house_order[$i][$zt]["end_time"]-$time)/86400); //剩余天数
                                    if($house_order[$i][$zt]['limit_time'] > 30){
                                        $house_order[$i][$zt]['limit_time'] = 0; //未到期
                                    } else {
                                        $house_order[$i][$zt]['limit_time'] = 1; //即将到期
                                    }
                                } else {
                                    $house_order[$i][$zt]['limit_time'] = 2; //已到期
                                }
                                if(!empty($house_order[$i][$zt]['special_id'])){
                                    $house_order[$i][$zt]['goods_bottom_money'] = Db::name("special")->where("id",$house_order[$i][$zt]['special_id'])->value("line");
                                }
                            }
                        }
                    
                    foreach($depot_name as $ds => $nm){
                        $depots_names[$ds]['name'] = $nm;
                        $depots_names[$ds]['getArr'] = $house_order[$ds];   
                        
                        if(empty($depots_names[$ds]['getArr'])){
                            unset($depots_names[$ds]);
                        }
                    }
                    $depots_names = array_values($depots_names);
                    if(!empty($depots_names)){
                        return ajax_success("传输成功",$depots_names);
                    } else {
                        return ajax_error("没有存茶订单");
                    }
                } else {
                    return ajax_error("该店铺没有存茶仓库");
                }
            } else {
                return ajax_error("参数有误");
            }         
        }
    }

    /**
     * @param int $uniacid
     * @param int member_id
     * [店铺小程序前端存茶总价值]
     * @return 成功时返回，其他抛异常
     */
    public function theStoreValue(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            if(isset($data['uniacid']) && isset($data['member_id'])){
                $depot  = Db::name("house_order")
                ->where(["store_id"=>$data['uniacid'],"member_id"=>$data['member_id']])
                ->where("status",'>',1)
                ->sum("order_quantity * goods_money");
                        
                $depot_value = round($depot,2);
                return json_encode(array("status"=>1,"info"=>"获取成功","data"=>['order_real_pay'=>$depot_value]));
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }              
    }


     /**
     * @param int $uniacid
     * @param int member_id
     * [店铺小程序前端所有仓库]
     * @return 成功时返回，其他抛异常
     */
    public function getStoreHouse(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            if(isset($data['uniacid']) && isset($data['member_id'])){
                $depot  = Db::table("tb_house_order")
                        ->field("tb_house_order.store_house_id,tb_store_house.number,name")
                        ->join("tb_store_house","tb_store_house.id = tb_house_order.store_house_id",'left')
                        ->where(["tb_house_order.store_id"=>$data['uniacid'],"tb_house_order.member_id"=>$data['member_id']])
                        ->group("tb_house_order.store_house_id")
                        ->select();
                        
                if(!empty($depot)){  
                    return ajax_success("传输成功",$depot);
                } else {
                    return ajax_error("该用户未进行存茶操作");
                }
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }              
    }

    /**
     * @param int $uniacid
     * @param int member_id
     * @param int store_house_id
     * [店铺小程序前端选择仓库]
     * @return 成功时返回，其他抛异常
     */
    public function doHouseOrder(Request $request)
    {
        if ($request->isPost()){
            $data = input();
            $time = time();
            $rest_number = self::$restel;
            if(isset($data['uniacid']) && isset($data['member_id']) && isset($data['store_house_id'])){
                $house_name = Db::name("store_house")->where("id",$data['store_house_id'])->value("number");
                if(empty($house_name)){
                    return ajax_error("获取仓库失败");
                }
                $house_order = Db::table("tb_house_order")
                                    ->field("tb_house_order.id,store_name,pay_time,goods_image,special_id,goods_id,end_time,goods_money,store_number,store_unit,tb_goods.date,tb_store_house.number,tb_goods.goods_name,brand,goods_bottom_money,tb_wares.name")
                                    ->join("tb_goods","tb_house_order.goods_id = tb_goods.id",'left')  
                                    ->join("tb_store_house"," tb_store_house.id = tb_house_order.store_house_id",'left')                                      
                                    ->join("tb_wares","tb_wares.id = tb_goods.pid",'left')                                                                                                                                                              
                                    ->where(["tb_house_order.store_id"=>$data['uniacid'],"tb_house_order.member_id"=>$data['member_id'],"tb_house_order.store_house_id"=>$data['store_house_id']])
                                    ->order("end_time desc")
                                    ->select(); 
                

                if(!empty($house_order)){
                    foreach($house_order as $k => $l){
                        $house_order[$k]["store_number"] = explode(',', $house_order[$k]["store_number"]);
                        if($time < $house_order[$k]["end_time"]){
                            $house_order[$k]['limit_time'] = round(($house_order[$k]["end_time"]-$time)/86400); //剩余天数
                            if($house_order[$k]['limit_time'] > 30){
                                $house_order[$k]['limit_time'] = 0; //未到期
                            } else {
                                $house_order[$k]['limit_time'] = 1; //即将到期
                            }
                        } else {
                            $house_order[$k]['limit_time'] = 2; //已到期
                        }
                        if(!empty($house_order[$k]['special_id'])){
                            $house_order[$k]['goods_bottom_money'] = Db::name("special")->where("id",$house_order[$k]['special_id'])->value("line");
                        }
                    }
                    $rest_house['name'] = $house_name;
                    $rest_house['getArr'] = $house_order;
                    $restul[$rest_number] = $rest_house;
                   
                    return ajax_success("发送成功",$restul);
                } else {
                    return ajax_error("该店铺没有存茶订单");
                }
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }              
    }


    /**
     * @param int $id
     * @param int member_id
     * @param int uniacid
     * [店铺小程序前端入仓详情]
     * @return 成功时返回，其他抛异常
     */
    public function takeOrderData(Request $request)
    {
        if ($request->isPost()){
            $data = input();
            if(isset($data['uniacid']) && isset($data['member_id']) && isset($data['id'])){
                $member_grade_id = Db::name("member")->where("member_id",$data['member_id'])->value("member_grade_id");
                $rank = Db::name("member_grade")->where("member_grade_id",$member_grade_id)->value("member_consumption_discount");
                $house_order = Db::table("tb_house_order")
                                    ->field("tb_house_order.id,pay_time,goods_image,special_id,goods_id,parts_order_number,end_time,order_quantity,goods_money,order_amount,store_number,store_unit,tb_store_house.number,tb_store_house.adress,tb_goods.goods_name,date,goods_new_money,goods_member,goods_bottom_money,brand,num,tb_goods.unit,tb_wares.name,tb_store_house.name store_name")
                                    ->join("tb_goods","tb_house_order.goods_id = tb_goods.id",'left') 
                                    ->join("tb_store_house"," tb_store_house.id = tb_house_order.store_house_id",'left')                                      
                                    ->join("tb_wares","tb_wares.id = tb_goods.pid",'left')                                                                                                                                                              
                                    ->where(["tb_house_order.store_id"=>$data['uniacid'],"tb_house_order.member_id"=>$data['member_id'],"tb_house_order.id"=>$data['id']])
                                    ->find();   
         
                if(!empty($house_order)){
                    $house_order['unit'] = explode(",", $house_order['unit']);
                    $house_order['num'] = explode(",",$house_order['num']);
                    $house_order["store_number"] = explode(',', $house_order["store_number"]);
                    if($house_order["goods_money"] > 0){
                        $house_order["scale"] = (($house_order["goods_new_money"] - $house_order["goods_money"]))*100/($house_order["goods_money"]);
                    } else {
                        $house_order["scale"] = 0;
                    }
                    if($house_order['goods_member'] != 1){
                        $rank = 1;
                    }
                        if(!empty($house_order['special_id'])){
                            $goods = Db::name("special")->where("id",$house_order['special_id'])->find();
                            $house_order['goods_bottom_money'] = $goods['line'];
                            $house_order['goods_new_money'] = $goods['price'] * $rank;

                        } else {
                            $house_order['goods_bottom_money'] = $house_order['goods_bottom_money'];
                            $house_order['goods_new_money'] = $house_order['goods_new_money'] * $rank;
                        }
                     
                    return ajax_success("获取成功",$house_order);
                } else {
                    return ajax_error("该店铺没有存茶订单");
                }
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }              
    }



    /**
     * @param int $id
     * @param int member_id
     * @param int uniacid
     * [店铺小程序前端订单出仓]
     * @return 成功时返回，其他抛异常
     */
    public function outPositionOrder(Request $request)
    {
        if ($request->isPost()){
            $data = input();
            if(isset($data['uniacid']) && isset($data['member_id']) && isset($data['id'])){
                $member_grade_id = Db::name("member")->where("member_id",$data['member_id'])->value("member_grade_id");
                $rank = Db::name("member_grade")->where("member_grade_id",$member_grade_id)->value("member_consumption_discount");
                $house_order = Db::table("tb_house_order")
                                    ->field("tb_house_order.id,pay_time,goods_image,special_id,goods_id,parts_order_number,end_time,order_quantity,goods_money,order_amount,store_number,store_unit,tb_store_house.number,tb_store_house.adress,tb_goods.goods_name,templet_id,date,goods_new_money,goods_member,goods_bottom_money,brand,num,tb_goods.unit,tb_wares.name,tb_store_house.name store_name")
                                    ->join("tb_goods","tb_house_order.goods_id = tb_goods.id",'left') 
                                    ->join("tb_store_house"," tb_store_house.id = tb_house_order.store_house_id",'left')                                      
                                    ->join("tb_wares","tb_wares.id = tb_goods.pid",'left')                                                                                                                                                              
                                    ->where(["tb_house_order.store_id"=>$data['uniacid'],"tb_house_order.member_id"=>$data['member_id'],"tb_house_order.id"=>$data['id']])
                                    ->find();   
         
                if(!empty($house_order)){
                    $house_order['unit'] = explode(",", $house_order['unit']);
                    $house_order['num'] = explode(",",$house_order['num']);
                    $house_order["store_number"] = explode(',', $house_order["store_number"]);
                    if($house_order['goods_member'] != 1){
                        $rank = 1;
                    }
                    if(!empty($house_order['special_id'])){
                        $goods = Db::name("special")->where("id",$house_order['special_id'])->find();
                        $house_order['goods_bottom_money'] = $goods['line'];
                    } 
                     
                    return ajax_success("获取成功",$house_order);
                } else {
                    return ajax_error("该店铺没有存茶订单");
                }
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }              
    }


    /**
     * @param int $goods_id
     * @param string  $are
     * @param int member_id
     * [店铺小程序前端订单出仓运费]
     * @return 成功时返回，其他抛异常
     */
    public function getHousePrice(Request $request)
    {
        if ($request->isPost()){
            $data = input();
            if(isset($data['goods_id']) && isset($data['member_id']) && isset($data['are'])){
                $goods_data = Db::name('goods')->where('id',$data['goods_id'])->find();
                if(empty($goods_data)){
                    return ajax_error("商品参数id不正确");
                }
                $templet_id = explode(",",$goods_data['templet_id']);
                $goods_franking = $goods_data['goods_franking'];
                if($goods_franking == 0){
                    foreach($templet_id as $kk => $yy){
                        $rest[$kk] =  db("express")->where("id",$templet_id[$kk])->find();
                        $rest[$kk]["are"] = explode(",",$rest[$kk]["are"]);
                        if(in_array($data['are'],$rest[$kk]["are"])){
                            $datas[$kk]["collect"] = $rest[$kk]["price"];//首费
                            $datas[$kk]["markup"] = $rest[$kk]["markup"];//续费
                        } else {
                            $datas[$kk]["collect"] = $rest[$kk]["price_two"];//首费
                            $datas[$kk]["markup"] = $rest[$kk]["markup_two"];//续费
                        }
                    }
                    return json_encode(array("status"=>1,"info"=>"发送成功","franking_type"=>1,"data"=>$datas));
                } else {
                    $datas['collect'] = $goods_franking;
                    return json_encode(array("status"=>1,"info"=>"发送成功","franking_type"=>2,"data"=>$datas));
                }
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }              
    }



    
}