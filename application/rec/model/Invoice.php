<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/11
 * Time: 15:59
 */
namespace app\rec\model;


use think\Model;

class Invoice extends Model{

    protected $table = "tp_invoice";
    protected $resultSetType = 'collection';


    //新增企业开票
    public function add_enterprise($uid, $no, $type,$status, $rise ,$duty,$price)
    {
        return $this->save([
            'user_id' => $uid,
            'no'=> $no,
            'type' => $type,
            'status' => $status,
            'rise' =>$rise,
            'duty' =>$duty,
            'price' =>$price,
            'state' =>1,
            'create_time' => time(),


        ]);
    }

    //新增个人开票
    public function add_personal($uid,$no, $type,$status, $rise ,$tel,$price)
    {
        return $this->save([
            'user_id' => $uid,
            'no'=> $no,
            'type' => $type,
            'status' => $status,
            'rise' =>$rise,
            'phone' =>$tel,
            'price' =>$price,
            'state' =>1,
            'create_time' => time(),


        ]);
    }
}