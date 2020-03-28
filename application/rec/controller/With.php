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
use app\city\model\CityCopartner;
Class With extends Controller{
    /**
     * 微信公众号提现申请
     * @return \think\response\Json
     */
    public function cash_with()
    {
        $request = Request::instance();
        $param = $request->param();
            // print_r($param);die;
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
        $type = $param['type'];

        switch ($type) {
            case 1:
                //用户信息
                $user = new \app\rec\model\User();
                $user_all = $user->user_index($param['user_id']);
                $phone = $user_all['phone_number'];
                //返佣金额
                $city = new \app\rec\model\CityDetail();
                $city_all = $city->dist_commission($phone);
                //判断
                if($param['money']  > $city_all){
                     returnJson(0,'提现金额不能大于佣金余额');
                }
                $param['balance'] = $city_all - $param['money'];
                $param['phone_number'] = $phone; //添加手机账号信息
                $with = new \app\rec\model\With();

                $data = $with->add($param);
                if($data){
                     //减去 返佣余额
                    
                     $data ? returnJson(1,'申请成功') : returnJson(0,'申请失败');
                }
                break;
            
            default:

                //返佣金额
                $city = CityCopartner::where('user_id',$param['user_id'])->field('user_id,member_wallet')->find()->toArray();
            
                //判断
                if($param['money']  > $city['member_wallet']){
                     returnJson(0,'提现金额不能大于佣金余额');
                }
                $param['balance'] = $city['member_wallet'] - $param['money'];
                $param['phone_number'] = $city['phone_number'];//添加手机账号信息
                $with = new \app\rec\model\With();

                $data = $with->add($param);
                if($data){
                     //减去 返佣余额
                     CityCopartner::where('user_id',$param['user_id'])->where('user_id',$param['user_id'])
                     ->update(['member_wallet'=> $param['balance']]);

                     $data ? returnJson(1,'申请成功') : returnJson(0,'申请失败');
                }
                break;
        }       
       
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