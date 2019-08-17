<?php
namespace app\admin\model;
use think\Model;

class MemberGrade extends Model
{
    protected $name = 'member_grade';

    /**
     * 获取店铺所有等级会员
     * @param $user_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($store_id)
    {
        return self::get(['store_id'=>$store_id])->select()->toArray();
    }

    /**
     * 获取店铺最低等级会员
     * @param $user_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function getLowestMember($store_id)
    {
        $model = self::where(['store_id'=>$store_id])->value('member_grade_id');
        return $model ? $model : false;
    }

}