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
     * 获取店铺等级会员id
     * @param $user_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function getGradeId($member_id)
    {
        $member_grade_id = self::where(['member_id'=>$member_id])->value('member_grade_id');
        return $member_grade_id;
    }


    /**
     * 判断
     * @param $user_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function is_scope($member_id,$member_data)
    {
        $member_grade_name = self::where(['member_id'=>$member_id])->value('member_grade_name');
        if(!empty($member_data)){
            $member_data = json_decode($member_data,true);
            if(in_array($member_grade_name,$member_data)){
                return true;
            }
            return false;
        }
        return false;
    }
 
 
}