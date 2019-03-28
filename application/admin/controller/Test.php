<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21 0021
 * Time: 11:27
 */
namespace app\admin\controller;

use think\Controller;
use think\Db;

class Test extends  Controller{
    /*图标库*/
    public function selecticon(){
        return $this->fetch('icon');
    }

}
