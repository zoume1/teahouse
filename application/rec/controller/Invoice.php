<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/11
 * Time: 14:57
 */
namespace app\rec\controller;
use app\rec\model\Invoice as InvoiceAll;
use think\Request;
use think\Validate;
use think\Controller;
use think\captcha\Captcha;

//include('../extend/lib/SendApi.php');
class Invoice extends Controller{

    /**
     * @author fyk
     * 获取token
     */
    public function index()
    {

        $data = $this->get_sn();
        print_r($data);die;
        $file = file_get_contents('filename.txt');
        print_r($file);die;
    }
    /**
     * @author fyk
     * 提交发票
     */
    public function  refer_invoice()
    {
        $request = Request::instance();
        $param = $request->param();
        $type = $param['type'];
        switch($type){
            case 1:
                $rules = [
                    'user_id' => 'require',
                    'type'=>'require',
                    'status'=>'require',
                    'email'=>'require',
                    'rise'=>'require',
                    'duty'=>'require',
                    'price'=>'require',
                    'invoiceLine'=>'require',
                ];
                $message = [
                    'user_id.require' => '用户不能为空',
                    'type.require' => '用户类型不能为空',
                    'status.require' => '发票类型不能为空',
                    'email.require' => '邮箱地址不能为空',
                    'rise.require' => '抬头不能为空',
                    'duty.require' => '税号不能为空',
                    'price.require' => '金额不能为空',
                    'invitation.require'=>'发票种类:p,普通发票(电票)(默认)s,专用发票不能为空',
                ];
                //验证
                $validate = new Validate($rules,$message);
                if(!$validate->check($param)){
                    return json(['code' => 0,'msg' => $validate->getError()]);
                }
                
                $invoice = new InvoiceAll();
                $res = $invoice->add_enterprise('','','','','','','');

                return $res;
                break;   // 跳出循环
            case 2:
                $rules = [
                    'user_id' => 'require',
                    'type'=>'require',
                    'status'=>'require',
                    'email'=>'require',
                    'user_name'=>'require',
                    'phone'=>'require',
                    'price'=>'require',
                    'invoiceLine'=>'require',
                ];
                $message = [
                    'user_id.require' => '用户id不能为空',
                    'type.require' => '用户类型不能为空',
                    'status.require' => '发票类型不能为空',
                    'email.require' => '邮箱地址不能为空',
                    'user_name.require' => '姓名不能为空',
                    'phone.require' => '手机号不能为空',
                    'price.require' => '金额不能为空',
                    'invitation.require'=>'发票种类:p,普通发票(电票)(默认)s,专用发票不能为空',
                ];
                //验证
                $validate = new Validate($rules,$message);
                if(!$validate->check($param)){
                    return json(['code' => 0,'msg' => $validate->getError()]);
                }

                $invoice = new InvoiceAll();
                $res = $invoice->add_personal('','','','','','','');

                return $res;
                break;
        }

    }

