<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/10
 * Time: 16:15
 */
namespace app\rec\model;

use think\Model;

class User extends Model
{
    protected $table = "tb_pc_user";
    protected $resultSetType = 'collection';

    //新增
    public function add($phone, $password, $invit ,$re_code)
    {
        return $this->save([
            'phone_number' => $phone,
            'password' => $password,
            'invitation' => $invit,
            'my_invitation' =>$re_code,
            'create_time' => time(),
            'status' =>1,
        ]);
    }

    //修改密码
    public function edit($tel, $password)
    {
        return $this->save([
            'password' => $password,

        ],['phone_number' => $tel,]);
    }

    //修改手机号
    public function edit_tel($uid, $phone)
    {
        return $this->save([
            'phone_number' => $phone,

        ],['id' => $uid,]);
    }
    //获取信息
    public function shop($my_invitation)
    {
    //      print_r($my_invitation);die;
        return self::get(['my_invitation'=>$my_invitation]);
    }
    
    //获取个人信息
    public function user_index($uid)
    {
        return self::get(['id'=>$uid]) ? self::get(['id'=>$uid])->toArray() : returnJson(0,'数据有误');
    }
    

    /**gy
     * 获取信息
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($data)
    {
        $rest = self::get($data);
        return $rest ? $rest->toArray() : false;
    }



    /**gy
     *  更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function user_update($data)
    {

        $model = new static;
        $rest = $model -> allowField(true)->save($data,['id'=>$data['id']]);
        return $rest ? $rest : false;
        
    }

}