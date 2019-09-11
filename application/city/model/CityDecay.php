<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;


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
        return $rest ? $rest : null;
        
    }


}