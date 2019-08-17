<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27 0027
 * Time: 15:20
 */
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\captcha\Captcha;

class Setting extends Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:高级分销设置
     **************************************
     * @return \think\response\View
     */
    public function setting_index(Request $request){
        if($request->isPost()){
            
        }
        return view("setting_index");
    }

}