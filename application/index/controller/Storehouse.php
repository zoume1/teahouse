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
            // $house = Db::name("house_order")
            // ->where(["store_id"=>$store_id,"member_id"=>$member_id])
            // ->select();
            // halt($depot);
            if(!empty($depot)){
                foreach($depot as $key => $value){
                    $house_order[$key] = Db::name("house_order")
                                        ->where(["store_house_id"=>$value["id"],"store_id"=>$store_id,"member_id"=>$member_id])
                                        ->select();
                }
            }
            halt($house_order);
        }

    }
}