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
     * [茶圈父级显示]
     * 郭杨
     */
    public function teacenter_data(Request $request)
    {
        if ($request->isPost()) {
            $store_id = $request->only(['uniacid'])['uniacid'];
            $tea = Db::name("goods_type")->field('name,icon_image,color,id')
                ->where('pid', 0)
                ->where("status", 1)
                ->where("store_id", $store_id)
                ->select();
            foreach($tea as $key => $value){
                $res = db("goods_type")
                    ->where("pid",$value['id'])
                    ->where("store_id", $store_id)
                    ->field("name,id")
                    ->find();
                $tea[$key]["tid"] = $res["id"];
                $tea[$key]["activity_name"] = $res["name"];
               
            }
            if (!empty($tea)) {
                return ajax_success('传输成功', $tea);
            } else {
                return ajax_error("数据为空");

            }


        }

    }
}