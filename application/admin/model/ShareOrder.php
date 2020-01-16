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
    public static function getShareOrder($order_id)
    {
        $data = self::get($order_id);
        return $data ? $data : false;

    }


    /**gy
     *  赠送记录添加
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

       /**
     * 计算分享存茶数量
     * @param $parts_order_number
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function countOrderNumber($order_id)
    {
        $data = self::where('order_id','=',$order_id)->where('status','=',2)->count();
        return $data;

    }

 
}