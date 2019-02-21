<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/2/21
 * Time: 15:21
 */
namespace app\index\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;


class Manage extends Controller
{



    /**
     * [问题列表]
     * 郭杨
     */
    public function problem_list()
    {

        $list = Db::name("problem")->select();
        if (!empty($list)) {
            return ajax_success('传输成功', $list);
        } else {
            return ajax_error("数据为空");

        }


        

    }
    /**
     * [问题列表描述]
     * 郭杨
     */
    public function problem_data(Request $request)
    {
        if ($request->isPost()) {
            $pid = $request->only(["pid"])["pid"];
            $problem_data = db("common_ailment")->where("pid",$pid)->select();
            if (!empty($problem_data)) {
                return ajax_success('传输成功', $problem_data);
            } else {
                return ajax_error("数据为空");

            }

        }

    }



    /**
     * [协议合同显示]
     * 郭杨
     */
    public function agreement_contract()
    {     
        $protocol = Db::name("protocol")->select();
        if (!empty($protocol)) {
            return ajax_success('传输成功', $protocol);
        } else {
            return ajax_error("数据为空");

        }

    }

}