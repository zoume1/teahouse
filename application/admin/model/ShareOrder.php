<?php
namespace app\admin\model;

use think\Model;

class ShareOrder extends Model
{
    protected $name = "share_order";
 
    /**
     * 获取存茶订单详情
     * @param $parts_order_number
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getHouseOrder($order_id)
    {
        $data = self::get($order_id);
        return $data ? $data : false;

    }



 
}