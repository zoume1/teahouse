<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;

const ONE = 1;
/**
 * 城市合伙人分销代理设置模型
 * Class StoreSetting
 * @package app\city\model
 */
class CitySetting extends Model
{
    protected $table = "tb_city_setting";


    /**
     *  城市合伙人分销代理设置
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_setting()
    {
        $model = new static;
        $rest = $model->find()->toArray();
        return $rest ? $rest : false;
        
    }


        /**
     *  城市合伙人分销代理设置更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_setting_update($data)
    {
        $rest_data = [
            'commission'=>$data['commission'],
            'reach_commission'=>$data['reach_commission'],
            'rank_city'=>$data['rank_city'],
            'one_city'=>$data['one_city'],
            'two_city'=>$data['two_city'],
            'three_city'=>$data['three_city']
        ];
        $model = new static;
        $rest = $model -> allowField(true)->save($rest_data,['id'=>ONE]);
        return $rest ? $rest : false;
        
    }

}