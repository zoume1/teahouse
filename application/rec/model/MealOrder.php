<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/18
 * Time: 11:52
 */
namespace app\rec\model;


use think\Model;

class MealOrder extends Model{

    protected $table = "tb_set_meal_order";
    protected $resultSetType = 'collection';

    //新增
    public function add($uid,$name,$quantity,$money,$store_id,$enter_all_id,$store_name,$pay,$openid)
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
            'create_time'=>time()
        ]);
        return $data;

    }


    //生成发票订单号
    function get_sn() {
        return 'TC'.date('YmdHi').rand(100000, 999999);
    }

}