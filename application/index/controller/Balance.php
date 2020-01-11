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
use think\Validate;
use think\Session;
use app\admin\model\Order as GoodsOrder;
use app\index\controller\Order as Orderset;
use app\common\model\dealer\Order as OrderModel;
use app\index\controller\Xgcontent;
use app\index\model\Serial;
use app\common\exception\BaseException;


class Balance extends Controller
{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:普通商品商品余额支付
     **************************************
     * @param Request $request
     */
    public function balance_payment(Request $request)
    {
        if ($request->isPost()) {
            $order_num = $request->only(['order_num'])['order_num'];
            //验证支付密码
            $user_id = $request->only(["member_id"])["member_id"];
            $user_info = Db::name("member")
                ->field("pay_password,member_wallet,member_recharge_money")
                ->where("member_id", $user_id)
                ->find(); //用户信息
            $password = $request->only("passwords")["passwords"]; //输入的密码
            if (password_verify($password, $user_info["pay_password"])) {
                //真实支付的价钱
                $money = Db::name("order")
                    ->where("parts_order_number", $order_num)
                    ->sum("order_amount");
                if (!empty($money)) {
                    $money = round($money, 2);
                } else {
                    return ajax_error("价钱出错，无法支付");
                }
                $user_wallet = $user_info["member_wallet"]; //升值进来的钱
                if ($money > $user_wallet) {
                    // 如果钱包的钱不够用，则使用充值的钱
                    $member_recharge_money = $user_info["member_recharge_money"]; //充值进来的钱
                    $n_money = $money - $user_wallet; //剩下需要支付的部分
                    $new_money = $member_recharge_money - $n_money;
                    if ($new_money < 0) {
                        return ajax_error("余额不足");
                    }

                    //钱包归0，充值进来的钱包进行余额减
                    $new_data = [
                        "member_wallet" => 0,
                        "member_recharge_money" => $new_money,
                    ];
                } else {
                    //钱包进行减（优先使用钱包的钱，即不可提现的钱）
                    $new_money = $user_wallet - $money;
                    $new_data = [
                        "member_wallet" => $new_money,
                    ];
                }
                $yuepay = Db::name("member")->where("member_id", $user_id)->update($new_data);
                if ($yuepay) {
                    $order_info = Db::name("order")
                        ->where("parts_order_number", $order_num)
                        ->find();
                    $order = GoodsOrder::getOrderInforMation($order_info);
                    $model = OrderModel::grantMoney($order);

                    $serial_data = array(
                        'serial_number' => $order_num,
                        'money' => $order_info['order_real_pay'],
                        'phone_number' => $order_info['user_phone_number'],
                        'create_time' => time(),
                        'type' => '1',
                        'status' => '普通订单',
                        'store_id' => $order_info['store_id'],
                        'prime' => $order_info['accounting']
                    );
                    Serial::serial_add($serial_data);
                    //订单类型
                    if ($order_info['order_type'] == 2) {   //到店自提
                        $status = 5;
                    } else {
                        $status = 2;
                    }
                    $result = Db::name("order")
                        ->where("parts_order_number", $order_num)
                        ->update(["status" => $status, "pay_time" => time(), "si_pay_type" => 1]);
                    $host_rest = Db::name("house_order")
                        ->where("parts_order_number", $order_num)
                        ->update(["status" => 3, "pay_time" => time(), "si_pay_type" => 1]);

                    //余额支付，减去商品库存
                    $goods_order = db('order')->where("parts_order_number", $order_num)->select();
                    foreach ($goods_order as $k => $v) {
                        if ($v['is_limit'] == 1) {
                            db('limited')->where('goods_id', $v['goods_id'])->setDec('goods_repertory', $v['order_quantity']);
                        }
                        if ($goods_order[$k]['special_id'] != 0) {
                            $boolw = Db::name('special')->where('id', $goods_order[$k]['special_id'])->setInc('volume', $goods_order[$k]['order_quantity']);
                        } else {
                            $booltt = Db::name('goods')->where('id', $goods_order[$k]['goods_id'])->setInc('goods_volume', $goods_order[$k]['order_quantity']);
                        }
                    }

                    //做消费记录                    
                    if ($result > 0) {
                        $information = Db::name("order")
                            ->field("member_id,order_real_pay,parts_goods_name")
                            ->where("parts_order_number", $order_num)->find();
                        $user_information = Db::name("member")
                            ->field("member_wallet,member_recharge_money")
                            ->where("member_id", $information["member_id"])
                            ->find();
                        $now_money = $user_information["member_wallet"] + $user_information["member_recharge_money"];
                        $datas = [
                            "user_id" => $information["member_id"], //用户ID
                            "wallet_operation" => $information["order_real_pay"], //消费金额
                            "wallet_type" => -1, //消费操作(1入，-1出)
                            "operation_time" => date("Y-m-d H:i:s"), //操作时间
                            "operation_linux_time" => time(), //操作时间
                            "wallet_remarks" => "订单号：" . $order_num . "，微信消费" . $information["order_real_pay"] . "元", //消费备注
                            "wallet_img" => " ", //图标
                            "title" => $information["parts_goods_name"], //标题（消费内容）
                            "order_nums" => $order_num, //订单编号
                            "pay_type" => "余额", //支付方式/
                            "wallet_balance" => $now_money, //此刻钱包余额
                        ];
                        Db::name("wallet")->insert($datas); //存入消费记录表
                        return ajax_success('支付成功', ['status' => 1]);
                    } else {
                        return ajax_error('验证失败了');
                    }
                } else {
                    return ajax_error('扣款失败');
                }
            } else {
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
    public function check_password(Request $request)
    {
        //验证支付密码
        $user_id = $request->only(["member_id"])["member_id"];
        $user_info = Db::name("member")
            ->field("pay_password")
            ->where("member_id", $user_id)
            ->find(); //用户信息
        $password = $request->only("passwords")["passwords"]; //输入的密码
        if (password_verify($password, $user_info["pay_password"])) {
            return ajax_success("支付密码正确", ["status" => 1]);
        } else {
            return ajax_error("支付密码错误", ["status" => 0]);
        }
    }


    /**
     **************GY*******************
     * @param Request $request
     * Notes:众筹打赏余额支付
     **************************************
     * @param Request $request
     */
    public function remainder_pay(Request $request)
    {
        if ($request->isPost()) {
            $order_num = $request->only(['order_num'])['order_num'];
            //验证支付密码
            $user_id = $request->only(["member_id"])["member_id"];
            $user_info = Db::name("member")
                ->field("pay_password,member_wallet,member_recharge_money")
                ->where("member_id", $user_id)
                ->find(); //用户信息
            $password = $request->only("passwords")["passwords"]; //输入的密码
            if (password_verify($password, $user_info["pay_password"])) {
                //真实支付的价钱
                $money = Db::name("reward")->where("order_number", $order_num)->value('money');
                if (!empty($money)) {
                    $money = round($money, 2);
                } else {
                    return ajax_error("价钱出错，无法支付");
                }
                $user_wallet = $user_info["member_wallet"]; //升值进来的钱
                if ($money > $user_wallet) {
                    // 如果钱包的钱不够用，则使用充值的钱
                    $member_recharge_money = $user_info["member_recharge_money"]; //充值进来的钱
                    $n_money = $money - $user_wallet; //剩下需要支付的部分
                    $new_money = $member_recharge_money - $n_money;
                    if ($new_money < 0) {
                        return ajax_error("余额不足");
                    }

                    //钱包归0，充值进来的钱包进行余额减
                    $new_data = [
                        "member_wallet" => 0,
                        "member_recharge_money" => $new_money,
                    ];
                } else {
                    //钱包进行减（优先使用钱包的钱，即不可提现的钱）
                    $new_money = $user_wallet - $money;
                    $new_data = [
                        "member_wallet" => $new_money,
                    ];
                }
                Db::name("member")->where("member_id", $user_id)->update($new_data);
                //对订单状态进行修改
                $result = Db::name("reward")
                    ->where("order_number", $order_num)
                    ->update(["status" => 2, "pay_time" => time()]);
                //如果修改成功则进行钱抵扣
                if ($result > 0) {
                    //做消费记录
                    $information = Db::name("reward")
                        ->field("member_id,money,crowd_name")
                        ->where("order_number", $order_num)->find();
                    $user_information = Db::name("member")
                        ->field("member_wallet,member_recharge_money")
                        ->where("member_id", $information["member_id"])
                        ->find();
                    $now_money = $user_information["member_wallet"] + $user_information["member_recharge_money"];
                    $datas = [
                        "user_id" => $information["member_id"], //用户ID
                        "wallet_operation" => $information["money"], //消费金额
                        "wallet_type" => -1, //消费操作(1入，-1出)
                        "operation_time" => date("Y-m-d H:i:s"), //操作时间
                        "operation_linux_time" => time(), //操作时间
                        "wallet_remarks" => "订单号：" . $order_num . "，微信消费" . $information["money"] . "元", //消费备注
                        "wallet_img" => " ", //图标
                        "title" => $information["crowd_name"], //标题（消费内容）
                        "order_nums" => $order_num, //订单编号
                        "pay_type" => "小程序", //支付方式/
                        "wallet_balance" => $now_money, //此刻钱包余额
                    ];
                    Db::name("wallet")->insert($datas); //存入消费记录表
                    return ajax_success('支付成功', ['status' => 1]);
                } else {
                    return ajax_error('验证失败了');
                }
            } else {
                return ajax_error('密码错误');
            }
        }
    }


    /**
     **************郭杨*******************
     * @param Request $request
     * Notes:众筹商品商品余额支付
     **************************************
     * @param Request $request
     */
    public function crowd_payment(Request $request)
    {
        if ($request->isPost()) {
            $order_num = $request->only(['order_num'])['order_num'];
            //验证支付密码
            $user_id = $request->only(["member_id"])["member_id"];
            $user_info = Db::name("member")
                ->field("pay_password,member_wallet,member_recharge_money")
                ->where("member_id", $user_id)
                ->find(); //用户信息

            $password = $request->only("passwords")["passwords"]; //输入的密码
            if (password_verify($password, $user_info["pay_password"])) {
                //真实支付的价钱
                $money = Db::name("crowd_order")
                    ->where("parts_order_number", $order_num)
                    ->value("order_real_pay");
                $order_info = Db::name("crowd_order")
                    ->where("parts_order_number", $order_num)
                    ->find();
                if (!empty($money)) {
                    $money = round($money, 2);
                } else {
                    return ajax_error("价钱出错，无法支付");
                }
                $user_wallet = $user_info["member_wallet"]; //升值进来的钱
                if ($money > $user_wallet) {
                    // 如果钱包的钱不够用，则使用充值的钱
                    $member_recharge_money = $user_info["member_recharge_money"]; //充值进来的钱
                    $n_money = $money - $user_wallet; //剩下需要支付的部分
                    $new_money = $member_recharge_money - $n_money;
                    if ($new_money < 0) {
                        return ajax_error("余额不足");
                    }

                    //钱包归0，充值进来的钱包进行余额减
                    $new_data = [
                        "member_wallet" => 0,
                        "member_recharge_money" => $new_money,
                    ];
                } else {
                    //钱包进行减（优先使用钱包的钱，即不可提现的钱）
                    $new_money = $user_wallet - $money;
                    $new_data = [
                        "member_wallet" => $new_money,
                    ];
                }
                Db::name("member")->where("member_id", $user_id)->update($new_data);
                //对订单状态进行修改
                $result = Db::name("crowd_order")
                    ->where("parts_order_number", $order_num)
                    ->update(["status" => 2, "pay_time" => time(), "si_pay_type" => 1]);
                //如果修改成功则进行钱抵扣
                if ($result > 0) {
                    //做消费记录
                    $information = Db::name("crowd_order")
                        ->field("member_id,order_real_pay,parts_goods_name")
                        ->where("parts_order_number", $order_num)->find();
                    $user_information = Db::name("member")
                        ->field("member_wallet,member_recharge_money")
                        ->where("member_id", $information["member_id"])
                        ->find();
                    $one = db("crowd_special")->where("id", $order_info['special_id'])->setInc("collecting");
                    $twe = db("crowd_special")->where("id", $order_info['special_id'])->setInc("collecting_money", $order_info['order_amount']);
                    $three = db("crowd_special")->where("id", $order_info['special_id'])->setInc("collecting_number", $order_info['order_quantity']);
                    $four = db("crowd_special")->where("id", $order_info['special_id'])->setDec("stock", $order_info['order_quantity']);

                    $now_money = $user_information["member_wallet"] + $user_information["member_recharge_money"];
                    $datas = [
                        "user_id" => $information["member_id"], //用户ID
                        "wallet_operation" => $information["order_real_pay"], //消费金额
                        "wallet_type" => -1, //消费操作(1入，-1出)
                        "operation_time" => date("Y-m-d H:i:s"), //操作时间
                        "operation_linux_time" => time(), //操作时间
                        "wallet_remarks" => "订单号：" . $order_num . "，微信消费" . $information["order_real_pay"] . "元", //消费备注
                        "wallet_img" => " ", //图标
                        "title" => $information["parts_goods_name"], //标题（消费内容）
                        "order_nums" => $order_num, //订单编号
                        "pay_type" => "小程序", //支付方式/
                        "wallet_balance" => $now_money, //此刻钱包余额
                    ];
                    Db::name("wallet")->insert($datas); //存入消费记录表
                    return ajax_success('支付成功', ['status' => 1]);
                } else {
                    return ajax_error('验证失败了');
                }
            } else {
                return ajax_error('密码错误');
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:普通商品商品出仓余额支付
     **************************************
     * @param Request $request
     */
    public function payment_out_order(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            $new_order = new Orderset;
            $validate  = new Validate([
                ['uniacid', 'require', 'uniacid不能为空'],
                ['id', 'require', '订单id不能为空'],
                ['member_id', 'require', '会员id不能为空'],
                ['house_charges', 'require', '出仓费用不能为空'],
                ['order_quantity', 'require', '出仓数量不能为空'],
                ['surplus', 'require', '剩余数量不能为空'],
                ['lowest_unit', 'require', '出仓单位不能为空'],
                ['surplus_number', 'require', '剩余仓储显示不能为空'],
                ['string_number', 'require', '出仓单位显示能为空'],
                ['address_id', 'require', '地址id不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($data)) {
                $error = $validate->getError();
                return jsonError($error);
            }
            $user_info = Db::name("member")
                ->field("pay_password,member_wallet,member_recharge_money")
                ->where("member_id", $data['member_id'])
                ->find(); //用户信息 
            if ($data['house_charges'] > $user_info['member_wallet']) jsonError('账户余额不足，请充值');
            $house_order = Db::name("house_order")->where("id", 'EQ', $data['id'])->find();
            if (!empty($house_order)) {
                Db::startTrans();
                try {
                    //生成定单号
                    $time = date("Y-m-d", time());
                    $v = explode('-', $time);
                    $time_second = date("H:i:s", time());
                    $vs = explode(':', $time_second);
                    $set_parts_number = "CC" . $v[0] . $v[1] . $v[2] . $vs[0] . $vs[1] . $vs[2] . ($data["member_id"] + 1001); //订单编号

                    //对应数量和单位
                    if (!empty($house_order['special_id'])) {
                        $special_data = Db::name('special')->where("id", $house_order['special_id'])->find();
                        $special_unit = $special_data['unit'];
                        $special_num = $special_data['num'];
                        $unit = explode(",", $special_unit);
                        $num = explode(",", $special_num);
                    } else {
                        $goods_data = Db::name('goods')->where("id", $house_order['goods_id'])->find();
                        $special_unit = $goods_data['unit'];
                        $special_num = $goods_data['num'];
                        $unit = explode(",", $special_unit);
                        $num = explode(",", $special_num);
                    }
                    $store_number = implode(',',$data['string_number']);
                    $new_quantity = $data['surplus']; //剩余数量
                    $new_store_number = $data['surplus_number'];
                    $out_order = array(
                        'house_order_id' => $data['id'],
                        'out_order_number' => $set_parts_number,
                        'goods_name' => $house_order['parts_order_number'],
                        'user_phone_number' => $house_order['user_phone_number'],
                        'store_house_id' => $house_order['store_house_id'],
                        'order_quantity' => $data['order_quantity'],
                        'house_charges' => $data['house_charges'],
                        'status' => 2,
                        'member_id' => $house_order['member_id'],
                        'goods_id' => $house_order['goods_id'],
                        'special_id' => $house_order['special_id'],
                        'goods_money' =>  $house_order['goods_money'],
                        'pay_time' => time(),
                        'si_pay_type' => 1,
                        'address_id' => $data['address_id'],
                        'store_number' => $store_number,
                        'store_unit' => $data['lowest_unit'],
                        'store_id' => $data['uniacid'],
                        'unit' => $special_unit,
                        'num' => $special_num
                    );
                    $bool = Db::name('out_house_order')->insert($out_order);
                    $boole =  Db::name('house_order')->where('id',$data['id'])->update(['store_number'=>$new_store_number]);
                    if ($bool && $boole) {
                        if($data['house_charges'] > 0){
                            $is_money = Db::name('member')->where('member_id', '=', $data['member_id'])->setDec('member_wallet', $data['house_charges']);
                        }
                        $is_money = Db::name('member')->where('member_id', '=', $data['member_id'])->setDec('member_wallet', $data['house_charges']);
                        $is_address_status =  Db::name("user_address")->where("id", $out_order['address_id'])->find();
                        $harvest_address_city = str_replace(',', '', $is_address_status['address_name']);
                        $harvest_address = $harvest_address_city . $is_address_status['harvester_real_address']; //收货人地址
                        $harvester = $is_address_status['harvester'];
                        $harvester_phone_num = $is_address_status['harvester_phone_num'];
                        //生成order订单
                        $order_data = [
                            'goods_id' => $house_order['goods_id'],
                            'goods_image' => $house_order['goods_image'], //订单号
                            'parts_goods_name' => $house_order['parts_goods_name'], //商品名称
                            'goods_money' => $house_order['goods_money'], //商品价格
                            'order_quantity' => $out_order['order_quantity'], //出仓数量
                            'order_amount' => $out_order['house_charges'],   //出仓金额
                            'order_real_pay' => $out_order['house_charges'], //订单实际支付的金额(即优惠券抵扣之后的价钱）
                            'user_account_name' => $house_order['user_account_name'], //用户名
                            'user_phone_number' => $house_order['user_phone_number'], //用户账号
                            'order_create_time' => $out_order['pay_time'], //下单时间
                            'harvester_address' => $harvest_address,
                            'status' => 2,
                            'parts_order_number' => $out_order['out_order_number'], //订单编号
                            'member_id' => $house_order['member_id'], //用户id
                            'pay_time' => $out_order['pay_time'], //支付时间
                            'goods_standard' => $house_order['goods_standard'], //商品规格
                            'harvester' => $harvester, //收件人
                            'harvest_phone_num' => $harvester_phone_num, //收件人手机
                            'refund_amount' => $out_order['house_charges'], //可退款金额,
                            'normal_future_time' => $house_order['normal_future_time'], //订单关闭时间
                            'goods_describe' => $house_order['goods_describe'], //商品买点
                            'special_id' => $house_order['special_id'], //特殊规格id
                            'order_type' => 1,
                            'freight'=> $out_order['house_charges'],   //出仓金额
                            'si_pay_type' => 2, //支付方式（微信）
                            'unit' => $data['lowest_unit'], //出仓单位
                            'store_id' => $out_order['store_id'], //店铺id
                            'coupon_type' => 1, //商品类型
                        ];
                        $restel = Db::name("order")->insert($order_data);
                        if($data['surplus'] == 0){
                            Db::name("house_order")->where('id',$data['id'])->delete();
                        }
                    }
                    Db::commit();
                    return jsonSuccess('出仓成功');
                } catch (\Exception $e) {
                    $this->error = $e->getMessage();
                    Db::rollback();
                    halt($this->error);
                    return jsonError('出仓失败');
                }
            } else {
                return jsonError('出仓订单数据错误');
            }
        }
    }
}
