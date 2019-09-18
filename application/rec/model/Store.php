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
        return self::get('id',$id)  -> toArray();
    }
}