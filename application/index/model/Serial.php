<?php

namespace app\index\model;
use think\Session;
use think\Model;
use think\Db;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;


/**
 * 资金明细模型
 * Class StoreSetting
 * @package app\city\model
 */
class Serial extends Model
{
    protected $table = "tb_serial";
	protected $resultSetType = 'collection';





    /**gy
     * 资金明细列表
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function index($search)
    {
        $model = new static;
        // 查询条件
        $store_id = Session::get('store_id');
        !empty($search) && $model->setWhere($search);
        $rest = $model->order(['create_time' => 'desc'])
        ->where('store_id','=',$store_id)
        ->paginate(20, false, [
            'query' => \request()->request()
        ]);
        return $rest;
    }

    /**gy
     *  资金明细添加
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function serial_add($data)
    {
        $model = new static;
        $rest = $model->save($data);
        return $rest ? $rest : false;
        
    }


        /**gy
     * 获取城市入驻资料
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        $rest =  self::get($id);
        return $rest ? $rest->toArray() :false;
    }

    /**gy
     *  资金明细列表更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function meal_update($data)
    {

        $model = new static;
        $rest = $model -> allowField(true)->save($data,['id'=>$data['id']]);
        return $rest ? $rest : false;
        
    }

    /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {
        $store_id = Session :: get("store_id");
        $this->where('store_id', '=', $store_id);
        if (isset($query['start_time']) && !empty($query['end_time'])) {
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
        if (isset($query['status']) && !empty($query['status'])) {
            $this->where('status', '=', $query['status']);
        }
        if (isset($query['type']) && !empty($query['type'])) {
            $this->where('type', '=', $query['type']);
        }
        if (isset($query['phone_number']) && !empty($query['phone_number'])) {
            $this->where('phone_number', '=', $query['phone_number']);
        }
    }

}