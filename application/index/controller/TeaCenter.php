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

            $tea = Db::name("goods_type")->field('name,icon_image,color')->where('pid', 0)->where("status", 1)->select();

            if (!empty($tea)) {
                return ajax_success('传输成功', $tea);
            } else {
                return ajax_error("传输失败");

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
            $resdata = Db::name("goods_type")->field('name,icon_image,color')->where('pid', $id)->where("status", 1)->select();
            
            if (!empty($resdata)) {
                return ajax_success('传输成功', $resdata);
            } else {
                return ajax_error("传输失败");

            }


        }


    }



}