<?php

namespace app\city\controller;
use think\Session;
use think\Validate;
use think\Request;
use think\Db;
use app\city\model\CityRank;
use app\city\model\User as UserModel;
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
            $user_data = CityCopartner::ServerShow($user_id);
            return jsonSuccess('发送成功',$user_data);
        }
        
    }


        /**
     * 城市合伙人PC端申请注册
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register(Request $request)
    {

        if ($request->isPost()) {
            $data = Request::instance()->param();
            $rest = new UserModel;
            if($rest->submit($data))
            {
                return $this->renderSuccess('注册成功');
            }

            return $this->renderError($rest->getError() ?: '注册失败');
        }
    }

    /**
     * 城市合伙人选择省份直辖市
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function chooseCity()
    {
        $data = CityRank::getList();
        
        if($data['one'])
        {
            return jsonSuccess('发送成功',$data['one']);
        }
        return jsonError('发送失败'); 
    }


    /**
     * 城市合伙人选择等级城市
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function chooseRank(Request $request)
    {
        if ($request->isPost()) {
            $rank = $request->only(['rank_status'])['rank_status'];      
            $data = CityRank::detail($rank);
            if($data)
            {
                return jsonSuccess('发送成功',$data->toArray());
            }
            return jsonError('发送失败'); 
        }
    }


    /**
     * 城市合伙人PC端忘记密码
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function forget_password()
    {

        if ($this->request->isPost()) {
            $data = Request::instance()->param();
            $model = new UserModel;
            $rest = $model->forget($data);
            if($rest){
                return jsonSuccess('修改密码成功');
            } else {
                return jsonError($model->getError());
            }
        }     
    }

    /**
     * 汇款详情页面
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function remittance_login()
    {
        $user = Session::get('User');
        $order = Db::name('city_order')
                ->where('city_user_id','=',$user['user_id'])
                ->where('judge_status','>',1)
                ->find();
        if($order){
            $remittance = [
                'remittance_account' => $order['remittance_account'],
                'payment_document' => $order['payment_document'],
                'order_number'=>$order['order_number']
            ];
            return jsonSuccess('返回凭证成功',$remittance);
        }
        return jsonError('返回凭证失败');
         
    }

}
