<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;
const CITY_ONE = 1;

/**
 * 城市等级套餐模型
 * Class CityRank
 * @package app\city\model
 */
class CityRank extends Model
{
    protected $table = "tb_city_rank";


    /**gy
     *  城市等级各等级添加
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function rank_add($data)
    {
        $model = new static;
        $rest = $model->save($data);
        return $rest ? $rest : false;
        
    }

    /**gy
     * 获取等级信息
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($meal_id)
    {
        return self::get($meal_id)->toArray();
    }


    /**获取所有等级
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
     *  城市等级更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function rank_update($data)
    {

        $model = new static;
        $rest = $model -> allowField(true)->save($data,['id'=>$data['id']]);
        return $rest ? $rest : false;
        
    }

}