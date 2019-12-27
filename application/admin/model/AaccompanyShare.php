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
 * 二维码扫描记录
 * Class AaccompanyShare
 * @package app\city\model
 */
class AaccompanyShare extends Model
{
    protected $table = "tb_accompany_share";




    /**gy
     *  二维码扫描记录添加
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public  function share_add($data)
    {
        
        $rest = $this->save($data);
        return $rest ? $this->id : false;
    
    }

 

    /**gy
     *获取二维码扫描记录
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        $data = self::get($id);
        return $data ? $data->toArray() : false;
    }





    /**gy
     *  赠茶商品二维码更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function share_update($data)
    {

        $model = new static;
        $rest = $model -> allowField(true)->save($data,['id'=>$data['id']]);
        return $rest ? $rest : false;
        
    }

}