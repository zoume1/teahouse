<?php

namespace app\city\controller;
use think\Session;
use think\Validate;
use think\Request;
use app\city\model\CityRank;
use app\city\model\User as UserModel;

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

        if ($this->request->isAjax()) {
            $model = new UserModel;
            if ($model->login($this->postData('User'))) {
                return $this->renderSuccess('登录成功');
            }
            return $this->renderError($model->getError() ?: '登录失败');
        }
        return false;
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

}
