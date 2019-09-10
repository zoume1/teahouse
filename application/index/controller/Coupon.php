<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/14
 * Time: 15:21
 */
namespace app\index\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;


class Coupon extends Controller
{
    /**
     * [未使用优惠券显示]
     * 郭杨
     */
    public function coupon_untapped(Request $request)
    {
        if ($request->isPost()) {
            $store_id  = $request->only(['uniacid'])['uniacid'];
            $member_grade_name = $request->only(['member_grade_name'])['member_grade_name'];
            $open_id = $request->only(['open_id'])['open_id'];
            $coupon = Db::name("coupon")
                    ->where("store_id","EQ",$store_id)
                    ->field('id,use_price,scope,start_time,end_time,money,suit,label,suit_price')
                    ->select();
            $time = strtotime(date("Y-m-d",strtotime("-1 day")));

            //已使用
            $member_id = Db::name("member")->where("member_openid",$open_id)->value('member_id');
            $coupon_id = Db::name("order")->where("member_id",$member_id)
                         ->where("coupon_id",'<>',0)
                         ->where("store_id","EQ",$store_id)
                         ->distinct($member_id)
                         ->field("coupon_id")
                         ->select();
            
            if(count($coupon_id)>0){
                foreach($coupon_id as $key => $value){
                    foreach($value as $ke => $va){
                        $rest[] = $va;
                    }
                }                        
            //未使用(去掉已使用)
                foreach($coupon as $key => $value){
                    if(!in_array($value['id'],$rest)){
                    $value['scope'] = explode(",",$value['scope']);
                    $value['suit_price2'] = explode(",",$value['suit_price']);
                    $value['start_time'] = strtotime($value['start_time']);
                    $value['end_time'] = strtotime($value['end_time']);
                    if(in_array($member_grade_name,$value['scope']) && $value['end_time'] > $time){
                        $data[] = $value;
                    } 
                } 
            }
        } else { //如果没有使用过
                foreach($coupon as $key => $values){
                    $values['scope'] = explode(",",$values['scope']);
                    $values['suit_price2'] = explode(",",$values['suit_price']);
                    $values['start_time'] = strtotime($values['start_time']);
                    $values['end_time'] = strtotime($values['end_time']);
                    if(in_array($member_grade_name,$values['scope']) && $values['end_time'] > $time){
                        $data[] = $values;
                    }
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
     * [已使用优惠券显示]
     * 郭杨
     */
    public function coupon_user(Request $request)
    {
        if ($request->isPost()) {
            $store_id = $request->only(['uniacid'])['uniacid'];
            $member_grade_name = $request->only(['member_grade_name'])['member_grade_name'];
            $open_id = $request->only(['open_id'])['open_id'];
            $member_id = Db::name("member")->where("member_openid",$open_id)->value('member_id');
            $coupon_id = Db::name("order")->where("member_id",$member_id)
                            ->where("coupon_id",'<>',0)
                            ->where("store_id","EQ",$store_id)	
                            ->distinct($member_id)
                            ->field("coupon_id")
                            ->select();
            if(count($coupon_id) > 0){
                foreach($coupon_id as $key => $value){
                    $rest[] = Db::name("coupon")
                    ->where("store_id","EQ",$store_id)
                    ->where("id",$value["coupon_id"])
                    ->field('id,use_price,scope,start_time,end_time,money,suit,label')
                    ->find();
                }
                foreach($rest as $k => $v){
                    $v['scope'] = explode(",",$v['scope']);
                }
            } else {
                $rest = null;
            }

            if (!empty($rest)) {
                return ajax_success('传输成功', $rest);
            } else {
                return ajax_error("数据为空",$rest);

            }
        }

    }



    /**
     * [过期优惠券显示]
     * 郭杨
     */
    public function coupon_time(Request $request)
    {
        if ($request->isPost()) {
            $store_id = $request->only(['uniacid'])['uniacid'];
            $time = strtotime(date("Y-m-d",strtotime("-1 day")));//当前时间戳减一天
            $member_grade_name = $request->only(['member_grade_name'])['member_grade_name'];
            $open_id = $request->only(['open_id'])['open_id'];
            $coupons = db("coupon")
                    ->where("store_id","EQ",$store_id)
                    ->select();
            foreach($coupons as $key => $value){
                $value['scope'] = explode(",",$value['scope']);
                $value['start_time'] = strtotime($value['start_time']);
                $value['end_time'] = strtotime($value['end_time']);
                if(in_array($member_grade_name,$value['scope']) && $value['end_time'] < $time){
                    $datas[] = $coupons[$key];
                }              
            }           
            if (!empty($datas)) {
                return ajax_success('传输成功', $datas);
            } else {
                return ajax_error("数据为空");

            }
        }

    }


    /**
     * [优惠券适用商品显示]
     * 郭杨
     */
    public function coupon_goods(Request $request)
    {
        if ($request->isPost()) {
            $coupon_id = $request->only(['coupon_id'])['coupon_id']; //优惠券id
            $member_id = $request->only(["open_id"])["open_id"];
            $coupon_good = 0;
            $member_grade_id = db("member")->where("member_openid", $member_id)->value("member_grade_id");
            $goods_id = db("join")->where("coupon_id",$coupon_id)->where("label",1)->field('goods_id')->select();
            $discount = db("member_grade")->where("member_grade_id", $member_grade_id)->value("member_consumption_discount");

            if(!empty($goods_id))
            {
                foreach($goods_id as $key=>$value)
                {
                    //根据优惠券id判断商品类型
                    $coupon_type = db("join")->where("coupon_id",$coupon_id)->where("label",1)->value('coupon_type');
                    if($coupon_type=='1'){
                        $goods[] = db("goods")->where("id",$value["goods_id"])->find(); //该商品是否上架                  
                    }else{
                        //众筹商品
                        $re= db("crowd_goods")->where("id",$value["goods_id"])->find(); //该商品是否上架    
                        $re['goods_standard'] ='2';  //多规格
                        $goods[]=$re;           
                    }
                }
                foreach ($goods as $k => $v)
                {
                    if($goods[$k]["goods_standard"] == 1){   //普通商品
                        $standard[$k] = db("special")->where("goods_id", $goods[$k]['id'])->select();
                        $max[$k] = db("special")->where("goods_id", $goods[$k]['id'])-> max("price") * $discount;//最高价格
                        $min[$k] = db("special")->where("goods_id", $goods[$k]['id'])-> min("price") * $discount;//最低价格
                        $goods[$k]["goods_standard"] = $standard[$k];
                        $goods[$k]["max_price"] = $max[$k];
                        $goods[$k]["min_price"] = $min[$k];
                    } elseif($goods[$k]["goods_standard"] == 2){  //众筹商品
                        $standard[$k] = db("crowd_special")->where("goods_id", $goods[$k]['id'])->select();
                        $max[$k] = db("crowd_special")->where("goods_id", $goods[$k]['id'])-> max("price") * $discount;//最高价格
                        $min[$k] = db("crowd_special")->where("goods_id", $goods[$k]['id'])-> min("price") * $discount;//最低价格
                        $goods[$k]["goods_standard"] = $standard[$k];
                        $goods[$k]["max_price"] = $max[$k];
                        $goods[$k]["min_price"] = $min[$k];
                    }else {
                        $goods[$k]["goods_new_money"] = $goods[$k]["goods_new_money"] * $discount;
                    }
                }                
            }
            if (!empty($goods)) {
                return ajax_success('传输成功', $goods);
            } else {
                return ajax_error("该优惠券适用所用商品",$coupon_good);

            }
        }
    }

    /**
     * [商品下单适用优惠券]
     * 郭杨
     * uniacid
     */
    public function coupon_appropriated(Request $request)
    {
        if($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $time = strtotime(date("Y-m-d",strtotime("-1 day")));//当前时间戳减一天
            $datas = $request->param(); //包含goods_id and  open_id
            $goods_id = array_unique($datas['goods_id']);
            // $goods_id = $datas['goods_id'];
            // $goods_id=array(
            //     '0'=>15,
            // );
            $open_id = $datas['open_id'];
            $member_id = Db::name("member")->where("member_openid",$open_id)->value('member_id');
            $money = $datas['money'];     //优惠价金额
            $member_grade_name = $datas['member_grade_name'];   //会员等级
            $goods_type = $datas['coupon_type'];  //商品类型   1  普通商品
            //所有使用的优惠券    
            // $coupon_info=[];
            foreach($goods_id as $k =>$v){
                    $coupons = Db::name("coupon")->where("use_price","<=",$money)->where("store_id","EQ",$store_id)->where("coupon_type",$goods_type)->field('id,use_price,scope,start_time,end_time,money,suit,label,suit_price')->select();
                    if(empty($coupons)){
                        continue;
                    }
                    foreach($coupons as $k2 =>$v2){
                        if($v2['suit']==1){   //部分商品
                            //获取该商品可用的优惠券
                            $pp=db('join')->where(['goods_id'=>$v,'coupon_id'=>$v2['id']])->select();
                            if($pp){
                                foreach($pp as $k4 =>$v4){
                                    $coupon_info[]=db('coupon')->where('id',$v4['coupon_id'])->find();
                                }
                            }
                        }else{
                            //全部商品
                            $coupon_info[]=$v2;
                        }
                    }
            }
            //去除使用的优惠券
            if(!empty($coupon_info)){
                foreach($coupon_info as $k3 =>$v3){
                    //判断优惠券是否使用
                    $is_use=db('order')->where(['member_id'=>$member_id,'coupon_id'=>$v3['id']])->find();
                    if($is_use){
                        unset($coupon_info[$k3]);
                        continue;
                    }
                    //判断会员面向范围
                    $scope = explode(",",$v3['scope']);
                    //判断是否在适用范围和是否过期
                    if(in_array($member_grade_name,$scope) && strtotime($v3['end_time']) > $time){ 
                       
                    }else{
                        unset($coupon_info[$k3]);
                        continue;
                    }
                    $coupon_info[$k3]['suit_price2'] = explode(",",$coupon_info[$k3]['suit_price']);

                }
            }
            if (!empty($coupon_info)) {
                return ajax_success('传输成功', $coupon_info);
            } else {
                return ajax_error("没有适用优惠券"); 
            }   
        }
    }


    /**
     * [优惠券显示]
     * 郭杨
     */
    public function coupon_minute(Request $request)
    {
        if ($request->isPost()) {
            $coupon_id = $request->only(['coupon_id'])['coupon_id'];
            $rest = Db::name("coupon")->where("id",$coupon_id)->field("money")->find();
            if (!empty($rest)) {
                return ajax_success('传输成功', $rest);
            } else {
                return ajax_error("数据为空",$rest);
            }
        }
    }

    

    /**
     * [积分商品显示]
     * 郭杨
     */
    public function bonus_index(Request $request)
    {
        if ($request->isPost()) {
        $store_id = $request->only(['uniacid'])['uniacid'];
        $bonus = db("bonus_mall")->where("store_id","EQ",$store_id)->where("status",1)->order('sort_number desc')->select();          
            if (!empty($bonus)) {
                return ajax_success('传输成功', $bonus);
            } else {
                return ajax_error("数据为空");

            }
        }
        
    }


    /**
     * [积分商品详细显示]
     * 郭杨
     */
    public function bonus_detailed(Request $request)
    {
        if ($request->isPost()) {
        $bonus_id = $request->only(['id'])['id']; //积分商城商品id
        $bonus = db("bonus_mall")->where('id',$bonus_id)->where("status",1)->order('id desc')->select();
        foreach ($bonus as $key => $value) {
            $bonus[$key]["goods_show_images"] = explode(",",$bonus[$key]["goods_show_images"]);
        }         
        if (!empty($bonus)) {
            return ajax_success('传输成功', $bonus);
        } else {
            return ajax_error("数据为空");

        }        
     }
  }

    /**
     * [积分流水显示]
     * 郭杨
     */
    public function integrals(Request $request)
    {
        if ($request->isPost()) {
        $open_id = $request->only(['open_id'])['open_id']; //open_id
        $member_id = db("member")->where("member_openid",$open_id)->value("member_id");
        $data = db("integral")->where("member_id",$member_id)->order('integral_id desc')->select();

        if (!empty($data)) {
            return ajax_success('传输成功', $data);
        } else {
            return ajax_error("数据为空");

        }        
     }
  }

    /**
     * [积分商城提交订单]
     * 郭杨
     */
    public function order_integaral(Request $request){
        if ($request->isPost()) {
            $store_id = $request->only(['uniacid'])['uniacid'];
            $open_id = $request->only("open_id")["open_id"];//open_id
            $address_id = $request->param("address_id");    //address_id
            $order_type =$request->only("order_type")["order_type"];//1为选择直邮，2到店自提，3选择存茶
            // $password = $request->only("passwords")["passwords"]; //输入的密码
            $commodity_id = $request->only("goods_id")["goods_id"];//商品id
            $numbers =$request->only("order_quantity")["order_quantity"];//购买数量

            $user_id =Db::name("member")
                ->where("member_openid",$open_id)
                ->value("member_id");
            if(empty($user_id)){
                return ajax_error("未登录",['status'=>0]);
            }

            // $passwordes =Db::name("member")
            // ->where("member_openid",$open_id)
            // ->value("pay_password");


            $user_information =Db::name("member")->where("member_id",$user_id)->find();
            $sum_integral = $user_information["member_integral_wallet"];//积分余额
            
            $is_address = Db::name('user_address')
                ->where("id",$address_id)
                ->where('user_id', $user_id)
                ->find();
            if (empty($is_address) ) {
                return ajax_error('请填写收货地址',['status'=>0]);
            }else{
                $is_address_status = Db::name('user_address')
                    ->where('user_id', $user_id)
                    ->where('id',$address_id)
                    ->find();
                if (empty($is_address_status) ) {
                    $is_address_status =$is_address;
                }
           
                // if (password_verify($password,$passwordes)){
                //     return ajax_success("支付密码正确",["status"=>1]);
                // }else{
                //     return ajax_error("支付密码错误",["status"=>0]);
                // }

                $harvest_address_city =str_replace(',','',$is_address_status['address_name']);
                $harvest_address =$harvest_address_city.$is_address_status['harvester_real_address']; //收货人地址
                $time=date("Y-m-d",time());
                $v=explode('-',$time);
                $time_second=date("H:i:s",time());
                $vs=explode(':',$time_second);
                //1为选择直邮，2到店自提，3选择存茶
                if($order_type ==1){
                    $parts_order_number ="ZY".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].rand(1000,9999).($user_id+100000); //订单编号
                }else if($order_type ==2){
                    $parts_order_number ="DD".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].rand(1000,9999).($user_id+100000); //订单编号
                }else if($order_type ==3){
                    $parts_order_number ="CC".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].rand(1000,9999).($user_id+100000); //订单编号
                }

                if (!empty($commodity_id)){
                    $goods_data = db('bonus_mall')->where('id',$commodity_id)->find();
                    $create_time = time();//下单时间
                    $normal_time =Db::name("order_setting")->find();//订单设置的时间
                    $normal_future_time = strtotime("+". $normal_time['normal_time']." minute");
                    if (!empty($goods_data)) {
                        $buy_message = NUll;                          
                        $datas['goods_show_image'] = $goods_data['goods_show_image'];//图片
                        $datas["integral"]= $goods_data['integral']; //单个商品所需积分
                        $datas["order_type"] = $order_type;//1为选择直邮，2到店自提，3选择存茶
                        $datas["goods_describe"] = $goods_data["goods_describe"];//卖点
                        $datas["goods_name"] = $goods_data["goods_name"];//名字
                        $datas["order_quantity"] = $numbers;//订单数量
                        $datas["member_id"] = $user_id;//用户id
                        $datas["user_account_name"] = $user_information["member_name"];//用户名
                        $datas["user_phone_number"] = $user_information["member_phone_num"];//用户名手机号
                        $datas["harvester"] = $is_address_status['harvester'];
                        $datas["harvest_phone_num"] = $is_address_status['harvester_phone_num'];
                        $datas["harvester_address"] = $harvest_address;
                        $datas["order_create_time"] = $create_time;
                        $datas["pay_time"] = $create_time;
                        $datas["order_amount"] = $goods_data['integral']*$numbers;//订单积分                             
                        $datas["status"] = 1;
                        $datas["goods_id"] = $commodity_id;
                        $datas["parts_order_number"] = $parts_order_number;//时间+4位随机数+用户id构成订单号
                        $datas["buy_message"] = $buy_message;//买家留言
                        $datas["normal_future_time"] = $normal_future_time;//未来时间
                        $datas["store_id"] = $store_id;//店铺id
                        
                        if($datas["order_amount"]>$sum_integral){
                            return ajax_error("您的积分不足",$datas);
                        } else {
                            $res = Db::name('buyintegral')->insertGetId($datas);
                        }
                        
                        if ($res) {
                            $order_datas = Db::name("buyintegral")
                                ->field("order_amount,goods_name,parts_order_number")
                                ->where('id',$res)
                                ->where("member_id",$user_id)
                                ->find();
                                //插入积分记录
                                $rest = db("member")->where("member_id",$user_id)->setDec('member_integral_wallet',$datas["order_amount"]);//消费积分
                                $volume = db("bonus_mall")->where("id",$commodity_id)->setDec("goods_repertory",$datas["order_quantity"]);//库存减少
                                //销量
                                //库存
                                $many = db("member")->where("member_id",$user_id)->value("member_integral_wallet");//获取所有积分
                                $integral_data = [
                                    "member_id" => $user_id,
                                    "integral_operation" => "-".$datas["order_amount"],//消费积分
                                    "integral_balance" => $many,//积分余额
                                    "integral_type" => -1, //积分类型（1获得，-1消费）
                                    "operation_time" => date("Y-m-d H:i:s"), //操作时间
                                    "integral_remarks" => "购买积分商城商品消费" . $datas["order_amount"] . "积分",
                                ];
                                Db::name("integral")->insert($integral_data);
                                //获取到小程序的版本id
                                $meal_name=db('set_meal_order')->where(['store_id'=>$store_id,'status_type'=>1])->value('goods_name');
                                if($meal_name=='茶进阶版'){
                                    $enter_all_id='3';
                                }elseif($meal_name=='行业版'){
                                    $enter_all_id='2';
                                }else{
                                    $enter_all_id='1';
                                }
                                $order_datas['enter_all_id']=$enter_all_id;
                            return ajax_success('下单成功',$order_datas);
                        }else{
                            return ajax_error('失败',['status'=>0]);
                        }
                    }
                }
                


            }
        }
    }


    /**
     * [积分订单详情页面信息]
     * 郭杨
     */
    public function integrals_detail(Request $request)
    {
        if($request->isPost()) {
            $user_id = $request->only("member_id")["member_id"]; //会员id
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }
            $order_number =$request->only("order_number")["order_number"];//订单编号
            $condition = "`member_id` = " . $user_id .  " and `parts_order_number` = " . $order_number;
            $data = Db::name("buyintegral")
                ->where($condition)
                ->find();
            if (!empty($data)) {              
                $datas = [
                    "buy_message" => $data["buy_message"], //买家留言
                    "create_time" => $data["order_create_time"],//订单创建时间
                    "parts_order_number" => $data["parts_order_number"],//订单编号
                    "pay_time" => $data["pay_time"],//支付时间
                    "harvester" => $data["harvester"],//收货人
                    "harvest_phone_num" => $data["harvest_phone_num"],//收件人电话
                    "harvester_address" => $data["harvester_address"],//收件人地址
                    "order_quantity" => $data["order_quantity"],//商品数量
                    "status" => $data["status"],//状态
                    "all_goods_pays" => $data["order_amount"],//商品总额（商品*数量）
                    "all_order_real_pay" => $data["order_amount"],//订单实际支付积分
                ];

                $rest[] = $datas;
                if (!empty($rest)) {
                    return ajax_success("数据返回成功", $rest);
                } else {
                    return ajax_error("没有数据信息", ["status" => 0]);
                }
            } else {
                return ajax_error("订单信息错误", ["status" => 0]);
            }
        }
  }



