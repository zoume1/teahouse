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
            foreach($coupon_id as $key => $value){
                foreach($value as $ke => $va){
                    $rest[] = $va;
                }
            }                        
            //未使用(包含已使用)
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
            
            foreach($coupon_id as $key => $value){
                $rest[] = Db::name("coupon")->where("id",$value["coupon_id"])->field('id,use_price,scope,start_time,end_time,money,suit,label')->find();
            }
            foreach($rest as $k => $v){
                $v['scope'] = explode(",",$v['scope']);
            }

            if (!empty($rest)) {
                return ajax_success('传输成功', $rest);
            } else {
                return ajax_error("数据为空");

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
            $time = strtotime(date("Y-m-d",strtotime("-1 day")));//当前时间戳
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
            $goods_id = db("join")->where("coupon_id",$coupon_id)->field('goods_id')->select();
            $discount = db("member_grade")->where("member_grade_id", $member_grade_id)->value("member_consumption_discount");

            if(!empty($goods_id))
            {
                foreach($goods_id as $key=>$value)
                {
                    $goods[] = db("goods")->where("id",$value["goods_id"])->where("status",1)->find();                   
                }
                foreach ($goods as $k => $v)
                {
                    if(!empty($v)){
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
                } else {
                    unset($v);
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




}