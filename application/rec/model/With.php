<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/23
 * Time: 10:15
 */
namespace app\rec\model;

use think\Model;

/**
 * Class With微信公众号提现
 * @package app\rec\model
 */
class With extends Model
{
    protected $table = "tp_wx_with";
    protected $resultSetType = 'collection';


    public function add($type, $uid, $money ,$name,$bank,$num)
    {
        return $this->save([
            'type' => $type,
            'user_id' => $uid,
            'money' => $money,
            'account_name' =>$name,
            'opening_bank' => $bank,
            'card_num' =>$num,
            'create_time' =>time()

        ]);
    }

}