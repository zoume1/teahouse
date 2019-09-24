<?php
namespace app\admin\model;

use think\Model;
use think\db\Query;
use app\city\model\CityCopartner;
const ADDRESS_ONE = 1;
const ADDRESS_TWO = 2;

class Store extends Model
{
    protected $table = "tb_store";
    protected $resultSetType = 'collection';

    public function getStoreName($id){
        $data  = $this->where(['id'=>$id])->value('store_name');    
        if($data){
            return $data;
        }
    }



    /**gy
     *  城市累计商户总数
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function store_number($city_user_id)
    {
        $model = new static;
        !$city_user_id && $rest = $model->where('city_iser_id', '=', $city_user_id)->count();
        return $rest;
        
    }

    /**gy
     * 获取店铺入驻资料
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($data)
    {
        $data = self::get($data);
        return $data ? $data->toArray() : false;
    }

    /**gy
     *  查询城市合伙人
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function find_city_user($address)
    {
        $address_two = explode(",", $address);
        $user = CityCopartner::detail(['city_address'=>$address_two[ADDRESS_TWO],'judge_status'=>ADDRESS_ONE]);
        return $user ? $user['user_id'] : false;
        
    }
}