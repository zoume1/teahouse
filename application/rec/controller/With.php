<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/23
 * Time: 15:07
 */
namespace app\rec\controller;
use think\Request;
use think\Validate;
use think\Controller;

Class With extends Controller{
    /**
     * 微信公众号提现申请
     * @return \think\response\Json
     */
    public function cash_with()
    {
        $request = Request::instance();
        $param = $request->param();

        $rules = [
            'type' => 'require',
            'invoice_type' => 'require',
            'user_id' => 'require',
            'money'=>'require',
            'express_name'=>'require',
            'odd_num'=>'require',
        ];
        $message = [
            'type.require' => '提现类型不能为空：1为店铺，2为合伙人',
            'invoice_type.require' => '提现类型不能为空：1纸制发票，2电子发票',
            'user_id.require' => '用户id不能为空',
            'money.require'=>'金额不能为空',
            'express_name.require' => '快递名不能为空',
            'odd_num.require'=>'快递号不能为空',
        ];
        //验证
        $validate = new Validate($rules,$message);
        if(!$validate->check($param)){
            return json(['code' => 0,'msg' => $validate->getError()]);
        }

        $with = new \app\rec\model\With();

        $data = $with->add($param);

        $data ? returnJson(1,'申请成功') : returnJson(0,'申请失败');
    }

    /**
     * 获取提现记录
     */
    public function record()
    {
        $request = Request::instance();
        $param = $request->param();

        if(!$param['user_id'])returnJson(0,'用户id不能为空');

        $with = new \app\rec\model\With();
        $data = $with->details($param['user_id']);

        $data ? returnJson(1,'获取记录成功',$data) : returnJson(0,'获取失败',$data);
    }


}