<?php
namespace app\api\model;

use think\Model;

class DirectSeeding extends Model
{
    protected $table = "tb_direct_seeding";
    protected $resultSetType = 'collection';

    //查询分类
    public static function detail($store_id)
    {
        return self::all(['store_id'=>$store_id,'status'=>1]) -> toArray();
    }

}
