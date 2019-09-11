<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;


/**
 * 城市合伙人代理服务考评体系设置模型
 * Class StoreSetting
 * @package app\city\model
 */
class CityEvaluate extends Model
{
    protected $table = "tb_city_evaluate";


    /**
     *  城市合伙人代理服务考评体系设置
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_evaluate()
    {
        $model = new static;
        $rest = $model->find()->toArray();
        return $rest ? $rest : null;
        
    }


}