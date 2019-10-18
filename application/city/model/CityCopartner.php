<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Db;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;
use app\city\model\CitySetting;

// const CITY_ONE = 1;

/**
 * 城市入驻资料模型
 * Class CityCopartner
 * @package app\city\model
 */
class CityCopartner extends Model
{
    protected $table = "tb_city_copartner";


    /**gy
     *  城市入驻资料显示
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_copartner($search)
    {
        $model = new static;
        // 查询条件
        !empty($search) && $model->setWhere($search);
        $rest = $model->order(['create_time' => 'desc'])
        ->where('status', '>',0)
        ->paginate(20, false, [
            'query' => \request()->request()
        ]);
        return $rest;
        
    }

    /**gy
     * 获取城市入驻资料
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($meal_id)
    {
        $rest =  self::get($meal_id);
        return $rest ? $rest->toArray() :false;
    }


    /**获取所有城市入驻资料
     * gy
     * @param $useid
     * @return false|static[]
     * @throws \think\exception\DbException
     */
    public static function getList()
    {
        return self::all();
    }

    /**gy
     *  城市入驻资料更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function meal_update($data)
    {

        $model = new static;
        $rest = $model -> allowField(true)->save($data,['user_id'=>$data['id']]);
        return $rest ? $rest : false;
        
    }


        /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {
        if (isset($query['name']) && !empty($query['name'])) {
            $this->where('phone_number|user_name', 'like', '%' . trim($query['name']) . '%');
        }
        if (isset($query['status']) && !empty($query['status'])) {
            $this->where('status', '=', $query['status']);
        }
    }

        /**gy
     * 获取城市入驻资料
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function get_number($user)
    {
        $setting = CitySetting::city_setting();
        $user_data =  self::detail($user['user_id']);
        $invitation_store_number = $user_data['invitation_store_number'];
        switch($user_data['city_rank']){
            case 2:
                $number = $setting['rank_city'];
                return $number;
                break;
            case 3:
                $number = $setting['one_city'];
                return $number;
                break;
            case 4:
                $number = $setting['two_city'];
                return $number;
                break;
            case 5:
                $number = $setting['three_city'];
                return $number;
                break;
            default:
                break;
        }
        if($number > $invitation_store_number){
            $return_number = $number - $invitation_store_number;
        } else {
            $return_number = 0;
        } 
        return $return_number;
    }


    /**
     * 城市合伙人公众号业绩查询页面
     * @param User 
     * @param $user_id
     * @return false|int
     * @throws BaseException
     */
    public static function ServerShow($user_id)
    {
        $model = new static();
        $user_data = $model->detail($user_id);
        $city_meal_name = meal_name($user_data['city_rank']);
        $create_time = $user_data['create_time'];
        $number = $model->get_number($user_data);
        $number ? $status = 0 : $status = 1;

        $data = [
            'weixin_head' => $user_data['weixin_head'],
            'user_name' => $user_data['user_name'],
            'city_meal_name'=>$city_meal_name,
            'city_address' => $user_data['city_address'],
            'create_time' => strtotime($create_time),
            'end_time' => strtotime("$create_time+1year"),
            'withdraw_money'=> $user_data['withdraw_money'],
            'member_wallet' => $user_data['member_wallet'],
            'commission' => $user_data['commission'],
            'reach_commission' => $user_data['reach_commission'],
            'lock_status' => $status,
            'city_store_number' => $user_data['city_store_number'],
            'invitation_store_number' => $user_data['invitation_store_number'],
            'number' => $number,
            'my_invitation' => $user_data['my_invitation'],
            'tea_merchants' => city_store_number($user_data['city_address'])
        ];

        return $data;

    }


        /**
     * 城市合伙人公众号保底佣金总额查询页面
     * @param User 
     * @param $user_id
     * @return false|int
     * @throws BaseException
     */
    public static function CommissionShow($user_id)
    {
        $model = new static();
        $user_data = $model->detail($user_id);
        $order = Db::name('city_detail') 
                ->where('city_user_id','=',$user_id) 
                ->field('create_time,phone_number,set_meal,base_commision')
                ->where('base_commision','>',0)
                ->select();

        $data = [
            
            'commission' => $user_data['commission'],
            'order_data' => $order,

        ];

        return $data;

    }

    /**
     * 城市合伙人公众号达标佣金总额查询页面
     * @param User 
     * @param $user_id
     * @return false|int
     * @throws BaseException
     */
    public static function ReachCommissionShow($user_id)
    {
        $model = new static();
        $user_data = $model->detail($user_id);
        $number = $model->get_number($user_data);
        $number ? $status = 0 : $status = 1;
        $order = Db::name('city_detail') 
                ->where('city_user_id','=',$user_id) 
                ->field('create_time,phone_number,set_meal,reach_commision')
                ->where('reach_commision','>',0)
                ->select();

        $data = [
            'lock_status'=> $status,
            'reach_commission' => $user_data['reach_commission'],
            'order_data' => $order,

        ];

        return $data;

    }

        /**
     * 城市合伙人公众号城市总计商户页面
     * @param User 
     * @param $user_id
     * @return false|int
     * @throws BaseException
     */
    public static function AccumulativeShow($user_id)
    {
        $model = new static();
        $user_data = $model->detail($user_id);
        $order = Db::name('city_detail') 
                ->where('city_user_id','=',$user_id) 
                ->field('phone_number,user_name,set_meal,store_id')
                ->select();
        if(!empty($order)){
            foreach($order as $key => $value){
                $order[$key]['money'] = Db::name('order') 
                ->where('store_id','=',$order[$key]['store_id'])
                ->where('status','=',2)
                ->count('order_amount');
            }
        }
        $data = [
            'number' => $user_data['city_store_number'],
            'order_data' => $order,

        ];

        return $data;

    }


    /**
     * 城市合伙人我邀请的商户页面
     * @param User 
     * @param $user_id
     * @return false|int
     * @throws BaseException
     */
    public static function MyinviteShow($user_id)
    {
        $model = new static();
        $user_data = $model->detail($user_id);
        $number = $model->get_number($user_data);
        $order = Db::name('city_detail') 
                ->where('city_user_id','=',$user_id) 
                ->where('highe_share_code','=',$user_data['my_invitation']) 
                ->field('phone_number,user_name,set_meal,store_id')
                ->select();
        if(!empty($order)){
            foreach($order as $key => $value){
                $order[$key]['money'] = Db::name('order') 
                ->where('store_id','=',$order[$key]['store_id'])
                ->where('status','=',2)
                ->count('order_amount');
            }
        }
        $data = [
            'number' => $number,
            'order_data' => $order,

        ];

        return $data;

    }

}