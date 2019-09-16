<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/11
 * Time: 15:59
 */
namespace app\rec\model;
use think\Model;
include('../extend/lib/SendApi.php');

class Invoice extends Model{

    protected $table = "tp_invoice";
    protected $resultSetType = 'collection';

    /**
     * @author fyk
     * 新增企业开票
     */
    public function add_enterprise($uid, $no, $type,$status, $rise ,$duty,$price)
    {
        return $this->save([
            'user_id' => $uid,
            'no'=> $no,
            'type' => $type,
            'status' => $status,
            'rise' =>$rise,
            'duty' =>$duty,
            'price' =>$price,
            'state' =>1,
            'create_time' => time(),


        ]);
    }
    /**
     * @author fyk
     * 新增个人开票
     */
    public function add_personal($uid,$no, $type,$status, $rise ,$tel,$price)
    {
        return $this->save([
            'user_id' => $uid,
            'no'=> $no,
            'type' => $type,
            'status' => $status,
            'rise' =>$rise,
            'phone' =>$tel,
            'price' =>$price,
            'state' =>1,
            'create_time' => time(),


        ]);
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
                            "goodsName"=> "电子防伪标",
                            "taxIncludedAmount"=> ""

                        )
                    ),
                    "buyerTel"=> $data['phone'],
                    "listFlag"=> "0",
                    "pushMode"=> "2",//推送方式:-1,不推送;0,邮箱;1,手机（默认）;2,邮箱、手机
                    "departmentId"=> "9F7E9439CA8B4C60A2FFF3EA3290B088",
                    "clerkId"=> "",
                    "checker"=> "",//复核
                    "remark"=> "备注信息",
                    "payee"=> "",//收款人
                    "buyerAddress"=> "",//购买人地址
                    "buyerTaxNum"=> "",//纳锐人识别号
                    "invoiceType"=> "1",
                    "invoiceLine"=> $data['invoiceLine'],
                    "email"=> $data['email'],
                    "salerAccount"=> "",//购买方开户行
                    "orderNo"=> $this->get_sn(),//订单编号唯一
                    "salerTel"=> "0571-81029365",
                    "buyerName"=> $data['rise'],
                    "invoiceDate"=> date('Y-m-d H:i:s',time()),
                    "invoiceCode"=> "125999915630",
                    "invoiceNum"=> "00130865",
                    "salerAddress"=> "北京西城区马连道8号院5号楼2层2079 63268696",
                    "clerk"=> "焦光华",
                    "buyerPhone"=> "17764096309",
                    "buyerAccount"=> "农商行马连道支行",
                    "productOilFlag"=> "0",
                    "salerTaxNum"=> "330100555190356",// //91110102MA006W4TXP
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