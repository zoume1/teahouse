<?php

namespace app\city\controller;
use think\Session;
use think\Validate;
use think\Request;
use app\city\model\CityRank;
use app\city\model\User as UserModel;
use app\city\model\CityOrder as Order;


/**
 * PC端城市合伙人认证
 * Class Passport
 * @package app\city\controller
 */
class Passport extends Controller
{
    /**
     * 城市合伙人PC端登录
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()
    {

        if ($this->request->isPost()) {
            $model = new UserModel;
            $code = $model->login($this->postData('User'));
            return jsonSuccess($model->getError(),array(),$code);
        }
        
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        Session::clear('User');
        $this->redirect('index/index/city_login');
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
                ->where('judge_status','<',3)
                ->find();
        if($order){
            $remittance = [
                'remittance_account' => $order['remittance_account'],
                'payment_document' => $order['payment_document']
            ];
            return jsonSuccess('返回凭证成功',$remittance);
        }
        return jsonError('返回凭证失败');
         
    }

}
