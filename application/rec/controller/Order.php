<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/18
 * Time: 10:00
 */
namespace app\rec\controller;
use app\rec\model\MealOrder;
use think\Request;
use think\Validate;
use think\Controller;
use think\Db;
use app\rec\model\Invoice as InvoiceAll;

Class Order extends Controller{
    /**
     * 创建订单
     * @return array
     * @author fyk
     */
    public function meal_order()
    {
        $request = Request::instance();
        $param = $request->param();

        $rules = [
            'goods_name' => 'require',
            'goods_quantity'=>'require',
            'amount_money'=>'require',
            'store_id'=>'require',
            'enter_all_id'=>'require',
            'user_id'=>'require',
            //发票资料
            'type'=>'require',
            'status'=>'require',
            'rise'=>'require',
            'price'=>'require',

        ];
        $message = [
            'goods_name.require' => '套餐名称不能为空',
            'goods_quantity.require' => '数量不能为空',
            'amount_money.require'=>'金额不能为空',
            'store_id.require'=>'店铺id不能为空',
            'enter_all_id.require'=>'套餐id不能为空',
            'user_id.require'=>'用户id不能为空',
            //发票
            'type.require'=>'发票类型不能为空',
            'status.require'=>'发票样式不能为空',
            'rise.require'=>'抬头不能为空',
            'price.require'=>'金额不能为空',
        ];
        //验证
        $validate = new Validate($rules,$message);
        if(!$validate->check($param)){
            return json(['code' => 0,'msg' => $validate->getError()]);
        }
        //查询个人信息
        $user = new \app\rec\model\User();
        $user_all = $user->user_index($param['user_id']);
        //查询店铺信息
        // 启动事务
        Db::startTrans();
        try{
            //生成订单
            $order = new MealOrder();
            $order_list = $order->add($param['user_id'],$param['goods_name'],$param['goods_quantity'],'',$param['store_id'],$param['enter_all_id'],'','',$user_all['openid']);
            $no = $order_list->order_number;
            // 提交事务
            Db::commit();
            $type = $param['type'];
            switch($type){
                case 1:
                    if(empty($param['email'])){
                        $param['email'] = '';
                    }
                    if(empty($param['duty'])){
                        $param['duty'] = '';
                    }
                    if(empty($param['address'])){
                        $param['address'] = '';
                    }

                    $invoice = new InvoiceAll();
                    $result = $invoice->add_enterprise($param['user_id'],$no,$type,$param['status'],$param['email'],$param['rise'],$param['duty'],$param['price'],$user_all['phone_number'],$param['address']);
                    $res = $result ? ['code' => 1,'msg' => '成功'] : ['code' => 0,'msg' => '失败'];

                    return json($res);
                    break;   // 跳出循环
                case 2:
                    if(empty($param['email'])){
                        $param['email'] = '';
                    }
                    if(empty($param['address'])){
                        $param['address'] = '';
                    }
                    $invoice = new InvoiceAll();
                    $result = $invoice->add_personal($param['user_id'],$no,$type,$param['status'],$param['email'],$param['rise'],$user_all['phone_number'],$param['price'],$param['address']);
                    $res = $result ? ['code' => 1,'msg' => '成功'] : ['code' => 0,'msg' => '失败'];

                    return json($res);
                    break;
            }
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }

    }

}