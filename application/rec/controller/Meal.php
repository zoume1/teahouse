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
use think\Db;
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
        $enter_id = $request->param('enter_id',5);
        $year = $request -> param('year', 0);
        $where['enter_id'] = $enter_id;
        $where['year'] = $year;

        $field = 'id,year,cost,favourable_cost,enter_id,version_introduce';
        $order = 'id asc';
        $data = db('enter_all')->where($where)->field($field)->order($order)->find();
        $data['max'] = db('enter_all')->max('year');
        $data ? returnJson(1,'请求成功',$data) : returnJson(0,'请求失败',$data);
    }

    /**
     * 合伙人考核
     * @return \think\response\Json
     */
    public function assessment()
    {
        $request = Request::instance();
        $param = $request->param();

        $rules = [
            'user_id' => 'require',
            'hid' => 'require',
            'type'=>'require',
            'con'=>'require',
        ];
        $message = [
            'hid.require' => '合伙人id不能为空',
            'type.require' => '考核类型不能为空',
            'con.require'=>'评价内容不能为空',
        ];
        //验证
        $validate = new Validate($rules,$message);
        if(!$validate->check($param)){
            return json(['code' => 0,'msg' => $validate->getError()]);
        }

        $data = Db::table('tp_evaluation')
                ->insert([
                    'user_id'=>$param['user_id'],
                    'user_id'=>$param['user_id'],
                    'type'=>$param['type'],
                    'con'=>$param['con'],
                    'create_time'=>time()
                ]);
        $data ? returnJson(1,'提交成功') : returnJson(0,'提交失败');
    }
}