<?php
namespace app\admin\model;

use think\Model;
use app\admin\model\Goods;
use app\common\model\dealer\Order as OrderModel;
class Order extends Model
{
    protected $table = "tb_order";
 
    /**
     * 获取订单信息
     * @param $order
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getOrderInforMation($order)
    {
        // 分销订单详情
        $model = OrderModel::detail(['order_no' => $order['parts_order_number']]);
        if (!$model || $model['is_settled'] == 1) {
            return false;
        }
        $order_data = self::where('parts_order_number',"=",$order['parts_order_number'])->select();
        if($order_data){
            foreach($order_data as $value){
                $goods_id[] = $value['goods_id'];
                $all_money[] = $value['order_amount'];
            }

        } 

        $goods_bool = Goods::getDistributionStatus($goods_id); // 是否分销商品
        $count_money = Goods::getDistributionPrice($goods_id,$goods_bool,$all_money);
        $data = [
            'member_id'=>$order['member_id'],
            'id'=>$order['id'],
            'parts_order_number'=>$order['parts_order_number'],
            'goods_id'=>$goods_bool,
            'store_id'=>$order['store_id'],
            'order_amount'=>$count_money,
            'goods_money'=>array_sum($count_money),
            'status'=>$order['status'],            
        ];
                                    
        return $data;
        
    }



 
}