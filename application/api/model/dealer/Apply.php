<?php

namespace app\api\model\dealer;

use app\common\model\dealer\Apply as ApplyModel;


/**
 * 分销商申请模型
 * Class Apply
 * @package app\api\model\dealer
 */
class Apply extends ApplyModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'create_time',
        'update_time',
    ];

    /**
     * 是否为分销商申请中
     * @param $user_id
     * @return bool
     * @throws \think\exception\DbException
     */
    public static function isApplying($user_id)
    {
        $detail = self::detail(['user_id' => $user_id]);
        return $detail ? ((int)$detail['apply_status'] === 10) : false;
    }

    /**
     * 分销商入驻
     * @param $user
     * @param $name
     * @param $mobile
     * @return bool
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function submit($user)
    {
        // 数据整理
        $data = [
            'user_id' => $user['member_id'],
            'real_name' => trim($user['member_name']),
            'mobile' => trim($user['member_phone_num']),
            'referee_id' => Referee::getRefereeUserId($user['member_id'], 1),
            'wxapp_id' => $user['store_id'],
        ];
        return $this->add($user, $data);
    }

    /**
     * 更新分销商申请信息
     * @param $user
     * @param $data
     * @return bool
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    private function add($user, $data)
    {

        // 实例化模型
        $model = self::detail(['user_id' => $user['member_id']]) ?: $this;
        // 更新记录
        $this->startTrans();
        try {
            // $data['create_time'] = time();
            // 保存申请信息
            $model->save($data);
            // 无需审核，自动通过
            // 新增分销商用户记录

            User::add($user['member_id'], [
                'real_name' => $data['real_name'],
                'mobile' => $data['mobile'],
                'referee_id' => $data['referee_id'],
                'wxapp_id' => $data['wxapp_id'],
            ]);
            $this->commit();
            return true;
        } catch (\Exception $e) {

            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

}
