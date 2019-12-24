<?php

namespace app\admin\model;
use think\Session;
use think\Model;
use think\Db;
use think\Validate;
use app\city\controller;
use app\admin\model\Goods;
use app\admin\model\AccompanySetting;

use app\common\exception\BaseException;


/**
 * 赠茶商品二维码
 * Class StoreSetting
 * @package app\city\model
 */
class AccompanyCode extends Model
{
    protected $table = "tb_accompany_code";




    /**gy
     *  赠茶商品赠茶商品二维码添加
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function code_add($data)
    {
        $model = new static;
        $rest = $model->save($data);
        return $rest ? $rest->id : false;
    
    }

 

    /**gy
     *获取赠茶商品二维码
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        return self::get($id)->toArray();
    }



    /**gy
     *  赠茶商品二维码更新
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