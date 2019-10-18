<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Db;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;


/**
 * 店铺评价合伙人
 * Class CityComment
 * @package app\city\model
 */
class CityComment extends Model
{
    protected $table = "tb_city_comment";
    // 设置返回数据集的对象名
	protected $resultSetType = 'collection';


    /**gy
     *  店铺反馈添加
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function store_comment_add($data)
    {
        $model = new static;
        $rest = $model->save($data);
        return $rest ? $rest : false;
        
    }

    /**gy
     * 获取店铺评论信息
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($store_id)
    {
        return self::where('store_id','=',$store_id)->select();
    }

    /**gy
     * 获取店铺评论信息
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function proving_comment($store_id)
    {
        $model = new static;
        $data_rest = $model->detail($store_id);
        $count_array = count($data_rest);
        if($count_array){
            $last = array_pop($count_array);
            halt($last);
        }

    }
}