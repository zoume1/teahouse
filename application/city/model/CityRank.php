<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\controller;
use app\common\exception\BaseException;
const CITY_ONE = 1;

/**
 * 城市等级套餐模型
 * Class CityRank
 * @package app\city\model
 */
class CityRank extends Model
{
    protected $table = "tb_city_rank";
    // 设置返回数据集的对象名
	protected $resultSetType = 'collection';


    /**gy
     *  城市等级各等级添加
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function rank_add($data)
    {
        $model = new static;
        $rest = $model->save($data);
        return $rest ? $rest : false;
        
    }


    /**gy
     *  城市等级各等级删除
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function rank_delete($id)
    {
        $model = new static;
        $rest = $model->where('id','=', $id)->delete();
        return $rest ? $rest : false;
        
    }

        /**gy
     *  查询城市信息
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function rank_find($name)
    {
        $model = new static;
        $rest = $model->where('name','=', $name)->find();
        return $rest ? $rest->toArray() : false;
        
    }

    /**gy
     * 获取等级信息
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($meal_id)
    {
        return self::where('rank_status','=',$meal_id)->select();
    }


    /**获取所有等级
     * gy
     * @param $useid
     * @return false|static[]
     * @throws \think\exception\DbException
     */
    public static function getList($city)
    {
        $model = new static;
        $data =  $model->all()->toArray();
        foreach($data as $key => $value){
            switch($value['rank_status'])
            {
                case 1:
                    $one[] = $value;
                    break;
                case 2:
                    if(!empty($city)){
                        if(in_array($value['name'], $city)){
                            $two[] = $value;
                        }
                    } else {
                        $two[] = $value;
                    }
                    
                    break;
                case 3:
                    if(!empty($city)){
                        if(in_array($value['name'], $city)){
                            $three[] = $value;
                        }
                    } else {
                        $three[] = $value;
                    }
                    
                    break;
                case 4:
                    if(!empty($city)){
                        if(in_array($value['name'], $city)){
                            $four[] = $value;
                        }
                    } else {
                        $four[] = $value;
                    }
                    break;
                case 5:
                    if(!empty($city)){
                        if(in_array($value['name'], $city)){
                            $five[] = $value;
                        }
                    } else {
                        $five[] = $value;
                    }
                    break;
                default:
                    break;
            }

        }
        isset($three) ? $three : $three = [];
        isset($one) ? $one : $one = [];
        isset($two) ? $two : $two = [];
        isset($four) ? $four : $four = [];
        isset($five) ? $five : $five = [];
        $rest = [
            'one'=>$one,
            'one_number'=>count($one),
            'two'=>$two,
            'two_number'=>count($two),
            'three'=>$three,
            'three_numebr'=>count($three),
            'four'=>$four,
            'four_number'=>count($four),
            'five'=>$five,
            'five_number'=>count($five),
        ];
        return  $rest;
    }

    /**gy
     *  城市等级更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function rank_update($data)
    {

        $model = new static;
        $rest = $model -> allowField(true)->save($data,['id'=>$data['id']]);
        return $rest ? $rest : false;
        
    }

}