<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/12
 * Time: 9:37
 */
namespace app\rec\model;


use think\Model;

class Copartner extends Model{

    protected $table = "tb_city_copartner";
    protected $resultSetType = 'collection';

    //新增
    public function add($status,$name,$image,$card,$tel,$img, $password, $advantage ,$city)
    {
        return $this->save([
            'id_status' => $status,
            'user_name' => $name,
            'id_image' => $image,
            'id_card' =>$card,
            'phone_number' => $tel,
            'id_image_reverse' =>$img,
            'password' =>$password,
            'advantage' => $advantage,
            'city_address' =>$city,
            'status' =>1,
            'create_time' =>time(),

        ]);
    }
}