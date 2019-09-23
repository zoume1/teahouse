<?php
namespace app\admin\model;

use think\Model;
use think\db\Query;

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
}