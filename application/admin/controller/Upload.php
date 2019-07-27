<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\Miniprogram;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Upload extends Controller
{
    private $appid = 'wx4a653e89161abf1c';            //第三方平台应用appid
    private $appsecret = '4d88679173c2eb375b20ed57459973be';     //第三方平台应用appsecret
    private $token = 'zhihuichacang';           //第三方平台应用token（消息校验Token）
    private $encodingAesKey = 'zhihuichacangzhihuicangxuanmingkeji12345678';      //第三方平台应用Key（消息加解密Key）
    private $component_ticket= 'ticket@@@mMQLlMnPx_y9E5HWGdfJKeKJadwSFBhcrzA8eJrMSmfIZInb_8ck42Y9eitnPWnkZXlNkgR33-P3otpQ1c00-A';   //微信后台推送的ticket,用于获取第三方平台接口调用凭据
    /**
     * 
     */
    public function __construct(){
         //获取component_ticket
         $this->component_ticket=db('wx_threeopen')->where('id',1)->value('component_verify_ticket');
    }
    // public function index(){
    //          $user_id=Session::get('user_id');
    //          if(!$user_id)
    //          {
    //             $this->redirect('Login/index');
    //          }
    //          $role_id=db('admin')->where('id',$user_id)->find();
    //          if($role_id['role_id']==7){
    //     		$id = $role_id['store_id'];     //小程序id
    //     		$res = Db::table('applet')->where("id",$id)->find();
    //             if(!$res){
    //                 $this->error("找不到对应的小程序！");
    //             }
    //             $this->assign('applet',$res);
    //             $commitData = [
    //                 'siteroot' => "https://teahouse.siring.com.cn/api/",//'https://duli.nttrip.cn/api/Wxapps/',
    //                 'uip' => $_SERVER['REMOTE_ADDR'] ,
    //                 'appid' => $res['appID'],
    //                 'site_name' => $res['name'],
    //                 'uniacid' => $id,
    //                 'version' => '2.05',//当前小程序的版本
    //                 'tominiprogram' => unserialize($res['tominiprogram'])
    //             ];

    //             $params = http_build_query($commitData);
    //             $url = "http://wx.hdewm.com/uploadApi.php?".$params;
    //             $response = $this->_requestGetcurl($url);

    //             $result = json_decode($response,true);
    //             if(isset($result['data']) && $result['data'] != ""){
    //                 $this->assign("code_uuid",$result['code_uuid']);
    //                 $this->assign("code_token",$result['data']['code_token']);
    //             }
    //             $this->assign("appid",$res['appID']);
    //             $this->assign("projectname",$res['name']);
    //             $this->assign("id",$id);
    //             $this->assign("url",$url);
    //     	}else{
    //     		$usergroup = Session::get('usergroup');
    //     		if($usergroup==1){
    //     			$this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
    //     		}
    //     		if($usergroup==2){
    //     			$this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
    //     		}
    //             if($usergroup==3){
    //                 $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
    //             }
    //     	}
    //         return $this->fetch('index');
    //     // }else{
    //     //     $this->redirect('Login/index');
    //     // }
    // }


    // public function wx_login(){
    //     // if(check_login()){
    //     //     if(powerget()){
    //         $user_id=Session::get('user_id');
    //         if(!$user_id)
    //         {
    //            $this->redirect('Login/index');
    //         }
    //         $role_id=db('admin')->where('id',$user_id)->find();
    //         if($role_id['role_id']==15){
    //        // if(powerget()){
    //             $id = input("appletid");
    //             $version = input('version');
    //             $desc = input('desc');
    //             $res = Db::table('applet')->where("id",$id)->find();
    //             if(!$res){
    //                 $this->error("找不到对应的小程序！");
    //             }else{
    //                 $map['version']=$version;
    //                 $map['version_des']=$desc;
    //                 $rr=db('applet')->where('id',$id)->update($map);
    //             }
    //             $this->assign('applet',$res);
    //             $commitData = [
    //                 // 'siteroot' => "https://".$_SERVER['HTTP_HOST']."/api/",//'https://duli.nttrip.cn/api/Wxapps/',
    //                 'siteroot' => "https://teahouse.siring.com.cn/api/",//'https://duli.nttrip.cn/api/Wxapps/',
    //                 'uip' => $_SERVER['REMOTE_ADDR'] ,
    //                 'appid' => $res['appID'],
    //                 'site_name' => $res['name'],
    //                 'uniacid' => $id,
    //                 'version' => '2.05',//当前小程序的版本
    //                 'tominiprogram' => unserialize($res['tominiprogram'])
    //             ];
    //             $params = http_build_query($commitData);
    //             $url = "http://wx.hdewm.com/uploadApi.php?".$params;
    //             $response = $this->_requestGetcurl($url);

    //             $result = json_decode($response,true);
    //             if(isset($result['data']) && $result['data'] != ""){
    //                 $this->assign("code_uuid",$result['code_uuid']);
    //                 $this->assign("code_token",$result['data']['code_token']);
    //             }
    //             $this->assign("appid",$res['appID']);
    //             $this->assign("projectname",$res['name']);
    //             $this->assign("id",$id);
    //             $this->assign("url",$url);
    //             $this->assign('version', $version);
    //             $this->assign('desc', $desc);
    //         }else{
    //             $usergroup = Session::get('usergroup');
    //             if($usergroup==1){
    //                 $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
    //             }
    //             if($usergroup==2){
    //                 $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
    //             }
    //             if($usergroup==3){
    //                 $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
    //             }
    //         }
    //         return $this->fetch('index');
    //     // }else{
    //     //     $this->redirect('Login/index');
    //     // }
    // }


    // public function checkscan(){
    //     $token = input("token");
    //     $last = input("last");
    //     $url = "http://wx.hdewm.com/uploadApi.php?do=checkscan&code_token=".$token."&last=".$last;
    //     $response = $this->_requestGetcurl($url);
    //     echo $response;
    // }
    // public function checklogin(){
    //     $uniacid = input("uniacid");
    //     $appid = input('appid');
    //     $name = input('name');
    //     if(strpos(ROOT_HOST,'https')===false){
    //         $host = "https".substr(ROOT_HOST,4);
    //     }
    //     $url = "http://122.114.217.68:8008/?type=get&op=open&appid=".$appid."&projectname=".$name."&url=".$host."/api/Wxapp2/&uniacid=".$uniacid;
    //     $result = json_decode($this->_requestGetcurl($url),true);
    //     if(isset($result['status']) && (int)$result['status'] == 1){
    //         return 1;
    //     }else{
    //         return 0;
    //     }
    // }
    // public function wxxcxinfo(){
    //     $store_id=Session::get('store_id');
    //     $uniacid = $store_id;
    //     $status = input("status");
    //     $token = input("token");
    //     $scan_token = input("scan_token");
    //     $code_uuid = input("code_uuid");
    //     // $this->assign("code_uuid",$code_uuid);
    //     $this->assign("code_uuid",'');
    //     // $this->assign("scan_token",$scan_token);
    //     $this->assign("scan_token",'');
    //     $res = Db::table('applet')->where("id",$uniacid)->find();
    //     if(!$res){ 
    //         $this->error("找不到对应的小程序！");
    //         exit;
    //     }
    //     $this->assign('applet',$res);
    //     return $this->fetch("wxxcxinfo");
    // }
    // /**
    //  * lilu
    //  * 小程序开发版本预览
    //  */
    // public function yulan(){
    //     $uniacid = input("uniacid");
    //     $res = Db::table('applet')->where("id",$uniacid)->find();
    //     if(!$res){ 
    //         $this->error("找不到对应的小程序！");
    //         exit;
    //     }
    //     $url = "http://122.114.217.68:8008/?type=get&op=preview&appid=".$res['appID'];
    //     $result = $this->_requestGetcurl($url);
    //     if(strpos($result,'错误 需要重新登录')===true){
    //         return 1;
    //     }else if($result){
    //         return "data:image/jpeg;base64,".$result;
    //     }
    // }
    // public function upload(){
    //     $uniacid = input("uniacid");
    //     $desc = input("desc");
    //     $version = input("version");
    //     $res = Db::table('applet')->where("id",$uniacid)->find();
    //     $url = "http://122.114.217.68:8008/?type=get&op=upload&appid=".$res['appID']."&version=".$version."&desc=".$desc;
    //     $result = json_decode($this->_requestGetcurl($url),true);
    //     if(isset($result['error']) &&  $result['error']== "错误 需要重新登录"){
    //         return 1;
    //     }else if($result){
    //         return 2;
    //     }
    // }
    // /*
    //  * 新版本预览
    //  * */
    // public function preview(){
    //     $token = input('token');
    //     $uuid = input("uuid");
    //     $url = "http://wx.hdewm.com/uploadApi.php?do=preview&code_token=".$token."&code_uuid=".$uuid;
    //     $response = $this->_requestGetcurl($url);
    //     echo $response;
    // }
    // /*新版本的代码提交*/
    // public function commitcode(){
    //     $token = input("token");
    //     $uuid = input("uuid");
    //     $version = input("version");
    //     $desc = input('desc');
    //     $data = [
    //         'user_version' => $version,'user_desc' => $desc,'code_token' => $token,'code_uuid' => $uuid
    //     ];
    //     $params = http_build_query($data);
    //     $url = "http://wx.hdewm.com/uploadApi.php?do=commitcode&".$params;
    //     $response = json_decode($this->_requestGetcurl($url));
    //      //发送短信提醒
    //      $user=Session::get('user_info');
    //      //获取店铺的信息
    //      $store_name=DB::table('applet')->where('id',$user[0]['store_id'])->value('name');
    //      $phone = '13922830809';
    //      $content = $store_name."一键生成小程序，请尽快查看";
    //      $account='chacang';
    //      $password="123qwe";
    //      phone($account,$password,$phone,$content);   //发送短信实时提醒
    //      return $response;
    // }
        
    // public function _requestGetcurl($url){
    //     $curl = curl_init();
    //     curl_setopt($curl, CURLOPT_URL, $url);
    //     curl_setopt($curl, CURLOPT_HEADER, 0);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //     $data = curl_exec($curl);
    //     curl_close($curl);
    //     return $data;
    // }

    // //跳转小程序
    // public function tominiprogram(){
    //     $uniacid = input('appletid');
    //     $res = Db::table('applet')->where("id",$uniacid)->find();
    //     if(!$res){
    //         $this->error("找不到对应的小程序！");
    //     }
    //     $this->assign('applet',$res);

        
    //     if($res['tominiprogram']){
    //         $tominiprogram = unserialize($res['tominiprogram']);
    //     }else{
    //         $tominiprogram = '';
    //     }

    //     $this->assign('tominiprogram', $tominiprogram);
    //     return $this->fetch('tominiprogram');
    // }

    // //添加页面
    // public function add_appid(){
    //     $uniacid = input('appletid');
    //     $res = Db::table('applet')->where("id",$uniacid)->find();
    //     if(!$res){
    //         $this->error("找不到对应的小程序！");
    //     }
    //     $this->assign('applet',$res);

    //     return $this->fetch('add_appid');
    // }


    // //保存
    // public function save_appid(){
    //     $uniacid = input('appletid');
    //     $appid = input('appid');
    //     if(!$appid){
    //         $this->error('请输入小程序APPID!');
    //         exit;
    //     }

    //     $res = Db::table('applet') ->where('id', $uniacid) ->find();

    //     if($res['tominiprogram']){

    //         $tominiprogram = unserialize($res['tominiprogram']);
    //         if(in_array($appid, $tominiprogram)){
    //             $this->error('该小程序已存在!');
    //         }else{
    //             if(count($tominiprogram) < 10){
    //                 array_push($tominiprogram, $appid);
    //                 $data['tominiprogram'] = serialize($tominiprogram);
    //                 $r = Db::table('applet') ->where('id', $uniacid) ->update($data);
    //                 if($r){
    //                     $this->success('添加成功!', Url('wxreview/tominiprogram').'?appletid='.$uniacid);
    //                 }else{
    //                     $this->error('发生未知错误, 操作失败, 请稍后再试!');
    //                 }
    //             }else{
    //                 $this->error('跳转小程序最多设置10个!', Url('wxreview/tominiprogram').'?appletid='.$uniacid);
    //             }
    //         }
            
    //     }else{
    //         $data['tominiprogram'] = serialize(array($appid));
    //         $r = Db::table('applet') ->where('id', $uniacid) ->update($data);
    //         if($r){
    //             $this->success('添加成功!', Url('wxreview/tominiprogram').'?appletid='.$uniacid);
    //         }else{
    //             $this->error('发生未知错误, 操作失败, 请稍后再试!');
    //         }
    //     }
    // }

    // //删除
    // public function del(){
    //     $uniacid = input('appletid');
    //     $appid = input('appid');

    //     $res = Db::table('applet') ->where('id', $uniacid) ->field('tominiprogram') ->find();
    //     $tominiprogram = unserialize($res['tominiprogram']);

    //     $tominiprogram = array_diff($tominiprogram, [$appid]);

    //     $data['tominiprogram'] = serialize($tominiprogram);
    //     $r = Db::table('applet') ->where('id', $uniacid) ->update($data);
    //     if($r){
    //         $this->success('删除成功!');
    //     }else{
    //         $this->error('发生未知错误, 操作失败, 请稍后再试!');
    //     }
    // }

    /**
     * lilu
     * 一键生成起始页面
     */
    public function auth_pre(){
        
        return view('auth_pre');
    }
    /**
     * lilu
     * 一键生成授权页面
     */
    public function auth_index(){
        //授权开始
        $redirect_uri='http://zhihuichacang.com/$APPID$/callback';
        $url=$this->startAuth($redirect_uri,$auth_type=3);   //授权地址
        return view('auth_index',['data'=>"/qrcode?url2=".$url]);
    }
    /**
     * lilu
     * 一键生成授权详情
     */
    public function auth_detail(){
 
        return view('auth_detail');
    }
     /*
        * 扫码授权，注意此URL必须放置在页面当中用户点击进行跳转，不能通过程序跳转，否则将出现“请确认授权入口页所在域名，与授权后回调页所在域名相同....”错误
        * @params string $redirect_uri : 扫码成功后的回调地址
        * @params int $auth_type : 授权类型，1公众号，2小程序，3公众号/小程序同时展现。不传参数默认都展示    
        */
    public function startAuth($redirect_uri,$auth_type = 3)

    {
        $url = "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=".$this->appid."&pre_auth_code=".$this->get_pre_auth_code()."&redirect_uri=".urlencode($redirect_uri)."&auth_type=".$auth_type;
        return $url;
    }

     
    // /**
    //  * lilu  
    //  * 获取component_access_token
    //  */
    // public function get_component_access_token(){
    //         $res = $this->component_detail();//获取第三方平台基础信息
    //         $last_time = $res['token_time'];//上一次component_access_token获取时间
    //         $component_access_token = $res['component_access_token'];//获取数据查询到的component_access_token
    //         $difference_time = $this->validity($last_time);//上一次获取时间与当前时间的时间差
    //         //判断component_access_token是否为空或者是否超过有效期
    //         if(empty($component_access_token) || $difference_time>0){
    //             $component_access_token = $this->get_component_access_token_again();
    //         }
    //         return $component_access_token;
    //     }
    //     //获取第三方平台基础信息
    //     public function component_detail(){
    //         $res = db('wx_threeopen')->where('id',1)->find();
    //         return $res;
    //     }
    //     //重新获取component_access_token
    //     public function get_component_access_token_again(){
    //         $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
    //         $tok = $this->component_detail();
    //         $param ['component_appid'] = $tok['appid'];
    //         $param ['component_appsecret'] = $tok['appsecret'];
    //         $param ['component_verify_ticket'] = $tok['component_verify_ticket'];
    //         $param=json_encode($param);
    //         $data = $this->https_post ( $url, $param );
    //         halt($data);
    //         $token['component_access_token'] = $data ['component_access_token'];
    //         $token['token_time'] = time()+7000;
    //         db('wx_threeopen')->where('id',1)->update($token);
    //         return $data['component_access_token'];
    //     }
    //     //获取时间差
    //     public function validity($time){
    //         $current_time = time();
    //         $difference_time = $current_time -$time;
    //         return $difference_time;
    //     }
            /*
            * 获取第三方平台access_token
            * 注意，此值应保存，代码这里没保存
            */
            private function get_component_access_token()
            {
                $url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
                $data = '{
                    "component_appid":"'.$this->appid.'" ,
                    "component_appsecret": "'.$this->appsecret.'",
                    "component_verify_ticket": "'.$this->component_ticket.'"
                }';
                $ret = json_decode($this->https_post($url,$data),true);
                if($ret['component_access_token']) {
                    return $ret['component_access_token'];
                } else {
                    return false;
                }
            }

    /*

    *  第三方平台方获取预授权码pre_auth_code

    */

    private function get_pre_auth_code()

    {
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=".$this->get_component_access_token();
        $data = '{"component_appid":"'.$this->appid.'"}';
        $ret = json_decode($this->https_post($url,$data),true);
        if($ret['pre_auth_code']) {
            return $ret['pre_auth_code'];
        } else {
            return false;
        }

    }
    /*
    * 发起POST网络提交
    * @params string $url : 网络地址
    * @params json $data ： 发送的json格式数据
    */
    private function https_post($url,$data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
     /*
        * 发起GET网络提交
        * @params string $url : 网络地址
        */
    private function https_get($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); 
        curl_setopt($curl, CURLOPT_HEADER, FALSE) ; 
        curl_setopt($curl, CURLOPT_TIMEOUT,60);
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);
        }
        else{$result=curl_exec($curl);}
        curl_close($curl);
        return $result;
    }
   

     

}