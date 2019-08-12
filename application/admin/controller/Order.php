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
use think\Session;
class  Order extends  Controller{
    /**
     *************lilu*******************
     * @param Request $request
     * Notes:初始订单页面
     **************************************
     * @return \think\response\View
     * 展示5种类型的订单
     */
    public function order_index(){
        $store_id = Session::get("store_id");
        $where['status']= array('between',array(0,8));
        $data =Db::name("order")
            ->order("order_create_time","desc")
            ->where("store_id",'EQ',$store_id)
            ->where($where)
            ->group('parts_order_number')
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
            $data2=[];
            foreach($data as $k=>$v){
                //获取相同订单的数据
                $list=db('order')->where('parts_order_number',$v['parts_order_number'])->select();
                $order=[];
                foreach($list as $k2 =>$v2){
                    $order[$k2]['goods_image']=$v2['goods_image'];
                    $order[$k2]['parts_goods_name']=$v2['parts_goods_name'];
                    $order[$k2]['order_quantity']=$v2['order_quantity'];
                }
                $num=count($order);
                $data2[$k]=$v;
                $data2[$k]['detail']=$order;
                $data2[$k]['num']=$num;
            }
        return view("order_index",["data"=>$data2]);
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
            $order_type =$request->only(["order_type"])["order_type"];
            $status =$request->only(["status"])["status"];

            if($order_type != 2){
                $courier_number =$request->only(["courier_number"])["courier_number"];
                $express_name =$request->only(["express_name"])["express_name"];
                $express_name2 =$request->only(["express_name_ch"])["express_name_ch"];
                $data =[
                    "status"=>$status,
                    "courier_number"=>$courier_number,
                    "express_name"=>$express_name,
                    "express_name_ch"=>$express_name2,
                ];
            } else {
                $data =[
                    "status"=>$status,
                ];
            }
            $bool = Db::name("order")->where("parts_order_number",$order_id)->update($data);
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
                $data =Db::name("order")->where("parts_order_number",$order_id)->find();
                if(!empty($data)){
                    $data['store_name'] = db("store")->where("id",$data['store_id'])->value('store_name');
                    $data['parts_goods_name'] = Db::name("order")->where("id",$order_id)->field('parts_goods_name')->select();
                    $data['order_quantity'] = Db::name("order")->where("id",$order_id)->field('order_quantity')->select();
                    $data["goods_franking"] = Db::name("goods")->where("id",$data["goods_id"])->value("goods_franking");
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
    public function order_search(){
            $store_id = Session::get("store_id");
            $search_a =input("search_a") ? input("search_a"):null;
            $order_type =input("order_type") ? input("order_type"):null;
            $time_min  =input("date_min") ? input("date_min"):null;
            $date_max  =input('date_max') ? input('date_max'):null;
            if(!empty($search_a)){
                $condition =" `parts_order_number` like '%{$search_a}%' or `parts_goods_name` like '%{$search_a}%' or `user_account_name` like '%{$search_a}%' or `user_phone_number` like '%{$search_a}%'";
                $data =Db::name("order")
                    ->where($condition)
                    ->where("store_id",'EQ',$store_id)
                    ->order("order_create_time","desc")
                    ->paginate(20 ,false, [
                        'query' => request()->param(),
                    ]);
            }else if (!empty($order_type)){
                $data =Db::name("order")
                    ->where("order_type",$order_type)
                    ->where("store_id",'EQ',$store_id)
                    ->order("order_create_time","desc")
                    ->paginate(20 ,false, [
                        'query' => request()->param(),
                    ]);
            }else{
                if(!empty($time_min)){
                    $timemin =strtotime($time_min);
                }
                if(!empty($date_max)){
                    /*添加一天（23：59：59）*/
                    $t=date('Y-m-d H:i:s',strtotime($date_max)+1*24*60*60);
                    $timemax  =strtotime($t);

                }
                if(!empty($time_min) && empty($date_max)){
                    $time_condition  = "order_create_time>{$timemin}";
                    //开始时间
                    $data =Db::name("order")
                        ->where($time_condition)
                        ->where("store_id",'EQ',$store_id)
                        ->order("order_create_time","desc")
                        ->paginate(20 ,false, [
                            'query' => request()->param(),
                        ]);
                }else if (empty($time_min) && (!empty($date_max))){
                    $time_condition  = "order_create_time< {$timemax}";
                    //结束时间
                    $data =Db::name("order")
                        ->where($time_condition)
                        ->order("order_create_time","desc")
                        ->where("store_id",'EQ',$store_id)
                        ->paginate(20 ,false, [
                            'query' => request()->param(),
                        ]);
                }else if((!empty($timemin)) && (!empty($date_max))){
                    $time_condition  = "order_create_time>{$timemin} and order_create_time< {$timemax}";
                    //既有开始又有结束
                    $data =Db::name("order")
                        ->where($time_condition)
                        ->order("order_create_time","desc")
                        ->where("store_id",'EQ',$store_id)
                        ->paginate(20 ,false, [
                            'query' => request()->param(),
                        ]);
                }else{
                    $data =Db::name("order")
                        ->order("order_create_time","desc")
                        ->where("store_id",'EQ',$store_id)
                        ->paginate(20 ,false, [
                        'query' => request()->param(),
                    ]);

                }
            }
            return view("order_index",["data"=>$data]);

    }


    /**
 **************李火生*******************
 * @param Request $request
 * Notes:待付款
 **************************************
 */
    public function order_way_pay(){
        $store_id = Session::get("store_id");
        $data =Db::name("order")
            ->order("order_create_time","desc")
            ->where("store_id",$store_id)
            ->where("status",1)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        return view("order_index",["data"=>$data]);
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:待发货
     **************************************
     */
    public function order_wait_send(){
        $store_id = Session::get("store_id");
        $condition ="`status` = '2' or `status` = '3'";
        $type ="`order_type` = '1' or `order_type` = '2'";
        $data =Db::name("order")
            ->where($condition)
            ->where($type)
            ->where("store_id",'EQ',$store_id)
            ->order("order_create_time","desc")
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        return view("order_index",["data"=>$data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:已发货
     **************************************
     */
    public function order_shipped(){
        $store_id = Session::get("store_id");
        $condition =" `status` = '4' or `status` = '5' ";
        $data =Db::name("order")
            ->order("order_create_time","desc")
            ->where("store_id",'EQ',$store_id)
            ->where($condition)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        return view("order_index",["data"=>$data]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:已完成
     **************************************
     */
    public function order_completed(){
        $store_id = Session::get("store_id");
        $data =Db::name("order")
            ->order("order_create_time","desc")
            ->where("status",8)
            ->where('store_id','EQ',$store_id)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
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
        $store_id = Session::get("store_id");
        $condition =" `status` = '9' or `status` = '10' ";
        $data =Db::name("order")
            ->order("order_create_time","desc")
            ->where($condition)
            ->where('store_id','EQ',$store_id)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
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
        $store_id = Session::get("store_id");
        $data =Db::name("buyintegral")
            ->order("order_create_time","desc")
            ->where('store_id','EQ',$store_id)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
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
        $store_id = Session::get("store_id");
        $store = config("store_id");
        $data = Db::name('order_setting')->where("store_id","EQ",$store_id)->find();
        if(empty($data)){
            $datas = Db::name('order_setting')->where("store_id","EQ",$store)->find();
            $datas["store_id"] = $store_id;
            unset($datas["order_setting_id"]);
            $bool = db('order_setting')->insert($datas);
            $data = Db::name('order_setting')->where("store_id","EQ",$store_id)->find();
        }
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
            $store_id = Session::get("store_id");
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
            $bool =Db::name('order_setting')->where("store_id","EQ",$store_id)->update($data);
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
        //获取店铺id
        $store_id=Session::get('store_id');
        $accessories=Db::name("after_sale")
            ->where('store_id',$store_id)
            ->order("operation_time","desc")
            ->select();
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
     * Notes:申请中
     **************************************
     * @return \think\response\View
     */
    public function refund_protection_applying(){
        $accessories=Db::name("after_sale")->where("status",1)->order("operation_time","desc")->select();
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
        $time_min  =input("date_min") ? input("date_min"):null;
        $date_max  =input('date_max') ? input('date_max'):null;
        if(!empty($search_a)){
            $condition =" `sale_order_number` like '%{$search_a}%' or `buy_order_number` like '%{$search_a}%' or `member_count` like '%{$search_a}%' ";
            $accessories=Db::name("after_sale")->where($condition)->order("operation_time","desc")->select();
        }else{
            if(!empty($time_min)){
                $timemin =strtotime($time_min);
            }
            if(!empty($date_max)){
                /*添加一天（23：59：59）*/
                $t=date('Y-m-d H:i:s',strtotime($date_max)+1*24*60*60);
                $timemax  =strtotime($t);
            }
            if(!empty($time_min) && empty($date_max)){
                $time_condition  = "operation_time>{$timemin}";
                $accessories=Db::name("after_sale")->where($time_condition)->order("operation_time","desc")->select();
                //开始时间
            }else if (empty($time_min) && (!empty($date_max))){
                $time_condition  = "operation_time< {$timemax}";
                $accessories=Db::name("after_sale")->where($time_condition)->order("operation_time","desc")->select();
                //结束时间
            }else if((!empty($timemin)) && (!empty($date_max))){
                $time_condition  = "operation_time>{$timemin} and operation_time< {$timemax}";
                $accessories=Db::name("after_sale")->where($time_condition)->order("operation_time","desc")->select();
                //既有开始又有结束
            }else{
                $accessories=Db::name("after_sale")->order("operation_time","desc")->select();
            }
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



    /**
     **************GY*******************
     * @param Request $request
     * Notes:更改订单价格
     **************************************
     * @return \think\response\View
     */
    public function  changeOderPrice(Request $request){
        if($request->isPost()){
            $status =$request->only(["status"])["status"];//订单状态
            $order_id =$request->only(["id"])["id"];
            $parts_order_number = Db::name("order")->where("id",'EQ',$order_id)->value("parts_order_number");
            $price = $request->only(["order_real_pay"])["order_real_pay"];//更改价格
            if($status != 1){
                return ajax_error("该订单不支持改价");
            } else {
                $bool = db("order")->where("parts_order_number",$parts_order_number)->update(["order_real_pay" =>$price]);
                if($bool){
                    return ajax_success("改价成功");
                } else {
                    return ajax_error("改价失败");
                }
            }
        }
    }
    /**
     **************GY*******************
     * @param Request $request
     * Notes:更改订单价格
     **************************************
     * @return \think\response\View
     */
    public function  reward_index(){
        return view("reward_index");
    }

}