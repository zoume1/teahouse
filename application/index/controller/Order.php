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
                    $data[$key]["goods_info"] = $goods_data;
                    $data[$key]["grade_price"] =$member_consumption_discount["member_consumption_discount"] * $goods_data["goods_new_money"];
                    $data[$key]["special_info"] = null;
                    $data[$key]["number"] =$number[$key];
                    $data[$key]["user_grade_image"] =$member_consumption_discount["member_grade_img"];
                    } else{
                    $data[$key]["goods_info"] = $goods_data;
                    if($special_id[$key] != 0){
                        $info = Db::name("special")
                            ->where("id", $special_id[$key])
                            ->find();
                        $data[$key]["special_info"] =$info;
                        $data[$key]["grade_price"] =$member_consumption_discount["member_consumption_discount"]* $info["price"];
                    }else{
                        $data[$key]["goods_info"] = $goods_data;
                        $data[$key]["grade_price"] =$member_consumption_discount["member_consumption_discount"] * $goods_data["goods_new_money"];
                    }
                    $data[$key]["number"] =$number[$key];
                    $data[$key]["user_grade_image"] =$member_consumption_discount["member_grade_img"];
                }
            }
            if(!empty($data)){
                return ajax_success("数据返回",$data);
            }else{
                return ajax_error("没有数据",["status"=>0]);
            }

        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:提交订单
     **************************************
     * @param Request $request
     */
    public function order_place(Request $request){
        if ($request->isPost()) {
            $open_id =$request->only("open_id")["open_id"];
            $address_id =$request->only("address_id")["address_id"];
            $user_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
            if(empty($user_id)){
                return ajax_error("未登录",['status'=>0]);
            }
            $member_grade_id =Db::name("member")->where("member_id",$user_id)->find();
            $member_consumption_discount =Db::name("member_grade")
                ->where("member_grade_id",$member_grade_id["member_grade_id"])
                ->find();
            $user_information =Db::name("member")->where("member_id",$user_id)->find();
            $is_address = Db::name('user_address')->where("status",1)->where('user_id', $user_id)->find();
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
                $all_money =$request->only("all_money")["all_money"];//总价钱
                $goods_standard_id =$request->only("guige")["guige"];//规格id
                $numbers =$request->only("num")["num"];

                $harvest_address_city =str_replace(',','',$is_address_status['address_name']);
                $harvest_address =$harvest_address_city.$is_address_status['harvester_real_address']; //收货人地址
                $time=date("Y-m-d",time());
                $v=explode('-',$time);
                $time_second=date("H:i:s",time());
                $vs=explode(':',$time_second);
                $parts_order_number =$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].rand(1000,9999).($user_id+100000); //订单编号
                foreach ($commodity_id as $keys=>$values){
                    if (!empty($commodity_id)){
                        $goods_data = Db::name('goods')->where('id',$values)->find();
                        $create_time = time();//下单时间
                        $normal_time =Db::name("order_setting")->find();//订单设置的时间
                        $normal_future_time =strtotime("+". $normal_time['normal_time']." minute");
                        if (!empty($goods_data)) {
//                        if(!empty($data["buy_message"])){
//                            $buy_message =$data["buy_message"];
//                        }else{
                            $buy_message = NUll;
//                        }
                            //判断是通用
                            if($goods_data["goods_standard"]==0){
                                $datas['goods_image'] = $goods_data['goods_show_image'];//图片
                                $datas["goods_money"]=$goods_data['goods_new_money']* $member_consumption_discount["member_consumption_discount"];//商品价钱
                            }else{
                                //图片
                                $special_data =Db::name("special")
                                    ->where("id",$goods_standard_id[$keys])
                                    ->find();
                                $datas['goods_image'] = $special_data['images'];//图片
                                $datas["goods_money"]= $special_data['price'] * $member_consumption_discount["member_consumption_discount"];//商品价钱
                                $datas['goods_standard'] = $special_data["name"]; //商品规格
                            }
                            $datas["goods_describe"] =$goods_data["goods_describe"];//卖点
                            $datas["parts_goods_name"] =$goods_data["goods_name"];//名字
                            $datas["order_quantity"] =$numbers[$keys];//订单数量
                            $datas["member_id"] =$user_id;//用户id
                            $datas["user_account_name"] =$goods_data["goods_describe"];//卖点
                            $datas["goods_describe"] =$user_information["member_name"];//用户名
                            $datas["user_phone_number"] =$user_information["member_phone_num"];//用户名手机号
                            $datas["harvester"] =$is_address_status['harvester'];
                            $datas["harvest_phone_num"] =$is_address_status['harvester_phone_num'];
                            $datas["harvester_address"] =$harvest_address;
                            $datas["order_create_time"] =$create_time;
                            $datas["order_amount"] =$datas["goods_money"]*$numbers[$keys];//订单金额
                            $datas["order_real_pay"] =$all_money;//订单实际支付的金额(即优惠券抵扣之后的价钱）
                            $datas["status"] =1;
                            $datas["goods_id"] =$values;
                            $datas["parts_order_number"] =$parts_order_number;//时间+4位随机数+用户id构成订单号
                            $datas["buy_message"] =$buy_message;//买家留言
                            $datas["normal_future_time"] =$normal_future_time;//未来时间
                            $datas["special_id"] =$goods_standard_id[$keys];//规格id
                            $res = Db::name('order')->insertGetId($datas);
                            if ($res) {
                                $order_datas =Db::name("order")
                                    ->field("order_real_pay,parts_goods_name,parts_order_number")
                                    ->where('id',$res)
                                    ->where("member_id",$user_id)
                                    ->find();
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


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:购物车提交订单
     **************************************
     * @param Request $request
     */
    public function order_place_by_shopping(Request $request){
        if ($request->isPost()) {
            $shopping_id =$request->only("shopping_id")["shopping_id"];
            $open_id =$request->only("open_id")["open_id"];
            $address_id =$request->only("address_id")["address_id"];
            $user_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
            if(empty($user_id)){
                return ajax_error("未登录",['status'=>0]);
            }
            $member_grade_id =Db::name("member")->where("member_id",$user_id)->find();
            $member_consumption_discount =Db::name("member_grade")
                ->where("member_grade_id",$member_grade_id["member_grade_id"])
                ->find();
            $user_information =Db::name("member")->where("member_id",$user_id)->find();
            $is_address = Db::name('user_address')->where("status",1)->where('user_id', $user_id)->find();
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
                $all_money =$request->only("all_money")["all_money"];//总价钱
                $goods_standard_id =$request->only("guige")["guige"];//规格id
                $numbers =$request->only("num")["num"];

                $harvest_address_city =str_replace(',','',$is_address_status['address_name']);
                $harvest_address =$harvest_address_city.$is_address_status['harvester_real_address']; //收货人地址
                $time=date("Y-m-d",time());
                $v=explode('-',$time);
                $time_second=date("H:i:s",time());
                $vs=explode(':',$time_second);
                $parts_order_number =$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].rand(1000,9999).($user_id+100000); //订单编号
                foreach ($commodity_id as $keys=>$values){
                    if (!empty($commodity_id)){
                        $goods_data = Db::name('goods')->where('id',$values)->find();
                        $create_time = time();//下单时间
                        $normal_time =Db::name("order_setting")->find();//订单设置的时间
                        $normal_future_time =strtotime("+". $normal_time['normal_time']." minute");
                        if (!empty($goods_data)) {
//                        if(!empty($data["buy_message"])){
//                            $buy_message =$data["buy_message"];
//                        }else{
                            $buy_message = NUll;
//                        }
                            //判断是通用
                            if($goods_data["goods_standard"]==0){
                                $datas['goods_image'] = $goods_data['goods_show_image'];//图片
                                $datas["goods_money"]=$goods_data['goods_new_money']* $member_consumption_discount["member_consumption_discount"];//商品价钱
                            }else{
                                //图片
                                $special_data =Db::name("special")
                                    ->where("id",$goods_standard_id[$keys])
                                    ->find();
                                $datas['goods_image'] = $special_data['images'];//图片
                                $datas["goods_money"]= $special_data['price'] * $member_consumption_discount["member_consumption_discount"];//商品价钱
                                $datas['goods_standard'] = $special_data["name"]; //商品规格
                            }
                            $datas["goods_describe"] =$goods_data["goods_describe"];//卖点
                            $datas["parts_goods_name"] =$goods_data["goods_name"];//名字
                            $datas["order_quantity"] =$numbers[$keys];//订单数量
                            $datas["member_id"] =$user_id;//用户id
                            $datas["user_account_name"] =$goods_data["goods_describe"];//卖点
                            $datas["goods_describe"] =$user_information["member_name"];//用户名
                            $datas["user_phone_number"] =$user_information["member_phone_num"];//用户名手机号
                            $datas["harvester"] =$is_address_status['harvester'];
                            $datas["harvest_phone_num"] =$is_address_status['harvester_phone_num'];
                            $datas["harvester_address"] =$harvest_address;
                            $datas["order_create_time"] =$create_time;
                            $datas["order_amount"] =$datas["goods_money"]*$numbers[$keys];//订单金额
                            $datas["order_real_pay"] =$all_money;//订单实际支付的金额(即优惠券抵扣之后的价钱）
                            $datas["status"] =1;
                            $datas["goods_id"] =$values;
                            $datas["parts_order_number"] =$parts_order_number;//时间+4位随机数+用户id构成订单号
                            $datas["buy_message"] =$buy_message;//买家留言
                            $datas["normal_future_time"] =$normal_future_time;//未来时间
                            $datas["special_id"] =$goods_standard_id[$keys];//规格id
                            $res = Db::name('order')->insertGetId($datas);
                            if ($res) {
                                $order_datas =Db::name("order")
                                    ->field("order_real_pay,parts_goods_name,parts_order_number")
                                    ->where('id',$res)
                                    ->where("member_id",$user_id)
                                    ->find();
                                //清空购物车数据
                                if(is_array($shopping_id)){
                                    $where ='id in('.implode(',',$shopping_id).')';
                                }else{
                                    $where ='id='.$shopping_id;
                                }
                                $list =  Db::name('shopping')->where($where)->delete();
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