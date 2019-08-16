<?php

namespace app\common\model\dealer;

use think\Hook;
use app\common\model\BaseModel;
use app\common\enum\OrderType as OrderTypeEnum;
use app\admin\model\Commodity  as Commodity;
const Setting = 2;
/**
 * 分销商订单模型
 * Class Apply
 * @package app\common\model\dealer
 */
class Order extends BaseModel
{
    protected $name = 'dealer_order';

    // /**
    //  * 订单模型初始化
    //  */
    // public static function init()
    // {
    //     parent::init();
    //     // 监听分销商订单行为管理
    //     $static = new static;
    //     Hook::listen('DealerOrder', $static);
    // }

    // /**
    //  * 订单所属用户
    //  * @return \think\model\relation\BelongsTo
    //  */
    // public function user()
    // {
    //     return $this->belongsTo('app\common\model\User');
    // }

    // /**
    //  * 一级分销商用户
    //  * @return \think\model\relation\BelongsTo
    //  */
    // public function dealerFirst()
    // {
    //     return $this->belongsTo('User', 'first_user_id');
    // }

    // /**
    //  * 二级分销商用户
    //  * @return \think\model\relation\BelongsTo
    //  */
    // public function dealerSecond()
    // {
    //     return $this->belongsTo('User', 'second_user_id');
    // }

    // /**
    //  * 三级分销商用户
    //  * @return \think\model\relation\BelongsTo
    //  */
    // public function dealerThird()
    // {
    //     return $this->belongsTo('User', 'third_user_id');
    // }

    /**
     * 订单类型
     * @param $value
     * @return array
     */
    public function getOrderTypeAttr($value)
    {
        $types = OrderTypeEnum::getTypeName();
        return ['text' => $types[$value], 'value' => $value];
    }

    /**
     * 订单详情
     * @param $where
     * @return Order|null
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        return static::get($where);
    }

    /**
     * 发放分销订单佣金
     * @param array|\think\Model $order 订单详情
     * @param int $orderType 订单类型
     * @return bool|false|int
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function grantMoney(&$order)
    {

        // 订单是否已完成、是否下单
        if (!in_array($order['status'],[2,8])) {
            return false;
        }
        // 分销订单详情
        $model = self::detail(['order_id' => $order['id']]);
        if (!$model || $model['is_settled'] == 1) {
            return false;
        }

        // 重新计算分销佣金
        $capital = $model->getCapitalByOrder($order,$level=Setting);
        // 发放一级分销商佣金
        $model['first_user_id'] > 0 && User::grantMoney($model['first_user_id'], $capital['first_money'],$order['store_id']);
        // 发放二级分销商佣金
        $model['second_user_id'] > 0 && User::grantMoney($model['second_user_id'], $capital['second_money'],$order['store_id']);
        // 发放三级分销商佣金
        $model['third_user_id'] > 0 && User::grantMoney($model['third_user_id'], $capital['third_money'],$order['store_id']);

        // 发放一级分销商积分
        $model['first_user_id'] > 0 && User::grantIntegral($model['first_user_id'], $capital['first_integral'],$order['store_id']);
        // 发放二级分销商积分
        $model['second_user_id'] > 0 && User::grantIntegral($model['second_user_id'], $capital['second_integral'],$order['store_id']);
        // 发放三级分销商积分
        $model['third_user_id'] > 0 && User::grantIntegral($model['third_user_id'], $capital['third_integral'],$order['store_id']);
        // 更新分销订单记录
        User::addMemberPrice($order, $capital['orderPrice']);
        return $model->save([
            'order_price' => $capital['orderPrice'],
            'first_money' => $capital['first_money'],
            'second_money' => $capital['second_money'],
            'order_price' => $capital['orderPrice'],
            'first_integral' => $capital['first_integral'],
            'second_integral' => $capital['second_integral'],
            'third_integral' => $capital['third_integral'],
            'is_settled' => 1,
            'settle_time' => time()
        ]);
    }

    /**
     * 计算订单分销佣金
     * @param $order
     * @return array
     */
    protected function getCapitalByOrder(&$order,$level=Setting)
    {

        // 分销订单佣金数据
        $capital = [
            // 订单总金额(不含运费)
            'orderPrice' => bcsub($order['goods_money'], 0, 2),
            // 一级分销佣金
            'first_money' => 0.00,
            // 二级分销佣金
            'second_money' => 0.00,
            // 三级分销佣金
            'third_money' => 0.00,
            // 一级分销积分
            'first_integral' => 0.00,
            // 二级分销积分
            'second_integral' => 0.00,
            // 三级分销积分
            'third_integral' => 0.00
        ];
        // 计算分销佣金
        foreach ($order['goods_id'] as $key => $value) {

            // 商品实付款金额
            $goodsPrice = $order['order_amount'][$key];
            // 计算商品实际佣金
            $set = new Commodity;
            $setting = $set->getCommissionScale($value,$order['store_id'],$order['member_id']);
            $goodsCapital = $this->calculateGoodsCapital($setting,$goodsPrice);

            // 累积分销佣金
            switch($level)
            {
                case 1:
                    $capital['first_money'] += $goodsCapital['first_money'];
                    $capital['first_integral'] += $goodsCapital['first_integral'];
                    break;
                case 2:
                    $capital['first_money'] += $goodsCapital['first_money'];
                    $capital['second_money'] += $goodsCapital['second_money'];
                    $capital['first_integral'] += $goodsCapital['first_integral'];
                    $capital['second_integral'] += $goodsCapital['second_integral'];
                    break;
                case 3:
                    $capital['first_money'] += $goodsCapital['first_money'];
                    $capital['second_money'] += $goodsCapital['second_money'];
                    $capital['third_money'] += $goodsCapital['third_money'];
                    $capital['first_integral'] += $goodsCapital['first_integral'];
                    $capital['second_integral'] += $goodsCapital['second_integral'];
                    $capital['third_integral'] += $goodsCapital['third_integral'];
                    break;
                default:
                    exit();
            }
        }
        return $capital;
    }

