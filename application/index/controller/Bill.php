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
class Bill extends Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的消费
     **************************************
     * @return \think\response\View
     */
    public function consume_index(Request $request){
        if($request->isPost()){
            $user_id =$request->only(["member_id"]);//用户id
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
            $datas =array(
                "january"=>[],
                "february"=>[],
                "march"=>[],
                "april"=>[],
                "may"=>[],
                "june"=>[],
                "july"=>[],
                "august"=>[],
                "september"=>[],
                "october"=>[],
                "november"=>[],
                "december"=>[],
            );
            foreach ($data as $ks=>$vs){
                if(strpos($vs["operation_time"],$now_time_one."-01") !==false){
                    $datas["january"][] =$vs;
                } else if(strpos($vs["operation_time"],$now_time_one."-02")  !==false){
                    $datas["sebruary"][] =$vs;
                }else if(strpos($vs["operation_time"],$now_time_one."-03")  !==false){
                    $datas["march"][] =$vs;
                }else if(strpos($vs["operation_time"],$now_time_one."-04")  !==false){
                    $datas["april"][] =$vs;
                }else if(strpos($vs["operation_time"],$now_time_one."-05")  !==false){
                    $datas["may"][] =$vs;
                }else if(strpos($vs["operation_time"],$now_time_one."-06")  !==false){
                    $datas["june"][] =$vs;
                }else if(strpos($vs["operation_time"],$now_time_one."-07")  !==false){
                    $datas["july"][] =$vs;
                }else if(strpos($vs["operation_time"],$now_time_one."-08")  !==false){
                    $datas["august"][] =$vs;
                }else if(strpos($vs["operation_time"],$now_time_one."-09") !==false){
                    $datas["september"][] =$vs;
                }else if(strpos($vs["operation_time"],$now_time_one."-10") !==false){
                    $datas["october"][] =$vs;
                }else if(strpos($vs["operation_time"],$now_time_one."-11") !==false){
                    $datas["november"][] =$vs;
                }else if(strpos($vs["operation_time"],$now_time_one."-12") !==false){
                    $datas["december"][] =$vs;
                }
            }
            $res =[
                "wallet_record"=>$datas
            ];
            if(!empty($data)){
                return ajax_success("消费细节返回成功",$res);
            }else{
                return ajax_error("暂无消费记录",["status"=>0]);
            }
        }
        return view("my_consume");
    }
}