    /**
     * [积分订单全部]
     * 郭杨
     */
    public function integaral_list(Request $request)
    {
        if($request->isPost()) {
            $member_id = $request->only("member_id")["member_id"]; //会员id
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }
            $data = Db::name("buyintegral")
                ->where("member_id",$member_id)
                ->order('order_create_time', 'desc')
                ->select();
            if (!empty($data)) {  
                foreach($data as $key => $value){                       
                    $datas[$key]["buy_message"] = $data[$key]["buy_message"]; //买家留言
                    $datas[$key]["create_time"] = $data[$key]["order_create_time"];//订单创建时间
                    $datas[$key]["goods_name"] = $data[$key]["goods_name"];//商品名
                    $datas[$key]["goods_describe"] = $data[$key]["goods_describe"];//商品买点
                    $datas[$key]["goods_show_image"] = $data[$key]["goods_show_image"];//商品图片                    
                    $datas[$key]["integral"] = $data[$key]["integral"];//商品积分                    
                    $datas[$key]["parts_order_number"] = $data[$key]["parts_order_number"];//订单编号
                    $datas[$key]["pay_time"] = $data[$key]["pay_time"];//支付时间
                    $datas[$key]["harvester"] = $data[$key]["harvester"];//收货人
                    $datas[$key]["harvest_phone_num"] = $data[$key]["harvest_phone_num"];//收件人电话
                    $datas[$key]["harvester_address"] = $data[$key]["harvester_address"];//收件人地址
                    $datas[$key]["order_quantity"] = $data[$key]["order_quantity"];//商品数量
                    $datas[$key]["status"] = $data[$key]["status"];//状态
                    $datas[$key]["all_order_real_pay"] = $data[$key]["order_amount"];//订单实际支付积分
                
            }
                if (!empty($datas)) {
                    return ajax_success("数据返回成功", $datas);
                } else {
                    return ajax_error("没有数据信息", ["status" => 0]);
                }
            } else {
                return ajax_error("订单信息错误", ["status" => 0]);
            }
        }
  }


    /**
     * [积分订单待发货]
     * 郭杨
     */
    public function integaral_delivered(Request $request)
    {
        if($request->isPost()) {
            $member_id = $request->only("member_id")["member_id"]; //会员id
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }
            //1已付款，2待发货，3已发货，4待收货，5已收货
            $data = Db::name("buyintegral")
                ->where("member_id",$member_id)
                ->where("status",1)
                ->order('order_create_time', 'desc')
                ->select();
            if (!empty($data)) {  
                foreach($data as $key => $value){         
                    $datas[$key]["buy_message"] = $data[$key]["buy_message"]; //买家留言
                    $datas[$key]["create_time"] = $data[$key]["order_create_time"];//订单创建时间
                    $datas[$key]["goods_name"] = $data[$key]["goods_name"];//商品名
                    $datas[$key]["goods_describe"] = $data[$key]["goods_describe"];//商品买点
                    $datas[$key]["goods_show_image"] = $data[$key]["goods_show_image"];//商品图片                    
                    $datas[$key]["integral"] = $data[$key]["integral"];//商品积分                    
                    $datas[$key]["parts_order_number"] = $data[$key]["parts_order_number"];//订单编号
                    $datas[$key]["pay_time"] = $data[$key]["pay_time"];//支付时间
                    $datas[$key]["harvester"] = $data[$key]["harvester"];//收货人
                    $datas[$key]["harvest_phone_num"] = $data[$key]["harvest_phone_num"];//收件人电话
                    $datas[$key]["harvester_address"] = $data[$key]["harvester_address"];//收件人地址
                    $datas[$key]["order_quantity"] = $data[$key]["order_quantity"];//商品数量
                    $datas[$key]["status"] = $data[$key]["status"];//状态
                    $datas[$key]["all_order_real_pay"] = $data[$key]["order_amount"];//订单实际支付积分              
            }

                if (!empty($datas)) {
                    return ajax_success("数据返回成功", $datas);
                } else {
                    return ajax_error("没有数据信息", ["status" => 0]);
                }
            } else {
                return ajax_error("订单信息错误", ["status" => 0]);
            }
        }
  }


  
    /**
     * [积分订单待收货]
     * 郭杨
     */
    public function integaral_collections(Request $request)
    {
        if($request->isPost()) {
            $member_id = $request->only("member_id")["member_id"]; //会员id
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }
            //1已付款，2待发货，3已发货，4待收货，5已收货
            $data = Db::name("buyintegral")
                ->where("member_id",$member_id)
                ->where("status",3)
                ->order('order_create_time', 'desc')
                ->select();
            if (!empty($data)) {  
                foreach($data as $key => $value){         
                    $datas[$key]["buy_message"] = $data[$key]["buy_message"]; //买家留言
                    $datas[$key]["create_time"] = $data[$key]["order_create_time"];//订单创建时间
                    $datas[$key]["goods_name"] = $data[$key]["goods_name"];//商品名
                    $datas[$key]["goods_describe"] = $data[$key]["goods_describe"];//商品买点
                    $datas[$key]["goods_show_image"] = $data[$key]["goods_show_image"];//商品图片                    
                    $datas[$key]["integral"] = $data[$key]["integral"];//商品积分                    
                    $datas[$key]["parts_order_number"] = $data[$key]["parts_order_number"];//订单编号
                    $datas[$key]["pay_time"] = $data[$key]["pay_time"];//支付时间
                    $datas[$key]["harvester"] = $data[$key]["harvester"];//收货人
                    $datas[$key]["harvest_phone_num"] = $data[$key]["harvest_phone_num"];//收件人电话
                    $datas[$key]["harvester_address"] = $data[$key]["harvester_address"];//收件人地址
                    $datas[$key]["order_quantity"] = $data[$key]["order_quantity"];//商品数量
                    $datas[$key]["status"] = $data[$key]["status"];//状态
                    $datas[$key]["all_order_real_pay"] = $data[$key]["order_amount"];//订单实际支付积分              
            }

                if (!empty($datas)) {
                    return ajax_success("数据返回成功", $datas);
                } else {
                    return ajax_error("没有数据信息", ["status" => 0]);
                }
            } else {
                return ajax_error("订单信息错误", ["status" => 0]);
            }
        }
  }

    /**
     * [限时限购提示]
     * 郭杨
     */
    public function limitations(Request $request)
    {
        if ($request->isPost()) {
            $store_id = $request->only(['uniacid'])['uniacid'];
            $goods_id = $request->only(['goods_id'])['goods_id']; //goods_id
            $member_id = $request->only(['member_id'])['member_id']; //member_id
            $member_grade_name = $request->only(["member_grade_name"])['member_grade_name'];//member_grade_name  
            $time = time();
            
            //判断会员等级
            $limit = db("limited")->where("goods_id",$goods_id)->where("store_id","EQ",$store_id)->find();
    
            if(!empty($limit)){
                $scope = explode(",",$limit["scope"]);
                if(!in_array($member_grade_name,$scope)){
                    return ajax_error("您的会员等级过低,请升级后再购买",["status"=>0]);
                }
                $order = db("order")
                ->where("member_id",$member_id)
                ->where("goods_id",$goods_id)
                ->where("status",2)
                ->order('order_create_time', 'desc')
                ->select();//查询近期定单
                if(!empty($order)){
                    $order_time = $order[0]["order_create_time"];//商品下单时间                
                    $limit_time = $limit["time"];  //限购时间
                    if($limit_time == 1){
                        $date_time = strtotime(date('Y-m-d H:i:s',strtotime("+1month",$order_time)));
                        if($time < $date_time){
                            return ajax_error("您当月已购买过该商品,请下月再来购买",["status"=>0]); 
                        }
                    } else {
                        $date_time = strtotime(date('Y-m-d H:i:s',strtotime("+2month",$order_time)));
                        if($time < $date_time){
                            return ajax_error("您已购买过该商品",["status"=>0]); 
                        }
                    }
                    
                }
                return ajax_success("您符合限时限购条件条件",["status"=>1]);
                } else {
                    return ajax_error("该商品无限时限购条件",["status"=>1]);
                }
            }     
     }


    /**
     * [积分订单确认收货]
     * 郭杨
     */
    public function take_delivery(Request $request)
    {
        if ($request->isPost()) {
            $member_id = $request->only("member_id")["member_id"]; //会员id
            $parts_order_number = $request->only("parts_order_number")["parts_order_number"]; //订单号
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }

            $bool = db("buyintegral")->where("member_id",$member_id)->where("parts_order_number",$parts_order_number)->update(["status"=>5]);
            //1已付款，2待发货，3已发货，4待收货，5已收货
            if($bool){
                return ajax_success('收货成功'); 
            } else {
                return ajax_error("收货失败");
            }    
        }

    }

    /**
     * [限时限购显示]
     * 郭杨
     */
    public function limitations_show(Request $request)
    {
        if ($request->isPost()) {
        $store_id = $request->only(['uniacid'])['uniacid']; 
        $goods_id = $request->only(['goods_id'])['goods_id']; //goods_id
        $limit = db("limited")->where("goods_id",$goods_id)->where("store_id","EQ",$store_id)->find();
        $limit["scope"] = explode(",",$limit["scope"]);

        if(!empty($limit)){
                return ajax_success('传输成功', $limit);
            }else{
                return ajax_error("该商品无限时限购条件",["status"=>0]);
            }
    
        }
    }



    /**
     * [积分订单提醒发货]
     * 郭杨
     */
    public function attention_to(Request $request)
    {
        if($request->isPost()){
            $order_num =$request->only("parts_order_number")["parts_order_number"]; //订单编号
            $timetoday = strtotime(date("Y-m-d",time()));//今天0点的时间点
            $time2 = time() + 3600*24;//今天24点的时间点，两个值之间即为今天一天内的数据
            $time_condition  = "create_time>$timetoday and create_time< $time2";
            $is_notice = Db::name("note_remind")
                ->where("parts_order_number",$order_num)
                ->where($time_condition)
                ->find();
            if(!empty($is_notice)){
                return ajax_success("您已提醒过");
            }
            $data =Db::name("order")
                ->field("id")
                ->where("parts_order_number",$order_num)
                ->select();
            foreach ($data as $k=>$v){
                $information_data =[
                    "information"=>"用户提醒发货",
                    "create_time"=>time(),
                    "option_name"=>"用户",
                    "order_id"=>$v["id"],
                    "parts_order_number"=>$order_num
                ];
                $res =  Db::name("note_remind")->insert($information_data);
            }
            if($res){
                return ajax_success("提醒成功",["status"=>1]);
            }else{
                return ajax_error("请重新操作",["status"=>0]);
            }
        }

    }


     /**
     * [积分记录搜索]
     * 郭杨
     */
    public function integaral_search(Request $request)
    {
        if($request->isPost()){
            $member_id = $request->only("member_id")["member_id"]; //会员id
            $remarks = $request->only("remarks")["remarks"];

            $data = db("integral")
                    ->where("member_id",$member_id)
                    ->where("integral_remarks", "like","%" .$remarks ."%")
                    ->order('integral_id desc')
                    ->select();
                    
            if (!empty($data)) {
                return ajax_success('传输成功', $data);
            } else {
                return ajax_error("数据为空");
    
            } 

        }
    }
  


}