<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/18 0018
 * Time: 15:13
 */
namespace  app\admin\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Db;

class Store extends  Controller{




    /**
     **************李火生*******************
     * @param Request $request
     * Notes:检测是否进行了支付密码设置，没有设置则前往设置
     **************************************
     */
    public function store_isset_password(Request $request){
        if($request->isPost()){
            $store_id =Session::get("store_id");
            $is_set =Db::name("store")
                ->where("id",$store_id)
                ->value("store_pay_pass");
            if(empty($is_set)){
               return ajax_error("没有设置支付密码，请前往设置");
            }else{
                return ajax_success("已检测到支付密码");
            }

        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺的支付密码修改
     **************************************
     */
    public function  store_update_password(){
        if($this->request->isPost()){
            $store_id =Session::get("store_id");
            $password =$this->request->only(["password"])["password"];
            $pass =$this->request->only(["pass"])["pass"];//第二次密码
            $code = $this->request->only(["code"])["code"];
            $mobileCodes =Session::get("mobileCodes");//验证码
            if($password !=$pass){
                return ajax_error("2次输入密码不一致，请重新设置");
            }
            if($code !=$mobileCodes){
                return ajax_error("验证码不正确");
            }
            $boll =Db::name("store")->where("id",$store_id)->update(["store_pay_pass"=>md5($password)]);
            if($boll){
                return ajax_success("支付密码修改成功");
            }else{
                return ajax_error("您设置的密码和原密码一致，请重新设置");
            }
        }

    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺余额返回
     **************************************
     */
    public function store_wallet_return(Request $request){
        if($request->isPost()){
            $store_id =Session::get("store_id");
            $id  = Db::name("store")
                ->where("id",$store_id)
                ->value("id");
            if(!$id){
                return ajax_error("店铺信息有误");
            }
            $wallet  = Db::name("store")
                ->where("id",$store_id)
                ->value("store_wallet");
            return ajax_success("余额返回成功",$wallet);
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺的钱包充值页面
     **************************************
     */
    public function store_wallet_add(){
        $store_id = Session::get("store_id");
        $phone_number = db("store")->where("id",'EQ',$store_id)->value("phone_number");

        $data = Db::name("store_bank_icard")
                ->where("store_id",'EQ',$store_id)
                ->where("status",'EQ',1)
                ->select();

        return view("store_wallet_add",['phone_number'=>$phone_number,'data'=>$data]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺的提现页面
     **************************************
     */
    public function store_wallet_reduce(){
        $store_id = Session::get("store_id");
        $store_information = db("store")->where("id",$store_id)->find();
        $data = Db::name("store_bank_icard")
        ->where("store_id",'EQ',$store_id)
        ->where("status",'EQ',1)
        ->select();
        
        return view("store_wallet_reduce",['store_information'=>$store_information,'data'=>$data]);
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:店铺资金管理银行卡列表
     **************************************
     * @return \think\response\View
     */
    public function store_add_bankcard(){
        //将已开户银行开发送过去
        $store_id = Session::get("store_id");
        $bank = Db::name("store_bank_icard")
                ->where("store_id",'EQ',$store_id)
                ->where("status",'EQ',1)
                ->select();
                
        return view("store_add_bankcard",["bank"=>$bank]);
    }


    /**
     **************GY*******************
     * @param Request $request
     * Notes:银行开添加入库
     **************************************
     */
    public function store_icard_save(Request $request){
        if($request->isPost()){
            $store_id = Session::get("store_id");
            $card = $request->param();
            $card['time'] = time();
            $card['store_id'] = $store_id;
            $card['status'] = 1;
            $bool  = Db::name("store_bank_icard")
                    ->insert($card);

            if($bool){
                return ajax_success("银行卡添加成功",$bool);
            } else {
                return ajax_success("参数错误");
            }
        }
    }


    /**
     **************GY*******************
     * @param Request $request
     * Notes:银行开删除
     **************************************
     */
    public function store_icard_delete(Request $request){
        if($request->isPost()){
            $id = $request->only(["id"])["id"];
            $bool  = Db::name("store_bank_icard")
                    ->where("id",'EQ',$id)
                    ->update(['status'=> -1]);
            if($bool){
                return ajax_success("删除成功",$bool);
            } else {
                return ajax_success("删除失败");
            }
        }
    }


    /**
     **************GY*******************
     * @param Request $request
     * Notes:店铺充值提交申请 
     **************************************
     */
    public function OfflineRecharge(Request $request){
        if($request->isPost()){
            $store_id = Session::get("store_id");
            //生成流水号
            $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
            $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
            $data = $request->param();
            $data['serial_number'] = $orderSn;
            $data['store_id'] = $store_id;
            $data['create_time'] = strtotime($data['create_time']);
            
            $bool  = Db::name("offline_recharge")
                ->insert($data);
            if($bool){
                return ajax_success("凭证已提交，我们将在3个工作日内审核完毕，通过后自动完成订购。",$bool);
            } else {
                return ajax_success("提交失败");
            }
        }
    }


    /**
     **************GY*******************
     * @param Request $request
     * Notes:店铺提现提交申请 
     **************************************
     */
    public function withdrawCash(Request $request){
        if($request->isPost()){
            $store_id = Session::get("store_id");
            //生成流水号
            $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
            $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
            $data = $request->param();
            $data['store_id'] = $store_id;
            $data['create_time'] = time();
            $data['pay_type'] = 3;
         
            $bool  = Db::name("offline_recharge")
                ->insert($data);
            if($bool){
                return ajax_success("已提交申请，我们将在3个工作日内审核完毕，通过后自动完成订购。",$bool);
            } else {
                return ajax_success("提交失败");
            }
        }
    }







}