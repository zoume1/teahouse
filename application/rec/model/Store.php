<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/18
 * Time: 16:58
 */
namespace app\rec\model;

use think\Model;

class Store extends Model
{
    protected $table = "tb_store";
    protected $resultSetType = 'collection';

    //获取个人信息
    public function store_index($id)
    {
        return self::get('id',$id) ? self::get('id',$id)  -> toArray(): returnJson(0,'数据有误');
    }


    //获取地址信息
    public function store_address($uid,$store_id)
    {
        return self::get(['id'=>$store_id,'user_id'=>$uid]) ? self::get(['id'=>$store_id,'user_id'=>$uid])->toArray() : returnJson(0,'数据有误');
    }

    //查询个人店铺总数
    public static function store_num($uid)
    {
        return self::where(['user_id'=>$uid,'store_del'=>1])->count();
    }
}