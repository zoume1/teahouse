<?php
namespace app\index\controller;
use think\Controller;
use think\Request;

class Commodity extends Controller
{

    /**
     * 商品分类 
     * GY
     */
    public function commodity_index(Request $request)
    {
        if($request->isPost()) {            
            $goods_type = db("wares")->where("status", 1)->select();
            $goods_type = _tree_sort(recursionArr($goods_type), 'sort_number');
            foreach($goods_type as $key => $value)
            {
                $goods_type[$key]['child'] = db("goods")->where("pid",$goods_type[$key]['id'])->where("label",1)->select();
 
            }
            return ajax_success("获取成功",array("goods_type"=>$goods_type));
        }
        
    }





    /**
     * 商品首页推荐
     * GY
     */
    public function commodity_recommend(Request $request)
    {

        if ($request->isPost()) {
            $member_id = $request->only(["open_id"])["open_id"];
            $member_grade_id = db("member")->where("member_openid", $member_id)->value("member_grade_id");
            $member_grade_id = db("member")->where("member_openid", $member_id)->value("member_grade_id");
            $discount = db("member_grade")->where("member_grade_id", $member_grade_id)->value("member_consumption_discount");
            $goods = db("goods")->where("status",1)->select();


            $status = "huiyuan";

            foreach ($goods as $k => $v) //所有商品
            {
                if($goods[$k]["goods_standard"] == 1){
                    $standard[$k] = db("special")->where("goods_id", $goods[$k]['id'])->select();
                    $max[$k] = db("special")->where("goods_id", $goods[$k]['id'])-> max("price") * $discount;//最高价格
                    $min[$k] = db("special")->where("goods_id", $goods[$k]['id'])-> min("price") * $discount;//最低价格
                    $line[$k] = db("special")->where("goods_id", $goods[$k]['id'])-> min("line");//最低价格
                    $goods[$k]["goods_standard"] = $standard[$k];
                    $goods[$k]["goods_show_images"] = explode(",",$goods[$k]["goods_show_images"]);
                    $goods[$k]["max_price"] = $max[$k];
                    $goods[$k]["min_price"] = $min[$k];
                    $goods[$k]["line"] = $line[$k];
                    $limite[$k] = db("limited")->where("goods_id", $goods[$k]['id'])->field("scope")->find();
                    $goods[$k]["scope"] = explode(",",$limite[$k]["scope"]);
                    if(is_null($goods[$k]["scope"])){
                    if(in_array($status,$goods[$k]["scope"])){
                         unset($goods[$k]);
                    }
                }
                } else {
                    $goods[$k]["goods_new_money"] = $goods[$k]["goods_new_money"] * $discount;
                    $goods[$k]["goods_show_images"] = explode(",",$goods[$k]["goods_show_images"]);
                }
                
            }

            $status = "huiyuan";

            // foreach ($goods as $k => $v) //所有商品
            // {
            //     if(!empty($goods[$k]["scope"])){
            //         if(!in_array($status,$goods[$k]["scope"])){
            //             unset($goods[$k]);
            //         }

            //     }
            // }



            if (!empty($goods) && !empty($member_id)) {
                return ajax_success("获取成功", $goods);
            } else {
                return ajax_error("获取失败");
            }
        }

    }




    /**
     * 商品列表
     * GY
     */
    public function commodity_list(Request $request)
    {

        if($request->isPost()){
            $goods_pid = $request->only(["id"])["id"];
            $goods = db("goods")->where("pid",$goods_pid)->select();

            foreach ($goods as $k => $v)
            {
                if($v["label"] == 1 ){
                    $goods_data[] = $v;
                    $goods_data[$k]["goods_show_images"] = (explode(",", $goods[$k]["goods_show_images"])[0]);
                }
            }
            if(!empty($goods_data) && !empty($goods_pid)){
                return ajax_success("获取成功",$goods_data);
            }else{
                return ajax_error("获取失败");
            }
        }

    }



    /**
     * 商品详情
     * GY
     */
    public function commodity_detail(Request $request)
    {
        if ($request->isPost()) {
            $goods_id = $request->only(["id"])["id"];
            $member_id = $request->only(["open_id"])["open_id"];
            $member_grade_id = db("member")->where("member_openid", $member_id)->value("member_grade_id");
            $discount = db("member_grade")->where("member_grade_id", $member_grade_id)->value("member_consumption_discount");
            $goods = db("goods")->where("id", $goods_id)->where("label",1)->select();
            $goods_standard = db("special")->where("goods_id", $goods_id)->select();
            $max_price = db("special")->where("goods_id", $goods_id)->max("price");
            $min_price = db("special")->where("goods_id", $goods_id)->min("price");
            $min_line = db("special")->where("goods_id", $goods_id)->min("line");
            $max_prices = $max_price * $discount;
            $min_prices = $min_price * $discount;

            foreach ($goods_standard as $key => $value) {
                $goods_standard[$key]["price"] = $goods_standard[$key]["price"] * $discount;
            }

            if ($goods[0]["goods_standard"] == 1) {
                $goods[0]["goods_standard"] = $goods_standard;
                $goods[0]["goods_show_images"] = (explode(",", $goods[0]["goods_show_images"]));
                $goods[0]["max_price"] = $max_prices;
                $goods[0]["min_price"] = $min_prices;
                $goods[0]["min_line"] = $min_line;

            } else {
                $goods[0]["goods_new_money"] = $goods[0]["goods_new_money"] * $discount;
                $goods[0]["goods_show_images"] = (explode(",", $goods[0]["goods_show_images"]));
            }
            if (!empty($goods) && !empty($goods_id)) {
                return ajax_success("获取成功", $goods);
            } else {
                return ajax_error("获取失败");
            }
        }

    }
}
