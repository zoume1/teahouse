<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27 0027
 * 订单
 * Time: 15:20
 */

namespace  app\index\controller;

use think\Controller;
use think\Request;
use think\Db;

class  Order extends  Controller
{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:立即购买过去购物清单数据返回
     **************************************
     */
    public function order_return(Request $request)
    {
        if ($request->isPost()) {
            $open_id =$request->only("open_id")["open_id"];
            $member_grade_id =Db::name("member")->where("member_openid",$open_id)->find();
            $member_consumption_discount =Db::name("member_grade")
                ->where("member_grade_id",$member_grade_id["member_grade_id"])
                ->find();
            $goods_id = $request->only("goods_id")["goods_id"];
            $special_id = $request->only("guige")["guige"];
            $number = $request->only("num")["num"];
            if (empty($goods_id)) {
                return ajax_error("商品信息有误，请返回重新提交", ["status" => 0]);
            }
            foreach ($goods_id as  $key=>$value){
                $goods_data =null;
                $goods_data = Db::name("goods")->where("id", $value)->find();

                //判断是为专用还是通用
                //专用规格
                if ($goods_data["goods_standard"] == 0) {
                    $data[$key]["goods_info"] = $goods_data[$key];
                    $data[$key]["grade_price"] =$member_consumption_discount["member_consumption_discount"] * $goods_data[$key]["goods_new_money"];
                    $data[$key]["special_info"] = null;
                    $data[$key]["number"] =$number[$key];
                    $data[$key]["user_grade_image"] =$member_consumption_discount["member_grade_img"];
                    } else{
                    $data[$key]["goods_info"] = $goods_data;
                    $info = Db::name("special")
                        ->where("id", $special_id[$key])
                        ->find();
                    $data[$key]["special_info"] =$info;
                    $data[$key]["grade_price"] =$member_consumption_discount["member_consumption_discount"]* $info["price"];
                    $data[$key]["number"] =$number[$key];
                    $data[$key]["user_grade_image"] =$member_consumption_discount["member_grade_img"];
                }

//                if (isset($special_id[$key])) {
//                    if ($goods_data["goods_standard"] != 0) {
//                        $data[$key]["goods_info"] = $goods_data;
//                        $info = Db::name("special")
//                            ->where("id", $special_id[$key])
//                            ->find();
//                        $data[$key]["special_info"] =$info;
//                        $data[$key]["grade_price"] =$member_consumption_discount["member_consumption_discount"]* $info["price"];
//                        $data[$key]["number"] =$number[$key];
//                        $data[$key]["user_grade_image"] =$member_consumption_discount["member_grade_img"];
//                    }
//                } else {
//                    //通用规格
//                    if ($goods_data["goods_standard"] == 0) {
//                        $data[$key]["goods_info"] = $goods_data[$key];
//                        $data[$key]["grade_price"] =$member_consumption_discount["member_consumption_discount"] * $goods_data[$key]["goods_new_money"];
//                        $data[$key]["special_info"] = null;
//                        $data[$key]["number"] =$number[$key];
//                        $data[$key]["user_grade_image"] =$member_consumption_discount["member_grade_img"];
//                    }
//                }
            }
            if(!empty($data)){
                return ajax_success("数据返回",$data);
            }else{
                return ajax_error("没有数据",["status"=>0]);
            }

        }
    }


}