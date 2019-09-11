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

    //获取信息
    public function shop($my_invitation)
    {
//        print_r($my_invitation);die;
        return self::get(['my_invitation'=>$my_invitation]);
    }
    

}