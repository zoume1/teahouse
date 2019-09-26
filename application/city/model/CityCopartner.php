<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;
use app\city\model\CitySetting;

// const CITY_ONE = 1;

/**
 * 城市入驻资料模型
 * Class CityCopartner
 * @package app\city\model
 */
class CityCopartner extends Model
{
    protected $table = "tb_city_copartner";


    /**gy
     *  城市入驻资料显示
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_copartner($search)
    {
        $model = new static;
        // 查询条件
        !empty($search) && $model->setWhere($search);
        $rest = $model->order(['create_time' => 'desc'])
        ->paginate(20, false, [
            'query' => \request()->request()
        ]);
        return $rest;
        
    }

    /**gy
     * 获取城市入驻资料
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($meal_id)
    {
        $rest =  self::get($meal_id);
        return $rest ? $rest->toArray() :false;
    }


    /**获取所有城市入驻资料
     * gy
     * @param $useid
     * @return false|static[]
     * @throws \think\exception\DbException
     */
    public static function getList()
    {
        return self::all();
    }

    /**gy
     *  城市入驻资料更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function meal_update($data)
    {

        $model = new static;
        $rest = $model -> allowField(true)->save($data,['id'=>$data['id']]);
        return $rest ? $rest : false;
        
    }


        /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {
        if (isset($query['name']) && !empty($query['name'])) {
            $this->where('phone_number|user_name', 'like', '%' . trim($query['name']) . '%');
        }
        if (isset($query['status']) && !empty($query['status'])) {
            $this->where('status', '=', $query['status']);
        }
    }

        /**gy
     * 获取城市入驻资料
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function get_number()
    {
        $user = Session::get('User');
        $setting = CitySetting::city_setting();
        $user_data =  self::detail($user['user_id']);
        $invitation_store_number = $user_data['invitation_store_number'];
        switch($user_data){
            case 2:
                $number = $setting['rank_city'];
                return $number;
                break;
            case 3:
                $number = $setting['one_city'];
                return $number;
                break;
            case 4:
                $number = $setting['two_city'];
                return $number;
                break;
            case 5:
                $number = $setting['three_city'];
                return $number;
                break;
            default:
                break;
        }
        if($number > $invitation_store_number){
            $return_number = $number - $invitation_store_number;
        } else {
            $return_number = 0;
        } 
        return $return_number;
    }

}