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
            $member_grade_name = $request->only(['member_grade_name'])['member_grade_name'];
            $open_id = $request->only(['open_id'])['open_id'];
            $coupon = Db::name("coupon")->field('id,use_price,scope,start_time,end_time,money,suit,label')->select();
            $time = strtotime(date("Y-m-d",strtotime("-1 day")));

            //已使用
            $member_id = Db::name("member")->where("member_openid",$open_id)->value('member_id');
            $coupon_id = Db::name("order")->where("member_id",$member_id)
                        ->where("coupon_id",'<>',0)
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
            $member_grade_name = $request->only(['member_grade_name'])['member_grade_name'];
            $open_id = $request->only(['open_id'])['open_id'];
            $member_id = Db::name("member")->where("member_openid",$open_id)->value('member_id');
            $coupon_id = Db::name("order")->where("member_id",$member_id)
                            ->where("coupon_id",'<>',0)
                            ->distinct($member_id)
                            ->field("coupon_id")
                            ->select();
            if(count($coupon_id) > 0){
                foreach($coupon_id as $key => $value){
                    $rest[] = Db::name("coupon")->where("id",$value["coupon_id"])->field('id,use_price,scope,start_time,end_time,money,suit,label')->find();
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
            $time = strtotime(date("Y-m-d",strtotime("-1 day")));//当前时间戳减一天
            $member_grade_name = $request->only(['member_grade_name'])['member_grade_name'];
            $open_id = $request->only(['open_id'])['open_id'];
            $coupons = db("coupon")->select();
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
                    $goods[] = db("goods")->where("id",$value["goods_id"])->find(); //该商品是否上架                  
                }
                foreach ($goods as $k => $v)
                {
                    if($goods[$k]["goods_standard"] == 1){
                        $standard[$k] = db("special")->where("goods_id", $goods[$k]['id'])->select();
                        $max[$k] = db("special")->where("goods_id", $goods[$k]['id'])-> max("price") * $discount;//最高价格
                        $min[$k] = db("special")->where("goods_id", $goods[$k]['id'])-> min("price") * $discount;//最低价格
                        $goods[$k]["goods_standard"] = $standard[$k];
                        $goods[$k]["max_price"] = $max[$k];
                        $goods[$k]["min_price"] = $min[$k];
                    } else {
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
     */
    public function coupon_appropriated(Request $request)
    {
        if($request->isPost()){
            $time = strtotime(date("Y-m-d",strtotime("-1 day")));//当前时间戳减一天
            $datas = $request->param(); //包含goods_id and  open_id
            $goods_id = array_unique($datas['goods_id']);
            $open_id = $datas['open_id'];
            $money = $datas['money'];
            $member_grade_name = $datas['member_grade_name'];

            // $goods = [122,120,121];
            // $goods_id = array_unique($goods);
            // $open_id = 'o_lMv5dwxVdyYvafw03wELn6YXxw';
            // $money = 300;
            // $member_grade_name = '普通会员';

            $coupons = Db::name("coupon")->where("use_price","<=",$money)->field('id,use_price,scope,start_time,end_time,money,suit,label')->select();
            $member_id = Db::name("member")->where("member_openid",$open_id)->value('member_id');
            $coupon_id = Db::name("order")->where("member_id",$member_id)
                        ->where("coupon_id",'<>',0)
                        ->distinct($member_id)
                        ->field("coupon_id")
                        ->select();
        
            if(count($coupon_id)>0){
                foreach($coupon_id as $key => $value){
                    foreach($value as $ke => $va){
                        $rest[] = $va;
                    }
                }

                foreach($coupons as $keyl => $valuel){
                    if((!in_array($valuel['id'],$rest)) && !empty($valuel) ){  //判断优惠券是否已被使用
                    $valuel['scope'] = explode(",",$valuel['scope']);
                    $valuel['start_time'] = strtotime($valuel['start_time']);
                    $valuel['end_time'] = strtotime($valuel['end_time']);
                    if(in_array($member_grade_name,$valuel['scope']) && $valuel['end_time'] > $time){ //判断是否在适用范围和是否过期
                        $data[] = $valuel;
                    } else {
                        $data[] = null;
                    }
                } else {
                    $data[] = null;
                }
            }
        
        } else { //如果没有使用过
                foreach($coupons as $key => $values){
                    $values['scope'] = explode(",",$values['scope']);
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
                return ajax_error("没有适用优惠券"); 
            }   
        }
    }

    /**
     * [积分商品显示]
     * 郭杨
     */
    public function bonus_index()
    {
        $bonus = db("bonus_mall")->where("status",1)->order('id desc')->select();          
        if (!empty($bonus)) {
            return ajax_success('传输成功', $bonus);
        } else {
            return ajax_error("数据为空");

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
        $data = db("integral")->where("member_id",$member_id)->select();

        if (!empty($data)) {
            return ajax_success('传输成功', $data);
        } else {
            return ajax_error("数据为空");

        }        
     }
  }

    /**
     * [积分商城订单显示]
     * 郭杨
     */
    public function order_integaral(Request $request){
        if ($request->isPost()) {
            $open_id = $request->only("open_id")["open_id"];//open_id
            $address_id = $request->param("address_id");//address_id
            $order_type =$request->only("order_type")["order_type"];//1为选择直邮，2到店自提，3选择存茶
            $user_id =Db::name("member")
                ->where("member_openid",$open_id)
                ->value("member_id");
            if(empty($user_id)){
                return ajax_error("未登录",['status'=>0]);
            }
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
                $commodity_id = $request->only("goods_id")["goods_id"];//商品id
                $numbers =$request->only("order_quantity")["order_quantity"];//购买数量

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
                foreach ($commodity_id as $keys=>$values){
                    if (!empty($commodity_id)){
                        $goods_data = db('bonus_mall')->where('id',$values)->find();
                        $create_time = time();//下单时间
                        $normal_time =Db::name("order_setting")->find();//订单设置的时间
                        $normal_future_time = strtotime("+". $normal_time['normal_time']." minute");
                        if (!empty($goods_data)) {
                            $buy_message = NUll;                          
                            $datas['goods_show_image'] = $goods_data['goods_show_image'];//图片
                            $datas["integral"]=$goods_data['integral']; //单个商品所需积分
                            $datas["order_type"] =$order_type;//1为选择直邮，2到店自提，3选择存茶
                            $datas["goods_describe"] =$goods_data["goods_describe"];//卖点
                            $datas["goods_name"] =$goods_data["goods_name"];//名字
                            $datas["order_quantity"] =$numbers[$keys];//订单数量
                            $datas["member_id"] =$user_id;//用户id
                            $datas["user_account_name"] =$user_information["member_name"];//用户名
                            $datas["user_phone_number"] =$user_information["member_phone_num"];//用户名手机号
                            $datas["harvester"] =$is_address_status['harvester'];
                            $datas["harvest_phone_num"] =$is_address_status['harvester_phone_num'];
                            $datas["harvester_address"] =$harvest_address;
                            $datas["order_create_time"] =$create_time;
                            $datas["order_amount"] =$datas["integral"]*$numbers[$keys];//订单积分
                            $datas["status"] =1;
                            $datas["goods_id"] =$values;
                            $datas["parts_order_number"] =$parts_order_number;//时间+4位随机数+用户id构成订单号
                            $datas["buy_message"] =$buy_message;//买家留言
                            $datas["normal_future_time"] = $normal_future_time;//未来时间
                            
                            if($datas["order_amount"]>$sum_integral){
                                return ajax_error("您的积分不足");
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
                                    $many = db("member")->where("member_id",$user_id)->value("member_integral_wallet");//获取所有积分
                                    $integral_data = [
                                        "member_id" => $user_id,
                                        "integral_operation" => $datas["order_amount"],//消费积分
                                        "integral_balance" => $many,//积分余额
                                        "integral_type" => -1, //积分类型（1获得，-1消费）
                                        "operation_time" => date("Y-m-d H:i:s"), //操作时间
                                        "integral_remarks" => "购买积分商城商品消费" . $datas["order_amount"] . "积分",
                                    ];
                                    Db::name("integral")->insert($integral_data);
                                    
                                return ajax_success('下单成功',$order_datas);
                            }else{
                                return ajax_error('失败',['status'=>0]);
                            }
                        }
                    }
                }


            }
        }
    }


}