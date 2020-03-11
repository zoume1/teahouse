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
     * 总返佣金额
     * @param $tel
     * @return float|int
     */
    public static function dist_commission($tel)
    {
        return self::where(['higher_phone'=>$tel])->sum ('commision') ? self::where(['higher_phone'=>$tel])->sum ('commision') : 0;
    }

    
}