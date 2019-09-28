<?php

namespace app\city\controller;
use think\Session;
use think\Validate;
use think\Request;
use think\Db;
use app\city\model\CityRank;
use app\city\model\User as UserModel;
use app\city\model\CityBack;
use app\city\model\CityCopartner;
use app\city\model\CityOrder as Order;


/**
 * 公众号城市合伙人系统
 * Class WeiChatSystem
 * @package app\city\controller
 */
class WeiChatSystem extends Controller
{
    /**
     *   公众号城市合伙人系统页面
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function WeiChatCityServerShow(Request $request)
    {

        if ($request->isPost()) 
        {
            $user_id = $request->only(['user_id'])['user_id'];
            $user_data = CityCopartner::ServerShow($user_id);
            return jsonSuccess('发送成功',$user_data);
        }
        
    }

    /**
     *   公众号城市合伙人保底佣金
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function WeiChatCityCommissionShow(Request $request)
    {

        if ($request->isPost()) {
            $user_id = $request->only(['user_id'])['user_id'];
            $data = CityCopartner::CommissionShow($user_id);
            return jsonSuccess('发送成功',$data);
        }
        
    }


    /**
     * 城市合伙人公众号达标佣金总额查询页面
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function WeiChatCityReachCommissionShow(Request $request)
    {

        if ($request->isPost()) {
            $user_id = $request->only(['user_id'])['user_id'];
            $data = CityCopartner::ReachCommissionShow($user_id);
            return jsonSuccess('发送成功',$data);
        }
    }

    /**
     * 城市合伙人公众号城市总计商户页面
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function WeiChatCityAccumulativeShow(Request $request)
    {

        if ($request->isPost()) {
            $user_id = $request->only(['user_id'])['user_id'];
            $data = CityCopartner::AccumulativeShow($user_id);
            return jsonSuccess('发送成功',$data);
        }
    }


    /**
     * 城市合伙人我邀请的商户页面
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function WeiChatCityMyinviteShow(Request $request)
    {

        if ($request->isPost()) {
            $user_id = $request->only(['user_id'])['user_id'];
            $data = CityCopartner::MyinviteShow($user_id);
            return jsonSuccess('发送成功',$data);
        }
    }


    /**
     * 城市合伙人商户市场反馈
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function market_feedback(Request $request)
    {

        if ($request->isPost()) {
            $data = $request->param();
            $model = new CityBack;
            $validate     = new Validate([
                ['user_id', 'require', 'user_id不能为空'],
                ['text', 'require', '反馈内容不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($data)) {
                $error = $validate->getError();
                return jsonError($error);
            } else {
                $data['create_time'] = time();
                $bool = $model->city_back_add($data);
                if($bool){
                    return jsonSuccess('反馈成功');
                } else {
                    return jsonError('反馈成功');
                }
            }
        }
    }


    /**
     * 城市合伙人官方回复
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function admin_market_feedback(Request $request)
    {

        if ($request->isPost()) {
            $user_id = $request->only(['user_id'])['user_id'];
            $model = new CityBack;
            $validate     = new Validate([
                ['user_id', 'require', 'user_id不能为空'],
                ['text', 'require', '反馈内容不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($data)) {
                $error = $validate->getError();
                return jsonError($error);
            } else {
                $data['create_time'] = time();
                $bool = $model->city_back_add($data);
                if($bool){
                    return jsonSuccess('反馈成功');
                } else {
                    return jsonError('反馈成功');
                }
            }
        }
    }




}
