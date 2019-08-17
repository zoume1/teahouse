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
use think\Session;
use app\admin\model\Goods;
use app\admin\model\Order as GoodsOrder;
use app\common\model\dealer\Order as OrderModel;

class  Order extends  Controller
{
    /**
     **************lilu*******************
     * @param Request $request
     * Notes:立即购买过去购物清单数据返回
     **************************************
     *open_id
     */
    public function order_return(Request $request)
    {
        if ($request->isPost()) {
            
            $open_id =$request->only("open_id")["open_id"];
            $store_id =$request->only("uniacid")["uniacid"];
            $member_grade_id = Db::name("member")->where("member_openid",$open_id)->find();
            $role_id =  Db::name("admin")->where("store_id",$member_grade_id['store_id'])->value("role_id");
            if($role_id > 13){
                $authority = 1;
            } else {
                $authority = 0;
            }
            $member_consumption_discount = Db::name("member_grade")
                ->where("member_grade_id",$member_grade_id["member_grade_id"])
                ->find();
            $goods_id = $request->only("goods_id")["goods_id"];
            $special_id = $request->only("guige")["guige"];
            $number = $request->only("num")["num"];
            if (empty($goods_id)) {
                return ajax_error("商品信息有误，请返回重新提交", ["status" => 0]);
            }
            
            foreach ($goods_id as  $key=>$value){
                //判断该商品是否为限时限购
                $is_limit=db('limited')->where(['store_id'=>$store_id,'goods_id'=>$value])->find();
                if($is_limit){
                   $data[$key]['is_limit']=1;
                   $condition=json_decode($is_limit['limit_condition'],true);
                   $data[$key]['limit_number']=$condition['limit']['number'];
                }else{
                    $data[$key]['is_limit']=0;
                    $data[$key]['limit_number']=-1;
                }
                $goods_data = null;
                $goods_data = Db::name("goods")->where("id", $value)->find();
                $goods_data['goods_sign'] = json_decode($goods_data["goods_sign"],true);
                //判断是为专用还是通用
                //专用规格

                //判断商品是否参加商品折扣
                if($goods_data["goods_member"] != 1){
                    $member_consumption_discount["member_consumption_discount"] = 1;
                }
                if ($goods_data["goods_standard"] == 0) {
                        $data[$key]["goods_info"] = $goods_data;
                        if($goods_data['limit_goods']=='1'){    //限时限购的商品
                        $data[$key]['grade_price']=$goods_data['limit_price'];
                        }else{
                            $data[$key]["grade_price"] = $member_consumption_discount["member_consumption_discount"] * $goods_data["goods_new_money"];//商品的价格
                        }
                        $data[$key]["special_info"] = null;
                        $data[$key]["number"] =$number[$key];
                        $data[$key]["user_grade_image"] =$member_consumption_discount["member_grade_img"];
                        $data[$key]["unit"]=$goods_data['monomer'];
                    } else{
                    $data[$key]["goods_info"] = $goods_data;
                    if($special_id[$key] != 0){
                        $info = Db::name("special")
                            ->where("id", $special_id[$key])
                            ->find();
                        $data[$key]["special_info"] =$info;
                        $data[$key]["grade_price"] =$member_consumption_discount["member_consumption_discount"]* $info["price"];
                        $data[$key]["unit"]=$info['offer'];
                    }else{
                        $data[$key]["goods_info"] = $goods_data;
                        $data[$key]["grade_price"] =$member_consumption_discount["member_consumption_discount"] * $goods_data["goods_new_money"];
                        $data[$key]["unit"]=$goods_data['monomer'];
                    }
                    $data[$key]["number"] =$number[$key];
                    $data[$key]["user_grade_image"] =$member_consumption_discount["member_grade_img"];
                }
            }
            $restul = $member_grade_id['store_id'];
            $da_change = Db::table("tb_set_meal_order")
            ->alias('a')
           ->where("store_id", $restul)
           ->where("audit_status",1)
           ->where('status_type',1)
           ->value('enter_all_id');
            if(!empty($da_change)){
                if($da_change <= 6){
                    $da_change = 1;
                }
                if(  ($da_change > 6) && ($da_change <= 17)){
                    $da_change = 2;
                }
                if( $da_change > 17){
                    $da_change = 3;
                }
            }
            if(!empty($data)){
                exit(json_encode(array("status" => 1, "info" => "数据返回成功","enter_all_id"=>$da_change,"data"=>$data,"authority"=>$authority)));
            }else{
                return ajax_error("没有数据",["status"=>0]);
            }

        }
    }



    /**
     **************郭杨*******************
     * @param Request $request
     * Notes:提交订单(修改过的)
     **************************************
     * @param Request $request
     */
    public function order_places(Request $request)
    {
        if ($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid']; 
            $user_id = $request->only("member_id")["member_id"];//member_id
            $address_id = $request->param("address_id");//address_id
            $store_house_id = $request->param("store_house_id");//仓库id
            $coupon_id = $request->only("coupon_id")["coupon_id"]; //添加使用优惠券id
            $order_type = $request->only("order_type")["order_type"];//1为选择直邮，2到店自提，3选择存茶
            $commodity_id = $request->only("goods_id")["goods_id"];//商品id
            $all_money = $request->only("order_amount")["order_amount"];//总价钱
            $goods_standard_id = $request->only("goods_standard_id")["goods_standard_id"];//规格id
            $numbers = $request->only("order_quantity")["order_quantity"];//商品对应数量
            $unit = $request->only("unit")["unit"];//商品单位
            $receipt_status = $request->only("receipt_status")["receipt_status"];//是否开发票
            $receipt_id = $request->only("receipt_id")["receipt_id"];//发票id
            $receipt_price = $request->only("receipt_price")["receipt_price"];//发票金额--税费
            $freight = $request->only("freight")["freight"];//发票金额
            $storage = $request->only("storage")["storage"];//发票金额
            
            if(empty($user_id)){
                return ajax_error("未登录",['status'=>0]);
            }
             //获取用户余额
             $balance=db('member')->where('member_id',$user_id)->field('member_wallet,member_recharge_money')->find();
             $bb=$balance['member_wallet']+$balance['member_recharge_money'];
             $money=round($bb,2);
            $member_grade_id = Db::name("member")->where("member_id",$user_id)->find();
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
                    //判断商品的库存的是否够用
                    if($goods_data['goods_repertory']< $numbers[$keys]){     //购买数量大于库存
                        return  ajax_error('请修改库存不足的商品（'.$goods_data['goods_name'].'）小于'.$goods_data['goods_repertory'],['status'=>2]);    //库存不足
                   }
                } else {
                    //图片
                    $special_data =Db::name("special")
                        ->where("id",$goods_standard_id[$keys])
                        ->find();
                    $datas['goods_image'] = $special_data['images'];   //图片
                    $datas["goods_money"]= $special_data['price'] * $member_consumption_discount["member_consumption_discount"];//商品价钱
                    $datas['goods_standard'] = $special_data["name"]; //商品规格  
                    $data['unit'] = explode(",",$special_data['unit']);
                    $data['num'] = explode(",",$special_data['num']);
                    //判断商品的库存的是否够用
                    if($special_data['goods_repertory']< $numbers[$keys]){     //购买数量大于库存
                        return  ajax_error('请修改库存不足的商品（'.$goods_data['goods_name'].'）小于'.$goods_data['goods_repertory'],['status'=>2]);    //库存不足
                   }

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
                            // $harvester = null;
                            $harvester_phone_num = $is_address_status['phone_num'];              
                            $harvester = $is_address_status['extract_name'];              
                        } 
                    }
                        $datas["order_type"] = $order_type;//1为选择直邮，2到店自提，3选择存茶
                        $datas["distribution"] = $goods_data["distribution_status"];//是否分销
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
                        $all_moneys[] = $datas["goods_money"]*$numbers[$keys];//订单金额
                        $datas["order_real_pay"] = $all_money;//订单实际支付的金额(即优惠券抵扣之后的价钱）
                        // $datas["order_real_pay"] = 0.01;//订单实际支付的金额(即优惠券抵扣之后的价钱）
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
                        $datas["store_id"] = $store_id;
                        $datas["freight"] = $freight;
                        $datas["storage"] = $storage;
                                        
