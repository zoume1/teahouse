<?php

/**
 * Created by Vscode.
 * User: admin
 * Date: 2019/4/14
 * Time: 15:21
 */
namespace app\index\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;


class Crowdfinancing extends Controller
{
    /**
     * [立即购买过去清单数据返回]
     * 郭杨
     */
    public function crowd_order_return(Request $request)
    {
        if($request->isPost()){
            $member_id = $request->only('member_id')['member_id'];
            $member_grade_id = db('member')->where('member_id',$member_id)->value('member_grade_id');
            $member_consumption_discount =Db::name('member_grade')  //会员折扣
            ->where('member_grade_id',$member_grade_id)
            ->find();
            $goods_id = $request->only('goods_id')['goods_id'];
            $number = $request->only('num')['num'];
            
            if(empty($goods_id)){
                return ajax_error('商品信息有误,请返回重新提交',['status'=>0]);
            }
            foreach($goods_id as $key => $value){
                $goods_data = Db::name("crowd_goods")->where("id", $goods_id[$key])->find();
                $info = Db::name("crowd_special")
                ->where("goods_id", $goods_id[$key])
                ->find();
                $data[$key]["goods_info"] = $goods_data;
                $data[$key]["special_info"] = $info;
                $data[$key]["grade_price"] = $member_consumption_discount["member_consumption_discount"] * $info["price"];
                $data[$key]["unit"] = $info['offer'];
                $data[$key]["number"] = $number[$key];
                $data[$key]["user_grade_image"] =$member_consumption_discount["member_grade_img"];
            }
            if(!empty($data)){
                return ajax_success("数据返回",$data);
            } else {
                return ajax_error("没有数据",["status"=>0]);
            }
        }
    }


