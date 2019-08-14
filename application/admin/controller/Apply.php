<?php

namespace app\admin\controller;

use app\api\model\dealer\Apply as DealerApplyModel;

/**
 * 分销商申请
 * Class Apply
 * @package app\api\controller
 */
class Apply  extends Controller
{


    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->user = $this->getUser();   // 用户信息
    }

    /**
     * 提交分销商申请
     * @param string $name
     * @param string $mobile
     * @return array
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function submit($data)
    {
        $model = new DealerApplyModel;
        if ($model->submit($data)) {
            halt(111111);
            return $this->renderSuccess();
        }
        return $this->renderError($model->getError() ?: '提交失败');
    }

}