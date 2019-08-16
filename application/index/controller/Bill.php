<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/18 0018
 * Time: 14:04
 * 账单
 */

namespace  app\index\controller;


use think\Controller;
use think\Request;
use think\Db;
use app\admin\model\Goods;
class Bill extends Controller{


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的消费
     **************************************
     * @return \think\response\View
     */
    public function ceshi12(Request $request){
        if($request->isPost()){
            $goods_id = [238,234,365,366];
            halt(Goods::getDistributionStatus($goods_id));
        }
        
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的消费
     **************************************
     * @return \think\response\View
     */
    public function consume_index(Request $request){
        if($request->isPost()){
            $user_id =$request->only(["member_id"])["member_id"];//用户id
            $now_time_one =date("Y");
            $condition = " `operation_time` like '%{$now_time_one}%' ";
            $data = Db::name("wallet")
                ->where("user_id",$user_id)
                ->where($condition)
                ->order("operation_time","desc")
                ->select();
            if(!empty($data)){
                return ajax_success("消费细节返回成功",$data);
            }else{
                return ajax_error("暂无消费记录",["status"=>0]);
            }
        }
        return view("my_consume");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的消费搜索
     **************************************
     */
    public function consume_search(Request $request){
        if($request->isPost()){
            $user_id =$request->only(["member_id"])["member_id"];//用户id
            $title =$request->only(["title"])["title"];//搜索关键词
            $now_time_one =date("Y");
            $condition = " `operation_time` like '%{$now_time_one}%' ";
            $conditions = " `title` like '%{$title}%' ";
            $data = Db::name("wallet")
                ->where("user_id",$user_id)
                ->where($condition)
                ->where($conditions)
                ->order("operation_time","desc")
                ->select();
            if(!empty($data)){
                return ajax_success("消费细节返回成功",$data);
            }else{
                return ajax_error("暂无消费记录",["status"=>0]);
            }
        }
        return view("my_consume");
    }
}