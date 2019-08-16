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
            //获取店铺id
            $store_id=$request->only(["uniacid"])["uniacid"];
            $data =Db::name("recharge_full_setting")->where('store_id',$store_id)->select();
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
            $type = isset($request->only(["type"])["type"])?$request->only(["type"])["type"]:null;
            $member_grade_id = isset($request->only(["member_grade_id"])["member_grade_id"])?$request->only(["member_grade_id"])["member_grade_id"]:null;
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
                if(!empty($member_grade_id)){
                    $data['upgrade_id'] = $member_grade_id;
                }else{
                    if(!empty($type || $type=='0')){
                        $pp['msg']=$type;
                        db('test')->insert($pp);
                            $data['upgrade_id'] = $type;
                    }
                }
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
     **************lilu*******************
     * @param Request $request
     * Notes:银行卡提现(未完成)
     **************************************
     */

    public function withdrawal(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];   //会员id
            $money =$request->only(["money"])["money"];               //金额
            $user_name =$request->only(["user_name"])["user_name"];
            $bank_name =$request->only(["bank_name"])["bank_name"];   //银行名称
            $bank_card =$request->only(["bank_card"])["bank_card"];   //银行卡号
            // $code =$request->only(["code"])["code"];
            $member_recharge_money =Db::name("member")
                ->where("member_id",$member_id)
                ->value("member_recharge_money");     //会员余额
            if($money <= 0){
                return ajax_error("金额不正确");
            }
            if($money >= $member_recharge_money){
                return ajax_error("提现金额不能超过可提现金额");
            }
            $user_real_name =Db::name("member")
                ->where("member_id",$member_id)
                ->value("member_real_name");           //用户认证名字
            if($user_real_name != $user_name){
                return ajax_error("户名必须跟绑定的身份证一致");
            }
            $member_phone_num =Db::name("member")
                ->where("member_id",$member_id)
                ->value("member_phone_num");
            // $mobileCode =Cache::get('mobileCode');    //
            // $mobile =Cache::get('mobile');
            // if($mobileCode != $code) {
            //     return ajax_error("验证码不正确");
            // }
            // if($member_phone_num != $mobile){
            //     return ajax_error("手机号不匹配");
            // }
            //提现限制条件
            $condition =Db::name("withdrawal")->find();
            //最少提现金额
            if($money < $condition["min_money"]){
                return ajax_error("最少提现金额".$condition["min_money"]."元");
            }
            //每天最高提现金额（微信则需要再做限制一次最高提现5000,一天最高提现总数50000）
            if($money > $condition["day_max_money"]){
                return ajax_error("最高提现金额".$condition["day_max_money"]."元");
            }
            //每日提现笔数需要低于设置
            $today =date("Y-m-d");
            $is_set_number =Db::name("recharge_reflect")
                ->where("operation_time","like","%" .$today ."%")
                ->where("operation_type",-1)
                ->where("user_id",$member_id)
                ->count();      //统计一天提现的笔数
            if($is_set_number >= $condition["day_frequency"]){
                return ajax_error("每天提现次数最多".$condition["day_frequency"]."次");
            }
            //提现手续费
            $service_charge_money =round($money * $condition["service_charge"] * 0.01,2);//注意单位
            //除去手续费的钱（实际到账的钱）
            $able_amount=round($money-$service_charge_money,2);
            $data =[
                "user_id"=>$member_id,//钱包支付记录ID（充值提现）
                "operation_time"=>date("Y-m-d H:i:s"), //操作时间
                "operation_linux_time"=>time(), //操作时间
                "operation_type"=>-1, //操作类型（-1提现,1充值）
                "operation_amount"=>$money, //操作金额
                "pay_type_content"=>"银行卡", //支付方式
                "money_status"=>2, //到款状态（1到账，2未到款）
                "recharge_describe"=>"提现",//描述
                "img_url"=>" ",//对应的图片链接
                "back_member"=>$user_name,//用户名
                "bank_card"=>$bank_card,//开户银行卡
                "bank_name"=>$bank_name,//开户银行
                "status"=>2, //审核状态（-1,2,1）不通过，待审核，通过
                "is_able_withdrawal"=>1, //是否可提现1可以提现，-1不可提现
                "able_amount"=>$able_amount,//除去手续费的钱（实际到账的钱）
                "service_charge"=>$service_charge_money, //提现手续费
            ];
            $res =Db::name("recharge_reflect")->insertGetId($data);
            if($res){
                //提现余额进行增减
              $result =  db("member")
              ->where("member_id",$member_id)
              ->setDec("member_recharge_money",$money);
                if($result){
                   $member_data = db("member")
                        ->field("member_recharge_money,member_wallet")
                        ->where("member_id",$member_id)
                        ->find();
                    //做记录
                    $datas=[
                        "user_id"=>$member_id,//用户ID
                        "wallet_operation"=> $money,//消费金额
                        "wallet_type"=>-1,//消费操作(1入，-1出)
                        "operation_time"=>date("Y-m-d H:i:s"),//操作时间
                        "operation_linux_time"=>time(), //操作时间
                        "wallet_remarks"=>"订单号：".$res."，微信提现".$money."元",//消费备注
                        "wallet_img"=>" ",//图标
                        "title"=>"微信提现",//标题（消费内容）
                        "order_nums"=>$res,//订单编号
                        "pay_type"=>"小程序", //支付方式
                        "wallet_balance"=>$member_data["member_recharge_money"] +$member_data["member_wallet"],//此刻钱包余额
                    ];
                    Db::name("wallet")->insert($datas); //存入消费记录表
                    return ajax_success("申请成功,请耐心等待审核",$res);
                }
            }else{
                return ajax_error("请重复申请");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:微信提现
     **************************************
     * @param Request $request
     */
    public function wechat_withdrawal(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $money =$request->only(["money"])["money"];
            $member_recharge_money =Db::name("member")
                ->where("member_id",$member_id)
                ->value("member_recharge_money");
            if($money <= 0){
                return ajax_error("金额不正确");
            }
            if($money <= $member_recharge_money){
                return ajax_error("提现金额不能超过可提现金额");
            }

            //条件
            $condition =Db::name("withdrawal")->find();
            //最少提现金额
            if($money < $condition["min_money"]){
                return ajax_error("最少提现金额".$condition["min_money"]."元");
            }
            //每天最高提现金额（微信则需要再做限制一次最高提现5000,一天最高提现总数50000）
            if($money > $condition["day_max_money"]){
                return ajax_error("最高提现金额".$condition["day_max_money"]."元");
            }

            //每日提现笔数需要低于设置
            $today =date("Y-m-d");
            $is_set_number =Db::name("recharge_reflect")
                ->where("operation_time","like","%" .$today ."%")
                ->where("operation_type",-1)
                ->where("user_id",$member_id)
                ->count();
            if($is_set_number >= $condition["day_frequency"]){
                return ajax_error("每天提现次数最多".$condition["day_frequency"]."次");
            }
            //微信需要多限制最高总钱
            $sum =Db::name("recharge_reflect")
                ->where("operation_time","like","%" .$today ."%")
                ->where("operation_type",-1)
                ->where("user_id",$member_id)
                ->sum("operation_amount");
            $condition_sum =$sum +$money;
            if($condition_sum >= 50000){
                $new_money = 50000 - $sum;
                return ajax_error("当天最高只能提现5万元，还可申请".$new_money."元");
            }
            //提现手续费
            $service_charge_money =round($money * $condition["service_charge"] * 0.01,2);//注意单位
            //除去手续费的钱（实际到账的钱）
            $able_amount=round($money-$service_charge_money,2);
            $data =[
                "user_id"=>$member_id,//钱包支付记录ID（充值提现）
                "operation_time"=>date("Y-m-d H:i:s"), //操作时间
                "operation_linux_time"=>time(), //操作时间
                "operation_type"=>-1, //操作类型（-1提现,1充值）
                "operation_amount"=>$money, //操作金额
                "pay_type_content"=>"微信", //支付方式
                "money_status"=>2, //到款状态（1到账，2未到款）
                "recharge_describe"=>"提现",//描述
                "img_url"=>" ",//对应的图片链接
                "status"=>2, //审核状态（-1,2,1）不通过，待审核，通过
                "is_able_withdrawal"=>1, //是否可提现1可以提现，-1不可提现
                "able_amount"=>$able_amount,//除去手续费的钱（实际到账的钱）
                "service_charge"=>$service_charge_money, //提现手续费
            ];
            $res =Db::name("recharge_reflect")->insertGetId($data);
            if($res){
                //提现余额进行增减
                $result =  db("member")
                    ->where("member_id",$member_id)
                    ->setDec("member_recharge_money",$money);
                if($result){
                    $member_data = db("member")
                        ->field("member_recharge_money,member_wallet")
                        ->where("member_id",$member_id)
                        ->find();
                    //做记录
                    $datas=[
                        "user_id"=>$member_id,//用户ID
                        "wallet_operation"=> $money,//消费金额
                        "wallet_type"=>-1,//消费操作(1入，-1出)
                        "operation_time"=>date("Y-m-d H:i:s"),//操作时间
                        "operation_linux_time"=>time(), //操作时间
                        "wallet_remarks"=>"订单号：".$res."，微信提现".$money."元",//消费备注
                        "wallet_img"=>" ",//图标
                        "title"=>"微信提现",//标题（消费内容）
                        "order_nums"=>$res,//订单编号
                        "pay_type"=>"小程序", //支付方式
                        "wallet_balance"=>$member_data["member_recharge_money"] +$member_data["member_wallet"],//此刻钱包余额
                    ];
                    Db::name("wallet")->insert($datas); //存入消费记录表
                    return ajax_success("申请成功",$res);
                }
                return ajax_success("申请成功,请耐心等待审核",$res);
            }else{
                return ajax_error("请重复申请");
            }
        }
    }







}