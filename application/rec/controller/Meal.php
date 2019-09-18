<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/17
 * Time: 9:22
 */
namespace app\rec\controller;
use think\Request;
use think\Validate;
use think\Controller;

Class Meal extends Controller{
    /**
     * 套餐名称列表
     * @return array
     * @author fyk
     */
    public function classify()
    {
        $where['status'] = 1;
        $field = 'id,name,price';
        $order = 'sort_number asc';
        $data = db('enter_meal')->where($where)->field($field)->order($order)->select();

        $data ? returnJson(1,'请求成功',$data) : returnJson(0,'请求失败',$data);
    }

    /**
     * 套餐分类列表
     * @return array
     * @author fyk
     */
    public function class_index()
    {
        $request = Request::instance();
        $enter_id = $request->param('enter_id',2);
//        $limit = $request -> post('size', 6);
        $where['enter_id'] = $enter_id;
        $where['enter_id'] = $enter_id;

        $field = 'id,name,price';
        $order = 'sort_number asc';
        $data = db('enter_meal')->where($where)->field($field)->order($order)->select();

        $data ? returnJson(1,'请求成功',$data) : returnJson(0,'请求失败',$data);
    }
}