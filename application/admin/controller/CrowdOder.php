<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/25/025
 * Time: 14:13
 */

namespace app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use think\paginator\driver\Bootstrap;
use think\Session;

class CrowdOder extends Controller{


    /**
     * [众筹商品订单显示]
     * GY
     */
    public function crowd_order_index()
    {
        $order_status =input("order_status") ? input("order_status"):null;
        $store_id = Session::get("store_id");
        if($order_status){
            $where['status']= array('between',array(2,3));
            $datas =Db::name("crowd_order")
                ->order("order_create_time","desc")
                ->where("store_id",'EQ',$store_id)
                ->where($where)
                ->group('parts_order_number')
                ->select();
        }else{
            $where['status']= array('between',array(0,8));
            $datas =Db::name("crowd_order")
                ->order("order_create_time","desc")
                ->where("store_id",'EQ',$store_id)
                ->where($where)
                ->group('parts_order_number')
                ->select();
        }
            foreach($datas as $k2 =>$v2){
                 //去除过期的未支付的众筹订单
                //获取订单过期的配置参数
                $time=db('order_setting')->where('store_id',$store_id)->value('normal_time');
                $time_now=time()-$v2['order_create_time']-$time*60;
                if($v2['status']==1 && $time_now >0){
                    //未支付并且已过期，（状态修改为已关闭）
                    db('crowd_order')->where('id',$v2['id'])->update(['status'=>0]);
                    // unset($data[$k]);
                    continue;
                }
                $datas[$k2]['type']=0;     //众筹----全额支持
            }
        //获取打赏的记录
        $reward=db('reward')->where('store_id',$store_id)->select();
        foreach($reward as $k =>$v){
            //获取商品的信息
            $goods=db('crowd_special')->where('id',$v['special_id'])->find();
            $reward[$k]['parts_goods_name']=db('crowd_goods')->where('id',$goods['goods_id'])->value('project_name');   //商品名称
            $reward[$k]['goods_image']=$goods['images'];
            $reward[$k]['order_quantity']=1;
            $reward[$k]['order_create_time']=$v['create_time'];
            $user_phone_number=db('member')->where('member_id',$v['member_id'])->value('member_phone_num');
            $reward[$k]['user_phone_number']=$user_phone_number;
            $reward[$k]['order_real_pay']=$v['money'];
            $reward[$k]['order_type']=1;    //订单类型
        }
        $url = 'admin/CrowdOder/crowd_order_index';
        $pag_number = 20;
        $data = paging_data($datas,$url,$pag_number);
        return view("crowd_order_index",["data"=>$data]);
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:订单确认发货（填写订单编号）
     **************************************
     */
    public function  crowd_order_confirm_shipment(Request $request){
        if($request->isPost()){
            $order_id =$request->only(["order_id"])["order_id"];
            $status =$request->only(["status"])["status"];
            $courier_number =$request->only(["courier_number"])["courier_number"];
            $express_name =$request->only(["express_name"])["express_name"];
            $express_name2 =$request->only(["express_name_ch"])["express_name_ch"];
            $data =[
                "status"=>$status,
                "courier_number"=>$courier_number,
                "express_name"=>$express_name,
                "express_name_ch"=>$express_name2,
            ];
            $order_number = Db::name("crowd_order")->where("id",$order_id)->value('parts_order_number');
            $bool = Db::name("crowd_order")->where("id",$order_id)->update($data);
            $boole = Db::name("house_order")->where("parts_order_number",$order_number)->update($data);
            if($bool){
                return ajax_success("发货成功",["status"=>1]);
            }else{
                return ajax_error("发货失败",["status"=>0]);
            }
        }
    }


    /**
     **************GY*******************
     * @param Request $request
     * Notes:初始订单的基本信息
     **************************************
     */
    public function crowd_order_information_return(Request $request){
        if($request->isPost()){
            $order_id = $request->only(["order_id"])["order_id"];
            if(!empty($order_id)){
                $data = Db::name("crowd_order")->where("id",$order_id)->find();
                if(!empty($data)){
                    $data["member_name"] = Db::name("member")->where("member_id",$data["member_id"])->value("member_name");
                    $data["store_name"] = Db::name("store")->where("id",$data["store_id"])->value("store_name");
                    $data["goods_franking"] = Db::name("crowd_goods")->where("id",$data["goods_id"])->value("goods_franking");
                    return ajax_success("数据返回成功",$data);
                }else{
                    return ajax_error("没有数据信息",["status"=>0]);
                }
            }
        }
    }


    /**
     **************GY*******************
     * @param Request $request
     * Notes:订单搜索
     **************************************
     */
    public function crowd_order_search(){
        $store_id = Session::get("store_id");
        $search_a =input("search_a") ? input("search_a"):null;
        $order_type =input("order_type") ? input("order_type"):null;
        $time_min  =input("date_min") ? input("date_min"):null;
        $date_max  =input('date_max') ? input('date_max'):null;
        if(!empty($search_a)){
            $condition =" `parts_order_number` like '%{$search_a}%' or `parts_goods_name` like '%{$search_a}%' or `user_account_name` like '%{$search_a}%' or `user_phone_number` like '%{$search_a}%'";
            $data =Db::name("crowd_order")
                ->where($condition)
                ->where("store_id",'EQ',$store_id)
                ->order("order_create_time","desc")
                ->select();
        }else if (!empty($order_type)){
            $data =Db::name("crowd_order")
                ->where("order_type",$order_type)
                ->where("store_id",'EQ',$store_id)
                ->order("order_create_time","desc")
                ->select();
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
                $data =Db::name("crowd_order")
                    ->where($time_condition)
                    ->where("store_id",'EQ',$store_id)
                    ->order("order_create_time","desc")
                    ->select();
            }else if (empty($time_min) && (!empty($date_max))){
                $time_condition  = "order_create_time< {$timemax}";
                //结束时间
                $data =Db::name("crowd_order")
                    ->where($time_condition)
                    ->order("order_create_time","desc")
                    ->where("store_id",'EQ',$store_id)
                    ->select();
            }else if((!empty($timemin)) && (!empty($date_max))){
                $time_condition  = "order_create_time>{$timemin} and order_create_time< {$timemax}";
                //既有开始又有结束
                $data =Db::name("crowd_order")
                    ->where($time_condition)
                    ->order("order_create_time","desc")
                    ->where("store_id",'EQ',$store_id)
                    ->select();
            }else{
                $data =Db::name("crowd_order")
                    ->order("order_create_time","desc")
                    ->where("store_id",'EQ',$store_id)
                    ->select();

            }
        }
        $url = 'admin/CrowdOder/crowd_order_index';
        $pag_number = 20;
        $data = paging_data($data,$url,$pag_number);
        return view("crowd_order_index",["data"=>$data]);

    }


/**
 **************GY*******************
 * @param Request $request
 * Notes:待付款
 **************************************
 */
public function crowd_order_way_pay(){
    $store_id = Session::get("store_id");
    $data =Db::name("crowd_order")
        ->order("order_create_time","desc")
        ->where("store_id",$store_id)
        ->where("status",1)
        ->select();
        $url = 'admin/CrowdOder/crowd_order_index';
        $pag_number = 20;
        $data = paging_data($data,$url,$pag_number);
    return view("crowd_order_index",["data"=>$data]);
}


