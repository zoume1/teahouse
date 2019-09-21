<?php

namespace app\city\model;
use think\Session;
use think\Model;
use think\Validate;
use app\city\model\CityMeal;
use app\city\model\User as UserModel;
use app\city\controller;
use app\common\exception\BaseException;
const CITY_ONES = 1;
const CITY_TWO = 2;
const CITY_THREE = 3;

/**
 * 城市入驻订单模型
 * Class CityOrder
 * @package app\city\model
 */
class CityOrder extends Model
{
    protected $table = "tb_city_order";


    /**gy
     *  城市入驻费用显示
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function city_order($search)
    {
        $model = new static;
        !empty($search) && $model->setWhere($search);
        $rest = $model->order(['create_time' => 'desc'])
        ->paginate(20, false, [
            'query' => \request()->request()
        ]);
        return $rest;
        
    }

    /**gy
     * 获取城市入驻订单
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($meal_id)
    {
        $data = self::get($meal_id);
        return $data ? $data->toArray() : false;
    }


    /**获取所有城市入驻订单
     * gy
     * @param $useid
     * @return false|static[]
     * @throws \think\exception\DbException
     */
    public static function getList()
    {
        return self::all();
    }

    /**gy
     *  城市入驻订单更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function meal_update($data)
    {

        $model = new static;
        $rest = $model -> allowField(true)->save($data,['id'=>$data['id']]);
        return $rest ? $rest : false;
        
    }


    /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {
        if (isset($query['name']) && !empty($query['name'])) {
            $this->where('phone_number|user_name', 'like', '%' . trim($query['name']) . '%');
        }
        if (isset($query['status']) && !empty($query['status'])) {
            $this->where('judge_status', '=', $query['status']);
        }
    }


        /**
     * 城市合伙人订单显示
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order_index()
    {
        $user = Session::get('User');
        if(!empty($user)){
            //拿到注册信息
            $user_data = UserModel::detail(['user_id'=>$user['user_id']]);
            //拿到城市套餐价格
            $city_meal = CityMeal::detail(['id'=>$user_data['city_rank']]); 
            //生成订单号
            $order_number = $this->getCityOrderNumber();
            //插入订单表
            $data = [
                'order_number' => $order_number,
                'phone_number' => $user_data['phone_number'],
                'user_name' => $user_data['user_name'],
                'rank_status' => $user_data['city_rank'],
                'city_address' => $user_data['city_address'],
                'id_status'=> $user_data['id_status'],
                'order_price' => $city_meal['meal_price'],
                'city_meal_name' => $city_meal['city_meal_name'],
                'create_time' => time(),
            ];
            $rest = $this -> allowField(true)->save($data);
            if($rest){
                $order_data = [
                    'user_id'=> $user['user_id'],
                    'city_address' => $user_data['city_address'],
                    'create_time' => time(),
                    'order_number'=> $order_number,
                    'number' => CITY_ONES,
                    'selling_point'=> $city_meal['selling_point'],
                    'meal_price' => $city_meal['meal_price'],
                    'city_meal_name' => $city_meal['city_meal_name'],
                    'line_price' => $city_meal['line_price'],
                ];
                return $order_data;
            } else {
                return false;
            }
        } else {
            return false;
        }


        
        
    }

    /**
     * //生成合伙人订单号
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCityOrderNumber(){
        return 'HH'.date('YmdHi').rand(10000, 99999);    
    }


    /**
     * //生成微信支付二维码
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function WeChatPayCode($order_number){
    
        header("Content-type: text/html; charset=utf-8");
        ini_set('date.timezone', 'Asia/Shanghai');
        include('../extend/WxpayAllone/lib/WxPay.Api.php');
        include('../extend/WxpayAllone/example/WxPay.NativePay.php');
        include('../extend/WxpayAllone/example/log.php');
        $data = $self::detail(['order_number'=>$order_number]);
        if($data){
            $notify = new \NativePay();
            $input = new \WxPayUnifiedOrder();//统一下单
            $goods_id = 123456789; //商品Id
            $input->SetBody($data['city_meal_name']);//设置商品或支付单简要描述
            $input->SetAttach($data['city_meal_name']);//设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
            $input->SetOut_trade_no($data['order_number']);//设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
            $input->SetTotal_fee($data['order_price'] * 100);//金额乘以100
            $input->SetTime_start(date("YmdHis")); //设置订单生成时间,格式为yyyyMMddHHmmss
            $input->SetTime_expire(date("YmdHis", time() + 600)); //设置订单失效时间
            $input->SetGoods_tag("test"); //设置商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
            $input->SetNotify_url(config("domain.url")."/city/city_meal_notify"); //回调地址
            $input->SetTrade_type("NATIVE"); //交易类型(扫码)
            $input->SetProduct_id($goods_id);//设置trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
            $result = $notify->GetPayUrl($input);
            $url2 = $result["code_url"];
            if($url2){
                return ["url"=>"/qrcode?url2=".$url2];
            }else{
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * //生成支付宝支付二维码页面
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function AlipayCode($order_number)
    {
        header("Content-type:text/html;charset=utf-8");
        include EXTEND_PATH . "/lib/payment/alipay/alipay.class.php";
        $data = $self::detail(['order_number'=>$order_number]);
        if($data){
            $obj_alipay = new \alipay();                                           
            $arr_data = array(
                "return_url" => trim(config("domain.url")."city"),
                "notify_url" => trim(config("domain.url")."/city/city_meal_notify_alipay.html"),
                "service" => "create_direct_pay_by_user", //服务参数，这个是用来区别这个接口是用的什么接口，所以绝对不能修改
                "payment_type" => 1, //支付类型，没什么可说的直接写成1，无需改动。
                "seller_email" => '717797081@qq.com', //卖家
                "out_trade_no" => $order_number, //订单编号
                "subject" => $data['city_meal_name'], //商品订单的名称
                "total_fee" => number_format($data['order_price'], 2, '.', ''),
            );
            $str_pay_html = $obj_alipay->make_form($arr_data, true);
            if($str_pay_html){
                return ["url"=>$str_pay_html];
            }else{
                return false;
            }
        }
    }
    


}