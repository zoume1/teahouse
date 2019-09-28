<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Db;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;


/**
 * 市场反馈
 * Class StoreSetting
 * @package app\city\model
 */
class CityBack extends Model
{
    protected $table = "tb_city_back";


    /**gy
     *  市场反馈添加
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_back_add($data)
    {
        $model = new static;
        $rest = $model->save($data);
        return $rest ? $rest : false;
        
    }

    /**gy
     * 获取市场反馈信息
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($user_id)
    {
        $data = Db::name('city_back')
        ->where('user_id','=',$user_id['user_id'])
        ->where('return_time','>',0)
        ->order("create_time desc")
        ->select();
        return $data;
    }


    /**获取所有市场反馈
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
     *  市场反馈更新
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

}