    /**
     **************郭杨*******************
     * @param Request $request
     * Notes:提交订单
     **************************************
     * @param Request $request
     */
    public function crowd_order_place(Request $request){
        if ($request->isPost()){
            $user_id = $request->only("member_id")["member_id"];//member_id
            $address_id = $request->param("address_id");//address_id
            $coupon_id = $request->only("coupon_id")["coupon_id"]; //添加使用优惠券id
            $order_type = $request->only("order_type")["order_type"];//1为选择直邮，2到店自提，3选择存茶
            $commodity_id = $request->only("goods_id")["goods_id"];//商品id
            $all_money = $request->only("order_amount")["order_amount"];//总价钱
            $goods_standard_id = $request->only("goods_standard_id")["goods_standard_id"];//规格id
            $numbers = $request->only("order_quantity")["order_quantity"];//商品对应数量
            $unit = $request->only("unit")["unit"];//商品单位
            $receipt_status = $request->only("receipt_status")["receipt_status"];//是否开发票
            $receipt_id = $request->only("receipt_id")["receipt_id"];//发票id
            $receipt_price = $request->only("receipt_price")["receipt_price"];//发票金额
            
            if(empty($user_id)){
                return ajax_error("未登录",['status'=>0]);
            }
            $user_information =Db::name("member")->where("member_id",$user_id)->find();
            $member_consumption_discount = Db::name("member_grade")
            ->where("member_grade_id",$user_information["member_grade_id"])
            ->find();
            $member_grade_name = $user_information['member_grade_name']; //会员等级
            foreach ($commodity_id as $keys=>$values){
                $goods_data = Db::name('goods')->where('id',$values)->find();
                $create_time = time();//下单时间
                $normal_time =Db::name("order_setting")->find();//订单设置的时间
                if(!empty($normal_time)){
                    $normal_future_time = strtotime("+". $normal_time['normal_time']." minute");
                } else {
                    $normal_future_time = null;
                }
                //图片
                $special_data =Db::name("special")
                    ->where("id",$goods_standard_id[$keys])
                    ->find();
                $datas['goods_image'] = $special_data['images'];   //图片
                $datas["goods_money"]= $special_data['price'] * $member_consumption_discount["member_consumption_discount"];//商品价钱
                $datas['goods_standard'] = $special_data["name"]; //商品规格  
                $data['unit'] = explode(",",$special_data['unit']);
                $data['num'] = explode(",",$special_data['num']);

                
                if($order_type != 3){
                    if($order_type == 1){
                            $parts_order_number ="ZY".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].($user_id+1001); //订单编号 
                            $is_address = Db::name('user_address')
                            ->where("id",$address_id)
                            ->where('user_id', $user_id)
                            ->find();
                            if (empty($is_address) ) {
                                return ajax_error('请填写收货地址',['status'=>0]);
                            } else {
                                $is_address_status = Db::name('user_address')
                                    ->where('user_id', $user_id)
                                    ->where('id',$address_id)
                                    ->find();
                                    $harvest_address_city = str_replace(',','',$is_address_status['address_name']);
                                    $harvest_address = $harvest_address_city.$is_address_status['harvester_real_address']; //收货人地址
                                    $harvester = $is_address_status['harvester'];
                                    $harvester_phone_num = $is_address_status['harvester_phone_num'];
                            } 
                        }

                    if ($order_type == 2) {
                        $parts_order_number ="ZT".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].($user_id+1001); //订单编号
                        $is_address_status = Db::name('extract_address')
                        ->where('id',$address_id)
                        ->find();
                        if(empty($is_address_status)){
                            return ajax_error('请填写到店自提地址',['status'=>0]);
                        } else {
                            $harvest_address_city = str_replace(',','',$is_address_status['extract_address']);
                            $harvest_address = $harvest_address_city.$is_address_status['extract_real_address']; //收货人地址  
                            $harvester = null;
                            $harvester_phone_num = $is_address_status['phone_num'];              
                        } 
                    }
                        $datas["order_type"] = $order_type;//1为选择直邮，2到店自提，3选择存茶
                        $datas["distribution"] = $goods_data["distribution"];//是否分销
                        $datas["goods_describe"] = $goods_data["goods_describe"];//卖点
                        $datas["parts_goods_name"] = $goods_data["goods_name"];//名字
                        $datas["order_quantity"] = $numbers[$keys];//订单数量
                        $datas["member_id"] = $user_id;//用户id
                        $datas["user_account_name"] = $user_information["member_name"];//用户名
                        $datas["user_phone_number"] = $user_information["member_phone_num"];//用户名手机号
                        $datas["harvester"] = $harvester;
                        $datas["harvest_phone_num"] = $harvester_phone_num;
                        $datas["harvester_address"] = $harvest_address;
                        $datas["order_create_time"] = $create_time;
                        $datas["order_amount"] = $datas["goods_money"]*$numbers[$keys];//订单金额
                        $datas["order_real_pay"] = $all_money;//订单实际支付的金额(即优惠券抵扣之后的价钱）
                        $datas["status"] = 1;
                        $datas["goods_id"] = $values;
                        $datas["parts_order_number"] = $parts_order_number;//时间+4位随机数+用户id构成订单号
                        $datas["buy_message"] = $buy_message;//买家留言
                        $datas["normal_future_time"] = $normal_future_time;//未来时间
                        $datas["special_id"] = $goods_standard_id[$keys];//规格id
                        $datas["coupon_id"] = $coupon_id;
                        $datas["refund_amount"] = $all_money;
                        $datas["unit"] = $unit[$keys];
                        $datas["receipt_status"] = $receipt_status; 
                        $datas["receipt_id"] = $receipt_id;
                        $datas["receipt_price"] = $receipt_price ;
                                        
                        $res = Db::name('crowd_order')->insertGetId($datas);
                        if ($res) {
                            $order_datas =Db::name("crowd_order")
                                ->field("order_real_pay,parts_goods_name,parts_order_number")
                                ->where('id',$res)
                                ->where("member_id",$user_id)
                                ->find();
                            return ajax_success('下单成功',$order_datas);
                        }else{

                            return ajax_error('失败',['status'=>0]);
                        }
                    } else {
                        $parts_order_number ="CC".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].($user_id+1001); //订单编号
                        $is_address_status = Db::name('store_house')
                        ->where('id',$address_id)
                        ->find();
                        $year = $request->only("year")["year"];//存茶年限
                        $harvest_address = $is_address_status['adress']; //仓库地址 
                        $store_name =  $is_address_status['name'];//仓库名
                        $harvester_phone_num = $is_address_status['phone'];
                        $datase["parts_order_number"] = $parts_order_number;//时间+4位随机数+用户id构成订单号
                        $datase["parts_goods_name"] = $goods_data["goods_name"];//名字
                        $datase["distribution"] = $goods_data["distribution"];//是否分销
                        $datase["goods_describe"] = $goods_data["goods_describe"];//卖点
                        $datase["order_quantity"] = $numbers[$keys];//订单数量
                        $datase["member_id"] = $user_id;//用户id
                        $datase["user_account_name"] = $user_information["member_name"];//用户名
                        $datase["user_phone_number"] = $user_information["member_phone_num"];//用户名手机号
                        $datase["harvest_phone_num"] = $harvester_phone_num;
                        $datase["harvester_address"] = $harvest_address;
                        $datase["order_create_time"] = $create_time;
                        $datase["order_amount"] = $datas["goods_money"]*$numbers[$keys];//订单金额
                        $datase["order_real_pay"] = $all_money;//订单实际支付的金额(即优惠券抵扣之后的价钱）
                        $datase["status"] = 1;
                        $datase["goods_id"] = $values;
                        $datase["buy_message"] = $buy_message; //买家留言
                        $datase["normal_future_time"] =$normal_future_time;//未来时间
                        $datase["special_id"] = $goods_standard_id[$keys];//规格id
                        $datase["coupon_id"] = $coupon_id;
                        $datase["receipt_status"] = $receipt_status; 
                        $datase["receipt_id"] = $receipt_id;
                        $datase["receipt_price"] = $receipt_price ;   

                        $rest_id = Db::name('order')->insertGetId($datase);
                        $datas = $datase;
                        $datas["store_house_id"] = $address_id;
                        $datas["store_name"] = $store_name;
                        $datas["store_unit"] = $unit[$keys];
                        $datas['end_time'] = strtotime(date('Y-m-d H:i:s',$create_time+$year*365*24*60*60));  
                        $datas["age_limit"] = $year;  
                
                        $key = array_search($unit[$keys],$data['unit']);
                        switch($key){
                            case 0:
                                $datas["store_number"] = $datas["order_quantity"].','.$unit[$keys];
                                break;
                            case 1:
                                $number_one = $data['unit'][$key];    //等级单位
                                $num_one = $data['num'][$key];        //等级数量
                                $number_zero = $data['unit'][$key-1]; //等级单位
                                $num_zero = $data['num'][$key]-1;     //等级数量

                                $number = $datas['order_quantity']/$num_one;
                                if($number > 1){
                                    $remainder = $datas['order_quantity']%$num_one;
                                    $datas["store_number"] = $number.','.$number_zero.','.$remainder.','.$num_one;
                                } else {
                                    $number = 0;
                                    $datas["store_number"] = $number.','.$number_zero.','.$datas['order_quantity'].','.$num_one;
                                }
                                break;
                            case 2: 
                                $number_two = $data['unit'][$key];    //等级单位
                                $num_two = $data['num'][$key];        //等级数量
                                $number_one = $data['unit'][$key-1];  //等级单位
                                $num_one = $data['num'][$key-1];      //等级数量
                                $number_zero = $data['unit'][$key-2]; //等级单位
                                $num_zero = $data['num'][$key-2];     //等级数量

                                $rank_one = $datas['order_quantity']/$number_two; //第二个数量
                                if($rank_one > 1){
                                    $three = $datas['order_quantity'] % $num_two; //第三个数量
                                    $two = $rank_one/$number_one ;//第一个数量
                                    if($two > 1){
                                        $foure = $rank_one % $number_one ;//第二个数量
                                        $datas["store_number"] = $two.','.$number_zero.','.$foure.','.$number_one.','.$rank_one.','.$number_two;
                                    } else {
                                        $two = 0;
                                        $datas["store_number"] = $two.','.$number_zero.','.$rank_one.','.$number_one.','.$three.','.$number_two;
                                    }
                                } else {
                                    $two = 0;
                                    $rank_six = 0;
                                    $datas["store_number"] = $two.','.$number_zero.','.$rank_six.','.$number_one.','.$datas['order_quantity'].','.$number_two;
                                }
                                break;                                                             
                        }
                        $res = Db::name('house_order')->insertGetId($datas);
                        if ($res) {
                            $order_datas =Db::name("house_order")
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


    
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:购物车提交订单
     **************************************
     * @param Request $request
     */
    public function crowd_order_place_by_shoppings(Request $request){
        if ($request->isPost()){
            $shopping_id = $request->only("shopping_id")["shopping_id"];
            $user_id = $request->only("member_id")["member_id"];//member_id
            $address_id = $request->param("address_id");//address_id
            $coupon_id = $request->only("coupon_id")["coupon_id"]; //添加使用优惠券id
            $order_type = $request->only("order_type")["order_type"];//1为选择直邮，2到店自提，3选择存茶
            $commodity_id = $request->only("goods_id")["goods_id"];//商品id
            $all_money = $request->only("order_amount")["order_amount"];//总价钱
            $goods_standard_id = $request->only("goods_standard_id")["goods_standard_id"];//规格id
            $numbers = $request->only("order_quantity")["order_quantity"];//商品对应数量
            $unit = $request->only("unit")["unit"];//商品单位
            $receipt_status = $request->only("receipt_status")["receipt_status"];//是否开发票
            $receipt_id = $request->only("receipt_id")["receipt_id"];//发票id
            $receipt_price = $request->only("receipt_price")["receipt_price"];//发票金额
            
            if(empty($user_id)){
                return ajax_error("未登录",['status'=>0]);
            }
            $member_grade_id =Db::name("member")->where("member_id",$user_id)->find();
            $member_consumption_discount =Db::name("member_grade")
                ->where("member_grade_id",$member_grade_id["member_grade_id"])
                ->find();
            $user_information =Db::name("member")->where("member_id",$user_id)->find();
            $buy_message = null; //买家留言
            $time = date("Y-m-d",time());
            $v = explode('-',$time);
            $time_second = date("H:i:s",time());
            $vs = explode(':',$time_second);
            
            foreach ($commodity_id as $keys=>$values){
                $goods_data = Db::name('goods')->where('id',$values)->find();
                $create_time = time();//下单时间
                $normal_time =Db::name("order_setting")->find();//订单设置的时间
                if(!empty($normal_time)){
                    $normal_future_time = strtotime("+". $normal_time['normal_time']." minute");
                } else {
                    $normal_future_time = null;
                }
                if($goods_data["goods_standard"]==0){
                    $datas['goods_image'] = $goods_data['goods_show_image'];//图片
                    $datas["goods_money"]=$goods_data['goods_new_money']* $member_consumption_discount["member_consumption_discount"];//商品价钱
                    $data['unit'] = explode(",",$goods_data['unit']);
                    $data['num'] = explode(",",$goods_data['num']);
                } else {
                    //图片
                    $special_data =Db::name("special")
                        ->where("id",$goods_standard_id[$keys])
                        ->find();
                    $datas['goods_image'] = $special_data['images'];//图片
                    $datas["goods_money"]= $special_data['price'] * $member_consumption_discount["member_consumption_discount"];//商品价钱
                    $datas['goods_standard'] = $special_data["name"]; //商品规格
                    $data['unit'] = explode(",",$special_data['unit']);
                    $data['num'] = explode(",",$special_data['num']);

                }
                if($order_type != 3){
                    if($order_type == 1){
                            $parts_order_number ="ZY".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].($user_id+1001); //订单编号 
                            $is_address = Db::name('user_address')
                            ->where("id",$address_id)
                            ->where('user_id', $user_id)
                            ->find();
                            if (empty($is_address) ) {
                                return ajax_error('请填写收货地址',['status'=>0]);
                            } else {
                                $is_address_status = Db::name('user_address')
                                    ->where('user_id', $user_id)
                                    ->where('id',$address_id)
                                    ->find();
                                    $harvest_address_city = str_replace(',','',$is_address_status['address_name']);
                                    $harvest_address = $harvest_address_city.$is_address_status['harvester_real_address']; //收货人地址
                                    $harvester = $is_address_status['harvester'];
                                    $harvester_phone_num = $is_address_status['harvester_phone_num'];
                            } 
                        }

                    if ($order_type == 2) {
                        $parts_order_number ="ZT".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].($user_id+1001); //订单编号
                        $is_address_status = Db::name('extract_address')
                        ->where('id',$address_id)
                        ->find();
                        if(empty($is_address_status)){
                            return ajax_error('请填写到店自提地址',['status'=>0]);
                        } else {
                            $harvest_address_city = str_replace(',','',$is_address_status['extract_address']);
                            $harvest_address = $harvest_address_city.$is_address_status['extract_real_address']; //收货人地址  
                            $harvester = null;
                            $harvester_phone_num = $is_address_status['phone_num'];              
                        } 
                    }
                        $datas["order_type"] = $order_type;//1为选择直邮，2到店自提，3选择存茶
                        $datas["distribution"] = $goods_data["distribution"];//是否分销
                        $datas["goods_describe"] = $goods_data["goods_describe"];//卖点
                        $datas["parts_goods_name"] = $goods_data["goods_name"];//名字
                        $datas["order_quantity"] = $numbers[$keys];//订单数量
                        $datas["member_id"] = $user_id;//用户id
                        $datas["user_account_name"] = $user_information["member_name"];//用户名
                        $datas["user_phone_number"] = $user_information["member_phone_num"];//用户名手机号
                        $datas["harvester"] = $harvester;
                        $datas["harvest_phone_num"] = $harvester_phone_num;
                        $datas["harvester_address"] = $harvest_address;
                        $datas["order_create_time"] = $create_time;
                        $datas["order_amount"] = $datas["goods_money"]*$numbers[$keys];//订单金额
                        $datas["order_real_pay"] = $all_money;//订单实际支付的金额(即优惠券抵扣之后的价钱）
                        $datas["status"] = 1;
                        $datas["goods_id"] = $values;
                        $datas["parts_order_number"] = $parts_order_number;//时间+4位随机数+用户id构成订单号
                        $datas["buy_message"] = $buy_message;//买家留言
                        $datas["normal_future_time"] = $normal_future_time;//未来时间
                        $datas["special_id"] = $goods_standard_id[$keys];//规格id
                        $datas["coupon_id"] = $coupon_id;
                        $datas["refund_amount"] = $all_money;
                        $datas["unit"] = $unit[$keys]; 
                        $datas["receipt_status"] = $receipt_status; 
                        $datas["receipt_id"] = $receipt_id;
                        $datas["receipt_price"] = $receipt_price ;                                        
                        $res = Db::name('order')->insertGetId($datas);
                    } else {
                        $parts_order_number ="CC".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].($user_id+1001); //订单编号
                        $is_address_status = Db::name('store_house')
                        ->where('id',$address_id)
                        ->find();
                        $year = $request->only("year")["year"];//存茶年限
                        $house_price = $request->only("house_price")["house_price"];//存茶年限
                        $harvest_address = $is_address_status['adress']; //仓库地址 
                        $store_name =  $is_address_status['name'];//仓库名
                        $harvester_phone_num = $is_address_status['phone'];
                        $datase["parts_order_number"] = $parts_order_number;//时间+4位随机数+用户id构成订单号
                        $datase["parts_goods_name"] = $goods_data["goods_name"];//名字
                        $datase["distribution"] = $goods_data["distribution"];//是否分销
                        $datase["goods_describe"] = $goods_data["goods_describe"];//卖点
                        $datase["order_quantity"] = $numbers[$keys];//订单数量
                        $datase["member_id"] = $user_id;//用户id
                        $datase["user_account_name"] = $user_information["member_name"];//用户名
                        $datase["user_phone_number"] = $user_information["member_phone_num"];//用户名手机号
                        $datase["harvest_phone_num"] = $harvester_phone_num;
                        $datase["harvester_address"] = $harvest_address;
                        $datase["order_create_time"] = $create_time;
                        $datase["order_amount"] = $datas["goods_money"]*$numbers[$keys];//订单金额
                        $datase["order_real_pay"] = $all_money;//订单实际支付的金额(即优惠券抵扣之后的价钱）
                        $datase["status"] = 1;
                        $datase["goods_id"] = $values;
                        $datase["buy_message"] = $buy_message;//买家留言
                        $datase["normal_future_time"] =$normal_future_time;//未来时间
                        $datase["special_id"] = $goods_standard_id[$keys];//规格id
                        $datase["coupon_id"] = $coupon_id;
                        $datase["receipt_status"] = $receipt_status; 
                        $datase["receipt_id"] = $receipt_id;
                        $datase["receipt_price"] = $receipt_price ;

                        $rest_id = Db::name('order')->insertGetId($datase);
                        $datas = $datase;
                        $datas["store_house_id"] = $address_id;
                        $datas["store_name"] = $store_name;
                        $datas["store_unit"] = $unit[$keys];
                        $datas['end_time'] = strtotime(date('Y-m-d H:i:s',$create_time+$year*365*24*60*60));  
                        $datas["age_limit"] = $year;
                        $datas["house_price"] = $house_price[$keys];
                      
                        $key = array_search($unit[$keys],$data['unit']);
                        switch($key){
                            case 0:
                                $datas["store_number"] = $datas["order_quantity"].','.$unit[$keys];
                                break;
                            case 1:
                                $number_one = $data['unit'][$key];    //等级单位
                                $num_one = $data['num'][$key];        //等级数量
                                $number_zero = $data['unit'][$key-1]; //等级单位
                                $num_zero = $data['num'][$key-1];     //等级数量

                                $number = $datas['order_quantity']/$num_one;
                                if($number > 1){
                                    $remainder = $datas['order_quantity']%$num_one;
                                    $datas["store_number"] = $number.','.$number_zero.','.$remainder.','.$number_one;
                                } else {
                                    $number = 0;
                                    $datas["store_number"] = $number.','.$number_zero.','.$datas['order_quantity'].','.$number_one;
                                }
                                break;
                            case 2: 
                                $number_two = $data['unit'][$key];    //等级单位
                                $num_two = $data['num'][$key];        //等级数量
                                $number_one = $data['unit'][$key-1];  //等级单位
                                $num_one = $data['num'][$key-1];      //等级数量
                                $number_zero = $data['unit'][$key-2]; //等级单位
                                $num_zero = $data['num'][$key-2];     //等级数量

                                $rank_one = $datas['order_quantity']/$number_two; //第二个数量
                                if($rank_one > 1){
                                    $three = $datas['order_quantity'] % $num_two; //第三个数量
                                    $two = $rank_one/$number_one ; //第一个数量
                                    if($two > 1){
                                        $foure = $rank_one % $number_one ;//第二个数量
                                        $datas["store_number"] = $two.','.$number_zero.','.$foure.','.$number_one.','.$rank_one.','.$number_two;
                                    } else {
                                        $two = 0;
                                        $datas["store_number"] = $two.','.$number_zero.','.$rank_one.','.$number_one.','.$three.','.$number_two;
                                    }
                                } else {
                                    $two = 0;
                                    $rank_six = 0;
                                    $datas["store_number"] = $two.','.$number_zero.','.$rank_six.','.$number_one.','.$datas['order_quantity'].','.$number_two;
                                }
                                break;                                                             
                            }
                        $res = Db::name('house_order')->insertGetId($datas);        
                    }
                }
            if($order_type != 3){
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
            } else {
                if ($res) {
                    $order_datas =Db::name("house_order")
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

     /**
     **************李火生*******************
     * @param Request $request
     * Notes:订单详情页面信息
     **************************************
     * @param Request $request
     * @return \think\response\View|void
     */
    public function crowd_order_detail(Request $request){
        if($request->isPost()) {
            $user_id = $request->only("member_id")["member_id"]; //会员id
            $order_number =$request->only("order_number")["order_number"];//订单编号
            $condition = "`member_id` = " . $user_id .  " and `parts_order_number` = " . $order_number;
            $data = Db::name("crowd_order")
                ->where($condition)
                ->select();
            if (!empty($data)) {
                $datas["buy_message"] = $data[0]["buy_message"];//买家留言
                $datas["create_time"] = $data[0]["order_create_time"];//订单创建时间
                $datas["parts_order_number"] = $data[0]["parts_order_number"];//订单编号
                $datas["pay_time"] = $data[0]["pay_time"]; //支付时间
                $datas["harvester"] = $data[0]["harvester"];//收货人
                $datas["harvest_phone_num"] = $data[0]["harvest_phone_num"];//收件人电话
                $datas["harvester_address"] = $data[0]["harvester_address"];//收件人地址
                $datas["status"] = $data[0]["status"];//状态
                foreach ($data as $ks=>$vs){
                    $datas["all_goods_money"][] =$vs["goods_money"]*$vs["order_quantity"];
                    $data[$ks]["express_namess"] =str_to_chinese($vs["express_name"]);
                }
                $datas["all_goods_pays"] =array_sum($datas["all_goods_money"]); //商品总额（商品*数量）
                $datas["normal_future_time"] = $data[0]["normal_future_time"];//正常订单未付款自动关闭的时间
                $datas["all_order_real_pay"] = $data[0]["order_real_pay"];//订单实际支付
                $datas["all_numbers"] = array_sum(array_map(create_function('$vals', 'return $vals["order_quantity"];'), $data));//订单总数量
                $datas["info"] = $data;
                if (!empty($datas)) {
                    return ajax_success("数据返回成功", $datas);
                } else {
                    return ajax_error("没有数据信息", ["status" => 0]);
                }
            } else {
                return ajax_error("订单信息错误", ["status" => 0]);
            }
        }

        return view('order_parts_detail');
    }








}