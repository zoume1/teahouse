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
            $user_info = Db::name("member")->field("pay_passwd,user_wallet")->where("id", $user_id)->find();//用户信息
            $password = $request->only("passwords")["passwords"]; //输入的密码
            if (password_verify($password,$user_info["pay_passwd"])) {
                //真实支付的价钱
                $money = Db::name("order_parts")->where("parts_order_number", $order_num)->sum("order_amount");
                if(!empty($money)){
                    $money =round($money,2);
                }else{
                    $money =0;
                }
                        $user_wallet = $user_info["user_wallet"];
                        if ($money > $user_wallet) {
                            exit(json_encode(array("status" => 3, "info" => "车主余额不足，请换其他方式支付")));
                        } else {
                            $select_data = Db::name("order_parts")
                                ->where("parts_order_number", $order_num)
                                ->select();
                            //对订单状态进行修改
                            $data['status'] = 2;
                            $data["pay_time"] = time();
                            $data['pay_type_content'] = "余额支付";
                            foreach ($select_data as $key => $val) {
                                $result = Db::name('order_parts')
                                    ->where("parts_order_number", $val["parts_order_number"])
                                    ->update($data);//修改订单状态,支付宝单号到数据库
                            }
                            //如果修改成功则进行钱抵扣
                            if ($result > 0) {
                                foreach ($select_data as $ks => $vs) {
                                    $titles[] = $vs["parts_goods_name"];
                                }
                                $title = implode("", $titles);
                                //进行钱包消费记录
                                Db::name("user")->where("id", $select_data[0]["user_id"])->update(["user_wallet" => $user_info["user_wallet"] - $money]);
                                $owner_wallet = Db::name("user")->where("id", $select_data[0]["user_id"])->value("user_wallet");
                                $arr_condition = "`status` = '1' and `is_deduction` = '1'  and  `user_id` = " . $select_data[0]["user_id"];
                                $business_wallet = Db::name("business_wallet")
                                    ->where($arr_condition)
                                    ->sum("money");
                                $new_wallet = $business_wallet+$owner_wallet;
                                $datas = [
                                    "user_id" => $select_data[0]["user_id"],//用户ID
                                    "wallet_operation" => -$money,//消费金额
                                    "wallet_type" => -1,//消费操作(1入，-1出)
                                    "operation_time" => date("Y-m-d H:i:s"),//操作时间
                                    "wallet_remarks" => "订单号：" . $order_num . "，余额消费，支出" . $money . "元",//消费备注
                                    "wallet_img" => "index/image/money2.png",//图标
                                    "title" => $title,//标题（消费内容）
                                    "order_nums" => $order_num,//订单编号
                                    "pay_type" => "余额支付", //支付方式
                                    "wallet_balance" => $new_wallet,//此刻钱包余额
                                    "is_business"=>1,//判断是车主消费还是商家消费（1车主消费，2商家消费）
                                ];
                                Db::name("wallet")->insert($datas);
                                foreach ($select_data as $keys => $vals) {
                                    //铃声
                                    $store_id =$vals["store_id"];
                                    $store_user_id =Db::name("store")->where("store_id",$store_id)->value("user_id");
                                    $user_count =Db::name("user")->where("id",$store_user_id)->value("phone_num");
                                    $X = new  Xgcontent;
                                    $X->push_Accountp("来新订单","来新订单了",$user_count);
                                }
                                return ajax_success('支付成功', ['status' => 1]);
                            } else {
                                return ajax_error('验证失败了', ['status' => 0]);
                            }
                        }

                } else {
                    return ajax_error('密码错误', ['status' => 0]);
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