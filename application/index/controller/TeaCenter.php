<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/8
 * Time: 15:21
 */
namespace app\index\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;


class TeaCenter extends Controller
{

    /**
     * [茶圈父级显示]
     * 郭杨
     */
    public function teacenter_data(Request $request)
    {
        if ($request->isPost()) {
            $store_id = $request->only(['uniacid'])['uniacid'];
            $tea = Db::name("goods_type")->field('name,icon_image,color,id')
                ->where('pid', 0)
                ->where("status", 1)
                ->where("store_id", $store_id)
                ->select();
            foreach($tea as $key => $value){
                $res = db("goods_type")
                    ->where("pid",$value['id'])
                    ->where("store_id", $store_id)
                    ->field("name,id")
                    ->find();
                $tea[$key]["tid"] = $res["id"];
                $tea[$key]["activity_name"] = $res["name"];
               
            }
            if (!empty($tea)) {
                return ajax_success('传输成功', $tea);
            } else {
                return ajax_error("数据为空");

            }


        }

    }




    /**
     * [茶圈子级显示]
     * 郭杨
     */
    public function teacenter_display(Request $request)
    {
        if ($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $id = $request->only(['id'])['id'];
            $resdata = Db::name("goods_type")
            ->field('name,icon_image,color,id')
            ->where('pid', $id)
            ->where("status", 1)
            ->where('store_id', 'EQ',$store_id)
            ->select();
            
            if (!empty($resdata)) {
                return ajax_success('传输成功', $resdata);
            } else {
                return ajax_error("数据为空");

            }


        }


    }

    /**
     * [茶圈活动页面显示]
     * 郭杨
     */
    public function teacenter_activity(Request $request)
    {
        if ($request->isPost()){
            $res = $request->only(['id'])['id'];   
            $store_id = $request->only(['uniacid'])['uniacid'];                    
            $activity = Db::name("teahost")
            ->field('id,activity_name,classify_image,cost_moneny,start_time,commodity,label,marker,participats,peoples,address,pid')
            ->where("label", 1)
            ->where("pid",$res)
            ->where("store_id","EQ",$store_id)
            ->order("order_ing desc")
            ->select();
            if(empty($activity)){
                return ajax_error("下面没有活动");
            }
            foreach($activity as $key => $value){
                if($value["id"]){       
                    $rest = db("goods_type")
                        ->where("id", $res)
                        ->where("store_id","EQ" ,$store_id)
                        ->field("name,pid")
                        ->find();
                    $retsd = db("goods_type")
                        ->where("id",$rest["pid"])
                        ->where("store_id","EQ" ,$store_id)
                        ->field("name,color")
                        ->find();
                    $activity[$key]["names"] = $rest["name"];
                    $activity[$key]["named"] = $retsd["name"];
                    $activity[$key]["color"] = $retsd["color"];
                    $activity[$key]["start_time"] = date('Y-m-d H:i',$activity[$key]["start_time"]);
                }
            }
          
            if (!empty($activity)) {
                return ajax_success('传输成功', $activity);
            } else {
                return ajax_error("数据为空");

            }


        }


    }

     /**
     * [茶圈活动详细显示]
     * 郭杨
     */
    public function teacenter_detailed(Request $request)
    {
        if ($request->isPost()){
            $resd = $request->only(['id'])['id'];
            $actdata = Db::name("teahost")->where("label", 1)->where("id",$resd)->find();

            $data = array(
                'id'=>$actdata['id'],
                'requirements'=>$actdata['requirements'],
                'open_request'=>$actdata['open_request'],
                'activity_name'=>$actdata['activity_name'],
                'classify_image'=>$actdata['classify_image'],
                'address'=>$actdata['address'],
                'cost_moneny'=>$actdata['cost_moneny'],
                'start_time'=>$actdata['one_time'],
                'end_time'=>$actdata['two_time'],
                'day_start_time'=>$actdata['day_start_time'],
                'day_end_time'=>$actdata['day_end_time'],
                'participats'=>$actdata['participats'],
                'describe'=>$actdata['describe'],
                'commodity'=>$actdata['commodity'],
                'peoples'=>$actdata['peoples'],
                'goods_sign'=>$actdata['goods_sign'],
                'day_array'=>explode(",",$actdata['day_array']),
                'day_number'=>$actdata['day_number']              
            );
        
            if (!empty($data)) {
                return ajax_success('传输成功', $data);
            } else {
                return ajax_error("数据为空");

            }


        }


    }

