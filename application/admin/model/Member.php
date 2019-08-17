<?php
namespace app\admin\model;
use think\Model;

class Member extends Model
{
    protected $name = 'member';

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
     * 发放分销商佣金
     * @param $user_id
     * @param $money
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    
    public static function grantMoney($user_id, $money)
    {
        // 分销商详情
        $model = static::detail($user_id);
        if (!$model) {
            return false;
        }
        // 累积分销商可提现佣金
        $model->where('member_id','=',$user_id)->setInc('member_wallet', $money);
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
    public static function grantIntegral($user_id,$integral)
    {
        // 分销商详情
        $model = static::detail($user_id);
        if (!$model) {
            return false;
        }
        // 发放分销商积分
        $model->where('member_id','=',$user_id)->setInc('member_integral_wallet', $integral);
        return true;
    }


    /**
     * 判断用户是否是普通会员
     * @param $user_id
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function isCommonMember($user_id)
    {
        // 分销商详情
        $model = static::detail($user_id);
        if (!$model) {
            return false;
        }
        // 发放分销商积分
        $model->where('member_id','=',$user_id)->setInc('member_integral_wallet', $integral);
        return true;
    }
 
 
}