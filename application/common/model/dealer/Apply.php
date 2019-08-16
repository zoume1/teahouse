<?php

namespace app\common\model\dealer;

use app\common\model\BaseModel;
use app\common\model\dealer\User;

/**
 * 分销商申请模型
 * Class Apply
 * @package app\common\model\dealer
 */
class Apply extends BaseModel
{
    protected $name = 'dealer_apply';

      /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'create_time',
        'update_time',
    ];

    /**
     * 获取器：申请时间
     * @param $value
     * @return false|string
     */
    public function getApplyTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 获取器：审核时间
     * @param $value
     * @return false|string
     */
    public function getAuditTimeAttr($value)
    {
        return $value > 0 ? date('Y-m-d H:i:s', $value) : 0;
    }

    // /**
    //  * 关联推荐人表
    //  * @return \think\model\relation\BelongsTo
    //  */
    // public function referee()
    // {
    //     return $this->belongsTo('app\common\model\User', 'referee_id')
    //         ->field(['user_id', 'nickName']);
    // }

    /**
     * 销商申请记录详情
     * @param $where
     * @return Apply|static
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        return self::get($where);
    }


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
     * 提交申请
     * @param $user
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
            'referee_id' => $user['inviter_id'],
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
    public function add($user, $data)
    {
        // // 实例化模型
        $model = self::detail(['user_id' => $user['member_id']]) ?: $this;
        // 更新记录
        $this->startTrans();
        try {
            $data['create_time'] = time();
            //保存申请信息
            $model->save($data);
            //无需审核，自动通过
            
            //新增分销商用户记录
            User::add($user['member_id'], [
                'real_name' => $data['real_name'],
                'mobile' => $data['mobile'],
                'referee_id' => $data['referee_id'],
                'wxapp_id' => $data['wxapp_id'],
                'create_time'=>$data['create_time']
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