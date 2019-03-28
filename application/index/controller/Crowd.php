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


}