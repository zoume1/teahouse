<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use think\Db;
use app\common\exception\BaseException;
use app\admin\model\Store as AddStore;
use app\city\model\User as UserModel;


const CITY_ONE_STATUS = 1;

/**gy
 * 分销代理模型
 * Class CityDetail
 * @package app\city\model
 */
class CityDetail extends Model
{
    protected $table = "tb_city_detail";
    protected $resultSetType = 'collection';


    /**gy
     *  分销代理列表显示
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_detail($search)
    {
        $model = new static;
        // 查询条件
        !empty($search) && $model->where('phone_number|order_number', 'like', "%$search%");
        $rest = $model->order(['create_time' => 'desc'])
        ->paginate(20, false, [
            'query' => \request()->request()
        ]);;
        return $rest;
        
    }


    
    /**gy
     *  城市累计商户获得佣金
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_store_commission($city_user_id)
    {
        $model = new static;
        !empty($city_user_id) && $rest = $model->where('city_user_id', '=', $city_user_id)->sum('commision');
        return $rest ? $rest : 0;
        
    }

    /**gy
     * 生成分销代理订单
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function store_order_commission($order_data,$store_data)
    {
        $model = new static;
        $address_two = explode(",", $store_data['address_data']);
        $city_user_id = AddStore::find_city_user($address_two[CITY_ONE_STATUS]);
        $retutn_data = find_rank_data($store_data['highe_share_code'],$order_data['pay_money'],$city_user_id);
        $data = [
            'order_number' => $order_data['order_number'],
            'phone_number' => $store_data['phone_number'],
            'share_code' => $store_data['share_code'], //自己的分享码
            'set_meal' => enter_name($order_data['enter_all_id']),  
            'meal_price' =>$order_data['pay_money'],
            'highe_share_code' => $store_data['highe_share_code'],
            'commision' => $retutn_data['commision'],
            'higher_phone' => $retutn_data['higher_phone'],
            'base_commision' => $retutn_data['base_commision'],
            'reach_commision' => $retutn_data['reach_commision'],
            'create_time' => $order_data['create_time'],
            'update_time' => time(),
            'city_user_id' => $city_user_id,
            'city_address' => $address_two[CITY_ONE_STATUS],
        ];
        $rest = $model->allowField(true)->save($data);
        return $rest ? $rest : false;
        //传入套餐id生成生成套餐名
        //是否有上级账号计算分销佣金
        //判断有无城市合伙人user_id 有计算保底佣金 + 是否有达标佣金
    }


    /**gy
     *  更新城市所有店铺所属合伙人id
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public  function city_store_update($city_address,$city_user_id)
    {
        
        $rest = Db::name('city_detail')->where('city_user_id', '=', 0)
                ->where('city_address',$city_address)
                ->field('id')
                ->select();

        if($rest){
            foreach($rest as $value){
                $value['city_user_id'] = $city_user_id;
                $data[] = $value;
            }
            $detail = $this->isUpdate(false)->saveAll($data);      
        }
         
        return true;
    }

    /**gy
     *  城市累计商户明细
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_store_detail()
    {
        $model = new static;
        // 查询条件
        $rest = $model->order(['create_time' => 'desc'])
        ->paginate(5, false, [
            'query' => \request()->request()
        ]);
        return $rest;
        
    }

        /**gy
     *  城市累计商户明细
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_store_search($search)
    {
        $model = new static;
        // 查询条件
        $model->setWhere($search);
        $rest = $model->order(['create_time' => 'desc'])
        ->paginate(5, false, [
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
        $user = Session::get('User');
        $user_data = UserModel::detail(['user_id'=>$user['user_id']]);
        // $this->where('city_user_id', '=' ,$user['user_id']);
        if (isset($query['status']) && $query['status'] == 1) {
            $this->where('highe_share_code', '=', $user_data['my_invitation']);
        }
        if (isset($query['name']) && !empty($query['name'])) {
            $this->where('phone_number|share_code', 'like', '%' . trim($query['name']) . '%');
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