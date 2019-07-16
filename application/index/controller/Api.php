<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/7 0007
 * Time: 15:03
 */
namespace  app\index\controller;

define("APPID", "wx301c1368929fdba8"); // 商户账号appid
define("MCHID", "1522110351"); 		// 商户号
//define("SECRECT_KEY", "94477ab333493c79f806f948f036f1e3");  //支付密钥签名
define("SECRECT_KEY", "TeahouseZwxcqgzyszhihuichacangZy");  //支付密钥签名
//define("IP", "119.23.79.230");   //IP
define("IP", "192.168.0.1");   //IP

use think\Controller;
use think\Request;
use think\Db;
include('../extend/WxpayAPI/lib/WxPay.Api.php');
include('../extend/WxpayAPI/example/WxPay.NativePay.php');
include('../extend/WxpayAPI/lib/WxPay.Notify.php');
include('../extend/WxpayAPI/example/log.php');
class  Api extends  Controller{
    /**
     **************李火生*******************
     * 快递100接口
     **************************************
     */
    public function express_hundred(Request $request)
    {
        if ($request->isPost()) {
            $order_id =$request->only(['by_order_id'])["by_order_id"];  //订单id
            // $order_id=123;
            if(!empty($order_id)) {
                $express =Db::name('order')
                    ->field('courier_number,express_name')
                    ->where('id',$order_id)
                    ->find();
                //测试
                // $express['express_name']='yuantong';
                // $express['courier_number']='806799086475402253';
                if(!empty($express)){
                    $express_type =$express['express_name'];
                    $express_num =$express['courier_number'];
                    if(!empty($express_num)) {
//                        $codes =$express_num;
                        //参数设置
                        $post_data = array();
                        $post_data["customer"] = config("express.customer");
                        $key = config("express.key");
//                        $post_data["customer"] = '4FB2D23DC8AC017B42F78D5A2E108860';
//                        $key = 'iYTBcMoU9991';
                        $post_data["param"] = '{"com":"'.$express_type.'","num":"' . $express_num . '"}';
                        $url = 'http://poll.kuaidi100.com/poll/query.do';
                        $post_data["sign"] = md5($post_data["param"] . $key . $post_data["customer"]);
                        $post_data["sign"] = strtoupper($post_data["sign"]);
                        $o = "";
                        foreach ($post_data as $k => $v) {
                            $o .= "$k=" . urlencode($v) . "&";        //默认UTF-8编码格式
                        }
                        $post_data = substr($o, 0, -1);
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                        $result = curl_exec($ch);
                        $data = str_replace("\"", '"', $result);
                        if(!empty($data)){
                            $data = json_decode($data,true);
                        //    return ajax_success("物流数据返回成功",$data);
                        }else{
                            $data = json_decode($data,true);
                        //    return ajax_error("暂无物流信息");
                        }
                    }
                }


            }
        }
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:后台初始订单退款
     **************************************
     */
    public function order_refund(Request $request){
        $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];
        $business_return_money =$request->only(["business_return_money"])["business_return_money"];
        $status =$request->only(["status"])["status"];
        $data =Db::name("after_sale")
            ->where("id",$after_sale_id)
            ->find();
        $refund_amount =Db::name("order")
            ->field("refund_amount,parts_order_number,order_real_pay,si_pay_type")
            ->where("id",$data["order_id"])
            ->find();
        //si_pay_type 支付方式（1为小程序余额支付，2是小程序微信支付）
        if(!$refund_amount){
            return ajax_error("未找到该订单信息");
        }
        if($business_return_money>$refund_amount["refund_amount"]){
            return ajax_error("所退款金额大于支付的金钱");
        }
        if($refund_amount["si_pay_type"] ==1){
            //如果是余额支付退回用户余额（不可提现）
            $refund_fee= $refund_amount["refund_amount"];//返回的金钱
            $result_data  =Db::name("member")->where("member_id",$data["member_id"])->setInc("member_wallet",$refund_fee);
           if($result_data){
               Db::name("after_sale")
                   ->where("id",$after_sale_id)
                   ->update(["status"=>$status]);
               //做回款记录
               $datas=[
                   "user_id"=> $data["member_id"],//用户ID
                   "wallet_operation"=> $business_return_money,//消费金额
                   "wallet_type"=>1,//消费操作(1入，-1出)
                   "operation_time"=>date("Y-m-d H:i:s"),//操作时间
                   "operation_linux_time"=>time(), //操作时间
                   "wallet_remarks"=>"售后号：".$data['sale_order_number']."，退款".$business_return_money,//消费备注
                   "wallet_img"=>" ",//图标
                   "title"=>"退款到余额",//标题（消费内容）
                   "order_nums"=>$refund_amount["parts_order_number"],//订单编号
                   "pay_type"=>"小程序", //支付方式/
                   "wallet_balance"=>$result_data,//此刻钱包余额
               ];
               Db::name("wallet")->insert($datas); //存入消费记录表
               return ajax_success("退款成功",$refund_amount);
           }else{
               return ajax_error("退款失败");
           }

        }
        $rest = \WxPayConfig::getStoreInformation();
        $out_trade_no=$refund_amount["parts_order_number"];
        $total_fee=$refund_amount["order_real_pay"] *100;
        $refund_fee= $refund_amount["refund_amount"] *100;
        $input = new \WxPayRefund();
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($refund_fee);
        $input->SetOut_refund_no($rest['mchid'].date("YmdHis"));
        $input->SetOp_user_id($rest['mchid']);
        $result =\WxPayApi::refund($input);
//      file_put_contents(EXTEND_PATH."refund.txt",$result);
//        if ($result['result_code'] == 'SUCCESS' && $result['return_code'] == 'SUCCESS') {
        if ($result['result_code'] == 'SUCCESS') {
            $result['code'] = 1;
            $result['data'] =  $result['transaction_id'];
            Db::name("after_sale")
                ->where("id",$after_sale_id)
                ->update(["status"=>$status]);
            $result_money =Db::name("member")->where("member_id",$data["member_id"])->find("member_wallet");
            //做回款记录
            $datas=[
                "user_id"=> $data["member_id"],//用户ID
                "wallet_operation"=> $business_return_money,//消费金额
                "wallet_type"=>1,//消费操作(1入，-1出)
                "operation_time"=>date("Y-m-d H:i:s"),//操作时间
                "operation_linux_time"=>time(), //操作时间
                "wallet_remarks"=>"售后号：".$data['sale_order_number']."，退款".$business_return_money,//消费备注
                "wallet_img"=>" ",//图标
                "title"=>"退款",//标题（消费内容）
                "order_nums"=>$refund_amount["parts_order_number"],//订单编号
                "pay_type"=>"小程序", //支付方式/
                "wallet_balance"=>$result_money,//此刻钱包余额
            ];
            Db::name("wallet")->insert($datas); //存入消费记录表
            return ajax_success("成功",$result);
        }else {
            $result['code'] = 0;
            $result['msg'] =  $result['err_code'];
            return ajax_error("失败",$result);
        }

    }
    function createstring($length =32)
    {

        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";

        $str ="";

        for ( $i = 0; $i < $length; $i++ )  {

            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);

        }
        return $str;
    }
    function unicode() {
        $str = uniqid(mt_rand(),1);
        $str=sha1($str);
        return md5($str);
    }
    function arraytoxml($data){
        $str='<xml>';
        foreach($data as $k=>$v) {
            $str.='<'.$k.'>'.$v.'</'.$k.'>';
        }
        $str.='</xml>';
        return $str;
    }
    function xmltoarray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }
    function curl($param="",$url) {

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();                                      //初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);                 //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);           // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//        curl_setopt($ch,CURLOPT_SSLCERT,EXTEND_PATH .'/WxpayAPI/cacert/apiclient_cert.pem'); //这个是证书的位置绝对路径
//        curl_setopt($ch,CURLOPT_SSLKEY,EXTEND_PATH .'/WxpayAPI/cacert/apiclient_key.pem'); //这个也是证书的位置绝对路径
        curl_setopt($ch,CURLOPT_SSLCERT,'E:/WWW/teahouse/extend/WxpayAPI/cert/apiclient_cert.pem'); //这个是证书的位置绝对路径
        curl_setopt($ch,CURLOPT_SSLKEY,'E:/WWW/teahouse/extend/WxpayAPI/cert/apiclient_key.pem'); //这个也是证书的位置绝对路径
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
        return $data;
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:
     **************************************
     * @param $amount 发送的金额（分） 不能少于1元
     * @param $re_openid   发送人的openid
     * @param string $desc   企业付款描述的信息（必填）
     * @param string $check_name  收款用户的姓名（必填）
     * @return \SimpleXMLElement[]
     */
    public function sendMoney(){
        //$amount,$re_openid,$desc='测试',$check_name=''
        $rest = WxPayConfig::getStoreInformation();
        $amount =1;
        $re_openid ="o_lMv5VTbQDkQxK08EkllWXtX-kY";
        $check_name ="李火生";
        $desc  ="微信提现测试使用";
        $total_amount = (100) * $amount;
        $data=array(
            'mch_appid'=>$rest['appID'],//商户账号appid
            'mchid'=> $rest['mchid'],//商户号
            'nonce_str'=>$this->createstring(),//随机字符串
            'partner_trade_no'=> date('YmdHis').rand(1000, 9999),//商户订单号
            'openid'=> $re_openid,//用户openid
            'check_name'=>'NO_CHECK',//校验用户姓名选项,
            're_user_name'=> $check_name,//收款用户姓名
            'amount'=>$total_amount,//金额
            'desc'=> $desc,//企业付款描述信息
            'spbill_create_ip'=> IP,//Ip地址
        );
        $secrect_key = $rest['signkey'];///这个就是个API密码。MD5 32位。
        $data=array_filter($data);
        ksort($data);
        $str='';
        foreach($data as $k=>$v) {
            $str.=$k.'='.$v.'&';
        }
//        $str.='key='.$secrect_key;
        $str.='key='.$secrect_key;
//        dump(strtoupper(md5($str))); //TODO：注意签名需要大写字母
        $data['sign']=strtoupper(md5($str));
        $xml=$this->arraytoxml($data);
//        halt($xml);
        $url='https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers'; //调用接口
        $res=$this->curl($xml,$url); //fales
//        halt($res);
        $return=$this->xmltoarray($res);
//        halt($return);

        //返回来的结果
        // [return_code] => SUCCESS [return_msg] => Array ( ) [mch_appid] => wxd44b890e61f72c63 [mchid] => 1493475512 [nonce_str] => 616615516 [result_code] => SUCCESS [partner_trade_no] => 20186505080216815
        // [payment_no] => 1000018361251805057502564679 [payment_time] => 2018-05-15 15:29:50
        $responseObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);

         $res= $responseObj->return_code;  //SUCCESS  如果返回来SUCCESS,则发生成功，处理自己的逻辑
//        dump($responseObj);
//        return $return;
        return $res;
    }
}