<?php
/**
 * Created by PhpStorm.
 * User: CHEN
 * Date: 2018/7/10
 * Time: 18:20
 */
namespace app\admin\controller;

use think\Controller;
use think\Config;
use think\captcha\Captcha;
use think\Request;
class Index extends Controller{

    /**
     * [后台首页]
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     * 陈绪
     */
    public function index(Request $request){
        $menu_list = Config::get("menu_list");
        return view("index",["menu_list"=>$menu_list]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:通过菜单栏id获取他权限内的信息
     **************************************
     */
    public function  get_id_return_info(Request $request){
        if($request->isPost()){
            $id =$request->only(['id'])['id'];//当前id
            //查找当前账号权限
            if(!empty($id)){
               return ajax_success("成功获取",$id);
            }else{
                return ajax_error("没有获取到id");
            }

        }
    }


}