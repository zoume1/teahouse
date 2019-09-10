<?php

namespace app\city\controller;
use think\Session;
use think\Controller;
use think\Validate;
use think\Request;
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
                $this->success("登录成功", url("admin/Goods/index"));
            }
            return $this->error($model->getError() ?: '登录失败');
        }
        return $this->fetch('index/index/city_login');
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
     * 返回封装后的 API 数据到客户端
     * @param int $code
     * @param string $msg
     * @param string $url
     * @param array $data
     * @return array
     */
    protected function renderJson($code = 1, $msg = '', $url = '', $data = [])
    {
        return compact('code', 'msg', 'url', 'data');
    }

    /**
     * 返回操作成功json
     * @param string $msg
     * @param string $url
     * @param array $data
     * @return array
     */
    protected function renderSuccess($msg = 'success', $url = '', $data = [])
    {
        return $this->renderJson(1, $msg, $url, $data);
    }

    /**
     * 返回操作失败json
     * @param string $msg
     * @param string $url
     * @param array $data
     * @return array
     */
    protected function renderError($msg = 'error', $url = '', $data = [])
    {
        return $this->renderJson(0, $msg, $url, $data);
    }

    /**
     * 获取post数据 (数组)
     * @param $key
     * @return mixed
     */
    protected function postData($key)
    {
        return $this->request->post($key . '/a');
    }
}
