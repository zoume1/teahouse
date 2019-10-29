<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/15 0015
 * Time: 14:21
 */
namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Request;
use app\index\model\Serial as Serials;

class Serial extends  Controller{


    /**gy
     *  分销明细添加
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function serial_add($data)
    {
        $rest = Serials::serial_add($data);
        return $rest;
        
    }
    
}