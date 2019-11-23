<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/12
 * Time: 11:09
 */
namespace app\rec\controller;
use EasyWeChat\Foundation\Application;
use app\rec\model\Wechat as WechatAll;
use think\Request;
use think\Validate;
use think\Controller;
use think\Config;
//微信授权登录 获取个人信息
class Wechat extends Controller{

    //微信公众平台信息（appid/secret）
    protected $sj_appid = 'wxf120ba19ce55a392';
    protected $sj_secret = '06c0107cff1e3f5fe6c2eb039ac2d0b7';

     //手机端跳转首页
    protected $app_index = 'app/wechat/user/index.html';
    //手机端跳转绑定账号页面
    protected $app_wx = 'app/wechat/user/login.html';

    /**
     * @function 手机端网页微信登录授权（微信公众平台微信登录授权）
     */
    public function wx_accredit(){
        $redirect_uri = Config::get('web_url').'rec/wx_code';
        $redirect_uri = urlencode($redirect_uri);
        //微信公众平台appid
        $appid = $this->sj_appid;

        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
        $url =new WechatAll();
        $data = $url->wx_accredit();
    }
    /**
     * @function 获取openid
     */
    public function wx_code1(){
    	
        $request = Request::instance();
        $param = $request->param();
        if(empty($param['code'])){
            return returnJson(0,'code参数为空');exit;
        }
        //微信信息调用model方法
        $code = new \app\rec\model\Wechat();
        $res = $code->WxOpenid($param['code']);
        $openid_name = db('pc_user')->where(array('openid'=> $res['openid']))->field('id,phone_number')->find();

        if($openid_name){
            db('pc_user')->where(array('id'=> $openid_name['id']))
                ->update([
                    'openid'=> $res['openid'],
                    'img'=>$res['headimgurl'],
                    'utime'=>time()
                ]);
            $data = ['user_id'=>$openid_name['id'],'phone'=>$openid_name['phone_number']];
            return returnJson(1,'登录成功',$data);exit;
        }else{
            $data['openid'] = $res['openid'];
            return returnJson(2,'获取openID成功',$data);exit;

        }
        


    }
	
	public function checkSignature(){
        //获取由微信服务器发过来的数据
        $nonce = $_GET['nonce'];
        $token = 'agent';
        $timestamp = $_GET['timestamp'];
        $echostr = isset($_GET['echostr'])?$_GET['echostr']:'';
        $signature = $_GET['signature'];
       
        //开始验证数据
        $array = array();

                $array =  array($nonce,$timestamp,$token);
        sort($array);

        $str = sha1(sha1implode($array));
    
        //对比数据
        if ($str == $signature && $echostr) {
            echo $echostr;
        } else {
            $this ->re1();
        }
    }

    /**
     * 用户登录直接跳转
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
     public function wx_code(){
         $request = Request::instance();
         $param = $request->param();
         if(empty($param['code'])){
             return returnJson(0,'code参数为空');exit;
         }
          //print_r($param['code']);die;
         //微信公众平台信息
         $appid = Config::get('wx_appid');
         $secret = Config::get('wx_secret');

         //获取token
         $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$param['code'].'&grant_type=authorization_code';
         $data = $this->curlGet($url);
         //print_r($data);die;
         if(empty($data['access_token'])){
             return returnJson(0,'access_token错误');exit;
         }
         if(empty($data['openid'])){
             return returnJson(0,'openid错误');exit;
         }
         //拿取头像相关信息
         $token = $data['access_token'];
         $openid = $data['openid'];
         $Allurl = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$token.'&openid='.$openid.'&lang=zh_CN';

         $res = $this->curlGet($Allurl);
         //print_r($res);die;
         $openid_name = db('pc_user')->where(array('openid'=> $openid))->field('id,phone_number')->find();
         // print_r($openid_name);die;
         if($openid_name){
             //更新用户信息
             db('pc_user')->where(array('openid'=> $openid))
                 ->update([
                     'img'=>$res['headimgurl'],
                     'utime'=>time()
                 ]);
             //跳转首页
             $url = Config::get('web_url').$this->app_index.'?user_id='.$openid_name['id'];
             header('Location:'.$url);
         }else{
             //跳转绑定账号页面
             $url = Config::get('web_url').$this->app_wx.'?openid='.$data['openid'];
             header('Location:'.$url);
         }

     }
   
    /**
     * @function curl以get方式连接
     */
    public function curlGet($url){
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        $data = curl_exec($curl);
        curl_close($curl);
        return json_decode($data,true);
    }
    


}