<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/18 0018
 * Time: 14:04
 * 账单
 */

namespace  app\index\controller;


use think\Controller;
use think\Request;
use think\Db;
use app\admin\model\MakeZip;
use app\index\controller\Order;
use app\admin\model\MemberGrade;
use app\admin\model\Order as GoodsOrder;
use app\common\model\dealer\Order as OrderModel;
use app\common\model\dealer\Setting;
use app\common\model\dealer\Referee;
use app\common\model\dealer\Apply;
use app\city\model\User;
use app\common\model\dealer\User as Users;

use app\admin\model\Goods;
use app\city\controller\Picture;
use app\admin\model\Store;
use app\city\model\CityDetail;
use app\city\model\CityRank;
use app\index\model\Serial as Serials;
use app\api\controller\Message as MessageService;
use app\admin\model\TempletMessage;
use think\Validate;
use think\Exception;
use app\common\exception\BaseException;




class Bill extends Controller
{


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的消费
     **************************************
     * @return \think\response\View
     */
    public function ceshi12(Request $request)
    {
        if ($request->isPost()) {
            // $rett =  Referee::getRefereeUserId(1550, 2);
            // $commodity_id = [439];
            // //判断是否生成分销订单
            // $goods_bool = Goods::getDistributionStatus($commodity_id);
            // $order_num = 'ZY202003121515532551';
            // if ($goods_bool) {
            //     $getDistributionStatus = [
            //         'member_id' => 1550,
            //         'id' => 3631,
            //         'parts_order_number' => 'ZY202003121515532551',
            //         'goods_id' => [439],
            //         'store_id' => 6,
            //         'order_amount' => 2300,
            //         'goods_money' => 2300, //总金额
            //         'status' => 0,

            //     ];
            //     $order_info = Db::name("order")
            //     ->where("parts_order_number", $order_num)
            //     ->find();
            // $order = GoodsOrder::getOrderInforMation($order_info);
            // $model = OrderModel::grantMoney($order);

            // 判断推荐人是否为分销商
            $bool = 1613;
            if (Users::isDealerUser($bool)) {
                $inviter_id = 1590;
                //新增分销商
                $member_data = Db::name("member")->where('member_id', '=', 1613)->find();
                $apply = new Apply;
                $rest = $apply->submit($member_data);
                Referee::createRelation($bool, $inviter_id, $member_data['store_id']);
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的消费
     **************************************
     * @return \think\response\View
     */
    public function consume_index(Request $request)
    {
        if ($request->isPost()) {
            $user_id = $request->only(["member_id"])["member_id"]; //用户id
            $now_time_one = date("Y");
            $condition = " `operation_time` like '%{$now_time_one}%' ";
            $data = Db::name("wallet")
                ->where("user_id", $user_id)
                ->where($condition)
                ->order("operation_time", "desc")
                ->select();
            if (!empty($data)) {
                return ajax_success("消费细节返回成功", $data);
            } else {
                return ajax_error("暂无消费记录", ["status" => 0]);
            }
        }
        return view("my_consume");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的消费搜索
     *param       member_id     title
     */
    public function consume_search(Request $request)
    {
        if ($request->isPost()) {
            $user_id = $request->only(["member_id"])["member_id"]; //用户id
            $title = $request->only(["title"])["title"]; //搜索关键词
            $now_time_one = date("Y");
            $condition = " `operation_time` like '%{$now_time_one}%' ";
            $conditions = " `title` like '%{$title}%' ";
            $data = Db::name("wallet")
                ->where("user_id", $user_id)
                ->where($condition)
                ->where($conditions)
                ->order("operation_time", "desc")
                ->select();
            if (!empty($data)) {
                return ajax_success("消费细节返回成功", $data);
            } else {
                return ajax_error("暂无消费记录", ["status" => 0]);
            }
        }
    }


    public function get_ACCESS_TOKEN() //获取token
    {
        $appid  = 'wx7a8782e472a6c34a';
        $secret = 'ae3dce2528dc43edd49e571cb95b9c25';
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
        $res = $this->curl_post($url);
        return  $res["access_token"];
    }

    public function buildQrcode()    //生成带参数二维码
    {
        // $str = $_POST['str'];
        $str = '123';
        $access_token = $this->get_ACCESS_TOKEN();
        // scene_id 参数
        $data = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "test"}}}';
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $access_token;
        $obj = $this->curl_post($url, $data, 'POST');
        halt($obj);
        $ticket = $obj->ticket;
        return json_encode(['src' => "?ticket=$ticket", 'scene_str' => $str]);
    }

    public function kf_login()    //扫码客服
    {
        //$GLOBALS["HTTP_RAW_POST_DATA"];  //这个用不了了；换成下面那个
        $postStr = file_get_contents("php://input");
        $postObj = json_decode(json_encode(simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $openid = $postObj['FromUserName'];
        $EventKey = $postObj['EventKey'];
        $user = M('sms_log')->field('id')->where(['openid' => $openid])->find();

        if (!$user['id']) {
            $id = M('sms_log')->add(['openid' => $openid]);
            $Login = new Login();
            $pass = $Login->get_sign('kf' . $id);
            M('sms_log')->where(['id' => $id])->save(['pass' => $pass, 'code' => $EventKey]);
        } else {
            M('sms_log')->where(['id' => $user['id']])->save(['code' => $EventKey]);
        }
    }

    public function pass()
    {  //登录
        $str = $_POST['str'];
        $user = M('sms_log')->field('id,pass')->where(['code' => $str])->find();
        M('sms_log')->where(['code' => $str])->save(['code' => '']);  //每次登录后清空参数，避免出现重复
        return json_encode(['user_name' => 'kf' . $user['id'], 'pass' => $user['pass']]);
    }

    public function curl_post($url, $data = null, $method = 'GET', $https = true)
    {
        // 创建一个新cURL资源 
        $ch = curl_init();
        // 设置URL和相应的选项 
        curl_setopt($ch, CURLOPT_URL, $url);
        //要访问的网站 //启用时会将头文件的信息作为数据流输出。
        curl_setopt($ch, CURLOPT_HEADER, false);
        //将curl_exec()获取的信息以字符串返回，而不是直接输出。 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($https) {

            //FALSE 禁止 cURL 验证对等证书（peer's certificate）。 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            //验证主机 } 
            if ($method == 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);

                //发送 POST 请求  //全部数据使用HTTP协议中的 "POST" 操作来发送。 
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            // 抓取URL并把它传递给浏览器 
            $content = curl_exec($ch);
            //关闭cURL资源，并且释放系统资源 
            curl_close($ch);

            return json_decode($content, true);
        }
    }


    public static function addFileToZip($path, &$zip, $root = '')
    {
        $handler = opendir($path); //打开当前文件夹由$path指定。
        !$root && $root = $path;

        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") { //文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if (is_dir($path . "/" . $filename)) { // 如果读取的某个对象是文件夹，则递归
                    self::addFileToZip($path . "/" . $filename, $zip, $root);
                } else { //将文件加入zip对象
                    $pathFilename = $path . "/" . $filename;
                    $zip->addFile($pathFilename, str_replace($root . '/', '', $pathFilename));
                }
            }
        }
        @closedir($path);
    }


    public function gettoken()
    {
        $applet = Db::table('applet')
            ->where('id', '=', 6)
            ->find();

        $APPID = $applet['appID'];
        $APPSECRET =  $applet['appSecret'];
        $access_token = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $APPID . "&secret=" . $APPSECRET;
        $json = $this->httpRequest($access_token);
        return  json_decode($json, true);
    }

    //curl
    public function httpRequest($url, $data = '', $method = 'GET')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data != '') {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public function Distribution($array,$number)
    {
        if($number == 1){
            $sort = 'first_money';
        } else if($number == 2)
        {
            $sort = 'second_money';

        } else if($number == 3)
        {
            $sort = 'third_money';

        }
        $now_money = db('member')->where('member_id','=',$array["user_id"])->value('member_wallet');
        $datas = [
            "user_id" => $array["user_id"], //用户ID
            "wallet_operation" => $array['first_money'], //分销金额
            "wallet_type" => 1, //消费操作(1入，-1出)
            "operation_time" => date("Y-m-d H:i:s"), //操作时间
            "operation_linux_time" => time(), //操作时间
            "wallet_remarks" => "获得分销佣金" . $array[$sort] . "元", //消费备注
            "wallet_img" => " ", //图标
            "title" => "分销佣金", //标题（消费内容）
            "order_nums" => $array['order_no'], //订单编号
            "pay_type" => "小程序", //支付方式/
            "wallet_balance" => $now_money, //此刻钱包余额
        ];
        Db::name("wallet")->insert($datas); //存入消费记录表
    }
}
