<?php

namespace app\city\controller;
use think\Session;
use think\Validate;
use think\Request;
use app\city\model\CityRank;
use app\city\model\User as UserModel;

/**
 * PC端城市合伙人订单
 * Class CityOrder
 * @package app\city\controller
 */
class CityOrder extends Controller
{
    /**
     * 城市合伙人订单显示
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order_index()
    {
        $user = Session::get('User');
        $user_data = UserModel::detail($user);
        
    }
}