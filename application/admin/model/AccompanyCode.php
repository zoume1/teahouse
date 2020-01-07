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
    public  function code_add($data)
    {
        ini_set('max_execution_time', '1000');
        $rest = $this->save($data);
        return $rest ? $this->id : false;
    
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
     *获取赠茶商品信息
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function goods_detail($code_id)
    {
        $code_data = self::detail($code_id);
        $goods_data = Db :: name('accompany') 
            -> where('id','=',$code_data['accompany_id'])
            ->where('is_del','=',0)->find();
        return $goods_data ? $goods_data : false;
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