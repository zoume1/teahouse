<?php

namespace app\common\model\dealer;

use app\common\model\BaseModel;
use think\Cache;
use\app\admin\model\MemberGrade; 
use\app\admin\model\Member; 

/**
 * 分销商设置模型
 * Class Apply
 * @package app\common\model\dealer
 */
class Setting extends BaseModel
{
    protected $name = 'setting';

    /**
     * 获取器: 转义数组格式
     * @param $value
     * @return mixed
     */
    public function getValuesAttr($value)
    {
        return json_decode($value, true);
    }

    /**
     * 修改器: 转义成json格式
     * @param $value
     * @return string
     */
    public function setValuesAttr($value)
    {
        return json_encode($value);
    }

    /**
     * 获取指定项设置
     * @param $key
     * @param $wxapp_id
     * @return array
     */
    public static function getItem($wxapp_id)
    {
        $data = static::get(['store_id'=>$wxapp_id])->find()->toArray();
        return isset($data) ? $data : false;
    }



    /**
     * 获取设置项信息
     * @param $key
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($key)
    {
        return static::get(compact('key'));
    }

    /**
     * 是否开启分销功能
     * @param null $wxapp_id
     * @return mixed
     */
    public static function isOpen($wxapp_id = null)
    {
        return static::getItem('basic', $wxapp_id)['is_open'];
    }

    /**
     * 是否满足设置条件
     * @param null $wxapp_id
     * @return mixed
     */
    public static function isMemberRank($data)
    {
        $stting =  self::getItem($data['store_id']);
        switch($setting['rank_status'])
        {
            case 1:
                if($data['goods_money'] >= $stting['price_status']){
                    return true;
                } else {
                    return false;
                }               
                break;
            case 2:
                //普通会员
                $lowest = MemberGrade::getLowestMember($data['store_id']);
                $grade_id = Member::getGradeId($data['member_id']);
                if($lowest == $grade_id){
                    return false;
                } else {
                    if($data['goods_money'] >= $stting['price_status']){
                        return true;
                    } else {
                        return false;
                    }
                }
                break;
            default:
                return false;
        }
    }


    /**
     * 是否满足返利条件
     * @param null $wxapp_id
     * @return mixed
     * 1=>付款立即返利  2=》确认收货
     */
    public static function isRturnPrice($store_id)
    {
        $stting =  self::getItem($store_id);
        return $setting ? $setting['opportunity'] : false;
        
    }


  



 

}