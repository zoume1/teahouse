<?php
namespace app\api\model;

use think\Model;

class UpdateLine extends Model
{
    protected $table = "tb_update_line";
    protected $resultSetType = 'collection';

        
     /**
     * 新增商品划线价记录
     * @param $where
     * @return UpdateLine|static
     * @throws \think\exception\DbException
     */
    public function add($data)
    {
        return $this->save([
            'goods_line' => $data['goods_line'],
            'goods_id' => $data['goods_id'],
            'store_id' => $data['store_id'],
            'creat_time' => time(),
            'year_number'=> date('Y')
        ]);
    }

    /**
     * 获取所有新增商品划线价记录
     * @param $where
     * @return UpdateLine|static
     * @throws \think\exception\DbException
     */
    public function getList($goods_id)
    { 
        return $this->where('goods_id',$goods_id)
        ->field("avg(goods_line) as line,year_number")
        ->group('year_number')
        ->select()->toArray();
        
    }


 
}