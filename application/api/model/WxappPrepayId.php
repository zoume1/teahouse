<?php

namespace app\api\model;
use think\Model;
/**
 * 小程序prepay_id模型
 * Class WxappPrepayId
 * @package app\common\model
 */
class WxappPrepayId extends Model
{
    protected $table = 'tp_wxapp_prepay_id';

    /**
     * prepay_id 详情
     * @param int $orderId
     * @param int $orderType 订单类型
     * @return array|false|\PDOStatement|string|\think\Model|static
     */
    public static function detail($orderId, $orderType)
    {
        return (new static)->where('order_id', '=', $orderId)
            ->where('order_type', '=', $orderType)
            ->order(['create_time' => 'desc'])
            ->find();
    }

    /**
     * 记录prepay_id使用次数
     * @return int|true
     * @throws \think\Exception
     */
    public function updateUsedTimes($prepay_id)
    {
        return $this->where('prepay_id','=',$prepay_id)->setInc('used_times', 1);
    }


    /**
     * 新增记录
     * @param $prepayId
     * @param $orderId
     * @param $userId
     * @param int $orderType
     * @return false|int
     */
    public function add($prepayId, $orderId, $userId, $orderType,$wxapp_id)
    {
        return $this->save([
            'prepay_id' => $prepayId,
            'order_id' => $orderId,
            'order_type' => $orderType,
            'user_id' => $userId,
            'can_use_times' => 0,
            'used_times' => 0,
            'expiry_time' => time() + (7 * 86400),
            'create_time' =>time(),
            'wxapp_id' => $wxapp_id,
        ]);
    }

        /**
     * 更新prepay_id已付款状态
     * @param $orderId
     * @param $orderType
     * @return false|int
     */
    public static function updatePayStatus($orderId, $orderType = OrderTypeEnum::MASTER)
    {
        // 获取prepay_id记录
        $model = static::detail($orderId, $orderType);
        if (empty($model)) {
            return false;
        }
        // 更新记录
        return $model->save(['can_use_times' => 3, 'pay_status' => 1]);
    }

}