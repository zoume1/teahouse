<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27 0027
 * 购物车
 * Time: 18:57
 */

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;

class  Shopping extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:购物车列表信息返回
     **************************************
     * @param Request $request
     */
    public function shopping_index(Request $request)
    {
        if ($request->isPost()) {
            $open_id = $request->only("open_id")["open_id"];
            $member_id = Db::name("member")->where("member_openid", $open_id)->value("member_id");
            if (empty($member_id)) {
                exit(json_encode(array("status" => 2, "info" => "请登录")));
            }
            $shopping_data = db("shopping")->where("user_id", $member_id)->select();
            if (!empty($shopping_data)) {
                exit(json_encode(array("status" => 1, "info" => "购物车数据返回成功", "data" => $shopping_data)));
            } else {
                exit(json_encode(array("status" => 0, "info" => "购物车未添加商品")));
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:获取商品id 存入购物车
     **************************************
     * @param Request $request
     */
    public function get_goods_id_to_shopping(Request $request){
        if ($request->isPost()){
            $open_id = $request->only("open_id")["open_id"];
            $member_id = Db::name("member")->where("member_openid", $open_id)->find();
            $member_consumption_discount =Db::name("member_grade")
                ->where("member_grade_id", $member_id["member_grade_id"])
                ->find();
            if (empty($member_id)) {
                exit(json_encode(array("status" => 2, "info" => "请登录")));
            }
            //存入购物车
            $goods_id = $request->only(['goods_id'])['goods_id'];//商品id
            $goods_unit = $request->only(['goods_unit'])['goods_unit'];//商品数量
            $goods_standard_id = $request->only(['goods_standard_id'])['goods_standard_id'];//商品通用专用规格id
            $goods_id = intval($goods_id);
            $goods = db("goods")->where("id",$goods_id)->find();
            $shopping_data = db("shopping")
                ->where("user_id",$member_id["member_id"])
                ->where("goods_id", $goods_id)
                ->select();
            foreach ($shopping_data as $key=>$value) {
                if (in_array($goods_standard_id,$value)) {
                    $shopping_num = $value['goods_unit'] + $goods_unit;
                    $shopping_id =$value["id"];
                }
            }
            if(!empty($shopping_num)){
                $bool = Db::name("shopping")
                    ->where("id",$shopping_id)
                    ->where("goods_id", $goods_id)
                    ->where("user_id", $member_id["member_id"])
                    ->where("goods_standard_id",$goods_standard_id)
                    ->update(["goods_unit"=>$shopping_num]);
                if($bool){
                    return ajax_success("成功", $bool);
                }else{
                    return ajax_error("失败",["status"=>0]);
                }
            }
            $data['goods_name'] = $goods['goods_name'];
            $goods_end_money =Db::name("special")
                ->field("price,name,images")
                ->where("id",$goods_standard_id)
                ->where("goods_id",$goods_id)
                ->find();
            $data['money'] =  $goods_end_money["price"] * $member_consumption_discount["member_consumption_discount"];
            $data['goods_images'] =$goods_end_money['images'];//商品图片
            $data['goods_unit'] = $goods_unit;
            $data['user_id'] =  $member_id["member_id"];
            $data['goods_id'] = $goods['id'];
            $data['goods_standard_id'] =$goods_standard_id;
            $data["special_name"] =$goods_end_money["name"];
            $bool = db("shopping")->insert($data);
            exit(json_encode(array("status" => 1, "info" => "加入购物车成功" ,"data"=>$bool)));
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:购物车添加商品数量
     **************************************
     * @param Request $request
     */
    public function shopping_information_add(Request $request){
        if($request->isPost()){
            $open_id = $request->only("open_id")["open_id"];
            $member_id = Db::name("member")->where("member_openid", $open_id)->find();
            if(empty($member_id)){
                return ajax_error("请登录",["status"=>0]);
                exit(json_encode(array("status" => 2, "info" => "请登录")));
            }
            $goods_unit = $request->only(['goods_unit'])['goods_unit'];//商品数量
            $shopping_id = $request->only(['shopping_id'])['shopping_id'];//shopping表中的id
            if(!empty($goods_unit)){
                $shopping_data = Db::name("shopping")
                    ->where("id",$shopping_id)
                    ->where("user_id",$member_id["member_id"])
                    ->find();
                $goods_units =$goods_unit+$shopping_data["goods_unit"];
                $bool = Db::name("shopping")
                    ->where("id",$shopping_id)
                    ->where("user_id",$member_id["member_id"])
                    ->update(["goods_unit"=>$goods_units]);
                if($bool){
                    exit(json_encode(array("status" => 1, "info" => "添加成功","data"=>$bool)));
                }else{
                    exit(json_encode(array("status" => 0, "info" => "添加失败","data"=>["status"=>0])));
                }
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:购物车减少商品数量
     **************************************
     * @param Request $request
     */
    public function shopping_information_del(Request $request){
        if($request->isPost()){
            $open_id = $request->only("open_id")["open_id"];
            $member_id = Db::name("member")->where("member_openid", $open_id)->find();
            if(empty($member_id)){
                return ajax_error("请登录",["status"=>0]);
                exit(json_encode(array("status" => 2, "info" => "请登录")));
            }
            $goods_unit = $request->only(['goods_unit'])['goods_unit'];//商品数量
            $shopping_id = $request->only(['shopping_id'])['shopping_id'];//shopping表中的id
            if(!empty($goods_unit)){
                $shopping_data = Db::name("shopping")
                    ->where("id",$shopping_id)
                    ->where("user_id",$member_id["member_id"])
                    ->find();
                $goods_units =$shopping_data["goods_unit"]-$goods_unit;
                $bool = Db::name("shopping")
                    ->where("id",$shopping_id)
                    ->where("user_id",$member_id["member_id"])
                    ->update(["goods_unit"=>$goods_units]);
                if($bool){
                    exit(json_encode(array("status" => 1, "info" => "删除成功","data"=>$bool)));
                }else{
                    exit(json_encode(array("status" => 0, "info" => "删除失败","data"=>["status"=>0])));
                }
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:购物车删除
     **************************************
     * @param Request $request
     */
    public function  shopping_del(Request $request){
        if($request->isPost()){
            $id =$request->only("shopping_id")["shopping_id"];
            if(is_array($id)){
                $where ='id in('.implode(',',$id).')';
            }else{
                $where ='id='.$id;
            }
            $list =  Db::name('shopping')->where($where)->delete();
            if($list!==false)
            {
                exit(json_encode(array("status" => 1, "info" => "成功删除","data"=>$list)));
            }else{
                exit(json_encode(array("status" => 0, "info" => "删除失败","data"=>["status"=>0])));

            }
        }
    }


}