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
     * 获取开票信息
     */
    public function get_order($id)
    {
        return self::get($id)->toArray();
    }

    /**
     * @author fyk
     * 更新开票信息
     */
    public function edit($id,$invoiceSerialNum, $state,$msg)
    {
        return $this->save([
            'invoiceSerialNum' => $invoiceSerialNum,
            'state' => $state,
            'msg' => $msg,

        ],['id' => $id,]);
    }
    /**
     * @author fyk
     * 新增企业开票
     */
    public function add_enterprise($uid, $no, $type, $status, $email, $rise ,$duty, $price, $tel, $address)
    {
        return $this->save([
            'user_id' => $uid,
            'no'=> $no,
            'type' => $type,
            'status' => $status,
            'email' =>$email,
            'rise' =>$rise,
            'duty' =>$duty,
            'price' =>$price,
            'phone'=>$tel,
            'address'=>$address,
            'invoiceLine'=>'p',
            'state' =>1,
            'create_time' => time(),


        ]);
    }
    /**
     * @author fyk
     * 新增个人开票
     */
    public function add_personal($uid, $no, $type, $status, $email, $rise ,$tel,$price,$address)
    {
        return $this->save([
            'user_id' => $uid,
            'no'=> $no,
            'type' => $type,
            'status' => $status,
            'email' =>$email,
            'rise' =>$rise,
            'phone' =>$tel,
            'price' =>$price,
            'address'=>$address,
            'invoiceLine'=>'p',
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
                    "orderNo"=> $data['no'],//订单编号唯一
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


    /**
     * //pc端支付开发票
     * gy
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cityOrderReceipt($data)
    {

        $rules = [
            'user_id'=>'require',
            'type'=>'require',
            'status'=>'require',
            'rise'=>'require',
            'price'=>'require',
            'address'=>'require',
            'phone'=>'require',

        ];
        $message = [
            'user_id.require'=>'用户id不能为空',
            'type.require'=>'发票类型不能为空',
            'status.require'=>'发票样式不能为空',
            'rise.require'=>'抬头不能为空',
            'price.require'=>'金额不能为空',
            'address.require'=>'邮寄地址不能为空',
            'phone'=>'联系方式不能为空'
        ];
        //验证
        $validate = new Validate($rules,$message);
        if(!$validate->check($data)){
            $this->error = $validate->getError();
            return false;
        }
        $data['create_time'] = time();
        return  $this -> allowField(true)->save($data);

    }
}