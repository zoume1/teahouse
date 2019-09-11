<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;


/**
 * 店铺分销代理设置模型
 * Class StoreSetting
 * @package app\city\model
 */
class StoreCommission extends Model
{
    protected $name = "store_commission";


    /**
     * 店铺分销代理设置
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function commission_setting()
    {
        
        $model = new static;
        $rest = $model->find()->toArray();
        return $rest ? $rest : null;
        
    }


}