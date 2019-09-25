<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;
use app\admin\model\Store as AddStore;

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
    public static function store_order_commission($order_data,$store_data)
    {
        $city_user_id = AddStore::find_city_user($store_data['address_data']);
        $retutn_data = find_rank_data($store_data['highe_share_code'],$order_data['pay_money'],$city_user_id);
        $data = [
            'order_number' => $order_data['order_number'],
            'phone_number' => $store_data['phone_number'],
            'share_code' => $store_data['share_code'], //自己的分享码
            'set_meal' => enter_name($order_data['enter_all_id']),  
            'meal_price' =>$order_data['pay_money'],
            'highe_share_code' => $store_data['highe_share_code'],
            'commision' => $retutn_data['commision'],
            'higher_phone' => $retutn_data['higher_phone'],
            'base_commision' => $retutn_data['base_commision'],
            'reach_commision' => $retutn_data['reach_commision'],
            'create_time' => $order_data['create_time'],
            'update_time' => time(),
            'city_user_id' => $city_user_id,
        ];

        //传入套餐id生成生成套餐名
        //是否有上级账号计算分销佣金
        //判断有无城市合伙人user_id 有计算保底佣金 + 是否有达标佣金
    }


}