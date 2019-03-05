<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/18 0018
 * Time: 14:04
 * 余额支付
 */
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use app\index\controller\Xgcontent;

class Balance extends Controller
{
    //商品余额支付
    public function balance_payment(Request $request)
    {
        if ($request->isPost()) {
            $order_num = $request->only(['order_num'])['order_num'];
            //验证支付密码
            $user_id = $request->only(["member_id"])["member_id"];
            $user_info = Db::name("member")
                ->field("pay_password,member_wallet,member_recharge_money")
                ->where("member_id", $user_id)
                ->find();//用户信息
            $password = $request->only("passwords")["passwords"]; //输入的密码
            if (password_verify($password,$user_info["pay_password"])) {
                //真实支付的价钱
                $money = Db::name("order")->where("parts_order_number", $order_num)->sum("order_amount");
                if(!empty($money)){
                    $money =round($money,2);
                }else{
                   return ajax_error("价钱出错，无法支付");
                }
                        $user_wallet = $user_info["member_wallet"];//升值进来的钱
                        if($money > $user_wallet) {
                            // 如果钱包的钱不够用，则使用充值的钱
                            $member_recharge_money =$user_info["member_recharge_money"];//充值进来的钱
                            $n_money = $money - $user_wallet; //剩下需要支付的部分
                            $new_money =$member_recharge_money - $n_money;
                            if($new_money < 0){
                                return ajax_error("余额不足");
                            }

                            //钱包归0，充值进来的钱包进行余额减
                            $new_data =[
                                "member_wallet" =>0,
                                "member_recharge_money"=>$new_money,
                            ];
                        }else {
                            //钱包进行减（优先使用钱包的钱，即不可提现的钱）
                            $new_money = $user_wallet - $money;
                            $new_data =[
                                "member_wallet" =>$new_money,
                            ];
                        }
                Db::name("member")->where("member_id",$user_id)->update($new_data);
                //对订单状态进行修改
                $result= Db::name("order")
                    ->where("parts_order_number",$order_num)
                    ->update(["status"=>2,"pay_time"=>time()]);
                //如果修改成功则进行钱抵扣
                if ($result > 0) {
                    //做消费记录
                    $information =Db::name("order")
                        ->field("member_id,order_real_pay,parts_goods_name")
                        ->where("parts_order_number",$order_num)->find();
                    $user_information =Db::name("member")
                        ->field("member_wallet,member_recharge_money")
                        ->where("member_id",$information["member_id"])
                        ->find();
                    $now_money =$user_information["member_wallet"]+$user_information["member_recharge_money"];
                    $datas=[
                        "user_id"=>$information["member_id"],//用户ID
                        "wallet_operation"=> $information["order_real_pay"],//消费金额
                        "wallet_type"=>-1,//消费操作(1入，-1出)
                        "operation_time"=>date("Y-m-d H:i:s"),//操作时间
                        "wallet_remarks"=>"订单号：".$order_num."，微信消费".$information["order_real_pay"]."元",//消费备注
                        "wallet_img"=>" ",//图标
                        "title"=>$information["parts_goods_name"],//标题（消费内容）
                        "order_nums"=>$order_num,//订单编号
                        "pay_type"=>"小程序", //支付方式/
                        "wallet_balance"=>$now_money,//此刻钱包余额
                    ];
                    Db::name("wallet")->insert($datas); //存入消费记录表
                    return ajax_success('支付成功', ['status' => 1]);
                } else {
                    return ajax_error('验证失败了');
                }
                }else {
                    return ajax_error('密码错误');
                }
            }
        }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:校验支付密码
     **************************************
     */
    public function check_password(Request $request){
        //验证支付密码
        $user_id = $request->only(["member_id"])["member_id"];
        $user_info = Db::name("member")
            ->field("pay_password")
            ->where("member_id", $user_id)
            ->find();//用户信息
        $password = $request->only("passwords")["passwords"]; //输入的密码
        if (password_verify($password,$user_info["pay_password"])){
            return ajax_success("支付密码正确",["status"=>1]);
        }else{
            return ajax_error("支付密码错误",["status"=>0]);
        }
    }

}