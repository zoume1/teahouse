<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/15 0015
 * Time: 14:45
 */
namespace app\index\controller;



use think\Controller;
use think\Request;
use think\Db;

class  Wallet extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:账户余额和积分返回
     **************************************
     * @param Request $request
     */
    public function  member_balance_return(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $data  =Db::name("member")
                ->where("member_id",$member_id)
                ->field("member_wallet,member_integral_wallet,member_recharge_money")
                ->find();
            if(!empty($data)){
                $datas =[
                    "member_wallet"=>$data["member_wallet"]+$data["member_recharge_money"],//总共的余额
                    "member_integral_wallet"=>$data["member_integral_wallet"],//积分
                    "member_recharge_money" =>$data["member_recharge_money"],//可提现金额
                ];
                return ajax_success("余额信息返回成功",$datas);
            }else{
                return ajax_error("没有信息返回",["status"=>0]);
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:账户充值页面对应的储值规则数据返回
     **************************************
     * @param Request $request
     */
    public function recharge_setting_return(Request $request){
        if($request->isPost()){
            $data =Db::name("recharge_full_setting")->select();
            if(!empty($data)){
                return ajax_success("储值信息返回成功",$data);
            }else{
                return ajax_error("暂无储值信息",["status"=>0]);
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:用户余额充值
     **************************************
     */
    public function member_balance_recharge(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $money =$request->only(["money"])["money"];
            if(!empty($money)){
                $time=date("Y-m-d",time());
                $v=explode('-',$time);
                $time_second=date("H:i:s",time());
                $vs=explode(':',$time_second);
                $recharge_order_number ="CZ".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].$member_id; //订单编号
                $data =[
                    "user_id"=>$member_id,
                    "recharge_order_number"=>$recharge_order_number,
                    "recharge_money"=>$money,
                    "status"=>-1
                ];
                $recharge_id =Db::name("recharge_record")->insertGetId($data);
                if(!empty($recharge_id)){
                    exit(json_encode(array("status" => 1, "info" => "下单成功,返回订单编号" , "data"=>$data["recharge_order_number"])));
                }else{
                    exit(json_encode(array("status" => 0, "info" => "下单不成功")));
                }
            }else{
                exit(json_encode(array("status" => 0, "info" => "充值金额不能为空")));
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:充值下单未付款自动关闭取消删除
     **************************************
     * @param Request $request
     */
    public function recharge_del(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            if(!empty($member_id)){
                $recharge_id =$request->only("recharge_id")["recharge_id"];//充值订单编号
                if(!empty($recharge_id)){
                    $bool =Db::name("recharge_record")
                        ->where("user_id",$member_id)
                        ->where("recharge_order_number",$recharge_id)
                        ->delete();
                    if($bool){
                        exit(json_encode(array("status" => 1, "info" => "取消成功")));
                    }else{
                        exit(json_encode(array("status" => 0, "info" => "取消失败")));
                    }
                }
            }else{
                exit(json_encode(array("status" => 2, "info" => "请登录")));
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:提现
     **************************************
     */








}