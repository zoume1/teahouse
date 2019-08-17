<?php

namespace app\common\model\dealer;

use app\common\model\BaseModel;
use app\common\model\dealer\Order as Order;
use app\admin\model\Member as Member;

/**
 * 分销商用户模型
 * Class Apply
 * @package app\common\model\dealer
 */
class User extends BaseModel
{
    protected $name = 'dealer_user';


    /**
     * 获取分销商用户信息
     * @param $user_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($user_id)
    {
        return self::get($user_id);
    }

    /**
     * 是否为分销商
     * @param $user_id
     * @return bool
     * @throws \think\exception\DbException
     */
    public static function isDealerUser($user_id)
    {
        $dealer = self::detail($user_id);
        return !!$dealer && !$dealer['is_delete'];
    }

    /**
     * 新增分销商用户记录
     * @param $user_id
     * @param $data
     * @return false|int
     * @throws \think\exception\DbException
     */
    public static function add($user_id, $data)
    {
        $model = static::detail($user_id) ?: new static;
        return $model->save(array_merge([
            'user_id' => $user_id,
            'is_delete' => 0        
        ], $data));
    }



    /**
     * 发放分销商佣金
     * @param $user_id
     * @param $money
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function grantMoney($user_id, $money,$store_id)
    {
        // 分销商详情
        $model = static::detail($user_id);
        if (!$model || $model['is_delete']) {
            return false;
        }
        // 累积分销商可提现佣金
        $model->where('user_id','=',$user_id)->setInc('money', $money);
        Member::grantMoney($user_id, $money);
        // 记录分销商资金明细
        Capital::add([
            'user_id' => $user_id,
            'flow_type' => 10,
            'number' => $money,
            'describe' => '订单佣金结算',
            'wxapp_id' => $store_id,
            'is_status' => 1,
        ]);
        return true;
    }


    /**
     * 发放分销商积分
     * @param $user_id
     * @param $money
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function grantIntegral($user_id, $integral,$store_id)
    {
        // 分销商详情
        $model = static::detail($user_id);
        if (!$model || $model['is_delete']) {
            return false;
        }
        // 累积分销商可提现佣金
        $model->where('user_id','=',$user_id)->setInc('integrales', $integral);
        Member::grantIntegral($user_id, $integral);
        // 记录分销商资金明细
        Capital::add([
            'user_id' => $user_id,
            'flow_type' => 10,
            'number' => $integral,
            'describe' => '订单积分结算',
            'wxapp_id' => $store_id,
            'is_status' => 2,

        ]);
        return true;
    }

    /**
     * 累计分销商成员数量
     * @param $dealer_id
     * @param $level
     * @return int|true
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function setMemberInc($dealer_id, $level)
    {
        $fields = [1 => 'first_num', 2 => 'second_num', 3 => 'third_num'];
        return self::where('user_id',$dealer_id)->setInc($fields[$level]);
    }

    /**
     * 增加分销订单总额
     * @param $user_id
     * @param $money
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function addMemberPrice($data, $money)
    {
        // 分销订单详情
        $model = Order::detail(['order_id' => $data['id']]);
        if (!$model || $model['is_settled'] == 1) {
            return false;
        }
        // 累积分销商可提现佣金
        self::where('user_id','=',$data['member_id'])->setInc('order_money', $money);

        // 记录分销商资金明细
        Capital::add([
            'user_id' => $data['member_id'],
            'flow_type' => 10,
            'number' => $money,
            'describe' => '增加分销订单金额',
            'wxapp_id' => $data['store_id'],
            'is_status' => 2,

        ]);
        return true;
    }

}