     /**
     * [茶圈所有活动]
     * 郭杨
     */
    public function teacenter_alls(Request $request)
    {
        if ($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $data = Db::name("teahost")
            ->field('id,activity_name,classify_image,cost_moneny,start_time,commodity,label,marker,participats,requirements,peoples,address,pid,one_time')
            ->where("label", 1)
            ->where("store_id","EQ",$store_id)	
            ->order("start_time")
            ->select();
            foreach($data as $key => $value){
                if($value){
                    $rest = db("goods_type")
                        ->where("id", $value["pid"])
                        ->where("store_id","EQ",$store_id)
                        ->field("name,pid")
                        ->find();
                    $retsd = db("goods_type")
                    ->where("id",$rest["pid"])
                    ->where("store_id","EQ",$store_id)	
                    ->field("name,color")
                    ->find();
                    $data[$key]["names"] = $rest["name"];
                    $data[$key]["named"] = $retsd["name"];
                    $data[$key]["color"] = $retsd["color"];
                    $data[$key]["start_time"] = date('Y-m-d H:i',$data[$key]["one_time"]);
                }
            }
           
            if (!empty($data)) {
                return ajax_success('传输成功', $data);
            } else {
                return ajax_error("数据为空");

            }


        }


    }


    /**
     * [茶圈首页推荐活动]
     * 郭杨
     */
    public function recommend(Request $request)
    {
        if ($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $data = Db::name("teahost")
                    ->where("status",1)
                    ->field('id,activity_name,classify_image,cost_moneny,start_time,commodity,label,marker,participats,requirements,peoples,address,pid,status,open_request')
                    ->where("label", 1)
                    ->where('status',1)
                    ->where("store_id","EQ",$store_id)	
                    ->order("start_time")
                    ->select();
            foreach($data as $key => $value){
                if($value){
                    $rest = db("goods_type")
                        ->where("id", $value["pid"])
                        ->where("store_id","EQ",$store_id)	
                        ->field("name,pid")
                        ->find();
                    $retsd = db("goods_type")
                        ->where("id",$rest["pid"])
                        ->where("store_id","EQ",$store_id)	
                        ->field("name,color")
                        ->find();
                    $data[$key]["names"] = $rest["name"];
                    $data[$key]["named"] = $retsd["name"];
                    $data[$key]["color"] = $retsd["color"];
                    $data[$key]["start_time"] = date('Y-m-d H:i',$data[$key]["start_time"]);
                }
            }
           
            if (!empty($data)) {
                return ajax_success('传输成功', $data);
            } else {
                return ajax_error("数据为空");

            }


        }


    }


