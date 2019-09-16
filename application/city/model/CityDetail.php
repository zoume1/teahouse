<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;
const CITY_ONE = 1;

/**gy
 * 分销代理模型
 * Class CityDetail
 * @package app\city\model
 */
class CityDetail extends Model
{
    protected $table = "tb_city_detail";


    /**gy
     *  分销代理显示
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_detail()
    {
        $model = new static;
        $rest = $model->order(['create_time' => 'desc'])
        ->paginate(20, false, [
            'query' => \request()->request()
        ]);;
        return $rest ? $rest : false;
        
    }


}