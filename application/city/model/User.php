<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\city\controller\Picture;
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
     * @param $user
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($data)
    {
        // 验证用户名密码是否正确
        $user = $this->isStatus($data);
        if ($user) {
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
            'password' =>  changcang_hash($data['password'])
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
        $resgister = $this->validation($data);
        // 新增申请记录
        $img = $this->image();
        $data['password'] = changcang_hash($data['password']);
        if(!$this->getError()){
           return $this->save([	
            'id_status' => $resgister['id_status'],
            'user_name' => $resgister['user_name'],
            'password' => changcang_hash($resgister['password']),
            'id_card' => $resgister['id_card'],
            'company_prove'=> $resgister['company_prove'],
            'company_name'=> $resgister['company_name'],
            'id_image' => $img['id_image'],
            'id_image_reverse' => $img['id_image_reverse'],
            'post_address_one' => $resgister['post_address_one'],
            'post_address_two' => $resgister['post_address_two'],
            'post_address_three' => $resgister['post_address_three'],
            'detail' => $resgister['detail'],
            'phone_number' => $resgister['phone_number'],
            'advantage' => $resgister['advantage'],
            'apply_city_one' => $resgister['apply_city_one'],
            'city_rank' => $resgister['city_rank'],
            'create_time'=>time(),
            'city_address' => $resgister['city_address']]);
        }

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
            ['phone_number', 'require', '手机号不能为空'],
            ['password', 'require', '密码不能为空'],
            ['id_card', 'require', '身份证不能为空'],
            ['city_address', 'require', '入驻城市不能为空'],
            ['user_name', 'require', '姓名不能为空'],
            ['identifying_code','require','验证码不能为空'],
            ['detail','require','请填写详细地址'],
            ['city_rank','require','城市等级不能为空'],
        ]);
        $identifying_code = Session::get('identifying_code');
        //验证部分数据合法性
        if (!$validate->check($data)) {
            $this->error = $validate->getError();
            return false;
        }
        //手机格式
        if(!isMobile($data['phone_number'])) {
            $this->error = '手机格式不正确';
            return false;
        }
        //手机验证码
        if($data['identifying_code'] != $identifying_code) {
            $this->error = '验证码不正确,请重新输入';
            return false;
        }
        // 最否注册
        if ($this->isStatus($data)) {
            $this->error = '该账号已注册';
            return false;
        }
        if ($this->cityStatus($data)) {
            $this->error =  '该城市已有合伙人注册';
            return false;  
        }
        halt($data);
        return $data;
    }

    /**
     * 上传图片
     * @param User 
     * @param $data
     * @return false|int
     * @throws BaseException
     */
    public function image()
    {
        // 数据验证
        $rest = new Picture;
        $id_image = $rest->upload_picture('id_image');
        $id_image_reverse = $rest->upload_picture('id_image_reverse');

        if($id_image && $id_image_reverse){
            return ['id_image' =>$id_image,'id_image_reverse' => $id_image_reverse ] ;
        } else {
            $this->error =  '图片上传失败';
            return 0;  
        }
 
    }
 

}