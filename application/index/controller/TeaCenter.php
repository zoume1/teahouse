<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/8
 * Time: 15:21
 */
namespace app\index\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;


class TeaCenter extends Controller
{

    /**
     * [茶圈父级显示]
     * 郭杨
     */
    public function teacenter_data(Request $request)
    {
        if ($request->isPost()) {

            $tea = Db::name("goods_type")->field('name,icon_image,color,id')->where('pid', 0)->where("status", 1)->select();

            if (!empty($tea)) {
                return ajax_success('传输成功', $tea);
            } else {
                return ajax_error("数据为空");

            }


        }


    }


    /**
     * [茶圈分类显示]
     * 郭杨
     */
    public function teacenter_display(Request $request)
    {
        if ($request->isPost()){
            $id = $request->only(['id'])['id'];
            $resdata = Db::name("goods_type")->field('name,icon_image,color,id')->where('pid', $id)->where("status", 1)->select();
            
            if (!empty($resdata)) {
                return ajax_success('传输成功', $resdata);
            } else {
                return ajax_error("数据为空");

            }


        }


    }

    /**
     * [茶圈活动显示]
     * 郭杨
     */
    public function teacenter_activity(Request $request)
    {
        if ($request->isPost()){
            $res = $request->only(['id'])['id'];
            $activity = Db::name("teahost")->field('id,activity_name,classify_image,cost_moneny,start_time,commodity,label,marker,address,pid')->where("label", 1)->where("pid",$res)->select();
            if(empty($activity)){
                return ajax_error("下面没有活动");
            }
            foreach($activity as $key => $value){
                if($value["id"]){
                    $rest = db("goods_type")->where("id",$value['id'])->field("name")->find();
                    $activity[$key]["names"] = $rest["name"];
                }
            }
           
            if (!empty($activity)) {
                return ajax_success('传输成功', $activity);
            } else {
                return ajax_error("数据为空");

            }


        }


    }


}