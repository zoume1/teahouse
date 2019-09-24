<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;
const CITY_ONE = 1;

/**gy
 * 分销代理模型
 * Class CityDetail
 * @package app\city\model
 */
class CityDetail extends Model
{
    protected $table = "tb_city_detail";


    /**gy
     *  分销代理列表显示
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_detail($search)
    {
        $model = new static;
        // 查询条件
        !empty($search) && $model->where('phone_number|order_number', 'like', "%$search%");
        $rest = $model->order(['create_time' => 'desc'])
        ->paginate(20, false, [
            'query' => \request()->request()
        ]);;
        return $rest;
        
    }


    
    /**gy
     *  城市累计商户获得佣金
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_store_commission($city_user_id)
    {
        $model = new static;
        !empty($city_user_id) && $rest = $model->where('city_user_id', '=', $city_user_id)->sum('commision');
        return $rest;
        
    }

    /**gy
     * 生成分销代理订单
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function store_order_commission($data)
    {
        $data = [
            'order_number' => '入驻订单号',
            'phone_number' => '联系电话',
            'share_code' => '分享码',
            'set_meal' => '订单套餐名 ', 
            'meal_price' =>'订单金额',
            'higher_phone' => '上级账号',
            'commision' =>'分销佣金',
            'base_commision' => '保低佣金',
            'reach_commision' => '达标佣金',
            'create_time' => '创建时间',
            'update_time' => 'update_time',
            'city_user_id' => '城市合伙人user_id'
        ];
        $model = new static;
        !empty($city_user_id) && $rest = $model->where('city_user_id', '=', $city_user_id)->sum('commision');
        return $rest;
        
    }


}