<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/23
 * Time: 17:38
 */
namespace app\admin\controller;
use think\Controller;

class Brand extends Controller{

    /**
     * 商品品牌
     * 陈绪
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function index(){

        return view("brand_index");

    }



    /**
     * 商品添加
     * 陈绪
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function add(){

        return view("brand_add");

    }



    /**
     * 商品入库
     * 陈绪
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function save(){



    }




    /**
     * 商品编辑
     * 陈绪
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function edit(){

        return view("brand_edit");

    }



    /**
     * 商品更新
     * 陈绪
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function updata(){


    }




    /**
     * 商品删除
     * 陈绪
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function del(){


    }

}