                        $res = Db::name('order')->insertGetId($datas);
                        if ($res) {
                            //判断是否使用优惠卷
                            if($coupon_id>0){    //使用
                                db('coupon')->where('id',$coupon_id)->setInc('use_number',1);
                            }
                            //下单成功，冻结库存
                            if($goods_standard_id[$keys]=='0'){
                                 //单规格商品扣除库存
                                 $re1 = Db::name('goods')->where('id',$values)->setDec('goods_repertory',$numbers[$keys]);
                            }else{
                                //多规格商品扣除库存
                                $re2=db('special')->where('id',$goods_standard_id[$keys])->setDec('stock',$numbers[$keys]);
                            }
                            $order_datas =Db::name("order")
                                ->field("order_real_pay,parts_goods_name,parts_order_number,order_type,coupon_type,order_amount")
                                ->where('id',$res)
                                ->where("member_id",$user_id)
                                ->find();
                            $order_datas['balance']=$money;

                            //判断是否生成分销订单
                            $goods_bool = Goods::getDistributionStatus($commodity_id);
                            if($goods_bool){
                                $data = [
                                    'member_id'=>$user_id,
                                    'id'=>$res,
                                    'parts_order_number'=>$order_datas['parts_order_number'],
                                    'goods_id'=>$goods_bool,
                                    'store_id'=>$store_id,
                                    'order_amount'=>$all_moneys,
                                    'goods_money'=>$order_datas['order_amount'],//总金额
                                    'status'=>0,
                                    
                                ];                                               
                                OrderModel::createOrder($data);
                            }
                            return ajax_success('下单成功',$order_datas);
                        }else{

                            return ajax_error('失败',['status'=>0]);
                        }
                    } else {       //存茶
                        $parts_order_number ="RC".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].($user_id+1001); //订单编号
                        $is_address_status = Db::name('store_house')
                        ->where('id',$store_house_id)
                        ->where('store_id',$store_id)
                        ->find();
                        if(empty($is_address_status)){
                            return ajax_error('仓库地址查询失败',['status'=>0]);
                        }
                        $year = $request->only("year")["year"];//存茶年限
                        $harvest_address = $is_address_status['adress']; //仓库地址 
                        $store_name =  $is_address_status['name'];//仓库名
                        $harvester_phone_num = $is_address_status['phone'];
                        $datase['goods_image'] = $datas['goods_image'] ;   //图片
                        $datase['order_type'] = $order_type ;   //订单类型
                        $datase["goods_money"]= $datas['goods_money'];//商品价钱
                        $datase["parts_order_number"] = $parts_order_number;//时间+4位随机数+用户id构成订单号
                        $datase["parts_goods_name"] = $goods_data["goods_name"];//名字
                        $datase["distribution"] = $goods_data["distribution_status"];//是否分销
                        $datase["goods_describe"] = $goods_data["goods_describe"];//卖点
                        $datase["order_quantity"] = $numbers[$keys];//订单数量
                        $datase["member_id"] = $user_id;//用户id
                        $datase["user_account_name"] = $user_information["member_name"];//用户名
                        $datase["user_phone_number"] = $user_information["member_phone_num"];//用户名手机号
                        $datase["harvest_phone_num"] = $harvester_phone_num;
                        $datase["harvester_address"] = $store_name;    //暂时先这样  
                        $datase["order_create_time"] = $create_time;
                        $datase["order_amount"] = $datas["goods_money"]*$numbers[$keys];//订单金额
                        $all_moneys[] = $datas["goods_money"]*$numbers[$keys];//订单金额
                        $datase["order_real_pay"] = $all_money;//订单实际支付的金额(即优惠券抵扣之后的价钱）
                        $datase["status"] = 1;
                        $datase["goods_id"] = $values;
                        $datase["buy_message"] = $buy_message;//买家留言
                        $datase["normal_future_time"] =$normal_future_time;//未来时间
                        $datase["special_id"] = $goods_standard_id[$keys];//规格id
                        $datase["coupon_id"] = $coupon_id;
                        $datase["receipt_status"] = $receipt_status; 
                        $datase["receipt_id"] = $receipt_id;
                        $datase["receipt_price"] = $receipt_price;   
                        $datase["store_id"] = $store_id;   
                        $datase["storage"] = $storage;   

                        $rest_id = Db::name('order')->insertGetId($datase);
                       
                        $datas = $datase;
                        $datas["store_house_id"] = $store_house_id;
                        $datas["store_name"] = $store_name;
                        $datas["store_unit"] = $unit[$keys];
                        $datas['end_time'] = strtotime(date('Y-m-d H:i:s',$create_time+$year*365*24*60*60));  
                        $datas["age_limit"] = $year;  
                        $datas["coupon_type"] = 1;  
                
                        $key = array_search($unit[$keys],$data['unit']);
                        //先判断有多少位数量等级
                        $datas["store_number"]= $this->unit_calculate($data['unit'], $data['num'],$key,$datase["order_quantity"]);
                        $res = Db::name('house_order')->insertGetId($datas);
                        if ($res) {
                            $order_datas =Db::name("house_order")
                                ->field("order_real_pay,parts_goods_name,parts_order_number,order_type,coupon_type")
                                ->where('id',$res)
                                ->where("member_id",$user_id)
                                ->find();
                            $order_datas['balance']=$money;


                        //判断是否生成分销订单
                        $goods_bool = Goods::getDistributionStatus($commodity_id);
                        if($goods_bool){
                            $data = [
                                'member_id'=>$user_id,
                                'id'=>$res,
                                'parts_order_number'=>$order_datas['parts_order_number'],
                                'goods_id'=>$goods_bool,
                                'store_id'=>$store_id,
                                'order_amount'=>$all_moneys,
                                'goods_money'=>$order_datas['order_amount'],//总金额
                                'status'=>0,
                                
                            ];                                               
                            OrderModel::createOrder($data);
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
     **************李火生*******************
     * @param Request $request
     * Notes:购物车提交订单(修改过的)
     **************************************
     * @param Request $request
     */
    public function order_place_by_shoppings(Request $request){
        if ($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $shopping_id = $request->only("shopping_id")["shopping_id"];
            $store_house_id = $request->param("store_house_id");//仓库id
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
            $freight = $request->only("freight")["freight"];//邮费
            $storage = $request->only("storage")["storage"];//仓储费
            
            if(empty($user_id)){
                return ajax_error("未登录",['status'=>0]);
            }
            //获取用户余额
            $balance=db('member')->where('member_id',$user_id)->field('member_wallet,member_recharge_money')->find();
            $bb=$balance['member_wallet']+$balance['member_recharge_money'];
            $money=round($bb,2);

            $member_grade_id = Db::name("member")->where("member_id",$user_id)->find();
            $role_id =  Db::name("admin")->where("store_id",$member_grade_id['store_id'])->value("role_id");
            if($role_id > 13){
                $authority = 1;
            } else {
                $authority = 0;
            }
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
                //判断商品是否参加商品折扣
                if($goods_data["goods_member"] != 1){
                    $member_consumption_discount["member_consumption_discount"] = 1;
                }
                if($goods_data["goods_standard"]==0){
                    $datas['goods_image'] = $goods_data['goods_show_image'];//图片
                    $datas["goods_money"]=$goods_data['goods_new_money']* $member_consumption_discount["member_consumption_discount"];//商品价钱
                    $datas['goods_standard'] = 0; //商品规格
                    $data['unit'] = explode(",",$goods_data['unit']);
                    $data['num'] = explode(",",$goods_data['num']);
                    //判断商品的库存的是否够用
                    if($goods_data['goods_repertory']<= $numbers[$keys]){     //购买数量大于库存
                         return  ajax_error('请修改库存不足的商品（'.$goods_data['good_name'].'）小于'.$goods_data['goods_repertory'],['status'=>2]);    //库存不足
                    }
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
                     //判断商品的库存的是否够用
                     if($special_data['stock']<= $numbers[$keys]){     //购买数量大于库存
                        return  ajax_error('请修改库存不足的商品（'.$goods_data['good_name'].'）小于'.$goods_data['goods_repertory'],['status'=>2]);    //库存不足
                   }
                }
                if($order_type != 3){           //不是存茶
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
                                // $harvester = null;
                                $harvester_phone_num = $is_address_status['phone_num'];              
                                $harvester = $is_address_status['extract_name'];              
                            } 
                        }
                        $datas["order_type"] = $order_type;//1为选择直邮，2到店自提，3选择存茶
                        $datas["distribution"] = $goods_data["distribution_status"];//是否分销
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
                        $all_moneys[] = $datas["goods_money"]*$numbers[$keys];//订单金额
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
                        $datas["store_id"] = $store_id;
                        $datas["receipt_price"] = $receipt_price ;                                        
                        $datas["freight"] = $freight ;                                        
                        $datas["storage"] = $storage ; 
                        $res = Db::name('order')->insertGetId($datas);
                        if($res){
                            if($coupon_id>0){    //使用
                                db('coupon')->where('id',$coupon_id)->setInc('use_number',1);
                            }
                            //下单成功
                            if($goods_standard_id[$keys]=='0'){
                                 //当前商品是单规格商品
                                $re1 = Db::name('goods')->where('id',$values)->setDec('goods_repertory',$numbers[$keys]);
                                 
                            }else{
                                 $re2=db('special')->where('id',$goods_standard_id[$keys])->setDec('stock',$numbers[$keys]);

                            }
                        }

                    } else {
                        $parts_order_number ="RC".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].($user_id+1001); //订单编号
                        $is_address_status = Db::name('store_house')
                        ->where('id',$store_house_id)
                        ->find();
                        $year = $request->only("year")["year"];//存茶年限
                        $house_price = $request->only("house_price")["house_price"];//存茶年限
                        $harvest_address = $is_address_status['adress']; //仓库地址 
                        $store_name =  $is_address_status['name'];//仓库名
                        $harvester_phone_num = $is_address_status['phone'];
                        $datase['goods_image'] = $datas['goods_image'];
                        $datase["goods_money"] = $datas["goods_money"];
                        $datase['goods_standard'] = $datas['goods_standard'];
                        $datase["parts_order_number"] = $parts_order_number;//时间+4位随机数+用户id构成订单号
                        $datase["parts_goods_name"] = $goods_data["goods_name"];//名字
                        $datase["distribution"] = $goods_data["distribution_status"];//是否分销
                        $datase["goods_describe"] = $goods_data["goods_describe"];//卖点
                        $datase["coupon_type"] = $goods_data["coupon_type"];//商品类型
                        $datase["order_quantity"] = $numbers[$keys];//订单数量
                        $datase["member_id"] = $user_id;//用户id
                        $datase["user_account_name"] = $user_information["member_name"];//用户名
                        $datase["user_phone_number"] = $user_information["member_phone_num"];//用户名手机号
                        $datase["harvest_phone_num"] = $harvester_phone_num;
                        $datase["harvester_address"] = $harvest_address;
                        $datase["order_create_time"] = $create_time;
                        $datase["order_amount"] = $datas["goods_money"]*$numbers[$keys];//订单金额
                        $all_money[] = $datas["goods_money"]*$numbers[$keys];//订单金额
                        $datase["order_real_pay"] = $all_money;//订单实际支付的金额(即优惠券抵扣之后的价钱）
                        $datase["status"] = 1;
                        $datase["goods_id"] = $values;
                        $datase["buy_message"] = $buy_message;//买家留言
                        $datase["normal_future_time"] =$normal_future_time;//未来时间
                        $datase["special_id"] = $goods_standard_id[$keys];//规格id
                        $datase["coupon_id"] = $coupon_id;
                        $datase["receipt_status"] = $receipt_status; 
                        $datase["receipt_id"] = $receipt_id;
                        $datase["store_id"] = $store_id;
                        $datase["receipt_price"] = $receipt_price ;
                        $datase["order_type"] = $order_type;
                        $datase["freight"] = $freight ;                                        
                        $datase["storage"] = $storage ;     
                        $rest_id = db('order')->insertGetId($datase);
                        $datas = $datase;
                        $datas["store_house_id"] = $store_house_id;
                        $datas["store_name"] = $store_name;
                        $datas["store_unit"] = $unit[$keys];
                        $datas['end_time'] = strtotime(date('Y-m-d H:i:s',$create_time+$year*365*24*60*60));  
                        $datas["age_limit"] = $year;
                        $datas["coupon_type"] = 1;
                        $datas["house_price"] = $house_price[$keys];
                        $key = array_search($unit[$keys],$data['unit']);
                        //先判断有多少位数量等级
                        $datas["store_number"]= $this->unit_calculate($data['unit'], $data['num'],$key,$datase["order_quantity"]);
                        $res = Db::name('house_order')->insertGetId($datas);        
                    }
                }
                
            if($order_type != 3){
                if ($res) {
                    $order_datas = Db::name("order")
                        ->field("order_real_pay,parts_goods_name,parts_order_number,order_type,coupon_type")
                        ->where('id',$res)
                        ->where("member_id",$user_id)
                        ->find();
                        $order_datas['balance']=$money;
                //清空购物车数据
                if(is_array($shopping_id)){
                    $where ='id in('.implode(',',$shopping_id).')';
                }else{
                    $where ='id='.$shopping_id;
                }
                $list =  Db::name('shopping')->where($where)->delete(); 
                
                //判断是否生成分销订单
                $goods_bool = Goods::getDistributionStatus($commodity_id);
                if($goods_bool){
                    $count_money = Goods::getDistributionPrice($commodity_id,$goods_bool,$all_money);
                    $data = [
                        'member_id'=>$user_id,
                        'id'=>$res,
                        'parts_order_number'=>$order_datas['parts_order_number'],
                        'goods_id'=>$goods_bool,
                        'store_id'=>$store_id,
                        'order_amount'=>$count_money,
                        'goods_money'=>array_sum($count_money),//总金额
                        'status'=>0,                      
                    ];                                               
                    OrderModel::createOrder($data);
                }  

                exit(json_encode(array("status" => 1, "info" => "下单成功","data"=>$order_datas,"authority"=>$authority)));
                }else{

                    return ajax_error('失败',['status'=>0]);
                } 
            } else {
                if ($res) {
                    $order_datas =Db::name("house_order")
                        ->field("order_real_pay,parts_goods_name,parts_order_number,order_type")
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
                
                //判断是否生成分销订单
                $goods_bool = Goods::getDistributionStatus($commodity_id);
                if($goods_bool){
                    $count_money = Goods::getDistributionPrice($commodity_id,$goods_bool,$all_money);
                    $data = [
                        'member_id'=>$user_id,
                        'id'=>$res,
                        'parts_order_number'=>$order_datas['parts_order_number'],
                        'goods_id'=>$goods_bool,
                        'store_id'=>$store_id,
                        'order_amount'=>$count_money,
                        'goods_money'=>array_sum($count_money),//总金额
                        'status'=>0,
                        
                    ];                                               
                    OrderModel::createOrder($data);
                } 
                
                exit(json_encode(array("status" => 1, "info" => "下单成功","data"=>$order_datas,"authority"=>$authority)));
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
    public function order_detail(Request $request){
        if($request->isPost()) {
            $open_id =$request->only("open_id")["open_id"]; //用户open_ID
            $user_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
            $order_number =$request->only("order_number")["order_number"];//订单编号
            $condition = "`member_id` = " . $user_id .  " and `parts_order_number` = " . $order_number;
            $data = Db::name("order")
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


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:未付款判断时间是否过了订单设置的时间，过了则进行自动关闭
     **************************************
     * @param Request $request
     */
    public function order_detail_cancel(Request $request){
        if($request->isPost()){
            $open_id =$request->only("open_id")["open_id"]; //用户open_ID

            $user_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");

            $cancel_order_description =$request->only('cancel_order_description')["cancel_order_description"];//取消原因
            $parts_order_number =$request->only("parts_order_number")["parts_order_number"];//订单编号

            if(!empty($parts_order_number)){
                $res =Db::name("order")
                    ->where("parts_order_number",$parts_order_number)
                    ->select();
                if(!empty($res)){
                    $normal_future_time =$res[0]["normal_future_time"];//未来时间（超过则自动关闭有优惠抵扣退回优惠抵扣）
                    $new_time =time();
                    if($new_time >= $normal_future_time){
                        foreach($res as $k=>$v){
                            $is_use_integral[$k] =Db::name("order_parts")
                                ->field("integral_discount_setting_id,id,integral_deductible_num")
                                ->where("id",$v["id"])
                                ->having("integral_discount_setting_id","NEQ",NULL)
                                ->group("integral_discount_setting_id")
                                ->find();
                            $data = [
                                "status"=>9,
                                "coupon_id"=>0,
                                "cancel_order_description"=>$cancel_order_description
                            ];
                            $bool =Db::name("order_parts")->where("id",$v["id"])->update($data);
                        }

                        if($bool){
                            //取消订单退回积分到积余额
                            if(!empty( $is_use_integral)){
                                if(!empty($is_use_integral[0]["integral_deductible_num"])){
                                    $user_info = Db::name("user")->field("user_integral_wallet,user_integral_wallet_consumed")->where("id",$user_id)->find();
                                    $update_data =[
                                        "user_integral_wallet"=>$user_info["user_integral_wallet"] + $is_use_integral[0]["integral_deductible_num"],
                                        "user_integral_wallet_consumed"=>$user_info["user_integral_wallet_consumed"] - $is_use_integral[0]["integral_deductible_num"]
                                    ];
                                    Db::name("user")->where("id",$user_id)->update($update_data); //积分增加
                                    $integral_data =[
                                        "user_id"=>$user_id,//用户ID
                                        "integral_operation"=>"+".$is_use_integral[0]["integral_deductible_num"],//积分操作
                                        "integral_balance"=>$user_info["user_integral_wallet"] + $is_use_integral[0]["integral_deductible_num"],//积分余额
                                        "integral_type"=> 1,//积分类型
                                        "operation_time"=>date("Y-m-d H:i:s") ,//操作时间
                                        "integral_remarks"=>"订单号:".$parts_order_number."因超时未付款，取消退回".$is_use_integral[0]["integral_deductible_num"]."积分",//积分备注
                                    ];
                                    Db::name("integral")->insert($integral_data); //插入积分消费记录
                                }
                            }
                            return ajax_success("取消成功",["status"=>1]);
                        }else{
                            return ajax_error("取消失败",["status"=>0]);
                        }
                    }else{
                        return ajax_error("还未到达自动取消订单时间",["status"=>0]);
                    }
                }
            }else{
            return ajax_error("所传参数不能为空",["status"=>0]);
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:订单状态全部订单接口
     **************************************
     * @param Request $request
     */
    public function   ios_api_order_all(Request $request)
    {
        if ($request->isPost()) {
            $open_id =$request->only("open_id")["open_id"]; //用户open_ID
            $uniacid = input("uniacid");
            $member_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
            
            // $da_change =Db::table("tb_set_meal_order")
            //      ->alias('a')
            //     ->field("a.id,a.order_number,a.create_time,a.goods_name,a.goods_quantity,
            //         a.amount_money,a.store_id,a.images_url,a.store_name,a.unit,a.cost,a.enter_all_id")
            //     ->where("store_id", $uniacid)
            //     ->where("audit_status",1)
            //     ->order('id desc')
            //    ->find();
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }
            $data = Db::name('order')
                ->field('parts_order_number,order_create_time,group_concat(id) order_id,status,special_id,order_quantity,coupon_id,goods_id')
                ->where('member_id', $member_id)
                ->order('order_create_time', 'desc')
                ->group('parts_order_number')
                ->select();
            foreach ($data as $key=>$value) {
                 //判断未支付订单
                 if($value['status']=='1'){   //未支付订单
                        $time=time()-$value['order_create_time']-30*60;
                        if($time>0){
                            //返回优惠券
                            if($value['coupon_id']>0){    //使用
                                db('coupon')->where('id',$value['coupon_id'])->setDec('use_number',1);
                            }
                            //删除订单，并返回库存
                            //1.返回库存
                            if($value['special_id']=='0'){
                                //单规格商品
                                db('goods')->where('id',$value['goods_id'])->setInc('goods_repertory',$value['order_quantity']);
                            }else{
                                //多规格商品
                                db('special')->where('id',$value['special_id'])->setInc('stock',$value['order_quantity']);
                            }
                            //2.删除订单
                            $rr=db('order')->where('parts_order_number',$value['parts_order_number'])->delete();
                            unset($data[$key]);
                            continue;
                        }
                 }

                if (strpos($value["order_id"], ",")) {
                    $order_id = explode(',', $value["order_id"]);
                    foreach ($order_id as $k=>$v){
                        $return_data_info[] = Db::name('order')
                            ->where('id', $v)
                            ->where('member_id', $member_id)
                            ->order('order_create_time', 'desc')
                            ->find();
                    }
                    foreach ($return_data_info as $ke => $item) {
                        $parts_order_number_all[$ke] = $item['parts_order_number'];
                    }
                    $unique_order_number = array_merge(array_unique($parts_order_number_all));

                    foreach ( $unique_order_number as $da_k =>$da_v){
                        $order_data['info'][$da_k] = Db::name('order')
                            ->where('member_id', $member_id)
                            ->where('parts_order_number', $da_v)
                            ->order('order_create_time', 'desc')
                            ->select();
                        $names = Db::name("order")
                            ->where("parts_order_number", $da_v)
                            ->where("member_id", $member_id)
                            ->find();
                        $order_data['status'][$da_k] = $names['status'];
                        $order_data['order_type'][$da_k] = $names['order_type'];
                        $order_data["parts_order_number"][$da_k] = $names["parts_order_number"];
                        $order_data["all_order_real_pay"][$da_k] = $names["order_real_pay"];
                        $order_data["order_create_time"][$da_k] = $names["order_create_time"];
                        foreach ($order_data["info"] as $kk => $vv) {
                            $order_data["all_numbers"][$kk] = array_sum(array_map(create_function('$vals', 'return $vals["order_quantity"];'), $vv));
                        }
                    }
                } else {
                    $return_data = Db::name('order')
                        ->where('id', $value['order_id'])
                        ->find();
                    $data_information["all_order_real_pay"][] = $return_data["order_real_pay"];
                    $data_information["all_numbers"][] = $return_data["order_quantity"];
                    $data_information['status'][] = $return_data['status'];
                    $data_information['order_type'][] = $return_data['order_type'];
                    $data_information['parts_order_number'][] = $return_data['parts_order_number'];
                    $data_information['order_create_time'][] = $value['order_create_time'];
                    $data_information['all'][] = Db::name('order')
                        ->where('id', $value['order_id'])
                        ->find();
                }
            }
            if(!empty($order_data)){

                //所有信息
                foreach ($order_data["info"] as $i=>$j){
                    if(!empty($j)){
                        $new_arr[] =$j;
                    }
                }
                foreach ($new_arr as $i=>$j){
                    $end_info[$i]["info"] =$j;
                }
                //状态值
                foreach ($order_data['status'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_status[] = $j;
                    }
                }
                //订单类型
                foreach ($order_data['order_type'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_status[] = $j;
                    }
                }
                foreach ($new_arr_status as $i=>$j){
                    $end_info[$i]['status'] = $j;
                }
                //实际支付的金额
                foreach ($order_data['all_order_real_pay'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_pay[] =$j;
                    }
                }
                foreach ($new_arr_pay as $i=>$j){
                    $end_info[$i]['all_order_real_pay'] = $j;
                }
                //总数量
                foreach ($order_data['all_numbers'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_all_numbers[] =$j;
                    }
                }
                foreach ($new_arr_all_numbers as $i=>$j){
                    $end_info[$i]['all_numbers'] = $j;
                }

                //订单编号
                foreach ($order_data['parts_order_number'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_all_order_number[] =$j;
                    }
                }
                foreach ($new_arr_all_order_number as $i=>$j){
                    $end_info[$i]['parts_order_number'] = $j;
                }

                //订单创建时间
                foreach ($order_data['order_create_time'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_order_create_time[] =$j;
                    }
                }
                foreach ($new_arr_order_create_time as $i=>$j){
                    $end_info[$i]['order_create_times'] = $j;
                }
            }
            if(!empty($data_information)){
                if(!empty($new_arr)){
                    $count =count($new_arr);
                }else{
                    $count =0;
                }
                //支付状态
                foreach ($data_information['status'] as $a=>$b){
                    $end_info[$a+$count]['status'] = $b;
                }
                //总支付
                foreach ($data_information['all_order_real_pay'] as $a=>$b){
                    $end_info[$a+$count]['all_order_real_pay'] = $b;
                }
                //所有数量
                foreach ($data_information['all_numbers'] as $a=>$b){
                    $end_info[$a+$count]['all_numbers'] = $b;
                }
                //订单类型
                foreach ($data_information['order_type'] as $a=>$b){
                    $end_info[$a+$count]['order_type'] = $b;
                }
                //订单编号
                foreach ($data_information['parts_order_number'] as $a=>$b){
                    $end_info[$a+$count]['parts_order_number'] = $b;
                }
                //所有信息

                foreach ($data_information['all'] as $a=>$b){
                    $end_info[$a+$count]['info'][] = $b;
                }
                //创建订单时间
                foreach ($data_information['order_create_time'] as $a=>$b){
                    $end_info[$a+$count]['order_create_times'] = $b;
                }
            }
            // $end_info['test_name']=$da_change;
            if (!empty($end_info)) {
                $ords =array();
                foreach ($end_info as $vl){
                    $ords[] =intval($vl["order_create_times"]);
                }
                array_multisort($ords,SORT_DESC,$end_info);
                return ajax_success('数据', $end_info);
            } else {
                return ajax_error('没数据');
            }

        }
    }

    /**
     **************lilu*******************
     * @param Request $request
     * Notes:我的待支付订单
     **************************************
     * @param Request $request
     */
    public function   ios_api_order_wait_pay(Request $request)
    {
        if ($request->isPost()) {
            $open_id =$request->only("open_id")["open_id"]; //用户open_ID
            $member_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }
            $data = Db::name('order')
                ->field('parts_order_number,order_create_time,group_concat(id) order_id,special_id,order_quantity,goods_id,coupon_id')
                ->where('member_id', $member_id)
                ->where("status",1)
                ->order('order_create_time', 'desc')
                ->group('parts_order_number')
                ->select();
            foreach ($data as $key=>$value) {
                //判断未支付订单
                $time=time()-$value['order_create_time']-30*60;
                if($time>0){
                    //返回优惠券
                    if($value['coupon_id']>0){    //使用
                        db('coupon')->where('id',$value['coupon_id'])->setDec('use_number',1);
                    }
                    // 删除订单，并返回库存
                    // 1.返回库存
                    if($value['special_id']=='0'){
                        //单规格商品
                        db('goods')->where('id',$value['goods_id'])->setInc('goods_repertory',$value['order_quantity']);
                    }else{
                        //多规格商品
                        db('special')->where('id',$value['special_id'])->setInc('stock',$value['order_quantity']);
                    }
                    //2.删除订单
                    $rr=db('order')->where('parts_order_number',$value['parts_order_number'])->delete();
                    unset($data[$key]);
                    continue;
                }
                if (strpos($value["order_id"], ",")) {
                    $order_id = explode(',', $value["order_id"]);
                    foreach ($order_id as $k=>$v){
                        $return_data_info[] = Db::name('order')
                            ->where('id', $v)
                            ->where('member_id', $member_id)
                            ->order('order_create_time', 'desc')
                            ->find();
                    }
                    foreach ($return_data_info as $ke => $item) {
                        $parts_order_number_all[$ke] = $item['parts_order_number'];
                    }
                    $unique_order_number = array_merge(array_unique($parts_order_number_all));

                    foreach ( $unique_order_number as $da_k =>$da_v){
                        $order_data['info'][$da_k] = Db::name('order')
                            ->where('member_id', $member_id)
                            ->where('parts_order_number', $da_v)
                            ->order('order_create_time', 'desc')
                            ->select();
                        $names = Db::name("order")
                            ->where("parts_order_number", $da_v)
                            ->where("member_id", $member_id)
                            ->find();
                        $order_data['status'][$da_k] = $names['status'];
                        $order_data["parts_order_number"][$da_k] = $names["parts_order_number"];
                        $order_data["all_order_real_pay"][$da_k] = $names["order_real_pay"];
                        $order_data["order_create_time"][$da_k] = $names["order_create_time"];
                        foreach ($order_data["info"] as $kk => $vv) {
                            $order_data["all_numbers"][$kk] = array_sum(array_map(create_function('$vals', 'return $vals["order_quantity"];'), $vv));
                        }
                    }
                } else {
                    $return_data = Db::name('order')
                        ->where('id', $value['order_id'])
                        ->find();
                    $data_information["all_order_real_pay"][] = $return_data["order_real_pay"];
                    $data_information["all_numbers"][] = $return_data["order_quantity"];
                    $data_information['status'][] = $return_data['status'];
                    $data_information['parts_order_number'][] = $return_data['parts_order_number'];
                    $data_information['order_create_time'][] = $value['order_create_time'];
                    $data_information['all'][] = Db::name('order')
                        ->where('id', $value['order_id'])
                        ->find();
                }
            }
            if(!empty($order_data)){

                //所有信息
                foreach ($order_data["info"] as $i=>$j){
                    if(!empty($j)){
                        $new_arr[] =$j;
                    }
                }
                foreach ($new_arr as $i=>$j){
                    $end_info[$i]["info"] =$j;
                }
                //状态值
                foreach ($order_data['status'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_status[] = $j;
                    }
                }
                foreach ($new_arr_status as $i=>$j){
                    $end_info[$i]['status'] = $j;
                }
                //实际支付的金额
                foreach ($order_data['all_order_real_pay'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_pay[] =$j;
                    }
                }
                foreach ($new_arr_pay as $i=>$j){
                    $end_info[$i]['all_order_real_pay'] = $j;
                }
                //总数量
                foreach ($order_data['all_numbers'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_all_numbers[] =$j;
                    }
                }
                foreach ($new_arr_all_numbers as $i=>$j){
                    $end_info[$i]['all_numbers'] = $j;
                }

                //订单编号
                foreach ($order_data['parts_order_number'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_all_order_number[] =$j;
                    }
                }
                foreach ($new_arr_all_order_number as $i=>$j){
                    $end_info[$i]['parts_order_number'] = $j;
                }

                //订单创建时间
                foreach ($order_data['order_create_time'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_order_create_time[] =$j;
                    }
                }
                foreach ($new_arr_order_create_time as $i=>$j){
                    $end_info[$i]['order_create_times'] = $j;
                }
            }
            if(!empty($data_information)){
                if(!empty($new_arr)){
                    $count =count($new_arr);
                }else{
                    $count =0;
                }
                //支付状态
                foreach ($data_information['status'] as $a=>$b){
                    $end_info[$a+$count]['status'] = $b;
                }
                //总支付
                foreach ($data_information['all_order_real_pay'] as $a=>$b){
                    $end_info[$a+$count]['all_order_real_pay'] = $b;
                }
                //所有数量
                foreach ($data_information['all_numbers'] as $a=>$b){
                    $end_info[$a+$count]['all_numbers'] = $b;
                }
                //订单编号
                foreach ($data_information['parts_order_number'] as $a=>$b){
                    $end_info[$a+$count]['parts_order_number'] = $b;
                }
                //所有信息

                foreach ($data_information['all'] as $a=>$b){
                    $end_info[$a+$count]['info'][] = $b;
                }
                //创建订单时间
                foreach ($data_information['order_create_time'] as $a=>$b){
                    $end_info[$a+$count]['order_create_times'] = $b;
                }
            }
            if (!empty($end_info)) {
                $ords =array();
                foreach ($end_info as $vl){
                    $ords[] =intval($vl["order_create_times"]);
                }
                array_multisort($ords,SORT_DESC,$end_info);
                return ajax_success('数据', $end_info);
            } else {
                return ajax_error('没数据');
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的待发货订单
     **************************************
     * @param Request $request
     */
    public function   ios_api_order_wait_send(Request $request)
    {
        if ($request->isPost()) {
            $open_id =$request->only("open_id")["open_id"]; //用户open_ID
            $member_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }
            $condition ="`status` = '2' or `status` = '3'";
            $condition2 ="`order_type` = '1' or `order_type` = '2'";
            $data = Db::name('order')
                ->field('parts_order_number,pay_time,group_concat(id) order_id')
                ->where('member_id', $member_id)
                ->where($condition)
                ->where($condition2)
                ->order('pay_time', 'desc')
                ->group('parts_order_number')
                ->select();
            foreach ($data as $key=>$value) {
                if (strpos($value["order_id"], ",")) {
                    $order_id = explode(',', $value["order_id"]);
                    foreach ($order_id as $k=>$v){
                        $return_data_info[] = Db::name('order')
                            ->where('id', $v)
                            ->where('member_id', $member_id)
                            ->find();
                    }
                    foreach ($return_data_info as $ke => $item) {
                        $parts_order_number_all[$ke] = $item['parts_order_number'];
                    }
                    $unique_order_number = array_merge(array_unique($parts_order_number_all));

                    foreach ( $unique_order_number as $da_k =>$da_v){
                        $order_data['info'][$da_k] = Db::name('order')
                            ->where('member_id', $member_id)
                            ->where('parts_order_number', $da_v)
                            ->order('order_create_time', 'desc')
                            ->select();
                        $names = Db::name("order")
                            ->where("parts_order_number", $da_v)
                            ->where("member_id", $member_id)
                            ->find();
                        $order_data['status'][$da_k] = $names['status'];
                        $order_data["parts_order_number"][$da_k] = $names["parts_order_number"];
                        $order_data["all_order_real_pay"][$da_k] = $names["order_real_pay"];
                        $order_data["pay_time"][$da_k] = $names["pay_time"];
                        foreach ($order_data["info"] as $kk => $vv) {
                            $order_data["all_numbers"][$kk] = array_sum(array_map(create_function('$vals', 'return $vals["order_quantity"];'), $vv));
                        }
                    }
                } else {
                    $return_data = Db::name('order')
                        ->where('id', $value['order_id'])
                        ->find();
                    $data_information["all_order_real_pay"][] = $return_data["order_real_pay"];
                    $data_information["all_numbers"][] = $return_data["order_quantity"];
                    $data_information['status'][] = $return_data['status'];
                    $data_information['parts_order_number'][] = $return_data['parts_order_number'];
                    $data_information['pay_time'][] = $value['pay_time'];
                    $data_information['all'][] = Db::name('order')
                        ->where('id', $value['order_id'])
                        ->find();
                }
            }
            if(!empty($order_data)){

                //所有信息
                foreach ($order_data["info"] as $i=>$j){
                    if(!empty($j)){
                        $new_arr[] =$j;
                    }
                }
                foreach ($new_arr as $i=>$j){
                    $end_info[$i]["info"] =$j;
                }
                //状态值
                foreach ($order_data['status'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_status[] = $j;
                    }
                }
                foreach ($new_arr_status as $i=>$j){
                    $end_info[$i]['status'] = $j;
                }
                //实际支付的金额
                foreach ($order_data['all_order_real_pay'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_pay[] =$j;
                    }
                }
                foreach ($new_arr_pay as $i=>$j){
                    $end_info[$i]['all_order_real_pay'] = $j;
                }
                //总数量
                foreach ($order_data['all_numbers'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_all_numbers[] =$j;
                    }
                }
                foreach ($new_arr_all_numbers as $i=>$j){
                    $end_info[$i]['all_numbers'] = $j;
                }

                //订单编号
                foreach ($order_data['parts_order_number'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_all_order_number[] =$j;
                    }
                }
                foreach ($new_arr_all_order_number as $i=>$j){
                    $end_info[$i]['parts_order_number'] = $j;
                }

                //订单创建时间
                foreach ($order_data['pay_time'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_order_create_time[] =$j;
                    }
                }
                foreach ($new_arr_order_create_time as $i=>$j){
                    $end_info[$i]['pay_time'] = $j;
                }
            }
            if(!empty($data_information)){
                if(!empty($new_arr)){
                    $count =count($new_arr);
                }else{
                    $count =0;
                }
                //支付状态
                foreach ($data_information['status'] as $a=>$b){
                    $end_info[$a+$count]['status'] = $b;
                }
                //总支付
                foreach ($data_information['all_order_real_pay'] as $a=>$b){
                    $end_info[$a+$count]['all_order_real_pay'] = $b;
                }
                //所有数量
                foreach ($data_information['all_numbers'] as $a=>$b){
                    $end_info[$a+$count]['all_numbers'] = $b;
                }
                //订单编号
                foreach ($data_information['parts_order_number'] as $a=>$b){
                    $end_info[$a+$count]['parts_order_number'] = $b;
                }
                //所有信息

                foreach ($data_information['all'] as $a=>$b){
                    $end_info[$a+$count]['info'][] = $b;
                }
                //创建订单时间
                foreach ($data_information['pay_time'] as $a=>$b){
                    $end_info[$a+$count]['pay_time'] = $b;
                }
            }
            if (!empty($end_info)) {
                $ords =array();
                foreach ($end_info as $vl){
                    $ords[] =intval($vl["pay_time"]);
                }
                array_multisort($ords,SORT_DESC,$end_info);
                return ajax_success('数据', $end_info);
            } else {
                return ajax_error('没数据');
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的待收货订单
     **************************************
     * @param Request $request
     */
    public function   ios_api_order_wait_deliver(Request $request)
    {
        if ($request->isPost()) {
            $open_id =$request->only("open_id")["open_id"]; //用户open_ID
            $member_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }
            $condition =" `status` = '4' or `status` = '5' ";
            $data = Db::name('order')
                ->field('parts_order_number,order_create_time,group_concat(id) order_id')
                ->where('member_id', $member_id)
                ->where($condition)
                ->order('order_create_time', 'desc')
                ->group('parts_order_number')
                ->select();
            foreach ($data as $key=>$value) {
                if (strpos($value["order_id"], ",")) {
                    $order_id = explode(',', $value["order_id"]);
                    foreach ($order_id as $k=>$v){
                        $return_data_info[] = Db::name('order')
                            ->where('id', $v)
                            ->where('member_id', $member_id)
                            ->order('order_create_time', 'desc')
                            ->find();
                    }
                    foreach ($return_data_info as $ke => $item) {
                        $parts_order_number_all[$ke] = $item['parts_order_number'];
                    }
                    $unique_order_number = array_merge(array_unique($parts_order_number_all));

                    foreach ( $unique_order_number as $da_k =>$da_v){
                        $order_data['info'][$da_k] = Db::name('order')
                            ->where('member_id', $member_id)
                            ->where('parts_order_number', $da_v)
                            ->order('order_create_time', 'desc')
                            ->select();
                        $names = Db::name("order")
                            ->where("parts_order_number", $da_v)
                            ->where("member_id", $member_id)
                            ->find();
                        $order_data['status'][$da_k] = $names['status'];
                        $order_data["parts_order_number"][$da_k] = $names["parts_order_number"];
                        $order_data["all_order_real_pay"][$da_k] = $names["order_real_pay"];
                        $order_data["order_create_time"][$da_k] = $names["order_create_time"];
                        foreach ($order_data["info"] as $kk => $vv) {
                            $order_data["all_numbers"][$kk] = array_sum(array_map(create_function('$vals', 'return $vals["order_quantity"];'), $vv));
                        }
                    }
                } else {
                    $return_data = Db::name('order')
                        ->where('id', $value['order_id'])
                        ->find();
                    $data_information["all_order_real_pay"][] = $return_data["order_real_pay"];
                    $data_information["all_numbers"][] = $return_data["order_quantity"];
                    $data_information['status'][] = $return_data['status'];
                    $data_information['parts_order_number'][] = $return_data['parts_order_number'];
                    $data_information['order_create_time'][] = $value['order_create_time'];
                    $data_information['all'][] = Db::name('order')
                        ->where('id', $value['order_id'])
                        ->find();
                }
            }
            if(!empty($order_data)){

                //所有信息
                foreach ($order_data["info"] as $i=>$j){
                    if(!empty($j)){
                        $new_arr[] =$j;
                    }
                }
                foreach ($new_arr as $i=>$j){
                    $end_info[$i]["info"] =$j;
                }
                //状态值
                foreach ($order_data['status'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_status[] = $j;
                    }
                }
                foreach ($new_arr_status as $i=>$j){
                    $end_info[$i]['status'] = $j;
                }
                //实际支付的金额
                foreach ($order_data['all_order_real_pay'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_pay[] =$j;
                    }
                }
                foreach ($new_arr_pay as $i=>$j){
                    $end_info[$i]['all_order_real_pay'] = $j;
                }
                //总数量
                foreach ($order_data['all_numbers'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_all_numbers[] =$j;
                    }
                }
                foreach ($new_arr_all_numbers as $i=>$j){
                    $end_info[$i]['all_numbers'] = $j;
                }

                //订单编号
                foreach ($order_data['parts_order_number'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_all_order_number[] =$j;
                    }
                }
                foreach ($new_arr_all_order_number as $i=>$j){
                    $end_info[$i]['parts_order_number'] = $j;
                }

                //订单创建时间
                foreach ($order_data['order_create_time'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_order_create_time[] =$j;
                    }
                }
                foreach ($new_arr_order_create_time as $i=>$j){
                    $end_info[$i]['order_create_times'] = $j;
                }
            }
            if(!empty($data_information)){
                if(!empty($new_arr)){
                    $count =count($new_arr);
                }else{
                    $count =0;
                }
                //支付状态
                foreach ($data_information['status'] as $a=>$b){
                    $end_info[$a+$count]['status'] = $b;
                }
                //总支付
                foreach ($data_information['all_order_real_pay'] as $a=>$b){
                    $end_info[$a+$count]['all_order_real_pay'] = $b;
                }
                //所有数量
                foreach ($data_information['all_numbers'] as $a=>$b){
                    $end_info[$a+$count]['all_numbers'] = $b;
                }
                //订单编号
                foreach ($data_information['parts_order_number'] as $a=>$b){
                    $end_info[$a+$count]['parts_order_number'] = $b;
                }
                //所有信息

                foreach ($data_information['all'] as $a=>$b){
                    $end_info[$a+$count]['info'][] = $b;
                }
                //创建订单时间
                foreach ($data_information['order_create_time'] as $a=>$b){
                    $end_info[$a+$count]['order_create_times'] = $b;
                }
            }
            if (!empty($end_info)) {
                $ords =array();
                foreach ($end_info as $vl){
                    $ords[] =intval($vl["order_create_times"]);
                }
                array_multisort($ords,SORT_DESC,$end_info);
                return ajax_success('数据', $end_info);
            } else {
                return ajax_error('没数据');
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我待评价订单
     **************************************
     * @param Request $request
     */
    public function   ios_api_order_wait_evaluate(Request $request)
    {
        if ($request->isPost()) {
            $open_id =$request->only("open_id")["open_id"]; //用户open_ID
            $member_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }
            $condition ="`status` = '6' or `status` = '7'";
            $data = Db::name('order')
                ->field('parts_order_number,order_create_time,group_concat(id) order_id,collect_goods_time')
                ->where('member_id', $member_id)
                ->where($condition)
                ->order('order_create_time', 'asc')
                ->group('parts_order_number')
                ->select();
            foreach ($data as $key=>$value) {
                //判断订单是否超过系统配置的评价时间
                $store_id=Session::get('store_id');
                $setting=db('order_setting')->where('store_id',$store_id)->find();
                $time=time()-$setting['start_evaluate_time']*24*60*60-$value['collect_goods_time'];
                if($time>0){   
                    // 超过规定的时间未评价,订单状态修改成已完成
                    $re=db('order')->where('parts_order_number',$value['parts_order_number'])->update(['status'=>8]);
                }
                if (strpos($value["order_id"], ",")) {
                    $order_id = explode(',', $value["order_id"]);
                    foreach ($order_id as $k=>$v){
                        $return_data_info[] = Db::name('order')
                            ->where('id', $v)
                            ->where('member_id', $member_id)
                            ->order('order_create_time', 'desc')
                            ->find();
                    }
                    foreach ($return_data_info as $ke => $item) {
                        $parts_order_number_all[$ke] = $item['parts_order_number'];
                    }
                    $unique_order_number = array_merge(array_unique($parts_order_number_all));

                    foreach ( $unique_order_number as $da_k =>$da_v){
                        $order_data['info'][$da_k] = Db::name('order')
                            ->where('member_id', $member_id)
                            ->where('parts_order_number', $da_v)
                            ->order('order_create_time', 'desc')
                            ->select();
                        $names = Db::name("order")
                            ->where("parts_order_number", $da_v)
                            ->where("member_id", $member_id)
                            ->find();
                        $order_data['status'][$da_k] = $names['status'];
                        $order_data["parts_order_number"][$da_k] = $names["parts_order_number"];
                        $order_data["all_order_real_pay"][$da_k] = $names["order_real_pay"];
                        $order_data["order_create_time"][$da_k] = $names["order_create_time"];
                        foreach ($order_data["info"] as $kk => $vv) {
                            $order_data["all_numbers"][$kk] = array_sum(array_map(create_function('$vals', 'return $vals["order_quantity"];'), $vv));
                        }
                    }
                } else {
                    $return_data = Db::name('order')
                        ->where('id', $value['order_id'])
                        ->find();
                    $data_information["all_order_real_pay"][] = $return_data["order_real_pay"];
                    $data_information["all_numbers"][] = $return_data["order_quantity"];
                    $data_information['status'][] = $return_data['status'];
                    $data_information['parts_order_number'][] = $return_data['parts_order_number'];
                    $data_information['order_create_time'][] = $value['order_create_time'];
                    $data_information['all'][] = Db::name('order')
                        ->where('id', $value['order_id'])
                        ->find();
                }
            }
            if(!empty($order_data)){

                //所有信息
                foreach ($order_data["info"] as $i=>$j){
                    if(!empty($j)){
                        $new_arr[] =$j;
                    }
                }
                foreach ($new_arr as $i=>$j){
                    $end_info[$i]["info"] =$j;
                }
                //状态值
                foreach ($order_data['status'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_status[] = $j;
                    }
                }
                foreach ($new_arr_status as $i=>$j){
                    $end_info[$i]['status'] = $j;
                }
                //实际支付的金额
                foreach ($order_data['all_order_real_pay'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_pay[] =$j;
                    }
                }
                foreach ($new_arr_pay as $i=>$j){
                    $end_info[$i]['all_order_real_pay'] = $j;
                }
                //总数量
                foreach ($order_data['all_numbers'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_all_numbers[] =$j;
                    }
                }
                foreach ($new_arr_all_numbers as $i=>$j){
                    $end_info[$i]['all_numbers'] = $j;
                }

                //订单编号
                foreach ($order_data['parts_order_number'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_all_order_number[] =$j;
                    }
                }
                foreach ($new_arr_all_order_number as $i=>$j){
                    $end_info[$i]['parts_order_number'] = $j;
                }

                //订单创建时间
                foreach ($order_data['order_create_time'] as $i => $j) {
                    if(!empty($j)){
                        $new_arr_order_create_time[] =$j;
                    }
                }
                foreach ($new_arr_order_create_time as $i=>$j){
                    $end_info[$i]['order_create_times'] = $j;
                }
            }
            if(!empty($data_information)){
                if(!empty($new_arr)){
                    $count =count($new_arr);
                }else{
                    $count =0;
                }
                //支付状态
                foreach ($data_information['status'] as $a=>$b){
                    $end_info[$a+$count]['status'] = $b;
                }
                //总支付
                foreach ($data_information['all_order_real_pay'] as $a=>$b){
                    $end_info[$a+$count]['all_order_real_pay'] = $b;
                }
                //所有数量
                foreach ($data_information['all_numbers'] as $a=>$b){
                    $end_info[$a+$count]['all_numbers'] = $b;
                }
                //订单编号
                foreach ($data_information['parts_order_number'] as $a=>$b){
                    $end_info[$a+$count]['parts_order_number'] = $b;
                }
                //所有信息

                foreach ($data_information['all'] as $a=>$b){
                    $end_info[$a+$count]['info'][] = $b;
                }
                //创建订单时间
                foreach ($data_information['order_create_time'] as $a=>$b){
                    $end_info[$a+$count]['order_create_times'] = $b;
                }
            }
            if (!empty($end_info)) {
                $ords =array();
                foreach ($end_info as $vl){
                    $ords[] =intval($vl["order_create_times"]);
                }
                array_multisort($ords,SORT_DESC,$end_info);
                return ajax_success('数据', $end_info);
            } else {
                return ajax_error('没数据');
            }

        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:订单详情
     **************************************
     */
    public function order_details(Request $request){
        if($request->isPost()){
            $status =$request->only(["status"])["status"];//订单状态
            $parts_order_number =$request->only(["parts_order_number"])["parts_order_number"];//订单编号
            $data =Db::name("order")
                ->where("status",$status)
                ->where("parts_order_number",$parts_order_number)
                ->select();
            if($data[0]["order_type"] ==1){
                    $order_type ="直邮";
                    $name =$data[0]["harvester"];
                    $phone_num =$data[0]["harvest_phone_num"];
                    $address =$data[0]["harvester_address"];
            }else if($data[0]["order_type"] ==2){
                $order_type ="自提";
                $name =$data[0]["harvester"];
                $phone_num =$data[0]["harvest_phone_num"];
                $address =$data[0]["harvester_address"];
            }else if($data[0]["order_type"] ==3){
                $order_type ="存茶";
                $name =$data[0]["harvester"];
                $phone_num =$data[0]["harvest_phone_num"];
                $address =$data[0]["harvester_address"];
            }
            if(!empty($data)){
                $datas =[
                    "data"=>$data,
                    "status"=>$status,
                    "parts_order_number"=>$parts_order_number,
                    "create_time"=>$data[0]["order_create_time"],
                    "pay_time"=>$data[0]["pay_time"],
                    "order_type_name"=>$order_type,
                    "order_type"=>$data[0]["order_type"],
                    "name"=>$name,
                    "phone_num"=>$phone_num,
                    "address"=>$address
                ];
                return ajax_success("详情数据返回成功",$datas);
            }else{
                return ajax_error("没有数据返回",["status"=>0]);
            }

        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:订单状态修改（买家确认收货）
     **************************************
     * @param Request $request
     */
    public function ios_api_order_collect_goods(Request $request){
        if($request->isPost()){
            $open_id =$request->only("open_id")["open_id"]; //用户open_ID
            $member_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }
            $parts_order_number =$request->only("parts_order_number")["parts_order_number"];//订单编号
            if(!empty($parts_order_number)){
                $res =Db::name("order")
                    ->where("member_id",$member_id)
                    ->where("parts_order_number",$parts_order_number)
                    ->select();
                if(!empty($res)){
                    foreach($res as $k=>$v){
                        $data =[
                            "status"=>7,
                            "collect_goods_time"=>time()
                        ];
                        $bool =Db::name("order")->where("id",$v["id"])->update($data);
                    }
                    if($bool){
                        return ajax_success("确认收货成功",["status"=>1]);
                    }else{
                        return ajax_error("确认收货失败",["status"=>0]);
                    }
                }
            }else{
                return ajax_error("所传参数不能为空",["status"=>0]);
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:买家删除订单接口(ajax)
     **************************************
     * @param Request $request
     */
    public function  ios_api_order_del(Request $request){
        if($request->isPost()){
            $open_id =$request->only("open_id")["open_id"]; //用户open_ID
            $member_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }
            $parts_order_number =$request->only("parts_order_number")["parts_order_number"];//订单编号
            if(!empty($parts_order_number)){
                $res =Db::name("order")
                    ->where("parts_order_number",$parts_order_number)
                    ->where("member_id",$member_id)
                    ->select();
                if(!empty($res)){
                    foreach($res as $k=>$v){
                        $bool =Db::name("order")
                            ->where("id",$v["id"])
                            ->delete();
                    }
                    if($bool){
                        return ajax_success("删除成功",["status"=>1]);
                    }else{
                        return ajax_error("删除失败",["status"=>0]);
                    }
                }
            }else{
                return ajax_error("所传参数不能为空",["status"=>0]);
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:订单状态修改（未付款买家取消订单）
     **************************************
     * @param Request $request
     */
    public function ios_api_order_no_pay_cancel(Request $request){
        if($request->isPost()){
            $open_id =$request->only("open_id")["open_id"]; //用户open_ID
            $member_id =Db::name("member")->where("member_openid",$open_id)->value("member_id");
            if(empty($member_id)){
                exit(json_encode(array("status" => 2, "info" => "请重新登录","data"=>["status"=>0])));
            }
            $cancel_order_description =$request->only('cancel_order_description')["cancel_order_description"];//取消原因
            $parts_order_number =$request->only("parts_order_number")["parts_order_number"];//订单编号
            if(!empty($parts_order_number)){
                $res =Db::name("order")
                    ->where("parts_order_number",$parts_order_number)
                    ->where("member_id",$member_id)
                    ->select();
                if(!empty($res)){
                    foreach($res as $k=>$v){
                        if($v['coupon_id']>0){
                            db('coupon')->where('id',$v['coupon_id'])->setDec('use_number',1);
                            $pp['msg']='111';
                            db('test')->insert($pp);
                        }
                        $data =[
                            "status"=>9,
                            "coupon_id"=>0,
                            "cancel_order_description"=>$cancel_order_description
                        ];
                        $bool =Db::name("order")->where("id",$v["id"])->update($data);
                    }
                    if($bool){
                        exit(json_encode(array("status" => 1, "info" => "取消成功","data"=>["status"=>1])));
                    }else{
                        exit(json_encode(array("status" => 0, "info" => "取消失败","data"=>["status"=>0])));
                    }
                }
            }else{
                return ajax_error("所传参数不能为空",["status"=>0]);
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序活动支付成功回来修改状态
     **************************************
     */

    public function notify(){
        include EXTEND_PATH."WxpayAPI/lib/WxPay.Data.php";
        include EXTEND_PATH."WxpayAPI/lib/WxPay.Notify.php";
        include EXTEND_PATH."WxpayAPI/lib/WxPay.Api.php";
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xml_data), true);
        if($val["result_code"] == "SUCCESS" ){
//            file_put_contents(EXTEND_PATH."data.txt",$val);
            $res = Db::name("activity_order")
                ->where("parts_order_number",$val["out_trade_no"])
                ->update(["status"=>2]);
            $activity = Db::name("activity_order")->where("parts_order_number",$val["out_trade_no"])->find();
            $day_array = Db::name("teahost")->where("id",$activity['teahost_id'])->find();
            $new_array = explode(",",$day_array["day_array"]);
            $index = $activity['index'];
            $new_array[$index] = $new_array[$index]-1;
            $intest = implode(",",$new_array);
            $peoples = $day_array["peoples"] + 1;
            $rest_order = Db::name("teahost")->where("id",$activity['teahost_id'])->update(["day_array"=>$intest,"peoples"=>$peoples]);
            
            if($res){
                //做消费记录
                $information =Db::name("activity_order")
                    ->field("member_openid,cost_moneny,activity_name")
                    ->where("parts_order_number",$val["out_trade_no"])
                    ->find();
                $user_information =Db::name("member")
                    ->field("member_id,member_wallet,member_recharge_money")
                    ->where("member_openid",$information["member_openid"])
                    ->find();
                $now_money =$user_information["member_wallet"]+$user_information["member_recharge_money"];
                $datas=[
                    "user_id"=>$user_information["member_id"],//用户ID
                    "wallet_operation"=> $information["cost_moneny"],//消费金额
                    "wallet_type"=>-1,//消费操作(1入，-1出)
                    "operation_time"=>date("Y-m-d H:i:s"),//操作时间
                    "operation_linux_time"=>time(), //操作时间
                    "wallet_remarks"=>"订单号：".$val["out_trade_no"]."，微信消费".$information["cost_moneny"]."元",//消费备注
                    "wallet_img"=>" ",//图标
                    "title"=>$information["activity_name"],//标题（消费内容）
                    "order_nums"=>$val["out_trade_no"],//订单编号
                    "pay_type"=>"小程序", //支付方式/
                    "wallet_balance"=>$now_money,//此刻钱包余额
                ];
                Db::name("wallet")->insert($datas); //存入消费记录表
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }else{
                return ajax_error("失败",$val["out_trade_no"]);
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序订单支付成功回来修改状态
     **************************************
     */
    public function order_notify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xml_data), true);
        if($val["result_code"] == "SUCCESS" ){

            $order_type = Db::name("order")->where("parts_order_number",$val["out_trade_no"])->find();
            if($order_type['order_type'] == 2){
                $status = 5;
            } else {
                $status = 2;
            }
            file_put_contents(EXTEND_PATH."data.txt",$val);
            $res = Db::name("order")
                ->where("parts_order_number",$val["out_trade_no"])
                ->update(["status"=>$status,"pay_time"=>time(),"si_pay_type"=>2]);

            $host_rest = Db::name("house_order")
            ->where("parts_order_number",$val["out_trade_no"])
            ->update(["status"=>3,"pay_time"=>time(),"si_pay_type"=>2]);
            


            if($res){
                $order = GoodsOrder::getOrderInforMation($order_type);
                $model = OrderModel::grantMoney($order);
                //商品库存减少、销量增加
                $goods_order = Db::name("order") 
                ->where("parts_order_number",$val["out_trade_no"])
                ->field("goods_id,order_quantity,special_id")
                ->select();

                foreach($goods_order as $k => $v){
                    if($goods_order[$k]['special_id'] != 0){
                        $boolw = Db::name('special')->where('id',$goods_order[$k]['special_id'])->setInc('volume',$goods_order[$k]['order_quantity']);
                        //按照需求下单即减库存,付款时间超过30分钟恢复库存
                        $booles = Db::name('special')->where('id',$goods_order[$k]['special_id'])->setDec('stock',$goods_order[$k]['order_quantity']);
                    } else {
                        //按照需求下单即减库存,付款时间超过30分钟恢复库存
                        $boolwtt = Db::name('goods')->where('id',$goods_order[$k]['goods_id'])->setDec('goods_repertory',$goods_order[$k]['order_quantity']);
                        $booltt = Db::name('goods')->where('id',$goods_order[$k]['goods_id'])->setInc('goods_volume',$goods_order[$k]['order_quantity']);
                    }
                }
                //做消费记录
                $information = Db::name("order")->field("member_id,order_real_pay,parts_goods_name")->where("parts_order_number",$val["out_trade_no"])->find();
                $user_information =Db::name("member")
                    ->field("member_wallet,member_recharge_money")
                    ->where("member_id",$information["member_id"])
                    ->find();
                $now_money =$user_information["member_wallet"]+$user_information["member_recharge_money"];
                $datas=[
                    "user_id"=>$information["member_id"],//用户ID
                    "wallet_operation"=> $information["order_real_pay"],//消费金额
                    "wallet_type"=>-1,//消费操作(1入，-1出)
                    "operation_time"=>date("Y-m-d H:i:s"),//操作时间
                    "operation_linux_time"=>time(), //操作时间
                    "wallet_remarks"=>"订单号：".$val["out_trade_no"]."，微信消费".$information["order_real_pay"]."元",//消费备注
                    "wallet_img"=>" ",//图标
                    "title"=>$information["parts_goods_name"],//标题（消费内容）
                    "order_nums"=>$val["out_trade_no"],//订单编号
                    "pay_type"=>"小程序", //支付方式/
                    "wallet_balance"=>$now_money,//此刻钱包余额
                ];
                Db::name("wallet")->insert($datas); //存入消费记录表



                $all_money = db("order")->where("parts_order_number",$val["out_trade_no"])->value("order_real_pay");//实际支付的金额
                $member_id = db("order")->where("parts_order_number",$val["out_trade_no"])->value("member_id");//会员id
                $coin = db("recommend_integral")->where("id",1)->value("coin"); //消费满多少送积分金额条件
                $integral = db("recommend_integral")->where("id",1)->value("consume_integral"); //消费满多少送多少积分
                //消费满多少金额赠送多少积分
                if( $all_money > $coin){
                    $rest = db("member")->where("member_id",$member_id)->setInc('member_integral_wallet',$integral);//满足条件则增加积分
                    $many = db("member")->where("member_id",$member_id)->value("member_integral_wallet");//获取所有积分
                    //插入积分记录
                    $integral_data = [
                        "member_id" => $member_id,
                        "integral_operation" => $integral,//获得积分
                        "integral_balance" => $many,//积分余额
                        "integral_type" => 1, //积分类型（1获得，-1消费）
                        "operation_time" => date("Y-m-d H:i:s"), //操作时间
                        "integral_remarks" => "消费满" . $coin . "送".$integral."积分",
                    ];
                    Db::name("integral")->insert($integral_data);
                }
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }else{
                return ajax_error("失败");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序充值支付成功回来处理数据
     **************************************
     */
    public function recharge_notify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xml_data), true);
        if($val["result_code"] == "SUCCESS" ){
//          file_put_contents(EXTEND_PATH."data.txt",$val);
            $data['status'] = 1;
            $data['pay_time'] = time();
            $data['pay_type_name'] = "微信";
            $condition['recharge_order_number'] = $val["out_trade_no"];
            $res = Db::name("recharge_record")
                ->where($condition)
                ->update($data);
            if($res > 0){
                //进行钱包消费记录
                $parts =Db::name("recharge_record")
                ->field("recharge_money,user_id,upgrade_id")
                    ->where($condition)
                    ->find(); 
                $title ="余额充值";
                $money = $parts["recharge_money"];//金额
                $recharge_record_data = Db::name("recharge_record")
                    ->where("recharge_order_number",$val["out_trade_no"])
                    ->find();
                //充值送积分
                $member_send = Db::name("member_grade")->where("member_grade_id",$parts['upgrade_id'])->find();
                $list = Db::name("recharge_full_setting")
                    ->field("recharge_setting_send_integral,recharge_setting_full_money")
                    ->select();
                $lists =0;
                foreach($list as $k=>$v){
                    if($v["recharge_setting_full_money"] ==$recharge_record_data["recharge_money"]){
                        $lists =$v["recharge_setting_send_integral"];
                    }
                }
                //如果达到充值送积分条件
                if(!empty($lists)){
                    $recharge_data = [
                        "user_id" =>$parts["user_id"],//用户id
                        "operation_time"=>date("Y-m-d H:i:s"),//操作时间
                        "operation_linux_time"=>time(),//操作时间
                        "operation_type"=>1,//充值为1，提现为负一
                        "pay_type_content"=>$recharge_record_data["pay_type_name"],//支付方式
                        "money_status"=>1 , //到款状态（1到账，2未到款）
                        "img_url"=>" ", //对应的图片链接
                        "operation_amount" =>$recharge_record_data["recharge_money"], //操作金额
                        "recharge_describe" =>"充值".$recharge_record_data["recharge_money"]."元,送了".$lists."积分",//描述
                        "status"=>1,
                        "is_able_withdrawal"=>1
                    ];
                    Db::name("recharge_reflect")->insert($recharge_data);//插到记录
                    //充值剩下的余额
                    $user_wallet =Db::name("member")
                        ->field("member_recharge_money")
                        ->where("member_id",$recharge_record_data["user_id"])
                        ->find();
                    //更新充值的余额
                    Db::name("member")->where("member_id",$recharge_record_data["user_id"])
                        ->update(["member_recharge_money"=>$user_wallet["member_recharge_money"]+$recharge_record_data["recharge_money"]]);
                    //插入积分记录
                    if($recharge_record_data['upgrade_id']<0){
                        Db::name("member")
                       ->where("member_id",$recharge_record_data["user_id"])
                       ->setInc('member_integral_wallet',$lists);//满足条件则增加积分
                    }
                    $integral_res = Db::name("member")
                        ->where("member_id",$recharge_record_data["user_id"])
                        ->value("member_integral_wallet");//获取所有积分
                    $integral_data = [
                        "member_id" => $recharge_record_data["user_id"],
                        "integral_operation" => $lists,//获得积分
                        "integral_balance" => $integral_res,//积分余额
                        "integral_type" => 1, //积分类型（1获得，-1消费）
                        "operation_time" => date("Y-m-d H:i:s"), //操作时间
                        "integral_remarks" => "充值满" . $money . "送".$lists."积分",
                    ];
                    Db::name("integral")->insert($integral_data);
                }
                $new_wallet =Db::name("member")
                    ->where("member_id",$recharge_record_data["user_id"])
                    ->value("member_recharge_money");
                $datas=[
                    "user_id"=>$parts["user_id"],//用户ID
                    "wallet_operation"=> $money,//消费金额
                    "wallet_type"=>1,//消费操作(1入，-1出)
                    "operation_time"=>date("Y-m-d H:i:s"),//操作时间
                    "operation_linux_time"=>time(), //操作时间
                    "wallet_remarks"=>"订单号：".$val["out_trade_no"]."，充值，余额增加".$money."元,送".$lists."积分",//消费备注
                    "wallet_img"=>" ",//图标
                    "title"=>$title,//标题（消费内容）
                    "order_nums"=>$val["out_trade_no"],//订单编号
                    "pay_type"=>"小程序", //支付方式/
                    "wallet_balance"=>$new_wallet,//此刻钱包余额
                ];
                Db::name("wallet")->insert($datas); //存入消费记录表
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }else{
                return "fail";
            }
        }
    }



       /**
     **************李火生*******************
     * @param Request $request
     * Notes:打赏订单支付成功回来修改状态
     **************************************
     */
    public function reward_notify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xml_data), true);
        if($val["result_code"] == "SUCCESS" ){
//             file_put_contents(EXTEND_PATH."data.txt",$val);
            $res = Db::name("reward")
                ->where("order_number",$val["out_trade_no"])
                ->update(["status"=>2,"pay_time"=>time()]);
            if($res){
                //做消费记录
                $information =Db::name("reward")->field("money,order_number,crowd_name,member_id")->where("order_number",$val["out_trade_no"])->find();
                //需要前端添加一个商品id
                $rest_one = Db::name("crowd_goods")->where("id",$information['goods_id'])->setInc('collecting_money',$information['money']);
                $rest_two = Db::name("crowd_goods")->where("id",$information['goods_id'])->setInc('collecting');
                $member_wallet =Db::name("member")
                    ->where("member_id",$information["member_id"])
                    ->value('member_wallet');
                $datas= [
                    "user_id"=>$information["member_id"],//用户ID
                    "wallet_operation"=> $information["money"],//消费金额
                    "wallet_type"=>-1,//消费操作(1入，-1出)
                    "operation_time"=> date("Y-m-d H:i:s"),//操作时间
                    "operation_linux_time"=>time(), //操作时间
                    "wallet_remarks"=>"订单号：".$val["out_trade_no"]."众筹打赏".$information["money"]."元",//消费备注
                    "wallet_img"=>" ",//图标
                    "title"=>$information["crowd_name"],//标题（消费内容）
                    "order_nums"=>$val["out_trade_no"],//订单编号
                    "pay_type"=>"小程序", //支付方式/
                    "wallet_balance"=>$member_wallet,//此刻钱包余额
                ];
                Db::name("wallet")->insert($datas); //存入消费记录表
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }else{
                return ajax_error("失败");
            }
        }
    }



    /**
     **************郭杨*******************
     * @param Request $request
     * Notes:默认存茶收货地址
     **************************************
     */
    public function tacitly_approve(Request $request){
        if($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $data =Db::name("store_house")
                ->where("label",1)
                ->where("store_id",$store_id)
                ->find();
            if(!empty($data)){
                $data["unit"] = explode(",",$data["unit"]);
                $data["cost"] = explode(",",$data["cost"]);
                return ajax_success("返回成功",$data);
            }else{
                return ajax_error("没有默认收货地址");
            }
        }
    }


        /**
     **************郭杨*******************
     * @param Request $request
     * Notes:默认存茶收货地址列表
     **************************************
     */
    public function tacitly_list(Request $request){
        if($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $data =Db::name("store_house")->where("store_id",'EQ',$store_id)
                ->order("label desc")
                ->select();

            if(!empty($data)){
                foreach($data as $key => $value){
                    $data[$key]["unit"] = explode(",",$data[$key]["unit"]);
                    $data[$key]["cost"] = explode(",",$data[$key]["cost"]);
                }
                return ajax_success("返回成功",$data);
            }else{
                return ajax_error("没有默认收货地址");
            }
        }
    }


    /**
     **************郭杨*******************
     * @param Request $request
     * Notes:运费
     **************************************
     */
    public function transportation(Request $request){
        if($request->isPost()){
            $goods_id = $request->only("goods_id")["goods_id"];//商品id
            $are = $request->only("are")["are"];//地区
            $standard = $request->only("goods_standard_id")["goods_standard_id"];//规格id
            $res = array();
            if(!empty($goods_id)){
                foreach($goods_id as $key => $value){
                    $goods = db("goods")->where("id",$value)->find();
                    if($goods["goods_standard"] == 1){
                        $goods["monomer"] = db("special")->where("id",$standard[$key])->value("offer");
                    }
                    $data["goods_id"] = $value;
                    if($goods['goods_franking'] != 0){
                        $data["collect"] = $goods["goods_franking"]; //统一邮费
                        $data["markup"] = 0; //统一邮费
                    }else{
                        $templet_name = explode(",",$goods["templet_name"]);
                        $templet_id = explode(",",$goods["templet_id"]);
                        $monomer = $goods["monomer"];
                        $tempid = array_search($monomer,$templet_name);
                        $express_id = $templet_id[$tempid];
                        $rest = db("express")->where("id",$express_id)->find();
                        if(!empty($rest)){
                            $are_block = explode(",",$rest["are"]);
                            if(in_array($are,$are_block)){
                                $data["collect"] = $rest["price"];//首费
                                $data["markup"] = $rest["markup"];//续费
                            } else {
                                $data["collect"] = $rest["price_two"];//首费
                                $data["markup"] = $rest["markup_two"];//续费
                            }
                        } else {
                            return ajax_error("没有运费模板");
                        }
                    }
                    array_push($res,$data);                    
                }
                return ajax_success("返回成功",$res);
            } else {
                return ajax_error("没有运费模板");
            }
        }

    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:小程序会员升级充值支付成功回来处理数据
     **************************************
     */
    public function member_notify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xml_data), true);
        if($val["result_code"] == "SUCCESS" ){
//          file_put_contents(EXTEND_PATH."data.txt",$val);
            $data['status'] = 1;
            $data['pay_time'] = time();
            $data['pay_type_name'] = "微信";
            $res = Db::name("recharge_record")
                ->where('recharge_order_number',$val["out_trade_no"])
                ->update($data);
            if($res){
                $recharge_record_data = Db::name("recharge_record")
                ->where("recharge_order_number",$val["out_trade_no"])
                ->find();
                $title ="会员升级";
                $money = $recharge_record_data["recharge_money"];//金额

                //升级会员等级
                $member_send = Db::name("member_grade")->where("member_grade_id",$recharge_record_data['upgrade_id'])->find();
                $recharge_integral_send = $member_send['recharge_integral_send']; //升级会员的赠送积分
                //充值剩下的余额
                $user_wallet = Db::name("member")
                    ->where("member_id",$recharge_record_data["user_id"])
                    ->find();
                //更新充值的余额
                $integral_res = $user_wallet["member_recharge_money"] + $recharge_record_data["recharge_money"];
                $integral_wallet = $user_wallet["member_integral_wallet"] + $recharge_integral_send;
                $member_update_data = array(
                    "member_recharge_money"=>$integral_res,
                    'member_integral_wallet'=>$integral_wallet,
                    'member_grade_id'=>$recharge_record_data['upgrade_id'],
                    'member_grade_name'=>$member_send['member_grade_name'],
                    'member_grade_create_time'=> time()
                );
                $update = Db::name("member")->where("member_id",$recharge_record_data["user_id"])
                    ->update($member_update_data);

                $integral_data = [
                    "member_id" => $recharge_record_data["user_id"],
                    "integral_operation" => $recharge_integral_send,//获得积分
                    "integral_balance" => $integral_wallet,//积分余额
                    "integral_type" => 1, //积分类型（1获得，-1消费）
                    "operation_time" => date("Y-m-d H:i:s"), //操作时间
                    "integral_remarks" => "充值" . $money . "送".$recharge_integral_send."积分",
                ];
                Db::name("integral")->insert($integral_data);
                
                $datas=[
                    "user_id"=>$recharge_record_data["user_id"],//用户ID
                    "wallet_operation"=> $money,//消费金额
                    "wallet_type"=>1,//消费操作(1入，-1出)
                    "operation_time"=>date("Y-m-d H:i:s"),//操作时间
                    "operation_linux_time"=>time(), //操作时间
                    "wallet_remarks"=>"订单号：".$val["out_trade_no"]."，充值，余额增加".$money."元,送".$recharge_integral_send."积分",//消费备注
                    "wallet_img"=>" ",//图标
                    "title"=> $title,//标题（消费内容）
                    "order_nums"=>$val["out_trade_no"],//订单编号
                    "pay_type"=>"小程序", //支付方式/
                    "wallet_balance"=>$integral_res,//此刻钱包余额
                ];
                Db::name("wallet")->insert($datas); //存入消费记录表
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }else{
                return "fail";
            }
        }
    }

    /**
	 * 
	 * 入仓时单位换算
	 * @param 
	 * @param array $unit  所有单位
	 * @param int $key     单位key值
	 * @param array $num   所有数量
	 * @param int $order_quantity  数量
	 * @return 成功时返回，其他抛异常
	 */
	public  function unit_calculate($unit, $num,$key,$order_quantity)
	{

        //先判断有多少位数量等级
        //然后进行单位换算
        //连接入库
        $order_quantity = intval($order_quantity);
        $length = count($unit);  
        if($length == 3){
            switch($key){
                case 0:
                    //数量一.第一单位,数量二.第二单位,数量三.第三单位
                    $store_number = $order_quantity.','.$unit[$key].','.[$key].','.$unit[$key+1].','.[$key].','.$unit[$key+2];
                    break;
                case 1:
                    $number_one = $unit[$key];              //对应数量单位       
                    $num_one = intval($num[$key]);          //对应数量
                    $number_zero = $unit[$key-1];           //上一级数量单位
                    $num_zero = intval($num[$key-1]);       //上一级数量
                                                            //后一级数量为零

                    $number = $order_quantity/$num_one;     //单位换算
                    if($number > 1){
                        $remainder = fmod($order_quantity , $num_one);//余下的值
                        $store_number = intval($number).','.$number_zero.','.$remainder.','.$number_one.','.($key-1).','.$unit[$key+1];
                    } else if($number == 1){
                        $store_number = intval($number).','.$number_zero.','.($key-1).','.$number_one.','.($key-1).','.$unit[$key+1];
                    } else {
                        $number = 0;
                        $store_number = intval($number).','.$number_zero.','.$order_quantity.','.$number_one.','.$number.','.$unit[$key+1];
                    }
                    break;
                case 2: 
                    $number_two = $unit[$key];               //当前单位
                    $num_two = intval($num[$key]);           //当前数量
                    $number_one = $unit[$key-1];             //上一级等级单位
                    $num_one = intval($num[$key-1]);         //上一级等级数量
                    $number_zero = $unit[$key-2];            //上上一级等级单位
                    $num_zero = intval($num[$key-2]);        //上上一级等级数量
                    $num_among = intval($num_two/$num_one);  //当前数量与上一级数量换算量


                    $rest_zero = $order_quantity/$num_two;    //第一级数量
                    if( $rest_zero > 1){
                        //第一级余数
                        $rest_one = fmod($order_quantity,$num_two); 
                        //判断是否还能再取上一等级
                        $rest_two = $rest_one/$num_among;
                        if($rest_two > 1){
                            $two = fmod($rest_one,$num_among); 
                            $store_number = intval($rest_zero).','.$number_zero.','.intval($rest_two).','.$number_one.','.intval($two).','.$number_two;
                        } else if($rest_two == 1){
                            $two = 0;
                            $store_number = intval($rest_zero).','.$number_zero.','.intval($rest_two).','.$number_one.','.intval($two).','.$number_two;
                        } else {
                            $rest_two = 0;
                            $store_number = intval($rest_zero).','.$number_zero.','.intval($rest_two).','.$number_one.','.intval($rest_one).','.$number_two;
                        }
                        
                    } else if( $rest_zero == 1){
                        $rest_two = 0;
                        $two = 0;
                        $store_number = intval($rest_zero).','.$number_zero.','.intval($rest_two).','.$number_one.','.intval($two).','.$number_two;
                    } else {
                        $rest_zero = 0;
                        $rest_two = 0;
                        $store_number = intval($rest_zero).','.$number_zero.','.intval($rest_two).','.$number_one.','.$order_quantity.','.$number_two;
                    }
                    break;                                                             
            }
        }




        if($length == 2){
            switch($key){
                case 0:
                    //数量一.第一单位,数量二.第二单位
                    $store_number = $order_quantity.','.$unit[$key].','.$key.','.$unit[$key+1];
                    break;
                case 1:
                    $number_one = $unit[$key];      //对应数量单位       
                    $num_one = $num[$key];          //对应数量
                    $number_zero = $unit[$key-1];   //上一级数量单位
                    $num_zero = $num[$key-1];       //上一级数量
                                                    

                    $number = $order_quantity/$num_one;//单位换算
                    if($number > 1){
                        $remainder = fmod($order_quantity,$num_one);//余下的值
                        $store_number = intval($number).','.$number_zero.','.$remainder.','.$number_one;
                    } else if($number == 1){
                        $store_number = intval($number).','.$number_zero.','.($key-1).','.$number_one;
                    } else {
                        $number = 0;
                        $store_number = $number.','.$number_zero.','.$order_quantity.','.$number_one;
                    }
                    break;                                                           
                }
            }
        

        if($length == 1){
            $store_number = $order_quantity.','.$unit[$key];
        }
        return $store_number;
        
    }


    
       /**
     **************郭杨*******************
     * @param Request $request
     * Notes:茶仓订单续费支付回调
     **************************************
     */
    public function series_notify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xml_data), true);
        if($val["result_code"] == "SUCCESS" ){
//             file_put_contents(EXTEND_PATH."data.txt",$val);
            $res = Db::name("series_house_order")
                ->where("series_parts_number",$val["out_trade_no"])
                ->update(["pay_status"=>1,"pay_time"=>time(),"si_pay_type"=>2]);
            if($res){
                //做消费记录
                $information = Db::name("series_house_order")->where("series_parts_number",$val["out_trade_no"])->find();
                $bools =  Db::name("house_order")
                ->where("id",$information['store_house_id'])
                ->update(["end_time"=>$information['never_time']]);

                $member_wallet =Db::name("member")
                    ->where("member_id",$information["member_id"])
                    ->value('member_wallet');
                $datas= [
                    "user_id"=>$information["member_id"],//用户ID
                    "wallet_operation"=> $information["series_price"],//消费金额
                    "wallet_type"=>-1,//消费操作(1入，-1出)
                    "operation_time"=> date("Y-m-d H:i:s"),//操作时间
                    "operation_linux_time"=>time(), //操作时间
                    "wallet_remarks"=>"订单号：".$val["out_trade_no"]."茶仓订单续费".$information["series_price"]."元",//消费备注
                    "wallet_img"=>" ",//图标
                    "title"=>"茶厂订单续费",//标题（消费内容）
                    "order_nums"=>$val["out_trade_no"],//订单编号
                    "pay_type"=>"小程序", //支付方式/
                    "wallet_balance"=>$member_wallet,//此刻钱包余额
                ];
                Db::name("wallet")->insert($datas); //存入消费记录表
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }else{
                return ajax_error("失败");
            }
        }
    }




    /***
     * 出仓订单微信支付回调
     * GY
     */
    
    public function continuAtion_notify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xml_data), true);
        if($val["result_code"] == "SUCCESS" ){
            //file_put_contents(EXTEND_PATH."data.txt",$val);
            $res = Db::name("out_house_order")
                ->where("out_order_number",$val["out_trade_no"])
                ->update(["status"=>2,"pay_time"=>time(),"si_pay_type"=>2]);
            if($res){
                $information = Db::name("out_house_order")->where("out_order_number",$val["out_trade_no"])->find();
                //更新仓库库存
                $house_order = Db::name("house_order")->where("id",$information['house_order_id'])->find();
                //做消费记录
                $stock = $house_order['order_quantity'] - $information['order_quantity'];//剩余仓储量
                $unit = explode(",",$information['unit']);
                $num = explode(",",$information['num']);
                $key = array_search($information['store_unit'],$unit);
                $store_number= $this->unit_calculate($unit,$num,$key,$stock);
                //更新
                $boole = Db::name("house_order")->where("id",$information['house_order_id'])->update(['order_quantity'=>$stock,'store_number'=>$store_number]);
                //配送地址
                $is_address_status =  Db::name("user_address")->where("id",$information['address_id'])->find();
                $harvest_address_city = str_replace(',','',$is_address_status['address_name']);
                $harvest_address = $harvest_address_city.$is_address_status['harvester_real_address']; //收货人地址
                $harvester = $is_address_status['harvester'];
                $harvester_phone_num = $is_address_status['harvester_phone_num'];
                //生成order订单
                $order_data = [
                    'goods_id' => $house_order['goods_id'],
                    'goods_image' => $house_order['goods_image'],//订单号
                    'parts_goods_name' => $house_order['parts_goods_name'],//商品名称
                    'goods_money' => $house_order['goods_money'],//商品价格
                    'order_quantity' => $information['order_quantity'], //出仓数量
                    'order_amount' => $information['house_charges'],   //出仓金额
                    'order_real_pay' => $information['house_charges'] ,//订单实际支付的金额(即优惠券抵扣之后的价钱）
                    'user_account_name' => $house_order['user_account_name'],//用户名
                    'user_phone_number' =>$house_order['user_phone_number'],//用户账号
                    'order_create_time' => $information['pay_time'],//下单时间
                    'harvester_address' => $harvest_address,
                    'status' => 2,
                    'parts_order_number' => $information['out_order_number'],//订单编号
                    'member_id' => $house_order['member_id'],//用户id
                    'pay_time' => $information['pay_time'], //支付时间
                    'goods_standard' => $house_order['goods_standard'],//商品规格
                    'harvester' => $harvester,//收件人
                    'harvest_phone_num' => $harvester_phone_num,//收件人手机
                    'refund_amount' => $information['house_charges'],//可退款金额,
                    'normal_future_time' =>$house_order['normal_future_time'],//订单关闭时间
                    'goods_describe' => $house_order['goods_describe'],//商品买点
                    'special_id' => $house_order['special_id'],//特殊规格id
                    'order_type' => 1,
                    'si_pay_type' => 2,//支付方式（微信）
                    'unit' => $information['store_unit'], //出仓单位
                    'store_id' => $information['store_id'],//店铺id
                    'coupon_type'=> 1,//商品类型
                ];

                $restel = Db::name("order")->insert($order_data);

                if($restel){
                    $member_wallet = Db::name("member")
                        ->where("member_id",$information["member_id"])
                        ->value('member_wallet');
                    $datas= [
                        "user_id"=>$information["member_id"],//用户ID
                        "wallet_operation"=> $information["house_charges"],//消费金额
                        "wallet_type"=>-1,//消费操作(1入，-1出)
                        "operation_time"=> date("Y-m-d H:i:s"),//操作时间
                        "operation_linux_time"=>time(), //操作时间
                        "wallet_remarks"=>"订单号：".$val["out_trade_no"]."茶仓订单出仓".$information["house_charges"]."元",//消费备注
                        "wallet_img"=>" ",//图标
                        "title"=>"茶厂订单续费",//标题（消费内容）
                        "order_nums"=>$val["out_trade_no"],//订单编号
                        "pay_type"=>"小程序", //支付方式/
                        "wallet_balance"=>$member_wallet,//此刻钱包余额
                    ];
                    Db::name("wallet")->insert($datas); //存入消费记录表
                } else {
                    file_put_contents(EXTEND_PATH."data.txt","插入订单表失败");
                }

                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }else{
                return ajax_error("失败");
            }
        }

    }
    /***
     * lilu
     * 小程序立即购买---点击取消，删除已生成的订单
     * @param parts_order_number   订单号
     * @param order_type   订单类型
     */
    public function del_order(){
        //获取参数
        $input=input();
        //判断是否有记录
        if($input['coupon_type'] == 1 ){   
            $re =  db('order')->where('parts_order_number',$input['parts_order_number'])->delete();
            $res = db('house_order')->where('parts_order_number',$input['parts_order_number'])->delete();
        }elseif($input['coupon_type'] == 2){
            $re=db('crowd_order')->where('parts_order_number',$input['parts_order_number'])->delete();
        }elseif($input['coupon_type']=='3'){
            $re=db('reward')->where('order_number',$input['parts_order_number'])->delete();
            
        }
        if($re){
            return ajax_success('删除成功');
        }else{
            return ajax_error('删除失败');
        }
        
    }
    /**
     * lilu
     * 获取账户余额
     * member_id
     */
    public function get_member_banlance(Request $request){
         $user_id = $request->only("member_id")["member_id"];//member_id
         //获取用户余额
         $balance=db('member')->where('member_id',$user_id)->field('member_wallet,member_recharge_money')->find();
         $bb=$balance['member_wallet']+$balance['member_recharge_money'];
         $money=round($bb,2);
         $data['balance']=$money;
         return ajax_success('获取成功',$data);

    }
}