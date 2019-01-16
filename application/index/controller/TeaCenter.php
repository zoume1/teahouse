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
            $tea = Db::name("goods_type")->field('name,icon_image,color,id')->where('pid', 0)->where("status", 1)->select();
            foreach($tea as $key => $value){
                $res = db("goods_type")->where("pid",$value['id'])->field("name,id")->find();
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
            $id = $request->only(['id'])['id'];
            $resdata = Db::name("goods_type")->field('name,icon_image,color,id')->where('pid', $id)->where("status", 1)->select();
            
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
                    
            $activity = Db::name("teahost")->field('id,activity_name,classify_image,cost_moneny,start_time,commodity,label,marker,participats,peoples,address,pid')->where("label", 1)->where("pid",$res)->order("start_time")->select();
            if(empty($activity)){
                return ajax_error("下面没有活动");
            }
            foreach($activity as $key => $value){
                if($value["id"]){       
                    $rest = db("goods_type")->where("id", $res)->field("name,pid")->find();
                    $retsd = db("goods_type")->where("id",$rest["pid"])->field("name,color")->find();
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
            $actdata = Db::name("teahost")->field('id,activity_name,classify_image,cost_moneny,start_time,commodity,label,marker,participats,peoples,requirements,address,pid')->where("label", 1)->where("id",$resd)->select();
            
            foreach($actdata as $key => $value){
                $actdata[$key]["start_time"] = date('Y-m-d H:i',$actdata[$key]["start_time"]);
            }

            if (!empty($actdata)) {
                return ajax_success('传输成功', $actdata);
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
            $data = Db::name("teahost")->field('id,activity_name,classify_image,cost_moneny,start_time,commodity,label,marker,participats,requirements,peoples,address,pid')->where("label", 1)->order("start_time")->select();
            foreach($data as $key => $value){
                if($value){
                    $rest = db("goods_type")->where("id", $value["pid"])->field("name,pid")->find();
                    $retsd = db("goods_type")->where("id",$rest["pid"])->field("name,color")->find();
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
     * [茶圈首页推荐活动]
     * 郭杨
     */
    public function recommend(Request $request)
    {
        if ($request->isPost()){
            $data = Db::name("teahost")->field('id,activity_name,classify_image,cost_moneny,start_time,commodity,label,marker,participats,requirements,peoples,address,pid,status,open_request')->where("label", 1)->where('status',1)->order("start_time")->select();
            foreach($data as $key => $value){
                if($value){
                    $rest = db("goods_type")->where("id", $value["pid"])->field("name,pid")->find();
                    $retsd = db("goods_type")->where("id",$rest["pid"])->field("name,color")->find();
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
            $activity_id = $request->only(['activity_id'])['activity_id'];
            $open_id = $request->only(['open_id'])['open_id'];
            $user_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
            $data = db("teahost")->where('id',$activity_id)->field("activity_name,classify_image,address,pid,cost_moneny,start_time,peoples")->find();
            $account = db("member")->where('member_openid',$open_id)->value('member_phone_num');

            $time=date("Y-m-d",time());
            $v=explode('-',$time);
            $time_second=date("H:i:s",time());
            $vs=explode(':',$time_second);
//            $parts_order_number =$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].rand(1000,9999).($user_id+100000); //订单编号
            $parts_order_number =$user_id + $v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2]; //订单编号
            $data['member_openid'] =  $open_id;
            $data['account'] =  $account;
            $data['parts_order_number'] =  $parts_order_number;
            $bool = db("activity_order")->insert($data);
            if (!empty($bool)) {
                return ajax_success('传输成功', $data);
            } else {
                return ajax_error("数据为空");
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
            $bool = db("comment")->insert($comment_data);
            if($bool){
                return ajax_success("存储成功");
            }else{
                return ajax_error("失败");
            }
        }

    }



    /**
     * [茶圈评论显示]
     * 陈绪
     */
    public function teacenter_comment_show(Request $request){

        if($request->isPost()){
            $goods_id = $request->only(["goods_id"])["goods_id"];
            $comment_data = db("comment")->where("goods_id",$goods_id)->select();
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
            $user_id = $request->only(["user_id"])["user_id"];
            $comment = db("comment")->where("user_id",$user_id)->update(["status"=>1]);
            if($comment){
                return ajax_success("更新成功");
            }else{
                return ajax_error("更新失败");
            }
        }

    }
}