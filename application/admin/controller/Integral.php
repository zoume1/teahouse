<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/25
 * Time: 11:44
 */

namespace  app\admin\controller;

use think\Controller;

class Integral extends Controller{
    /**
     **************李火生*******************
     * @return \think\response\View
     * 积分中心
     **************************************
     */
    public function index(){
        return view('center');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 积分详情
     **************************************
     */
    public function detail(){
        return view('detail');
    }


}