    /**
     * [茶圈活动订单]
     * 郭杨
     */
    public function activity_order(Request $request)
    {
        if ($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $activity_id = isset($request->only(['activity_id'])['activity_id'])?$request->only(['activity_id'])['activity_id']:null;
            $open_id = isset($request->only(['open_id'])['open_id'])?$request->only(['open_id'])['open_id']:null;
            $start_time = isset($request->only(['start_time'])['start_time'])?$request->only(['start_time'])['start_time']:null;
            $index = $request->only(['index'])['index'];

            if(!empty($activity_id) && !empty($open_id) && !empty($start_time) ){                     
                $user_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
                $data = db("teahost")->where('id',$activity_id)->field("activity_name,classify_image,address,pid,cost_moneny,peoples")->find();
                $account = db("member")->where('member_openid',$open_id)->value('member_phone_num');
                $names = db("goods_type")->where("id",$data['pid'])->value("name");

                $time=date("Y-m-d",time());
                $v=explode('-',$time);
                $time_second=date("H:i:s",time());
                $vs=explode(':',$time_second);
                $parts_order_number ="HD".($user_id + intval($v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2])); //订单编号
                $data['member_openid'] =  $open_id;
                $data['teahost_id'] =  $activity_id;
                $data['account'] =  $account;
                $data['status'] =  1;
                $data['index'] =  $index;
                $data['names'] =  $names;
                $data['store_id'] =  $store_id;
                $data['create_time'] = time();
                $data['start_time'] = strtotime($start_time); //活动开始时间
                $data['parts_order_number'] =  $parts_order_number;
                $bool = db("activity_order")->insert($data);
                if (!empty($bool)) {
                    return ajax_success('下单成功', $data['parts_order_number']);
                } else {
                    return ajax_error("下单失败");
                }
            } else {
                return ajax_error("参数错误");
            }
        }
    }

    /**
     * [茶圈活动取消订单]
     * 郭杨
     */
    public function activity_order_delete(Request $request)
    {
        if ($request->isPost()){
            $number = $request->only(['parts_order_number'])['parts_order_number'];
            $bool = db("activity_order")->where('parts_order_number',$number)->delete();
            if ($bool) {
                return ajax_success('取消订单成功', $bool);
            } else {
                return ajax_error("取消订单失败");
            }
        }
    }




    /**
     * [茶圈评论]
     * 陈绪
     */
    public function teacenter_comment(Request $request){

        if($request->isPost()){
            $comment_data = $request->param();
            $user_account = db("member")->where("member_openid",$comment_data["user_id"])->find();
            $comment_data["user_account"] = $user_account["member_name"];
            $comment_data["user_id"] = $user_account["member_id"];
            $comment_data["address"] = $user_account["member_address"];
            $comment_set = db("comment_set")->find();
            $comment_set_id = empty($comment_set) ? null : $comment_set["id"];
            $comment_data["comment_set_id"] = $comment_set_id;
            $comment_data["create_time"] = time();
            $comment_data["status"] = 0;
            $comment_data["store_id"] = $user_account['store_id'];
            $bool = db("comment")->insert($comment_data);
            if($bool){
                return ajax_success("存储成功");
            }else{
                return ajax_error("失败");
            }
        }

    }


    /**
     * [茶圈活动是否已报名]
     * 郭杨
     */
    public function activity_status(Request $request)
    {
        if ($request->isPost()){
            $open_id = $request->only(['open_id'])['open_id']; //账户id  
            $store_id = $request->only(['uniacid'])['uniacid'];
            $activity_id = $request->only(['id'])['id'];  //活动id
            $activity_pid = db('teahost')->where('id',$activity_id)->value('pid'); //活动pid
            $activity_name = db('teahost')->where('id',$activity_id)->value('activity_name');//活动名称   
          
            $rest = db("activity_order")
                    ->where("pid",$activity_pid)
                    ->where("member_openid",$open_id)
                    ->where("store_id","EQ",$store_id)	
                    ->where("activity_name",$activity_name)
                    ->value('status');

            if ($rest == 2) {
                return ajax_success('该用户已报名', $rest);
            } else {
                $rest = 0;
                return ajax_error("未报名",$rest);
            }
        }
    }



    /**
     * [茶圈评论显示]
     * 陈绪
     */
    public function teacenter_comment_show(Request $request){

        if($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $teahost_id= $request->only(["teahost_id"])["teahost_id"];
            $comment_data = db("comment")
                        ->where("teahost_id",$teahost_id)
                        ->where("store_id","EQ",$store_id)	
                        ->select();
            foreach ($comment_data as $key=>$value){
                $comment_data[$key]["user_images"] = db("member")->where("member_id",$value["user_id"])->value("member_head_img");
                $comment_data[$key]["member_address"] = db("member")->where("member_id",$value["user_id"])->value("member_address");
            }
            if($comment_data) {
                return ajax_success("获取成功", $comment_data);
            }else{
                return ajax_error("获取失败");
            }
        }

    }



    /**
     * [茶商评论点赞]
     * 陈绪
     */
    public function teacenter_comment_updata(Request $request){

        if ($request->isPost()){
            $opend_id = $request->only(["user_id"])["user_id"];
            $teahost_id = $request->only(["teahost_id"])["teahost_id"];
            $comment_id = $request->only(["id"])["id"];
            $user_id = db("member")->where("member_openid",$opend_id)->value("member_id");
            $comment = db("comment")->where("id",$comment_id)->where("teahost_id",$teahost_id)->update(["status"=>1]);
            if($comment){
                return ajax_success("更新成功");
            }else{
                return ajax_error("更新失败");
            }
        }

    }

    /**
     **************郭杨*******************
     * @param Request $request
     * Notes:收货地址详情
     **************************************
     */
    public function tacitly_adress(Request $request){

        if($request->isPost()){
            $id = $request->only(["id"])["id"];
            $store_id = $request->only(['uniacid'])['uniacid'];    
            $data =Db::name("store_house")
                ->where("id",$id)
                ->where("store_id","EQ",$store_id)
                ->find(); 
            if(!empty($data)) {          
                $data["unit"] = explode(",",$data["unit"]);
                $data["cost"] = explode(",",$data["cost"]);
                return ajax_success("返回成功",$data);
            }else{
                return ajax_error("没有默认收货地址");
            }
        }
    }






    
}