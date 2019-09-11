<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;



const STATUS_NOPAY = 1;        //审核中
const STATUS_PAYED = 2;        //通过
const STATUS_OTHER = 3;        //拒绝

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
        if ($this->isStatus($data)) {
            $this->error = '登录失败, 账号或密码错误';
            return false;
        }  
        if($this->useApplyStatus($user['status'])){
            // 保存登录状态
            Session::set('User', [
                'User' => [
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

    /**
     * 城市合伙人信息
     * @param $admin_user_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public function isStatus($data)
    {
        $user = self::useGlobalScope(false)->where([
            'phone_number' => $data['phone_number'],
            'password' =>  $data['password']
        ])->find();
        return $user ? true : false;
    }

        /**
     * 城市是否已被注册
     * @param $admin_user_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public function cityStatus($data)
    {
        $user = self::where([
            'phone_number' => $data['phone_number'],
            'city_address'=>$data['city_address'],
            'status' => STATUS_PAYED 
        ])->find();
        return $user ? true : false;
    }

       /**
     * 提交申请
     * @param User 
     * @param $data
     * @return false|int
     * @throws BaseException
     */
    public function submit($data)
    {
        // 数据验证
        $this->validation($data);
        // 新增申请记录
        $this->save($data); 
        
    }

        /**
     * 数据验证
     * @param $dealer
     * @param $data
     * @throws BaseException
     */
    private function validation($data)
    {
        
        $validate     = new Validate([
            ['phone_number', 'require', '账号不能为空'],
            ['phone_number', 'require|mobile', '手机格式错误'],
            ['password', 'require', '密码不能为空'],
            ['id_image', 'require', '身份证正面照不能为空'],
            ['id_card', 'require', '身份证不能为空'],
            ['id_card', 'require|idCard', '身份证格式错误'],
            ['city_address', 'require', '城市不能为空'],
            ['user_name', 'require', '姓名不能为空'],
            ['id_image_reverse','require','证件证明不能为空']
        ]);
        //验证部分数据合法性
        if (!$validate->check($data)) {
            $this->error = $validate->getError();
            return false;
        }
       
        // 最否注册
        if ($this->isStatus($data)) {
            throw new BaseException(['msg' => '该账号已注册']);
        }
        if ($this->cityStatus($data)) {
            throw new BaseException(['msg' => '该城市已有合伙人注册']);
        }

        return true;
    }
 

}