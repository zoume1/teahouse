<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/28
 * Time: 9:27
 */
namespace app\rec\model;


use think\Model;

class CityDetail extends Model{

    protected $table = "tb_city_detail";
    protected $resultSetType = 'collection';

    /**
     * 总返佣金额-根据手机号
     * @param $tel
     * @return float|int
     */
    public static function dist_commission($tel)
    {
        return self::where(['higher_phone'=>$tel])->sum ('commision') ? self::where(['higher_phone'=>$tel])->sum ('commision') : 0;
    }

    /**
     * 总返佣金额-根据邀请码
     * @param $tel
     * @return float|int
     */
    public static function dist_commission_code($code)
    {
        return self::where(['highe_share_code'=>$code])->sum ('commision') ? self::where(['highe_share_code'=>$code])->sum ('commision') : 0;
    }  
}