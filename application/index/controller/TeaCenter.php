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

    public function teacenter_data(Request $request)
    {
        if ($request->isPost()) {

            $tea = Db::name("goods_type")->field('name,icon_image,color')->where("status", 1)->select();

            if (!empty($tea)) {
                return ajax_success('传输成功', $tea);

            } else {
                return ajax_error("传输失败");

            }


        }


    }



}