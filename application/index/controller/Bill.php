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
            $data =[
                'serial_number' => 'RET223456789',
                'create_time' => time(),
                'type'=> 1,
                'status'=> '普通订单',
                'money'=> 12
            ];
            $rest = Serials::serial_add($data);
            halt($rest);
            // $array_city = [
            //     ['name'=>'蚌埠市'	,'rank_status'=>'5','store_number'=>1356],	
            //     ['name'=>'焦作市'	,'rank_status'=>'5','store_number'=>997],	
            //     ['name'=>'拉萨市'	,'rank_status'=>'5','store_number'=>987	],
            //     ['name'=>'遂宁市'	,'rank_status'=>'5','store_number'=>968	],
            //     ['name'=>'抚顺市'	,'rank_status'=>'5','store_number'=>963	],
            //     ['name'=>'阜新市'	,'rank_status'=>'5','store_number'=>935	],
            //     ['name'=>'莱芜市'	,'rank_status'=>'5','store_number'=>927	],
            //     ['name'=>'大庆市'	,'rank_status'=>'5','store_number'=>926	],
            //     ['name'=>'通辽市'	,'rank_status'=>'5','store_number'=>926	],
            //     ['name'=>'营口市'	,'rank_status'=>'5','store_number'=>893	],
            //     ['name'=>'鹤壁市'	,'rank_status'=>'5','store_number'=>887	],
            //     ['name'=>'鄂尔多斯市'	,'rank_status'=>'5','store_number'=>866	],
            //     ['name'=>'崇左市'	,'rank_status'=>'5','store_number'=>859	],
            //     ['name'=>'铜川市'	,'rank_status'=>'5','store_number'=>859	],
            //     ['name'=>'呼伦贝尔市'	,'rank_status'=>'5','store_number'=>847	],
            //     ['name'=>'白山市'	,'rank_status'=>'5','store_number'=>820	],
            //     ['name'=>'鞍山市'	,'rank_status'=>'5','store_number'=>808	],
            //     ['name'=>'通化市'	,'rank_status'=>'5','store_number'=>778	],
            //     ['name'=>'长治市'	,'rank_status'=>'5','store_number'=>776	],
            //     ['name'=>'鹰潭市'	,'rank_status'=>'5','store_number'=>773	],
            //     ['name'=>'临夏回族自治州'	,'rank_status'=>'5','store_number'=>772	],
            //     ['name'=>'秦皇岛市','rank_status'=>'5','store_number'=>	758	],
            //     ['name'=>'丹东市'	,'rank_status'=>'5','store_number'=>713	],
            //     ['name'=>'包头市'	,'rank_status'=>'5','store_number'=>710	],
            //     ['name'=>'酒泉市'	,'rank_status'=>'5','store_number'=>707	],
            //     ['name'=>'兴安盟'	,'rank_status'=>'5','store_number'=>682	],
            //     ['name'=>'松原市'	,'rank_status'=>'5','store_number'=>679	],
            //     ['name'=>'广安市'	,'rank_status'=>'5','store_number'=>676	],
            //     ['name'=>'武威市'	,'rank_status'=>'5','store_number'=>648],	
            //     ['name'=>'鄂州市'	,'rank_status'=>'5','store_number'=>621],	
            //     ['name'=>'锦州市'	,'rank_status'=>'5','store_number'=>615],	
            //     ['name'=>'宿迁市'	,'rank_status'=>'5','store_number'=>615	],
            //     ['name'=>'铁岭市'	,'rank_status'=>'5','store_number'=>612	],
            //     ['name'=>'朝阳市'	,'rank_status'=>'5','store_number'=>612	],
            //     ['name'=>'辽源市'	,'rank_status'=>'5','store_number'=>602	],
            //     ['name'=>'淮北市'	,'rank_status'=>'5','store_number'=>590	],
            //     ['name'=>'攀枝花市'	,'rank_status'=>'5','store_number'=>576	],
            //     ['name'=>'舟山市'	,'rank_status'=>'5','store_number'=>572	],
            //     ['name'=>'锡林郭勒盟'	,'rank_status'=>'5','store_number'=>562	],
            //     ['name'=>'迪庆藏族自治州'	,'rank_status'=>'5','store_number'=>539	],
            //     ['name'=>'新余市'	,'rank_status'=>'5','store_number'=>536	],
            //     ['name'=>'资阳市'	,'rank_status'=>'5','store_number'=>526	],
            //     ['name'=>'葫芦岛市'	,'rank_status'=>'5','store_number'=>511	],
            //     ['name'=>'朔州市'	,'rank_status'=>'5','store_number'=>508	],
            //     ['name'=>'金昌市'	,'rank_status'=>'5','store_number'=>495	],
            //     ['name'=>'阿坝藏族羌族自治州'	,'rank_status'=>'5','store_number'=>489],	
            //     ['name'=>'甘南藏族自治州'	,'rank_status'=>'5','store_number'=>471	],
            //     ['name'=>'辽阳市'	,'rank_status'=>'5','store_number'=>468	],
            //     ['name'=>'双鸭山市'	,'rank_status'=>'5','store_number'=>407	],
            //     ['name'=>'乌兰察布市'	,'rank_status'=>'5','store_number'=>392	],
            //     ['name'=>'盘锦市'	,'rank_status'=>'5','store_number'=>370	],
            //     ['name'=>'那曲市'	,'rank_status'=>'5','store_number'=>342	],
            //     ['name'=>'本溪市'	,'rank_status'=>'5','store_number'=>337	],
            //     ['name'=>'日喀则市'	,'rank_status'=>'5','store_number'=>331	],
            //     ['name'=>'晋城市'	,'rank_status'=>'5','store_number'=>327	],
            //     ['name'=>'甘孜藏族自治州'	,'rank_status'=>'5','store_number'=>315	],
            //     ['name'=>'怒江傈僳族自治州'	,'rank_status'=>'5','store_number'=>296	],
            //     ['name'=>'林芝市'	,'rank_status'=>'5','store_number'=>260	],
            //     ['name'=>'巴彦淖尔市'	,'rank_status'=>'5','store_number'=>249	],
            //     ['name'=>'阿拉善盟'	,'rank_status'=>'5','store_number'=>217	],
            //     ['name'=>'克孜勒苏柯尔克孜自治州'	,'rank_status'=>'5','store_number'=>211]	,
            //     ['name'=>'乌海市'	,'rank_status'=>'5','store_number'=>203	],
            //     ['name'=>'克拉玛依市'	,'rank_status'=>'5','store_number'=>203	],
            //     ['name'=>'昌都市'	,'rank_status'=>'5','store_number'=>192	],
            //     ['name'=>'嘉峪关市'	,'rank_status'=>'5','store_number'=>157	],
            //     ['name'=>'山南市'	,'rank_status'=>'5','store_number'=>119	],
            //     ['name'=>'博尔塔拉蒙古自治州'	,'rank_status'=>'5','store_number'=>118	],
            //     ['name'=>'三沙市'	,'rank_status'=>'5','store_number'=>66]	,
            //     ['name'=>'阿里地区','rank_status'=>'5','store_number'=>	52],                                                        
                
            // ];
            
            // $onee = new CityRank;
            // $reste = $onee->saveAll($array_city); 
            //生成分销代理订单
            // $one = new CityDetail;
            // $bool = $one->city_store_update('云南省',31);
            // halt($bool);
  
            // $order_number='TC2019060616044231';
            // $enter_all_data = Db::name("set_meal_order")
            //         ->where("order_number",$order_number)
            //         ->find();
            
            // $store_data_rest = Db::name('store')->where('id',$enter_all_data['store_id'])->find();
            // // halt($store_data_rest);
            // CityDetail::store_order_commission($enter_all_data,$store_data_rest);
            // halt(222);
            //     $rest = db('store')->field('address_data,id')->select();
            //     // halt($rest);
            //     $city = "北京市";
                
            //     foreach($rest as $key =>  $value){
            //         if(in_array($city,explode(",",$value["address_data"]))){
            //             $one[$key]['id'] = $value['id'];
            //             $one[$key]['city_user_id'] = 1;
            //             // $one = new Store;
            //             // $reste[] = $one->where('id', $rest[$key]["id"])->saveAll(['city_user_id'=>1]); 
            //     }
            // }
            //  $onee = new Store;
            //  $reste = $onee->saveAll($one); 
            //     halt($one);
            //     foreach($one as $k => $l){
            //         unset($l['address_data']);
            //         $one[$k]['ll'] = 1;

            //     }

            //     $rest->cheshi2();
            // return  jsonError("失败",array(),ERROR_100);
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