    public function ele_invoice()
    {

        $id = 1;
        $data = InvoiceAll::where('id',$id)->find()->toArray();
//        print_r($data);die;
        $res = $this->requestBilling($data);
        print_r($res);die;
    }
    /**
     * @author fyk
     * 诺诺发票 请求开具发票请求，先填写appkey、appsecret
     */
    public function requestBilling($data)
    {
        $appKey = "SD54278460";
        $appSecret = "SD5306BAB0F24B7E";
        $token = "a2146b6330bf40cdb361a10ilweat07s";// 唯一的token
        $taxnum = "339901999999142"; //商家模式对应的是注册的税号
        $url = "https://sandbox.nuonuocs.cn/open/v1/services"; // 请求地址（沙箱）
        $method = "nuonuo.electronInvoice.requestBilling"; // 请求api对应的方法名称
        $senid = uniqid(); // 唯一标识，由自己生成32位随机码
        $body = json_encode(
            array(
                "order"=> array(
                    'invoiceDetail'=>array(
                        array(
                            "taxExcludedAmount"=> "",
                            "invoiceLineProperty"=> "0",
                            "favouredPolicyName"=> "",
                            "num"=> "2",
                            "withTaxFlag"=> "1",
                            "tax"=> "",
                            "favouredPolicyFlag"=> "0",
                            "taxRate"=> "0.13",
                            "unit"=> "台",
                            "deduction"=> "0",
                            "price"=> $data['price'],
                            "zeroRateFlag"=> "",
                            "goodsCode"=> "1090511030000000000",//税收分类编码是税局定义的，不能随便乱传值，具体传什么可以咨询下你们的财务
                            "goodsName"=> "茶叶",
                            "taxIncludedAmount"=> ""

                        )
                    ),
                    "buyerTel"=> $data['phone'],
                    "listFlag"=> "0",
                    "pushMode"=> "2",//推送方式:-1,不推送;0,邮箱;1,手机（默认）;2,邮箱、手机
                    "departmentId"=> "9F7E9439CA8B4C60A2FFF3EA3290B088",
                    "clerkId"=> "",
                    "checker"=> "",
                    "remark"=> "备注信息",
                    "payee"=> "",
                    "buyerAddress"=> "",
                    "buyerTaxNum"=> "",
                    "invoiceType"=> "1",
                    "invoiceLine"=> $data['invoiceLine'],
                    "email"=> $data['email'],
                    "salerAccount"=> "",
                    "orderNo"=> $this->get_sn(),//订单编号唯一
                    "salerTel"=> "0571-81029365",
                    "buyerName"=> $data['rise'],
                    "invoiceDate"=> date('Y-m-d H:i:s',time()),
                    "invoiceCode"=> "125999915630",
                    "invoiceNum"=> "00130865",
                    "salerAddress"=> "杭州市西湖区万塘路30号高新东方科技园",
                    "clerk"=> "张三",
                    "buyerPhone"=> "17764096309",
                    "buyerAccount"=> "",
                    "productOilFlag"=> "0",
                    "salerTaxNum"=> "339901999999142",
                    "listName"=> "详见销货清单",
                    "proxyInvoiceFlag"=> "0"
                )
            )
        );

        $send = new \SendApi();
        $res = $send->sendPostSyncRequest($url, $senid, $appKey, $appSecret, $token, $taxnum, $method, $body);

        return $res;
    }

    /**
     * @author fyk
     * 诺诺发票  通过流水号查询查询发票接口,先填写appkey、appsecret
     */
    public function CheckEInvoice()
    {
        $appKey = "SD54278460";
        $appSecret = "SD5306BAB0F24B7E";
        $token = "a2146b6330bf40cdb361a10ilweat07s";//
        $taxnum = "339901999999142";
        $url = "https://sandbox.nuonuocs.cn/open/v1/services"; // （沙箱）
        $method = "nuonuo.electronInvoice.CheckEInvoice"; //
        $senid = uniqid(); // 随机字符32位
        $body = json_encode(
            array(
                "invoiceSerialNum" => array("19091118163501000882")
            )
        );
        $send = new \SendApi();
        $res = $send->sendPostSyncRequest($url, $senid, $appKey, $appSecret, $token, $taxnum, $method, $body);
        return $res;
    }
    /**
     * @author fyk
     * 诺诺发票 先填写appkey、appsecret获取唯一token 24小时需更新
     */
    public function getMerchantToken() {
        $appKey = "SD54278460";
        $appSecret = "SD5306BAB0F24B7E";

        $send = new \SendApi();
        $res =  $send->getMerchantToken($appKey, $appSecret);
        $data = json_decode($res,true);
       $token =  $data['access_token'];
//        print_r($token);die;
        file_put_contents('filename.txt', print_r($token, true));
        return $token;
    }

    //生成发票订单号
    function get_sn() {
        return date('YmdHis').rand(100000, 999999);
    }


}