    /**
     * 计算商品实际佣金
     * @param $setting
     * @param $goods
     * @param $goodsPrice
     * @return array
     */
    private function calculateGoodsCapital($setting, $goodsPrice)
    {

            return [
                'first_money' => $goodsPrice * ($setting['grade'][0] * 0.01) + $setting['award'][0],
                'second_money' => $goodsPrice * ($setting['grade'][1] * 0.01) + $setting['award'][1],
                'third_money' => $goodsPrice * ($setting['grade'][2] * 0.01) + $setting['award'][2],
                'first_integral' => $goodsPrice * ($setting['scale'][0] * 0.01) + $setting['integral'][0],
                'second_integral' => $goodsPrice * ($setting['scale'][1] * 0.01)+ $setting['integral'][1],
                'third_integral' => $goodsPrice * ($setting['scale'][2] * 0.01)+ $setting['integral'][2]
            ];
        

    }

    /**
     * 验证商品是否存在售后
     * @param $goods
     * @return bool
     */
    private function checkGoodsRefund(&$goods)
    {
        return !empty($goods['refund'])
            && $goods['refund']['type']['value'] == 10
            && $goods['refund']['is_agree']['value'] != 20;
    }


     /**
     * 创建分销商订单记录
     * @param $order
     * @param int $order_type 订单类型 (10商城订单 20拼团订单)
     * @return bool|false|int
     * @throws \think\exception\DbException
     */
    public static function createOrder(&$order,$order_type=10)
    {
        // 分销订单模型
        $model = new self;
        // 获取当前买家的所有上级分销商用户id
        $dealerUser = $model->getDealerUserId($order['member_id'], Setting);
        // 计算订单分销佣金
        $capital = $model->getCapitalByOrder($order);
        // 保存分销订单记录
    
        return $model->save([
            'user_id' => $order['member_id'],
            'order_id' => $order['id'],
            'order_type' => $order_type,
            'order_no' => $order['parts_order_number'],  
            'order_price' => $capital['orderPrice'],
            'first_money' => max($capital['first_money'], 0),
            'second_money' => max($capital['second_money'], 0),
            'third_money' => max($capital['third_money'], 0),
            'first_integral' => max($capital['first_integral'], 0),
            'second_integral' => max($capital['second_integral'], 0),
            'third_integral' => max($capital['third_integral'], 0),
            'first_user_id' => $dealerUser['first_user_id'],
            'second_user_id' => $dealerUser['second_user_id'],
            'third_user_id' => $dealerUser['third_user_id'],
            'is_settled' => 0,
            'wxapp_id' => $order['store_id']
        ]);
    }

    /**
     * 获取当前买家的所有上级分销商用户id
     * @param $user_id
     * @param $level
     * @param $self_buy
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getDealerUserId($user_id, $level)
    {
        $dealerUser = [
            'first_user_id' => $level >= 1 ? Referee::getRefereeUserId($user_id, 1, true) : 0,
            'second_user_id' => $level >= 2 ? Referee::getRefereeUserId($user_id, 2, true) : 0,
            'third_user_id' => $level == 3 ? Referee::getRefereeUserId($user_id, 3, true) : 0
        ];
        //分销商自购
        if (User::isDealerUser($user_id)) {
            return [
                'first_user_id' => $user_id,
                'second_user_id' => $dealerUser['first_user_id'],
                'third_user_id' => $dealerUser['second_user_id'],
            ];
        }
        return $dealerUser;
    }

}
