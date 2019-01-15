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
            $time = time();
        
            foreach($coupon as $key => $value){
                $coupon[$key]['scope'] = explode(",",$coupon[$key]['scope']);
                $coupon[$key]['start_timed'] = strtotime($coupon[$key]['start_time']);
                $coupon[$key]['end_timed'] = strtotime($coupon[$key]['end_time']);
                unset($coupon[$key]['start_time']);
                unset($coupon[$key]['end_time']);
                if(in_array($member_grade_name,$coupon[$key]['scope']) && $value['end_timed'] > $time){
                    $data[] = $coupon[$key];
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
            $coupon_id = Db::name("order")->where("member_id",$member_id)->distinct($member_id)->field("coupon_id")->select();
            
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
            $time = time();//当前时间戳
            $member_grade_name = $request->only(['member_grade_name'])['member_grade_name'];
            $open_id = $request->only(['open_id'])['open_id'];
            $coupons = db("coupon")->select();
            foreach($coupons as $key => $value){
                $value['scope'] = explode(",",$value['scope']);
                $value['start_timed'] = strtotime($value['start_time']);
                $value['end_timed'] = strtotime($value['end_time']);
                if(in_array($member_grade_name,$value['scope']) && $value['end_timed'] < $time){
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
            $coupon_id = $request->only(['coupon_id'])['coupon_id'];
            $member_id = $request->only(["open_id"])["open_id"];
            $member_grade_id = db("member")->where("member_openid", $member_id)->value("member_grade_id");
            $goods = db("join")->where("coupon_id",$coupon_id)->field('goods_id,goods_name,goods_show_images,goods_standard,goods_repertory')->select();
            $discount = db("member_grade")->where("member_grade_id", $member_grade_id)->value("member_consumption_discount");
            foreach($goods as $key => $value){
                $goods[$key]["goods_new_money"] = (db("goods")-> where("id",$goods[$key]["goods_id"])->value("goods_new_money,goods_selling")) * $discount;
                if($goods[$key]["goods_standard"] == 1){
                    $goods[$key]["max_price"] = (db("special")->where("goods_id", $goods[$key]["goods_id"])->max("price")) * $discount;
                    $goods[$key]["min_price"] = (db("special")->where("goods_id", $goods[$key]["goods_id"])->min("price"))* $discount;
                }
            }          
            if (!empty($goods)) {
                return ajax_success('传输成功', $goods);
            } else {
                return ajax_error("数据为空");

            }
        }
    }
}