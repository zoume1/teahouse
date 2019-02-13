<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/13 0013
 * Time: 17:47
 */
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
class  Evaluate extends  Controller {

    public function order_evaluate_add(Request $request){
        if($request->isPost()){
            $img = $request->file("img");
            dump($img);
        }
//            $order_id =$request->only("orderId")["orderId"];//订单排序号（数组）
//            foreach ($order_id as $k=>$v){
//                $filesArr[$k] = "filesArr".$v;
//            }
//            foreach ($filesArr as $ks=>$vs){
//                $str=str_replace('filesArr','',$vs);
//                $img = $request->file("$vs");
//                if(!empty($img)){
//                    foreach ($img as $k=>$v) {
//                        $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
//                        $images["$str"][] = str_replace("\\", "/", $info->getSaveName());
//                    }
//                }
//            }
//            $user_id = Session::get("user");//用户id
//            $evaluate_content =$request->only("evaluateContent")["evaluateContent"];//评价内容（数组）
//            $is_on_time =$request->only("isOnTime")["isOnTime"];//是否准时（是否准时，1为准时,-1为不准时）
//            $logistics_stars =$request->only("starArr")["starArr"];//所有的星星（1为1颗星，...5为5颗星）
//            $start_length =count($logistics_stars);
//            $user_info =Db::name("user")->field("phone_num,user_name,id")->where("id",$user_id)->find();
//            $create_time =time();//创建时间
//            foreach ($order_id  as $k=>$v){
//                //所有的订单信息
//                $order_information =  Db::name("order_parts")
//                    ->field("parts_goods_name,goods_id,parts_order_number,store_id")
//                    ->where("id",$v)
//                    ->find();
//                $data =[
//                    "evaluate_content"=>$evaluate_content[$k], //评价的内容
//                    "goods_id" =>$order_information["goods_id"],
//                    "store_id" =>$order_information["store_id"],
//                    "goods_name"=>$order_information["parts_goods_name"],
//                    "user_phone_num"=>$user_info["phone_num"],
//                    "user_id"=>$user_info["id"],
//                    "status"=>0,
//                    "order_information_number"=>$order_information["parts_order_number"],
//                    "order_id"=>$v,
//                    "create_time"=>$create_time,
//                    "user_name"=> $user_info["user_name"],
//                    "evaluate_stars"=>$logistics_stars[$k], //商品描述星星（1为1颗星，...5为5颗星）
//                    "service_attitude_stars"=>$logistics_stars[$start_length-2],  //服务态度的星星（1为1颗星，...5为5颗星） //服务星星(先计算长度-2)
//                    "logistics_stars"=>$logistics_stars[$start_length-1], //物流服务的星星（1为1颗星，...5为5颗星） //物流星星(先计算长度-1)
//                    "is_repay"=>0,
//                    "is_on_time"=>$is_on_time,
//
//                ];
//                $bool =Db::name("order_parts_evaluate")->insertGetId($data);
//                if(!empty( $bool)){
//                    Db::name("order_parts")
//                        ->where("id",$v)
//                        ->update(["status"=>8]);
//                    if(!empty($images)){
//                        foreach ($images as $ks=>$vs){
//                            if( $v == intval($ks)){
//                                foreach ($vs as $j=>$i){
//                                    //插入评价图片数据库
//                                    $insert_data =[
//                                        "images"=>$i,
//                                        "evaluate_order_id"=>$bool,
//                                    ];
//                                    Db::name("order_parts_evaluate_images")->insert($insert_data);
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//            if($bool){
//                //进行消费积分奖励
//                $order_id_one =$order_id[0];
//                $order_real_pays =Db::name("order_parts")->where("id",$order_id_one)->value("order_real_pay");//消费的钱数
//                $recommend_integral =Db::name("recommend_integral")->where("id",1)->find();
//                if($order_real_pays >= $recommend_integral["coin"]) {
//                    //判断消费是否满足送积分条件
//                    $old_integral_wallet = Db::name("user")
//                        ->where("id", $user_id)
//                        ->value("user_integral_wallet");
//                    //推荐人的积分添加
//                    $new_integral =$old_integral_wallet + $recommend_integral["consume_integral"];
//                    $add_res = Db::name("user")
//                        ->where("id", $user_id)
//                        ->update(["user_integral_wallet" =>$new_integral ]);
//                    if ($add_res) {
//                        //余额添加成功(做积分消费记录)
//                        //插入积分记录
//                        $integral_data = [
//                            "user_id" => $user_id,
//                            "integral_operation" => $recommend_integral["consume_integral"],//获得积分
//                            "integral_balance" => $recommend_integral["consume_integral"] + $old_integral_wallet,//积分余额
//                            "integral_type" => 1, //积分类型（1获得，-1消费）
//                            "operation_time" => date("Y-m-d H:i:s"), //操作时间
//                            "integral_remarks" => "消费满".$recommend_integral["coin"]."送" . $recommend_integral["consume_integral"] . "积分",
//                        ];
//                        Db::name("integral")->insert($integral_data);
//                    }
//                }
//                return ajax_success("评价成功",$bool);
//            }else{
//                return ajax_error("评价失败",["status"=>0]);
//            }
//        }
    }

}