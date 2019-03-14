<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13 0013
 * Time: 16:55
 */

namespace app\admin\controller;


use think\Controller;
use think\Db;
use  think\Request;
use think\paginator\driver\Bootstrap;
class  Order extends  Controller{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:初始订单页面
     **************************************
     * @return \think\response\View
     */
    public function order_index(){
        $data =Db::name("order")->order("order_create_time","desc")->paginate(20);
        return view("order_index",["data"=>$data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:订单确认发货（填写订单编号）
     **************************************
     */
    public function  order_confirm_shipment(Request $request){
        if($request->isPost()){
            $order_id =$request->only(["order_id"])["order_id"];
            $status =$request->only(["status"])["status"];
            $courier_number =$request->only(["courier_number"])["courier_number"];
            $express_name =$request->only(["express_name"])["express_name"];
            $data =[
                "status"=>$status,
                "courier_number"=>$courier_number,
                "express_name"=>$express_name
            ];
            $bool =Db::name("order")->where("id",$order_id)->update($data);
            if($bool){
                return ajax_success("发货成功",["status"=>1]);
            }else{
                return ajax_error("发货失败",["status"=>0]);
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:初始订单的基本信息
     **************************************
     * @param Request $request
     */
    public function order_information_return(Request $request){
        if($request->isPost()){
            $order_id =$request->only(["order_id"])["order_id"];
            if(!empty($order_id)){
                $data =Db::name("order")->where("id",$order_id)->find();
                if(!empty($data)){
                    $data["member_name"] =Db::name("member")->where("member_id",$data["member_id"])->value("member_name");
                    $data["goods_franking"] =Db::name("goods")->where("id",$data["goods_id"])->value("goods_franking");
                    return ajax_success("数据返回成功",$data);
                }else{
                    return ajax_error("没有数据信息",["status"=>0]);
                }
            }
        }
    }




    /**
     **************李火生*******************
     * @param Request $request
     * Notes:订单搜索
     **************************************
     */
    public function order_search(Request $request){
        if($request->isGet()){
            $search_a =input("search_a") ? input("search_a"):null;
            $order_type =input("order_type") ? input("order_type"):null;
            $time_min  =input("date_min") ? input("date_min"):null;
            $date_max  =input('date_max') ? input('date_max'):null;
            if(!empty($time_min)){
                $timemin =strtotime($time_min);
            }
            if(!empty($date_max)){
                /*添加一天（23：59：59）*/
                $t=date('Y-m-d H:i:s',$date_max+1*24*60*60);
                $timemax  =strtotime($t);
            }
            if(!empty($time_min) && empty($date_max)){
                //开始时间
            }else if (empty($time_min) && (!empty($date_max))){
                //结束时间
            }else if((!empty($timemin)) && (!empty($date_max))){
                //既有开始又有结束
            }
            if(!empty($search_a)){
                $condition =" `parts_order_number` like '%{$search_a}%' or `parts_goods_name` like '%{$search_a}%' or `user_account_name` like '%{$search_a}%' or `user_phone_number` like '%{$search_a}%'";
                $data =Db::name("order")
                    ->where($condition)
                    ->order("order_create_time","desc")
                    ->paginate(20);
            }else if (!empty($order_type)){
                $data =Db::name("order")
                    ->where("order_type",$order_type)
                    ->order("order_create_time","desc")
                    ->paginate(20);
            }else{
                $data =Db::name("order")->order("order_create_time","desc")->paginate(20);
            }


            return view("order_index",["data"=>$data]);
        }
    }


    /**
 **************李火生*******************
 * @param Request $request
 * Notes:待付款
 **************************************
 */
    public function order_way_pay(){
        $data =Db::name("order")
            ->order("order_create_time","desc")
            ->where("status",1)
            ->paginate(20);
        return view("order_index",["data"=>$data]);
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:待发货
     **************************************
     */
    public function order_wait_send(){
        $condition ="`status` = '2' or `status` = '3'";
        $data =Db::name("order")
            ->where($condition)
            ->order("order_create_time","desc")
            ->paginate(20);
        return view("order_index",["data"=>$data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:已发货
     **************************************
     */
    public function order_shipped(){
        $condition =" `status` = '4' or `status` = '5' ";
        $data =Db::name("order")
            ->order("order_create_time","desc")
            ->where($condition)
            ->paginate(20);
        return view("order_index",["data"=>$data]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:已完成
     **************************************
     */
    public function order_completed(){
        $data =Db::name("order")
            ->order("order_create_time","desc")
            ->where("status",8)
            ->paginate(20);
        return view("order_index",["data"=>$data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:已关闭
     **************************************
     * @return \think\response\View
     */
    public function order_closed(){
        $condition =" `status` = '9' or `status` = '10' ";
        $data =Db::name("order")
            ->order("order_create_time","desc")
            ->where($condition)
            ->paginate(20);
        return view("order_index",["data"=>$data]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:积分订单
     **************************************
     * @return \think\response\View
     */
    public function order_integral(){
        $data =Db::name("buyintegral")->order("order_create_time","desc")->paginate(20);
        return view("order_integral",["data"=>$data]);
    }




    /**
     **************李火生*******************
     * @param Request $request
     * Notes:交易设置
     **************************************
     * @return \think\response\View
     */
    public function transaction_setting(){
        $data =Db::name('order_setting')->find();
        return view("transaction_setting",['data'=>$data]);
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:订单设置更新
     **************************************
     * @param Request $request
     */
    public function order_setting_update(Request $request){
        if ($request->isPost())
        {
            $normal_time =$request->only(['normal'])['normal']; //正常订单
            $deliver_goods_time =$request->only(['deliver_goods'])['deliver_goods']; //发货超时
            $after_sale_time =$request->only(['after_sale'])['after_sale']; //售后
            $start_evaluate_time =$request->only(['start_evaluate'])['start_evaluate']; //自动好评
            $time =time();
            $details ="正常订单超过：".$normal_time." 分未付款，订单自动关闭,发货超过：".$deliver_goods_time."分未收货，订单自动完成,订单完成超过：". $after_sale_time."分自动结束交易，不能申请售后。订单完成超过：".$start_evaluate_time."分自动五星好评";
            $data =[
                'details'=>$details,
                'normal_time'=>$normal_time,
                'deliver_goods_time'=>$deliver_goods_time,
                'after_sale_time'=>$after_sale_time,
                'start_evaluate_time'=>$start_evaluate_time,
                'update_time'=>$time
            ];
            $bool =Db::name('order_setting')->where('order_setting_id',1)->update($data);
            if($bool){
                $this->success('更新成功');
            }else{
                $this->error('更新失败');
            }
        }
    }






    /**
     **************李火生*******************
     * @param Request $request
     * Notes:退款维权
     **************************************
     * @return \think\response\View
     */
    public function refund_protection_index(){
        $accessories=Db::name("after_sale")->order("operation_time","desc")->select();
        foreach ($accessories as $key => $value) {
            if ($value["id"]) {
                $res = db("member")->where("member_id", $value['member_id'])->field("member_phone_num,member_real_name,member_name")->find();
                $accessories[$key]["member_phone_num"] = $res["member_phone_num"];
                $accessories[$key]["member_real_name"] = $res["member_real_name"];
                $accessories[$key]["member_name"] = $res["member_name"];
                $images =Db::name("after_image")->field("url")->where("after_sale_id",$value["id"])->select();
                $accessories[$key]["images"] =$images;
            }
        }
        $all_idents = $accessories;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $accessories = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Order/refund_protection_index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $accessories->appends($_GET);
        $this->assign('access', $accessories->render());
        return view("refund_protection_index",["data" => $accessories]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:处理中
     **************************************
     * @return \think\response\View
     */
    public function refund_protection_processing(){
        $accessories=Db::name("after_sale")->where("status",3)->order("operation_time","desc")->select();
        foreach ($accessories as $key => $value) {
            if ($value["id"]) {
                $res = db("member")->where("member_id", $value['member_id'])->field("member_phone_num,member_real_name,member_name")->find();
                $accessories[$key]["member_phone_num"] = $res["member_phone_num"];
                $accessories[$key]["member_real_name"] = $res["member_real_name"];
                $accessories[$key]["member_name"] = $res["member_name"];
                $images =Db::name("after_image")->field("url")->where("after_sale_id",$value["id"])->select();
                $accessories[$key]["images"] =$images;
            }
        }
        $all_idents = $accessories;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $accessories = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Order/refund_protection_index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $accessories->appends($_GET);
        $this->assign('access', $accessories->render());
        return view("refund_protection_index",["data" => $accessories]);
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:收货中
     **************************************
     * @return \think\response\View
     */
    public function refund_protection_receipting(){
        $accessories=Db::name("after_sale")->where("status",2)->order("operation_time","desc")->select();
        foreach ($accessories as $key => $value) {
            if ($value["id"]) {
                $res = db("member")->where("member_id", $value['member_id'])->field("member_phone_num,member_real_name,member_name")->find();
                $accessories[$key]["member_phone_num"] = $res["member_phone_num"];
                $accessories[$key]["member_real_name"] = $res["member_real_name"];
                $accessories[$key]["member_name"] = $res["member_name"];
                $images =Db::name("after_image")->field("url")->where("after_sale_id",$value["id"])->select();
                $accessories[$key]["images"] =$images;
            }
        }
        $all_idents = $accessories;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $accessories = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Order/refund_protection_index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $accessories->appends($_GET);
        $this->assign('access', $accessories->render());
        return view("refund_protection_index",["data" => $accessories]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:换货完成
     **************************************
     * @return \think\response\View
     */
    public function  refund_protection_completed(){
        $accessories=Db::name("after_sale")->where("status",4)->order("operation_time","desc")->select();
        foreach ($accessories as $key => $value) {
            if ($value["id"]) {
                $res = db("member")->where("member_id", $value['member_id'])->field("member_phone_num,member_real_name,member_name")->find();
                $accessories[$key]["member_phone_num"] = $res["member_phone_num"];
                $accessories[$key]["member_real_name"] = $res["member_real_name"];
                $accessories[$key]["member_name"] = $res["member_name"];
                $images =Db::name("after_image")->field("url")->where("after_sale_id",$value["id"])->select();
                $accessories[$key]["images"] =$images;
            }
        }
        $all_idents = $accessories;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $accessories = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Order/refund_protection_index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $accessories->appends($_GET);
        $this->assign('access', $accessories->render());
        return view("refund_protection_index",["data" => $accessories]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:退款维权拒绝
     **************************************
     * @return \think\response\View
     */
    public function  refund_protection_refuse(){
        $accessories=Db::name("after_sale")->where("status",5)->order("operation_time","desc")->select();
        foreach ($accessories as $key => $value) {
            if ($value["id"]) {
                $res = db("member")->where("member_id", $value['member_id'])->field("member_phone_num,member_real_name,member_name")->find();
                $accessories[$key]["member_phone_num"] = $res["member_phone_num"];
                $accessories[$key]["member_real_name"] = $res["member_real_name"];
                $accessories[$key]["member_name"] = $res["member_name"];
                $images =Db::name("after_image")->field("url")->where("after_sale_id",$value["id"])->select();
                $accessories[$key]["images"] =$images;
            }
        }
        $all_idents = $accessories;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $accessories = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Order/refund_protection_index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $accessories->appends($_GET);
        $this->assign('access', $accessories->render());
        return view("refund_protection_index",["data" => $accessories]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:退款维权搜索
     **************************************
     */
    public function  refund_protection_search(){
        $search_a =input("search_a") ? input("search_a"):null;
        if(!empty($search_a)){
            $condition =" `sale_order_number` like '%{$search_a}%' or `buy_order_number` like '%{$search_a}%' or `member_count` like '%{$search_a}%' ";
            $accessories=Db::name("after_sale")->where($condition)->order("operation_time","desc")->select();
        }else{
            $accessories=Db::name("after_sale")->order("operation_time","desc")->select();
        }
        foreach ($accessories as $key => $value) {
            if ($value["id"]) {
                $res = db("member")->where("member_id", $value['member_id'])->field("member_phone_num,member_real_name,member_name")->find();
                $accessories[$key]["member_phone_num"] = $res["member_phone_num"];
                $accessories[$key]["member_real_name"] = $res["member_real_name"];
                $accessories[$key]["member_name"] = $res["member_name"];
                $images =Db::name("after_image")->field("url")->where("after_sale_id",$value["id"])->select();
                $accessories[$key]["images"] =$images;
            }
        }
        $all_idents = $accessories;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $accessories = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Order/refund_protection_index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $accessories->appends($_GET);
        $this->assign('access', $accessories->render());
        return view("refund_protection_index",["data" => $accessories]);
    }



}