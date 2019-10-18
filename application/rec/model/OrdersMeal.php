<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/10/18
 * Time: 16:44
 */
namespace app\rec\model;


use think\Model;

class OrdersMeal extends Model{

    protected $table = "tb_meal_orders";
    protected $resultSetType = 'collection';

    //新增
    public function add($uid,$name,$quantity,$money,$store_id,$enter_all_id,$store_name,$pay,$openid,$img)
    {
        $data = new MealOrder;
        $data->save([
            'user_id' => $uid,
            'order_number' => $this->get_sn(),
            'goods_name' => $name,
            'goods_quantity' => $quantity,
            'amount_money' => $money,
            'store_id' => $store_id,
            'pay_type' => 4,
            'enter_all_id' => $enter_all_id,
            'store_name' => $store_name,
            'status'=> -1,
            'unit' =>'年',
            'pay_money'=>$pay,
            'openid' =>$openid,
            'images_url'=>$img,
            'create_time'=>time()
        ]);

        if($data !== false){
            return $data;
        }else{
            return false;
        }


    }

    //编辑
    public function edit($store_id,$uid,$name,$quantity,$money,$enter_all_id,$store_name,$pay,$openid,$img)
    {
        $data = new MealOrder;
        $data->save([
            'user_id' => $uid,
            'order_number' => $this->get_sn(),
            'goods_name' => $name,
            'goods_quantity' => $quantity,
            'amount_money' => $money,
            'pay_type' => 4,
            'enter_all_id' => $enter_all_id,
            'store_name' => $store_name,
            'status'=> -1,
            'unit' =>'年',
            'pay_money'=>$pay,
            'openid' =>$openid,
            'images_url'=>$img,
            'create_time'=>time()
        ],['store_id' => $store_id,]);

        if($data !== false){
            return $data;
        }else{
            return false;
        }


    }

    //生成发票订单号
    function get_sn() {
        return 'TC'.date('YmdHi').rand(100000, 999999);
    }

}