<?php
/**
 * Created by Vscode.
 * User: Administrator
 * Date: 2019/04/22 0027
 * 购物车
 * Time: 15:57
 */

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;

class  CrowdShopping extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:购物车列表信息返回
     **************************************
     * @param Request $request
     */
    public function crowd_shopping_index(Request $request)
    {
        if ($request->isPost()) {
            $member_id = $request->only("member_id")["member_id"];
            if (empty($member_id)) {
                exit(json_encode(array("status" => 2, "info" => "请登录")));
            }
            $shopping_data = Db::table("tb_crowd_shopping")
                ->field("tb_crowd_shopping.* ,tb_crowd_goods.goods_describe goods_describe")
                ->join("tb_crowd_goods","tb_crowd_shopping.goods_id = tb_crowd_goods.id","left")
                ->where("tb_crowd_shopping.user_id", $member_id)
                ->select();
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
    public function get_crowd_goods_id_to_shopping(Request $request){
        if ($request->isPost()){
            $member = $request->only("member_id")["member_id"];
            $member_id = Db::name("member")->where("member_id", $member)->find();
            $member_consumption_discount = Db::name("member_grade")
                ->where("member_grade_id", $member_id["member_grade_id"])
                ->find();
            if (empty($member)) {
                exit(json_encode(array("status" => 2, "info" => "请登录")));
            }
            //存入购物车
            $goods_id = $request->only(['goods_id'])['goods_id'];//商品id
            $goods_unit = $request->only(['goods_unit'])['goods_unit'];//商品数量
            $goods_id = intval($goods_id);
            $goods = db("crowd_goods")->where("id",$goods_id)->find();
            $shopping_data = db("crowd_shopping")
                ->where("user_id",$member)
                ->where("goods_id", $goods_id)
                ->select();
            if(!empty($shopping_data)){
                foreach ($shopping_data as $key=>$value) {     
                    $shopping_num = $value['goods_unit'] + $goods_unit;
                    $shopping_id =$value["id"];
                    if(!empty($shopping_num)){
                        $bool = Db::name("crowd_shopping")
                            ->where("id",$shopping_id)
                            ->where("goods_id", $goods_id)
                            ->where("user_id", $member)
                            ->update(["goods_unit"=>$shopping_num]);
                        if($bool){
                            return ajax_success("成功", $bool);
                        }else{
                            return ajax_error("失败",["status"=>0]);
                        }
                    }
                }
            }
            $data['goods_name'] = $goods['project_name']; 
            $goods_end_money = Db::name("crowd_special")
                ->field("cost,name,images")
                ->where("goods_id",$goods_id)
                ->find();
            $data['money'] =  $goods_end_money["cost"] * $member_consumption_discount["member_consumption_discount"];
            $data['goods_images'] =$goods_end_money['images'];//商品图片
            $data['goods_unit'] = $goods_unit;
            $data['user_id'] =  $member;
            $data['goods_id'] = $goods['id'];
            $data["special_name"] =$goods_end_money["name"];
            $bool = db("crowd_shopping")->insert($data);
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
    public function crowd_shopping_information_add(Request $request){
        if($request->isPost()){
            $member_id = $request->only("member_id")["member_id"];
            $goods_unit = $request->only(['goods_unit'])['goods_unit'];//商品数量
            $shopping_id = $request->only(['shopping_id'])['shopping_id'];//crowd_shopping表中的id
   
            if(empty($member_id)){
                return ajax_error("请登录",["status"=>0]);
                exit(json_encode(array("status" => 2, "info" => "请登录")));
            }

            if(!empty($goods_unit)){
                $shopping_data = Db::name("crowd_shopping")
                    ->where("id",$shopping_id)
                    ->where("user_id",$member_id)
                    ->find();
                $goods_units =$goods_unit+$shopping_data["goods_unit"];
                $bool = Db::name("crowd_shopping")
                    ->where("id",$shopping_id)
                    ->where("user_id",$member_id)
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
    public function crowd_shopping_information_del(Request $request){
        if($request->isPost()){
            $member_id = $request->only("member_id")["member_id"];
            $goods_unit = $request->only(['goods_unit'])['goods_unit'];//商品数量
            $shopping_id = $request->only(['shopping_id'])['shopping_id'];//crowd_shopping表中的id
   
            if(empty($member_id)){
                return ajax_error("请登录",["status"=>0]);
                exit(json_encode(array("status" => 2, "info" => "请登录")));
            }

            if(!empty($goods_unit)){
                $shopping_data = Db::name("crowd_shopping")
                    ->where("id",$shopping_id)
                    ->where("user_id",$member_id)
                    ->find();
                    $goods_units =$shopping_data["goods_unit"]-$goods_unit;
                $bool = Db::name("crowd_shopping")
                    ->where("id",$shopping_id)
                    ->where("user_id",$member_id)
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
     * Notes:购物车删除
     **************************************
     * @param Request $request
     */
    public function  crowd_shopping_del(Request $request){
        if($request->isPost()){
            $id = $request->only("shopping_id")["shopping_id"];
            if(is_array($id)){
                $where ='id in('.implode(',',$id).')';
            }else{
                $where ='id='.$id;
            }
            $list =  Db::name('crowd_shopping')->where($where)->delete();
            if($list!==false)
            {
                exit(json_encode(array("status" => 1, "info" => "成功删除","data"=>$list)));
            }else{
                exit(json_encode(array("status" => 0, "info" => "删除失败","data"=>["status"=>0])));
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:购物车数量返回
     **************************************
     * @param Request $request
     */
    public function crowd_shopping_numbers(Request $request){
        if($request->isPost()){
            $user_id = $request->only(["member_id"])["member_id"];
            $number =Db::name("crowd_shopping")->where("user_id",$user_id)->sum("goods_unit");
            if($number > 0){
                return ajax_success("购物车数量返回成功",$number);
            }else{
                $number = 0;
                return ajax_error("购物车里面没有商品",$number);
            }
        }
    }



}