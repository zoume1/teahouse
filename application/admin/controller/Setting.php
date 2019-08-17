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
use think\db;
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
        $store_id = Session::get('store_id');
        $setting = Db::name("setting")->where("store_id",$store_id)->select();
        return view("setting_index",['setting'=>$setting]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:高级分销设置
     **************************************
     * @return \think\response\View
     */
    public function setting_update(Request $request){
        $store_id = Session::get('store_id');
        $data = input();
        $bool = Db::name("setting")->where("store_id",$store_id)->update($data);
        if ($bool) {
            $this->success("编辑成功", url("admin/Setting/setting_index"));
        } else {
            $this->error("编辑失败", url("admin/Setting/setting_index"));
        }
        
    }

}