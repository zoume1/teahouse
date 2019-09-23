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


}