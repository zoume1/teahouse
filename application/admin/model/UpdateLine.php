<?php
namespace app\admin\model;

use think\Model;

class UpdateLine extends Model
{
    protected $table = "tb_update_line";


        
     /**
     * 新增商品划线价记录
     * @param $where
     * @return UpdateLine|static
     * @throws \think\exception\DbException
     */
    public function add($data)
    {
        return $this->save([
            'goods_line' => $data['goods_bottom_money'],
            'goods_id' => $data['goods_id'],
            'store_id' => $data['store_id'],
            'creat_time' => time(),
        ]);
    }


 
}