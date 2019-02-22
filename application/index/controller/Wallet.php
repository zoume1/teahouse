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
use think\Cache;

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
            $data =Db::name("recharge_full_setting")->where("status",1)->select();
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
     * Notes:提现页面数据返回（如果没有设置为默认选择第一个）
     **************************************
     */
    public function withdrawal_return(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $id =$request->only(["id"])["id"];//银行卡id
            if(!empty($id)){
                $is_bank =Db::name("user_bank")
                    ->where("user_id", $member_id)
                    ->where("id",$id)
                    ->find();
                if(!empty($is_bank)){
                    return ajax_success('银行卡成功返回', $is_bank);
                }else{
                    exit(json_encode(array("status"=>0,"info"=>"请先选择银行卡")));
                }
            }
            $is_bank_status = Db::name('user_bank')
                ->where('user_id',  $member_id)
                ->order("id","desc")
                ->find();
            if (!empty($is_bank_status)) {
                $is_bank = Db::name('user_bank')
                    ->where('user_id',  $member_id)
                    ->where("status", 1)
                    ->find();
                if(!empty($is_bank)){
                    return ajax_success('银行卡信息成功返回', $is_bank);
                }else{
                    return ajax_success('银行卡信息成功返回', $is_bank);
                }
            } else {
                exit(json_encode(array("status"=>0,"info"=>"请先填写银行卡信息")));
            }

        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:提现
     **************************************
     */

    public function withdrawal(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $money =$request->only(["money"])["money"];
            $user_name =$request->only(["user_name"])["user_name"];
            $bank_name =$request->only(["bank_name"])["bank_name"];
            $bank_card =$request->only(["bank_card"])["bank_card"];
            $code =$request->only(["code"])["code"];

            $member_recharge_money =Db::name("member")
                ->where("member_id",$member_id)
                ->value("member_recharge_money");
            if($money <= 0){
                return ajax_error("金额不正确");
            }
            if($money <= $member_recharge_money){
                return ajax_error("提现金额不能超过可提现金额");
            }
            $user_real_name =Db::name("member")
                ->where("member_id",$member_id)
                ->value("member_real_name");
            if($user_real_name != $user_name){
                return ajax_error("户名必须跟绑定的身份证一致");
            }
            $member_phone_num =Db::name("member")
                ->where("member_id",$member_id)
                ->value("member_phone_num");
            $mobileCode =Cache::get('mobileCode');
            $mobile =Cache::get('mobile');
            if($mobileCode != $code) {
                return ajax_error("验证码不正确");
            }
            if($member_phone_num != $mobile){
                return ajax_error("手机号不匹配");
            }
            $data =[
                "user_id"=>$member_id,//钱包支付记录ID（充值提现）
                "operation_time"=>date("Y-m-d H:i:s"), //操作时间
                "operation_type"=>-1, //操作类型（-1提现,1充值）
                "operation_amount"=>$money, //操作金额
                "pay_type_content"=>"微信", //支付方式
                "money_status"=>2, //到款状态（1到账，2未到款）
                "recharge_describe"=>"提现",//描述
                "img_url"=>" ",//对应的图片链接
                "back_member"=>$user_name,//用户名
                "bank_card"=>$bank_card,//开户银行卡
                "bank_name"=>$bank_name,//开户银行
                "status"=>2, //审核状态（-1,2,1）不通过，待审核，通过
                "is_able_withdrawal"=>1, //是否可提现1可以提现，-1不可提现
            ];
            $res =Db::name("recharge_reflect")->insertGetId($data);
            if($res){
                return ajax_success("申请成功",$res);
            }else{
                return ajax_error("请重复申请");
            }
        }
    }







}