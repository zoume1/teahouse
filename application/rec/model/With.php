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
            'invoice_type' => $param['invoice_type'],
            'user_id' => $param['user_id'],
            'money' => $param['money'],
            'balance' => $param['balance'],
            'express_name' =>$param['express_name'],
            'odd_num' =>$param['odd_num'],
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
        return self::where(['user_id'=>$uid,'status'=>2])-> sum ('money')  ? self::where(['user_id'=>$uid,'status'=>2])-> sum ('money') : 0;
    }

    /**gy
     *  admin后台显示资金提现
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function management_index($search)
    {
        $model = new static;
        !empty($search) && $model->setWhere($search);
        $rest = $model->order(['create_time' => 'desc'])
        ->paginate(20, false, [
            'query' => \request()->request()
        ]);
        return $rest;
        
    }

    
    /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {
        if (isset($query['name']) && !empty($query['name'])) {
            $this->where('phone_number', 'like', '%' . trim($query['name']) . '%');
        }
        if (isset($query['status']) && !empty($query['status'])) {
            $this->where('status', '=', $query['status']);
        }
        if (isset($query['start_time']) && !empty($query['start_time'])) {
            $start_time = strtotime($query['start_time']);
            $time_condition  = "create_time > {$start_time} ";
            $this->where($time_condition);
        }
        if (isset($query['end_time']) && !empty($query['end_time'])) {
            $end_time = strtotime($query['end_time']);
            $time_condition  = "create_time < {$end_time} ";
            $this->where($time_condition);
        }
        if(isset($query['end_time']) && !empty($query['end_time']) && isset($query['start_time']) && !empty($query['start_time'])){
            $start_time = strtotime($query['start_time']);
            $end_time = strtotime($query['end_time']);
            $time_condition  = "create_time > {$start_time} and create_time< {$end_time} ";
            $this->where($time_condition);
        }
    }

}