<?php

namespace app\city\model;
use think\Session;
use think\Model;

/**
 * 城市合伙人后台用户模型
 * Class User
 * @package app\admin\model\admin
 */
class User extends Model
{
    protected $table = "tb_city_copartner";
    /**
     * 城市合伙人用户登录
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($data)
    {
        // 验证用户名密码是否正确
        if (!$user = self::useGlobalScope(false)->where([
            'phone_number' => $data['phone_number'],
            'password' =>  changcang_hash($data['password'])
        ])->find()) {
            $this->error = '登录失败, 用户名或密码错误';
            return false;
        }

        if($this->useApplyStatus($user['status'])){
            // 保存登录状态
            Session::set('User', [
                'user' => [
                    'user_id' => $user['user_id'],
                    'phone_number' => $user['phone_number'],
                ],
                'is_login' => true,
            ]);
            return true;
        }
        
    }

    /**
     * 城市合伙人信息
     * @param $admin_user_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($admin_user_id)
    {
        return self::get($admin_user_id);
    }

    /**
     * 更新当前合伙人信息
     * @param $data
     * @return bool
     */
    public function renew($data)
    {
        if ($data['password'] !== $data['password_confirm']) {
            $this->error = '确认密码不正确';
            return false;
        }
        // 更新管理员信息
        if ($this->save([
                'phone_number' => $data['phone_number'],
                'password' => yoshop_hash($data['password']),
            ]) === false) {
            return false;
        }
        // 更新session
        Session::set('yoshop_admin.user', [
            'user_id' => $this['user_id'],
            'phone_number' => $data['phone_number'],
        ]);
        return true;
    }

    /**
     * 设置是否使用全局查询范围
     * @param bool $use 是否启用全局查询范围
     * @access public
     * @return Query
     */
    public static function useGlobalScope($use)
    {
        $model = new static();
        return $model->db($use);
    }


    /**
     * 是否使用全局查询范围
     * @param bool $use 是否启用全局查询范围
     * @access public
     * @return Query
     */
    public  function useApplyStatus ($status)
    {
        switch($status)
        {
            case 1:
                $this->error = '您的城市合伙人申请还在审核中，请稍后重试';
                return false;
                break;
            case 2:
                return true;
                break;
            case 3:
                $this->error = '您的城市合伙人申请被拒绝，请重行申请';
                return false;
                break;
            default:
                return false;
        }
    }

}