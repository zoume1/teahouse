<?php

namespace app\admin\model;
use think\Session;
use think\Model;
use think\Db;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;


/**
 * 送存商品出仓设置
 * Class StoreSetting
 * @package app\city\model
 */
class AccompanySetting extends Model
{
    protected $table = "tb_accompany_setting";


    /**gy
     *  设置添加
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function setting_add($data)
    {
        $model = new static;
        if(isset($data['one']) && isset($data['two'])){
            $rest = 3;
        } elseif(!isset($data['one']) && isset($data['two'])){
            $rest = 2;
        } elseif(isset($data['one']) && !isset($data['two'])){
            $rest = 1;
        } else {
            $rest = 0;
        }
        $rest_data = [
            'min_price' => $data['min_price'],
            'min_number' => $data['min_number'],
            'status' => $rest,
            'accompany_id' => $data['accompany_id'],
        ];
        $rest = $model->save($rest_data);
        return $rest ? true : false;
        
    }

    /**gy
     * 获取设置
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        return self::get($id)->toArray();
   
    }



    /**gy
     *  设置更新
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