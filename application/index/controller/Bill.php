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
use app\admin\model\Goods;
use app\admin\model\MemberGrade;
use app\admin\model\Order as GoodsOrder;
use app\common\model\dealer\Order as OrderModel;
use app\common\model\dealer\Setting;
use app\city\model\User;
use app\city\controller\Picture;
use app\admin\model\Store;
use app\city\model\CityDetail;
use app\city\model\CityRank;
use app\index\model\Serial as Serials;

class Bill extends Controller{


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的消费
     **************************************
     * @return \think\response\View
     */
    public function ceshi12(Request $request){
        if($request->isPost()){

            $rest = Serials::serial_add($data);
            
        }
    }
    

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的消费
     **************************************
     * @return \think\response\View
     */
    public function consume_index(Request $request){
        if($request->isPost()){
            $user_id =$request->only(["member_id"])["member_id"];//用户id
            $now_time_one =date("Y");
            $condition = " `operation_time` like '%{$now_time_one}%' ";
            $data = Db::name("wallet")
                ->where("user_id",$user_id)
                ->where($condition)
                ->order("operation_time","desc")
                ->select();
            if(!empty($data)){
                return ajax_success("消费细节返回成功",$data);
            }else{
                return ajax_error("暂无消费记录",["status"=>0]);
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
    public function consume_search(Request $request){
        if($request->isPost()){
            $user_id =$request->only(["member_id"])["member_id"];//用户id
            $title =$request->only(["title"])["title"];//搜索关键词
            $now_time_one =date("Y");
            $condition = " `operation_time` like '%{$now_time_one}%' ";
            $conditions = " `title` like '%{$title}%' ";
            $data = Db::name("wallet")
                ->where("user_id",$user_id)
                ->where($condition)
                ->where($conditions)
                ->order("operation_time","desc")
                ->select();
            if(!empty($data)){
                return ajax_success("消费细节返回成功",$data);
            }else{
                return ajax_error("暂无消费记录",["status"=>0]);
            }
        }
    }
    

    public function get_ACCESS_TOKEN() //获取token
        {
        $appid  = 'wx7a8782e472a6c34a';
        $secret = 'ae3dce2528dc43edd49e571cb95b9c25';
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
        $res = $this->curl_post($url);
        return  $res["access_token"];
        }

public function buildQrcode()    //生成带参数二维码
{  
	// $str = $_POST['str'];
	$str = '123';
    $access_token= $this->get_ACCESS_TOKEN();
  // scene_id 参数
 	$data = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "test"}}}';  
    $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
    $obj = $this->curl_post($url, $data, 'POST');
    halt($obj);
    $ticket= $obj->ticket;
    return json_encode(['src'=>"?ticket=$ticket",'scene_str'=>$str]);
}

public function kf_login()    //扫码客服
{
	//$GLOBALS["HTTP_RAW_POST_DATA"];  //这个用不了了；换成下面那个
	$postStr = file_get_contents("php://input");
	$postObj = json_decode(json_encode(simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA)),true);
	$openid = $postObj['FromUserName'];
	$EventKey = $postObj['EventKey'];
	$user=M('sms_log')->field('id')->where(['openid'=>$openid])->find();

	if(!$user['id']){
		$id = M('sms_log')->add(['openid'=>$openid]);
		$Login=new Login();
		$pass=$Login->get_sign('kf'.$id);
		M('sms_log')->where(['id'=>$id])->save(['pass'=>$pass,'code'=>$EventKey]);
	}else{
		M('sms_log')->where(['id'=>$user['id']])->save(['code'=>$EventKey]);
	}
}

public function pass(){  //登录
	$str = $_POST['str'];
	$user=M('sms_log')->field('id,pass')->where(['code'=>$str])->find();
	M('sms_log')->where(['code'=>$str])->save(['code'=>'']);  //每次登录后清空参数，避免出现重复
	return json_encode(['user_name'=>'kf'.$user['id'],'pass'=>$user['pass']]);
}

public function curl_post($url, $data=null,$method='GET', $https=true)
{
   // 创建一个新cURL资源 
   $ch = curl_init();   
   // 设置URL和相应的选项 
   curl_setopt($ch, CURLOPT_URL, $url);  
   //要访问的网站 //启用时会将头文件的信息作为数据流输出。
   curl_setopt($ch, CURLOPT_HEADER, false);   
   //将curl_exec()获取的信息以字符串返回，而不是直接输出。 
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     
   
   if($https){ 
      
       //FALSE 禁止 cURL 验证对等证书（peer's certificate）。 
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
       curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 

       //验证主机 } 
       if($method == 'POST'){ 
           curl_setopt($ch, CURLOPT_POST, true); 
           
           //发送 POST 请求  //全部数据使用HTTP协议中的 "POST" 操作来发送。 
           curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        }    
            // 抓取URL并把它传递给浏览器 
            $content = curl_exec($ch);   
            //关闭cURL资源，并且释放系统资源 
            curl_close($ch);   
         
            return json_decode($content,true);

     }
}
}