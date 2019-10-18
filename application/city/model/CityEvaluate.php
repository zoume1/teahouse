<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;

const EONE = 1;

/**
 * 城市合伙人代理服务考评体系设置模型
 * Class StoreSetting
 * @package app\city\model
 */
class CityEvaluate extends Model
{
    protected $table = "tb_city_evaluate";


    /**gy
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
        return $rest ? $rest : false;
        
    }


    /**gy
     *  城市合伙人代理服务考评体系设置更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_evaluate_update($data)
    {
        $rest_data = [
            'one'=>$data['one'],
            'two'=>$data['two'],
            'three'=>$data['three'],
        ];
        $model = new static;
        $rest = $model -> allowField(true)->save($rest_data,['id'=>EONE]);
        return $rest ? $rest : false;
        
        
    }


}