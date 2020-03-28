<?php

namespace app\index\model;

use think\Session;
use think\Model;
use think\Db;
use think\Validate;
use app\common\exception\BaseException;
use app\admin\model\Store;
use app\city\model\CityCopartner;

/**
 * 资金奖罚模型
 * Class StoreSetting
 * @package app\city\model
 */
class Sanction extends Model
{
    protected $table = "tb_sanction";
    protected $resultSetType = 'collection';





    /**gy
     * 资金奖罚列表
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function index($search)
    {
        // halt($search);
        $model = new static;
        // 查询条件
        !empty($search) && $model->setWhere($search);
        $rest = $model->order(['create_time' => 'desc'])
            ->paginate(20, false, [
                'query' => \request()->request()
            ]);
        return $rest;
    }

    /**gy
     *  资金奖罚添加
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function sanction_add($data)
    {
        $model = new static;
        switch ($data['id_status']) {
            case 1:
                $find_bool = Store::detail(['phone_number' => $data['phone_number']]);
                if ($find_bool) {
                    $data['user_name'] = $find_bool['contact_name'];
                    $data['city_address'] = explode(',',$find_bool['address_data'])[1];
                    $add_bool = $model->save($data);
                    Store::store_sanction($data);
                } else {
                    $add_bool = 2;
                }
                break;
            case 2:
                $find_bool = CityCopartner::detail(['phone_number' => $data['phone_number']]);
                if ($find_bool) {
                    $data['user_name'] = $find_bool['user_name'];
                    $data['city_address'] = $find_bool['city_address'];
                    $add_bool = $model->save($data);
                    CityCopartner::city_sanction($data);
                } else {
                    $add_bool = 3;
                }
                break;
            default:
                break;
        }
        return $add_bool;
    }


    /**gy
     * 获取资金奖罚
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        $rest =  self::get($id);
        return $rest ? $rest->toArray() : false;
    }

    /**gy
     *  资金奖罚列表更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function meal_update($data)
    {

        $model = new static;
        $rest = $model->allowField(true)->save($data, ['id' => $data['id']]);
        return $rest ? $rest : false;
    }

    /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {
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
        if (isset($query['end_time']) && !empty($query['end_time']) && isset($query['start_time']) && !empty($query['start_time'])) {
            $start_time = strtotime($query['start_time']);
            $end_time = strtotime($query['end_time']);
            $time_condition  = "create_time > {$start_time} and create_time< {$end_time} ";
            $this->where($time_condition);
        }
        if (isset($query['name']) && !empty($query['name'])) {
            $this->where('phone_number', '=', $query['name']);
        }

    }

    /**gy
     * 查询用户是否存在
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function find_user($data)
    {
        $model = new static;
        switch ($data['id_status']) {
            case 1:
                $find_bool = Store::detail(['phone_number' => $data['phone_number']]);
                if ($find_bool) {
                    $add_bool = $model->sanction_add($data);
                    if (!$add_bool) {

                        $this->error = '添加奖罚失败';
                    }
                    $this->success = '添加奖罚成功';
                }
                $this->error = '该商户不存在，请仔细核对';
                break;
            case 2:
                $find_bool = CityCopartner::detail(['phone_number' => $data['phone_number']]);
                if ($find_bool) {
                    $add_bool = $model->sanction_add($data);
                    if (!$add_bool) {
                        $this->error = '添加奖罚失败';
                    }
                    $this->success = '添加奖罚成功';
                }
                $this->error = '该城市合伙不存在，请仔细核对';
                break;
        }
    }
}
