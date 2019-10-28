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
            $user_id = $request->param();
            $validate     = new Validate([
                ['user_id', 'require', 'user_id不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($user_id)) {
                $error = $validate->getError();
                return jsonError($error);
            } else {
                $data =  CityBack::detail($user_id);
                if($data){
                    return jsonSuccess("发送成功", $data);
                } else {
                    return jsonError("暂无回复");
                }
            }
        }
    }

    /**
     * 城市合伙人官方回复总数
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function admin_market_feedback_number(Request $request)
    {

        if ($request->isPost()) {
            $firstday = date('Y-m-01', strtotime(date("Y-m-d")));
            $Begin = strtotime(date('Y-m-01', strtotime(date("Y-m-d"))));
            $End= strtotime(date('Y-m-d', strtotime("$firstday +1 month -1 day")));
            $time_condition  = "create_time>{$Begin} and create_time< {$End}";
            $user_id = $request->param();
            $validate     = new Validate([
                ['user_id', 'require', 'user_id不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($user_id)) {
                $error = $validate->getError();
                return jsonError($error);
            } else {
                $number = Db::name('city_back')
                ->where('user_id',$user_id['user_id'])  
                ->where('return_time','>',0)
                ->count();

                $comment_value = Db::name('city_comment')
                ->where('city_user_id',$user_id['user_id'])  
                ->where($time_condition)
                ->avg('grade');

                $comment = $comment_value ? $comment_value : 5;
                return jsonSuccess("发送成功",['number'=>$number,'comment_value'=>$comment]);
            }
        }
    }




}
