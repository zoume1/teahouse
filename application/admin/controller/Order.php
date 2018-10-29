<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/24
 * Time: 9:56
 * 订单管理
 */
namespace app\admin\controller;

use think\Controller;

class Order extends Controller{


    /***
     * TODO：配件商订单开始
     */

    /**
     **************李火生*******************
     * @return \think\response\View
     * 订单列表
     **************************************
     */
    public function index(){

       return view('index');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     *订单编辑
     **************************************
     */
    public function edit(){
       return view('edit');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     *订单订单评价
     **************************************
     */
    public function evaluate(){
        return view('evaluate');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 订单评价详情
     **************************************
     */
    public function evaluate_details(){
        return view('evaluate_details');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     *订单维修售后
     **************************************
     */
    public function after_sale(){
        return view('after_sale');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     *订单维修售后待处理
     **************************************
     */
    public function after_sale_wait_handle(){
        return view('after_sale_wait_handle');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     *订单维修售后待发货
     **************************************
     */
    public function after_sale_wait_deliver(){
        return view('after_sale_wait_deliver');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     *发票列表
     **************************************
     */
    public function invoice(){
        return view('invoice');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     *发票信息
     **************************************
     */
    public function invoice_edit(){
        return view('invoice_edit');
    }


    /**
     * TODO:配件商订单结束
     */




    /**
     * TODO:平台商订单开始
     */
    /**
     **************李火生*******************
     * @return \think\response\View
     * 平台商服务商订单列表
     **************************************
     */
    public function platform_order_service_index(){
        return view('platform_order_service_index');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 平台商配件商订单列表
     **************************************
     */
    public function platform_order_parts_index(){
        return view('platform_order_parts_index');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 平台商售后服务
     **************************************
     *
     */
    public function platform_after_sale(){
        return view('platform_after_sale');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 平台商发票列表
     **************************************
     */
    public function platform_invoice_index(){
        return view('platform_invoice_index');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 平台商发票详情
     **************************************
     */
    public function platform_invoice_details(){
        return view('platform_invoice_details');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 平台商订单评价
     **************************************
     */
    public function platform_order_evaluate(){
        return view('platform_order_evaluate');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 平台商订单评价编辑
     **************************************
     */
    public function platform_order_evaluate_edit(){
        return view('platform_order_evaluate_edit');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 平台商订单设置
     **************************************
     */
    public function platform_order_set_up(){
        return view('platform_order_set_up');
    }



    /**
     * TODO:平台商订结束
     */



    /**
     * TODO:服务商订单开始
     */
    /**
     **************李火生*******************
     * @return \think\response\View
     * 服务商界面服务商订单列表
     **************************************
     */
    public function service_order_index(){
        return view('service_order_index');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 服务商界面订单评价
     **************************************
     */
    public function service_order_evaluate(){
        return view('service_order_evaluate');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 服务商界面订单评价
     **************************************
     */
    public function service_order_evaluate_edit(){
        return view('service_order_evaluate_edit');
    }

    /**
     * TODO:服务商订结束
     */


}