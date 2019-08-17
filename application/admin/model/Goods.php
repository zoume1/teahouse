<?php
namespace app\admin\model;

use think\Model;

class Goods extends Model
{
    protected $table = "tb_goods";


        
     /**
     * 销商申请记录详情
     * @param $where
     * @return Apply|static
     * @throws \think\exception\DbException
     */
    public static function detail($goods_id)
    {
        return self::get(['id'=>$goods_id,'distribution_status'=>1]);
    }

    /**
     * 判断商品是否设置分销
     * @param $goods_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getDistributionStatus($goods_id)
    {
        
        foreach($goods_id as $value)
        {
            $detail = self::detail($value);
            if($detail){
                $data[] = $value;
            }
        }

         return  isset($data) ? array_values($data) : null;
        
    }


    /**
     * 返回订单金额
     * @param $goods_first
     * @param $goods_second
     * @param $money
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getDistributionPrice($goods_first,$goods_second,$money)
    {
        
        foreach($goods_first as $key => $value)
        {
            if(in_array($value,$goods_second)){
                $data[] = $money[$key];
            }
        }

         return  isset($data) ? array_values($data) : null;
        
    }
 
}