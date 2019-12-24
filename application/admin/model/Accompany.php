<?php

namespace app\admin\model;
use think\Session;
use think\Model;
use think\Db;
use think\Validate;
use app\city\controller;
use app\admin\model\Goods;
use app\admin\model\AccompanySetting;
use app\admin\model\AccompanyCode;

use app\common\exception\BaseException;


/**
 * 赠茶商品
 * Class StoreSetting
 * @package app\city\model
 */
class Accompany extends Model
{
    protected $table = "tb_accompany";


    /**gy
     *  城市入驻费用显示
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function accompany_index($search)
    {
        $model = new static;
        !empty($search) && $model->setWhere($search);
        $rest = $model->order(['create_time' => 'desc'])
        ->where('is_del','=',0)
        ->paginate(20, false, [
            'query' => \request()->request()
        ]);
        return $rest;
        
    }

    /**gy
     *  赠茶商品添加
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public  function accompany_add($data)
    {
        $store_id =  Session :: get('store_id');
        $this->startTrans();
        try {
            $goods_data = Goods::accompany_goods($data['goods_number'],1);
            if(isset($data['scope']) && !empty($data['scope'])) 
            {
                $scope = json_encode($data['scope'],true);
            } else {
                $scope = null;
            }
            $rest_data = [
                'choose_status' => $data['choose_status'],
                'goods_number' => $data['goods_number'],
                'goods_id' => $goods_data['id'],
                'goods_show_image' => $goods_data['goods_show_image'],
                'goods_name' => $goods_data['goods_name'],
                'scope' => $scope,
                'accompany_number' => $data['accompany_number'],
                'single_number' => $data['single_number'],
                'start_time' => strtotime($data['start_time']),
                'end_time' =>  strtotime($data['end_time']),
                'store_house_name' => $data['store_house_name'],
                'label' => $data['label'],
                'blessing' => $data['blessing'],
                'create_time' => time(),
                'store_id' => $store_id,
            ];
            $rest = $this->save($rest_data);
            if($rest){
                $data['accompany_id'] = $this->id;
                $setting = AccompanySetting::setting_add($data);
            }
            switch($data['choose_status'])
            {
                case 1 :
                    $restul = [
                        'accompany_id' => $this->id,
                        'code_status' => $data['choose_status'],
                        'start_time' => strtotime($data['start_time']),
                        'end_time' =>  strtotime($data['end_time']),
                        'accompany_number' => $data['accompany_number'],
                        'single_number' => $data['single_number'],
                    ];
                    $code_id = (new AccompanyCode())->code_add($restul);
                    $res = (new Goods())->unique_qrcode($code_id,$this->id);
                    break;
                case 2:
                    for($i = 0 ; $i < $data['accompany_number'] ; $i++){
                        $code_id = AccompanyCode::code_add($restul);
                        $res = (new Goods())->directional_qrcode($code_id);
                    }
                    break;
                default :
                    break;

            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    
    }

        /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {

        if (isset($query['name']) && !empty($query['name'])) {
            $this->where('goods_number|goods_name', 'like', '%' . trim($query['name']) . '%');
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

    /**gy
     *获取赠茶商品
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        return self::get($id)->toArray();
    }


    /**获取所有市场反馈
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
     *  市场反馈更新
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

}