   /**
     **************GY*******************
     * @param Request $request
     * Notes:待发货
     **************************************
     */
    public function crowd_order_wait_send(){
        $store_id = Session::get("store_id");
        $condition ="`status` = '2' or `status` = '3'";
        $type ="`order_type` = '1' or `order_type` = '2'";
        $data =Db::name("crowd_order")
            ->where($condition)
            ->where($type)
            ->where("store_id",'EQ',$store_id)
            ->order("order_create_time","desc")
            ->select();
            $url = 'admin/CrowdOder/crowd_order_index';
            $pag_number = 20;
            $data = paging_data($data,$url,$pag_number);
        return view("crowd_order_index",["data"=>$data]);
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:已发货
     **************************************
     */
    public function crowd_order_shipped(){
        $store_id = Session::get("store_id");
        $condition =" `status` = '4' or `status` = '5' ";
        $data =Db::name("crowd_order")
            ->order("order_create_time","desc")
            ->where("store_id",'EQ',$store_id)
            ->where($condition)
            ->select();
            $url = 'admin/CrowdOder/crowd_order_index';
            $pag_number = 20;
            $data = paging_data($data,$url,$pag_number);
        return view("crowd_order_index",["data"=>$data]);
    }


    /**
     **************GY*******************
     * @param Request $request
     * Notes:已完成
     **************************************
     */
    public function crowd_order_completed(){
        $store_id = Session::get("store_id");
        $data =Db::name("crowd_order")
            ->order("order_create_time","desc")
            ->where("status",8)
            ->where('store_id','EQ',$store_id)
            ->select();
            $url = 'admin/CrowdOder/crowd_order_index';
            $pag_number = 20;
            $data = paging_data($data,$url,$pag_number);
        return view("crowd_order_index",["data"=>$data]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:已关闭
     **************************************
     * @return \think\response\View
     */
    public function crowd_order_closed(){
        $store_id = Session::get("store_id");
        $condition =" `status` = '9' or `status` = '10' ";
        $data =Db::name("crowd_order")
            ->order("order_create_time","desc")
            ->where($condition)
            ->where('store_id','EQ',$store_id)
            ->select();
            $url = 'admin/CrowdOder/crowd_order_index';
            $pag_number = 20;
            $data = paging_data($data,$url,$pag_number);
        return view("crowd_order_index",["data"=>$data]);
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:更改订单价格
     **************************************
     * @return \think\response\View
     */
    public function  changeCrowdOderPrice(Request $request){
        if($request->isPost()){
            $status =$request->only(["status"])["status"];//订单状态
            $order_id =$request->only(["id"])["id"];
            $parts_order_number = Db::name("order")->where("id",'EQ',$order_id)->value("parts_order_number");
            $price = $request->only(["order_real_pay"])["order_real_pay"];//更改价格
            if($status != 1){
                return ajax_error("该订单不支持改价");
            } else {
                $bool = db("crowd_order")->where("parts_order_number",$parts_order_number)->update(["order_real_pay" =>$price]);
                if($bool){
                    return ajax_success("改价成功");
                } else {
                    return ajax_error("改价失败");
                }
            }
        }
    }

}