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
            $depot = Db::name("store_house")->where("store_id",$store_id)->select();

            if(!empty($depot)){
                foreach($depot as $key => $value){
                    $house_order[$key] = Db::table("tb_house_order")
                                        ->field("tb_house_order.id,store_name,pay_time,goods_image,goods_id,end_time,goods_money,store_number,tb_goods.date,tb_store_house.number,tb_goods.goods_name,tb_wares.name")
                                        ->join("tb_goods","tb_house_order.goods_id = tb_goods.id",'left')  
                                        ->join("tb_store_house"," tb_store_house.id = tb_house_order.store_house_id",'left')                                      
                                        ->join("tb_wares","tb_wares.id = tb_goods.pid",'left')                                                                                                                                                              
                                        ->where(["tb_house_order.store_house_id"=>$value["id"],"tb_house_order.store_id"=>$store_id,"tb_house_order.member_id"=>$member_id])
                                        ->select();

                }               
                if(!empty($house_order)){
                    foreach($house_order as $key => $value){
                        if(empty($house_order[$key])){
                            unset($house_order[$key]);
                        }
                    }
                    $house_order = array_values($house_order);
                    return ajax_success("获取成功",$house_order);
                } else {
                    return ajax_error("该店铺没有存茶订单");
                }
            } else {
                return ajax_error("该店铺没有存茶仓库");
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
                ->sum("order_amount");
                        
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

    
}