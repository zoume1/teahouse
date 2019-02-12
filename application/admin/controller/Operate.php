<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/12 0012
 * Time: 15:09
 */

namespace app\admin\controller;


use think\Controller;

class Operate extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:运营模块页
     **************************************
     * @return \think\response\View
     */
    public function operate_index(){
        return view("operate_index");
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:常见问题
     **************************************
     * @return \think\response\View
     */
    public function operate_problem(){
        return view("operate_problem");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:常见问题添加
     **************************************
     * @return \think\response\View
     */
    public function operate_problem_add(){
        return view("operate_problem_add");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:协议合同
     **************************************
     * @return \think\response\View
     */
    public function operate_contract(){
        return view("operate_contract");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:协议合同编辑
     **************************************
     * @return \think\response\View
     */
    public function operate_contract_edit(){
        return view("operate_contract_edit");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:消息提醒
     **************************************
     * @return \think\response\View
     */
    public function operate_message(){
        return view("operate_message");
    }
     /**
     **************李火生*******************
     * @param Request $request
     * Notes:消息提醒添加编辑
     **************************************
     * @return \think\response\View
     */
    public function operate_message_add(){
        return view("operate_message_add");
    }

}