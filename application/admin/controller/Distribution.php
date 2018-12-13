<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3 0003
 * Time: 18:21
 */

namespace  app\admin\controller;

use think\Controller;

class  Distribution extends  Controller{

    /**
     * [分销设置显示]
     * GY
     */
    public function setting_index()
    {
        $distribution = db("distribution") -> select();
        
        return view("setting_index",["distribution" =>$distribution ]);
    }


    /**
     * [分销设置编辑]
     * GY
     */
    public function setting_edit()
    {
        return view('setting_edit');
    }

    /**
     * [分销设置编辑]
     * GY
     */
    public function setting_save()
    {
        return view('setting_edit');
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:分销商品页面
     **************************************
     * @return \think\response\View
     */
    public function goods_index(){
        return view('goods_index');
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:分销商品添加
     **************************************
     * @return \think\response\View
     */
    public function goods_add(){
        return view('goods_add');
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:分销商品编辑
     **************************************
     * @return \think\response\View
     */
    public function goods_edit(){
        return view('goods_edit');
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:分销记录页面
     **************************************
     * @return \think\response\View
     */
    public function record_index(){
        return view('record_index');
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:分销成员页面
     **************************************
     * @return \think\response\View
     */
    public function member_index(){
        return view('member_index');
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:分销成员页面编辑
     **************************************
     * @return \think\response\View
     */
    public function member_edit(){
        return view('member_edit');
    }





}