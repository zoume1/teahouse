<?php
namespace app\admin\model;

use think\Model;
use think\Db;
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
        $user = CityCopartner::detail(['city_address'=>$address,'judge_status'=>ADDRESS_ONE,'is_delete'=>0]);
        return $user ? $user['user_id'] : 0;
        
    }

    /**gy
     * 店铺奖罚
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function store_sanction($data)
    {
        $add_money = Db::name('store')->where('phone_number',$data['phone_number'])->setInc('store_wallet',$data['money']);
        return $add_money;
    }
}