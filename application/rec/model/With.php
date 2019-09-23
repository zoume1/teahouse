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


    public function add($param)
    {
        return $this->save([
            'type' => $param['type'],
            'user_id' => $param['user_id'],
            'money' => $param['money'],
            'account_name' =>$param['account_name'],
            'opening_bank' => $param['opening_bank'],
            'card_num' =>$param['card_num'],
            'create_time' =>time()

        ]);
    }

}