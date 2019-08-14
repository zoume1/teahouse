<?php

namespace app\api\model\dealer;

use app\common\model\dealer\Referee as RefereeModel;

/**
 * 分销商推荐关系模型
 * Class Apply
 * @package app\api\model\dealer
 */
const Threes = 2;
class Referee extends RefereeModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [];

    /**
     * 创建推荐关系
     * @param $user_id
     * @param $referee_id
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function createRelation($user_id, $referee_id,$setting=Threes)
    {
        // 新增关系记录
        $model = new self;
        $model->add($referee_id, $user_id, 1);
        // # 记录二级推荐关系
        if ($setting >= 2) {
            // 二级分销商id
            $referee_2_id = self::getRefereeUserId($referee_id, 1, true);
            // 新增关系记录
            $referee_2_id > 0 && $model->add($referee_2_id, $user_id, 2);
        }
        // # 记录三级推荐关系
        if ($setting == 3) {
            // 三级分销商id
            $referee_3_id = self::getRefereeUserId($referee_id, 2, true);
            // 新增关系记录
            $referee_3_id > 0 && $model->add($referee_3_id, $user_id, 3);
        }
        return true;
    }

    /**
     * 新增关系记录
     * @param $dealer_id
     * @param $user_id
     * @param int $level
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    private function add($dealer_id, $user_id, $level = 1)
    {
        // 新增推荐关系
        $wxapp_id = self::$wxapp_id;
        $create_time = time();
        $this->insert(compact('dealer_id', 'user_id', 'level', 'wxapp_id', 'create_time'));
        // 记录分销商成员数量
        User::setMemberInc($dealer_id, $level);
        return true;
    }

    /**
     * 是否已存在推荐关系
     * @param $user_id
     * @return bool
     * @throws \think\exception\DbException
     */
    private static function isExistReferee($user_id)
    {
        return !!self::get(['user_id' => $user_id]);
    }

}