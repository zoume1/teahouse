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
    protected $hidden = ['account_name','opening_bank','card_num','update_time'];
    /**
     * 申请
     * @param $param
     * @return false|int
     */
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

    /**
     * 申请记录
     * @param $uid
     * @throws \think\exception\DbException
     */
    public function details($uid)
    {
        return self::all(['user_id'=>$uid]) ?  self::all(['user_id'=>$uid])->toArray(): returnJson(0,'数据有误');
    }

    /**
     * 统计已提现金额
     * @param $uid
     * @return float|int
     */
    public static function wals($uid)
    {
        return self::where(['user_id'=>$uid,'status'=>2])->sum ('money');
    }

}