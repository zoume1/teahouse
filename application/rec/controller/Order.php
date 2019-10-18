<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/18
 * Time: 10:00
 */
namespace app\rec\controller;
use app\rec\model\MealOrder;
use app\rec\model\Store;
use think\Request;
use think\Validate;
use think\Controller;
use think\Db;
use app\rec\model\Invoice as InvoiceAll;

Class Order extends Controller{


    /**
     * 发票地址
     * @return array
     * @author fyk
     */
    public function in_address()
    {
        $request = Request::instance();
        $param = $request->param();

        if(!$param['user_id'])returnJson(0,'用户ID不能为空');
        if(!$param['store_id'])returnJson(0,'店铺ID不能为空');

        $model = new Store();
        $data = $model->store_address($param['user_id'],$param['store_id']);

        $address = str_replace(',','',$data['address_data'] . $data['address_real_data']);

        $address ? returnJson(1,'地址获取成功',$address) : returnJson(0,'地址获取失败',$address);

    }

    /**
     * 创建订单
     * @return array
     * @author fyk
     */
    public function meal_order()
    {
        $request = Request::instance();
        $param = $request->param();
        $ifout = $param['ifout'];//判断是过期订单还是正常订单 1正常 2过期
        switch($ifout) {
            case 1:
                $order_all = MealOrder::where('store_id', $param['store_id'])->find();

                if ($order_all) {

                    $pay = new WechatPay();
                    $data = $pay->get_pay($order_all['id']);

                    //存入微信支付返回参数
                    MealOrder::where('id', $order_all['id'])->update(['wx_pay' => $data, 'wx_time' => date('Y-m-d H:i:s')]);

                    $data ? returnJson(1, '成功', $data) : returnJson(0, '失败');
                } else {
                    //验证
                    $param = $this->Verification($param);
                    //查询个人信息
                    $user = new \app\rec\model\User();
                    $user_all = $user->user_index($param['user_id']);

                    //查询店铺信息
                    $store_all = Store::where('id', $param['store_id'])->find()->toArray();
                    // 启动事务
                    Db::startTrans();
                    try {
                        //店铺logo
                        $img = $this->imgurl($param['enter_all_id']);
                        //生成订单
                        $order = new MealOrder();
                        $order_list = $order->add($param['user_id'], $param['goods_name'], $param['goods_quantity'], $param['amount_money'], $param['store_id'], $param['enter_all_id'], $store_all['store_name'], $param['price'], $user_all['openid'],$img);
                        $no = $order_list->order_number;
                        $order_id = $order_list->id;
                        //            print_r($order_id);die;
                        // 提交事务
                        Db::commit();
                        $type = $param['type'];
                        switch ($type) {
                            case 1:
                                if (empty($param['email'])) {
                                    $param['email'] = '';
                                }
                                if (empty($param['duty'])) {
                                    $param['duty'] = '';
                                }
                                if (empty($param['address'])) {
                                    $param['address'] = '';
                                }

                                $invoice = new InvoiceAll();
                                $result = $invoice->add_enterprise($param['user_id'], $no, $type, $param['status'], $param['email'], $param['rise'], $param['duty'], '100', $user_all['phone_number'], $param['address']);

                                $pay = new WechatPay();
                                $data = $pay->get_pay($order_id);

                                //存入微信支付返回参数
                                $order->where('id', $order_id)->update(['wx_pay' => $data, 'wx_time' => date('Y-m-d H:i:s')]);

                                $data ? returnJson(1, '成功', $data) : returnJson(0, '失败');

                                break;   // 跳出循环
                            case 2:
                                if (empty($param['email'])) {
                                    $param['email'] = '';
                                }
                                if (empty($param['address'])) {
                                    $param['address'] = '';
                                }
                                $invoice = new InvoiceAll();
                                $result = $invoice->add_personal($param['user_id'], $no, $type, $param['status'], $param['email'], $param['rise'], $user_all['phone_number'], '100', $param['address']);

                                $pay = new WechatPay();
                                $data = $pay->get_pay($order_id);

                                //存入微信支付返回参数
                                $order->where('id', $order_id)->update(['wx_pay' => $data]);

                                $data ? returnJson(1, '成功', $data) : returnJson(0, '失败');

                                break;
                        }
                    } catch (\Exception $e) {
                        // 回滚事务
                        Db::rollback();
                    }
                }
                break;   // 跳出循环
            case 2: //过期订单
                //验证
                $param = $this->Verification($param);
                //查询个人信息
                $user = new \app\rec\model\User();
                $user_all = $user->user_index($param['user_id']);

                //查询店铺信息
                $store_all = Store::where('id', $param['store_id'])->find()->toArray();
                if(!$store_all)returnJson(0,'店铺有误');
                // 启动事务
                Db::startTrans();
                try {
                    //店铺logo
                    $img = $this->imgurl($param['enter_all_id']);
                    //生成订单
                    $order = new MealOrder();
                    $order_list = $order->add($param['user_id'], $param['goods_name'], $param['goods_quantity'], $param['amount_money'], $param['store_id'], $param['enter_all_id'], $store_all['store_name'], $param['price'], $user_all['openid'],$img);
                    $no = $order_list->order_number;
                    $order_id = $order_list->id;
                    //            print_r($order_id);die;
                    // 提交事务
                    Db::commit();
                    $type = $param['type'];
                    switch ($type) {
                        case 1:
                            if (empty($param['email'])) {
                                $param['email'] = '';
                            }
                            if (empty($param['duty'])) {
                                $param['duty'] = '';
                            }
                            if (empty($param['address'])) {
                                $param['address'] = '';
                            }

                            $invoice = new InvoiceAll();
                            $result = $invoice->add_enterprise($param['user_id'], $no, $type, $param['status'], $param['email'], $param['rise'], $param['duty'], '100', $user_all['phone_number'], $param['address']);

                            $pay = new WechatPay();
                            $data = $pay->get_pay($order_id);

                            //存入微信支付返回参数
                            $order->where('id', $order_id)->update(['wx_pay' => $data, 'wx_time' => date('Y-m-d H:i:s')]);

                            $data ? returnJson(1, '成功', $data) : returnJson(0, '失败');

                            break;   // 跳出循环
                        case 2:
                            if (empty($param['email'])) {
                                $param['email'] = '';
                            }
                            if (empty($param['address'])) {
                                $param['address'] = '';
                            }
                            $invoice = new InvoiceAll();
                            $result = $invoice->add_personal($param['user_id'], $no, $type, $param['status'], $param['email'], $param['rise'], $user_all['phone_number'], '100', $param['address']);

                            $pay = new WechatPay();
                            $data = $pay->get_pay($order_id);

                            //存入微信支付返回参数
                            $order->where('id', $order_id)->update(['wx_pay' => $data]);

                            $data ? returnJson(1, '成功', $data) : returnJson(0, '失败');

                            break;
                    }
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                }

                break;

        }
    }

    /**
     * 重新购买
     * @return array
     * @author fyk
     */
    public function shop_wxpay()
    {
        $request = Request::instance();
        $param = $request->param();

        if(!$param['store_id'])returnJson(0,'店铺ID不能为空');
        if(!$param['store_name'])returnJson(0,'店铺名不能为空');

        $data = MealOrder::where(['store_id'=>$param['store_id'],'store_name'=>$param['store_name']])->field('wx_pay')->find();
         //判断
        returnArray($data);
        
        $res = $data['wx_pay'];
        $res ? returnJson(1,'支付信息获取成功',$res) : returnJson(0,'支付信息获取失败',$res);

    }

    /**
     * 店铺图
     * @param $enter_data
     * @return string
     */
    function imgurl($enter_data){
        if($enter_data === 5){
            $images_url ="/static/admin/common/img/wanyong.png";
        }else if($enter_data === 7){
            $images_url ="/static/admin/common/img/hangye.png";
        }else{
            $images_url ="/static/admin/common/img/jingjie.png";
        }

        return $images_url;
    }

    /**
     * 验证
     * @param $param
     * @return \think\response\Json
     */
    function Verification($param){
        $rules = [
            'goods_name' => 'require',
            'goods_quantity' => 'require',
            'amount_money' => 'require',
            'store_id' => 'require',
            'enter_all_id' => 'require',
            'user_id' => 'require',
            //发票资料
            'type' => 'require',
            'status' => 'require',
            'rise' => 'require',
            'price' => 'require',

        ];
        $message = [
            'goods_name.require' => '套餐名称不能为空',
            'goods_quantity.require' => '数量不能为空',
            'amount_money.require' => '金额不能为空',
            'store_id.require' => '店铺id不能为空',
            'enter_all_id.require' => '套餐id不能为空',
            'user_id.require' => '用户id不能为空',
            //发票
            'type.require' => '发票类型不能为空',
            'status.require' => '发票样式不能为空',
            'rise.require' => '抬头不能为空',
            'price.require' => '金额不能为空',
        ];
        //验证
        $validate = new Validate($rules, $message);
        if (!$validate->check($param)) {
            return json(['code' => 0, 'msg' => $validate->getError()]);
        }

        return $param;
    }


}