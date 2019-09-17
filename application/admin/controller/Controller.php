<?php

namespace app\admin\controller;

use think\Config;
use think\Request;
use think\Session;



/**
 * 超管后台控制器基类
 * Class Controller
 * @package app\admin\controller
 */
class Controller extends \think\Controller
{

    /**
     * 验证登录状态
     * @return bool
     */
    private function checkLogin()
    {
        // 验证当前请求是否在白名单
        if (in_array($this->routeUri, $this->allowAllAction)) {
            return true;
        }
        // 验证登录状态
        if (empty($this->admin)
            || (int)$this->admin['is_login'] !== 1
        ) {
            $this->redirect('passport/login');
            return false;
        }
        return true;
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
        if(!empty($url) && !empty($data))
        {
            return json(compact('code', 'msg', 'url', 'data'));
        } elseif(empty($url) && !empty($data))
        {
            return json(compact('code', 'msg','data'));
        } elseif(!empty($url) && empty($data))
        {
            return json(compact('code', 'msg','url'));
        } else {
            return json(compact('code', 'msg'));
        }
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
