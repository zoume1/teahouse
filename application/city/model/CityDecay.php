<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;
const CITY_ONE = 1;

/**
 * 城市合伙人代理衰减配置设置模型
 * Class StoreSetting
 * @package app\city\model
 */
class CityDecay extends Model
{
    protected $table = "tb_city_decay";


    /**
     *  城市合伙人代理衰减配置理设置
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_decay()
    {
        $model = new static;
        $rest = $model->find()->toArray();
        return $rest ? $rest : false;
        
    }


    /**
     *  城市合伙人代理衰减配置理设置更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_decay_update($data)
    {
        $rest_data = [
            'two_year'=>$data['two_year'],
            'three_year'=>$data['three_year'],
            'four_year'=>$data['four_year'],
            'five_year'=>$data['five_year'],
        ];
        $model = new static;
        $rest = $model -> allowField(true)->save($rest_data,['id'=>CITY_ONE]);
        return $rest ? $rest : false;
        
    }

}