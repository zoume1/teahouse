<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\Miniprogram;
use app\index\controller\My;
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
    // private $component_ticket= 'ticket@@@mMQLlMnPx_y9E5HWGdfJKeKJadwSFBhcrzA8eJrMSmfIZInb_8ck42Y9eitnPWnkZXlNkgR33-P3otpQ1c00-A';   //微信后台推送的ticket,用于获取第三方平台接口调用凭据
    /**
     * 
     */
    public function __construct(){
        //获取授权的APPID
        $store_id=Session::get('store_id');
        $this->appid_auth=db('miniprogram')->where('store_id',$store_id)->value('appid');
        ///获取component_ticket
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
        //获取店铺id
        $store_id=Session::get('store_id');
        //判断是否已授权
        //获取小程序二维码
        if (file_exists(ROOT_PATH . 'public' . DS . 'uploads'.DS.'D'.$store_id.'.txt')) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            $re=file_get_contents(ROOT_PATH . 'public' . DS . 'uploads'.DS.'D'.$store_id.'.txt');  //小程序二维码
        }else{
            //获取携带参数的小程序的二维码
            $page='pages/logs/logs';
            $qr=new My();
            $qrcode=$qr->mpcode($page,0,$store_id);
            $pp['msg']=$qrcode;
            db('test')->insert($pp);
            //把qrcode文件写进文件中，使用的时候拿出来
            $new_file = ROOT_PATH . 'public' . DS . 'uploads'.DS.'D'.$store_id.'.txt';
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                // mkdir($new_file, 0777);
                // mkdir($new_file, 750);
            if (file_put_contents($new_file, $qrcode)) {
                $re=file_get_contents(ROOT_PATH . 'public' . DS . 'uploads'.DS.'D'.$store_id.'.txt');
            } 
        }
        //判断是否已授权
        $is_shou=db('miniprogram')->where('store_id',$store_id)->find();
        if($is_shou){
            $is_shou['qr_img']=$re;
            return view('auth_detail',['data'=>$is_shou]);
        }else{
            //授权开始
            $redirect_uri='https://www.zhihuichacang.com/callback/appid/$APPID$';
            $url=$this->startAuth($redirect_uri,$auth_type=3);   //授权地址
            return view('auth_pre',['data'=>$url]);
        }
    }
    /**
     * lilu
     * 一键生成授权页面
     */
    public function auth_index(){
        //授权开始
        $redirect_uri='https://www.zhihuichacang.com/callback/appid/$APPID$';
        $url=$this->startAuth($redirect_uri,$auth_type=3);   //授权地址
        return view('auth_index',['data'=>$url]);
    }
    /**
     * lilu
     * 一键生成授权详情
     */
    public function auth_detail(){
         //获取店铺id
         $store_id=Session::get('store_id');
         //获取小程序二维码
          if (file_exists(ROOT_PATH . 'public' . DS . 'uploads'.DS.'D'.$store_id.'.txt')) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            $re=file_get_contents(ROOT_PATH . 'public' . DS . 'uploads'.DS.'D'.$store_id.'.txt');  //小程序二维码
        }else{
            //获取携带参数的小程序的二维码
            // $page='pages/logs/logs';
            $qr=new My();
            $re=$qr->create_qrcode($store_id);
            // //把qrcode文件写进文件中，使用的时候拿出来
            // $dateFile =$store_id . "/";  //创建目录
            // $new_file = ROOT_PATH . 'public' . DS . 'uploads'.DS.'D'.$store_id.'.txt';
            // // if (!file_exists($new_file)) {
            // //     //检查是否有该文件夹，如果没有就创建，并给予最高权限
            // //     mkdir($new_file, 750);
            // // }
            // if (file_put_contents($new_file, $qrcode)) {
            //     $re=file_get_contents(ROOT_PATH . 'public' . DS . 'uploads'.DS.'D'.$store_id.'.txt');
            // } 
        }
         //判断是否已授权
         $is_shou=db('miniprogram')->where('store_id',$store_id)->find();
         $is_shou['qr_img']=$re;
         if($is_shou){
             return view('auth_detail',['data'=>$is_shou]);
         }else{
             //授权开始
             $redirect_uri='https://www.zhihuichacang.com/callback/appid/$APPID$';
             $url=$this->startAuth($redirect_uri,$auth_type=3);   //授权地址
             return view('auth_pre',['data'=>$url]);
         }
 
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
     /*
         测试获取体验码
        * 发起GET网络提交
        * @params string $url : 网络地址
        */
    private function https_get2($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        // $proxy = "80.25.198.25";
        // $proxyport = "8080";
        // curl_setopt($curl,CURLOPT_proxy,$proxy);
        // curl_setopt($curl,CURLOPT_proxyPORT,$proxyport);
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
     /*
        * 成员管理，绑定小程序体验者
        * @params string $wechatid : 体验者的微信号
        * */
    public function set_tiyan()
    {
        //获取参数
        $input=input();
        //判断access_token是否过期，重新获取
        $store_id=Session::get('store_id');
        $appid=db('miniprogram')->where('store_id',$store_id)->value('appid');
        $timeout=$this->is_timeout($appid);
        $url = "https://api.weixin.qq.com/wxa/bind_tester?access_token=".$timeout['authorizer_access_token'];
        $data = '{"wechatid":"'.$input['wx'].'"}';
        $ret = json_decode($this->https_post($url,$data),true);
        if($ret['errcode'] == 0) {
            return  ajax_success('绑定成功');
        } else {
            return   ajax_error("绑定小程序体验者操作失败");
        }
    }
    /**
     * lilu
     * 检验access_token是否过期，重新获取
     * appid    授权小程序APPID
     */
    public function is_timeout($appid)
    {
        $store_id=Session::get('store_id');
        $weixin_account = Db::name('wx_threeopen')->where('id','1')->find(); //第三方信息
        if ($weixin_account) {
            //获取第三方的开放平台access_token
            $this->component_ticket=db('wx_threeopen')->where('id',1)->value('component_verify_ticket');
            $url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
            $data = '{
                "component_appid":"'.$this->appid.'" ,
                "component_appsecret": "'.$this->appsecret.'",
                "component_verify_ticket": "'.$this->component_ticket.'"
            }';
            $ret = json_decode($this->https_post($url,$data),true);
            $this->thirdAccessToken=$ret['component_access_token'];
            if($ret['component_access_token']) {
            $miniprogram = Db::name('miniprogram')->where('appid',$appid)
                ->field('access_token,authorizer_refresh_token')->find();
            //重新获取小程序的authorizer_access_token
            $access=$this->update_authorizer_access_token($appid,$miniprogram['authorizer_refresh_token'],$this->thirdAccessToken);
            $access['thirdAccessToken']=$ret['component_access_token'];
            return $access;
        } else {
            $this->errorLog("请增加微信第三方公众号平台账户信息",'');
            exit;
        }
    }
 }
  /*
    * 更新授权小程序的authorizer_access_token
    * @params string $appid : 小程序appid
    * @params string $refresh_token : 小程序authorizer_refresh_token
    * */
    private function update_authorizer_access_token($appid,$refresh_token,$thirdAccessToken)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token='.$thirdAccessToken;
        $data = '{"component_appid":"' . $this->appid . '","authorizer_appid":"' . $appid . '","authorizer_refresh_token":"' . $refresh_token . '"}';
        $ret = json_decode($this->https_post($url, $data),true);
        if (isset($ret['authorizer_access_token'])) {
            Db::name('miniprogram')->where(['appid' => $appid])->update(['access_token' => $ret['authorizer_access_token'], 'authorizer_refresh_token' => $ret['authorizer_refresh_token']]);
            return $ret;
        } else {
            $this->errorLog("更新授权小程序的authorizer_access_token操作失败,appid:".$appid,$ret);
            return null;
        }
    }
     /**
    * lilu
    * 错误日志
    */
    private function errorLog($msg,$ret)
    {
        // file_put_contents(ROOT_PATH . 'runtime/error/miniprogram.log', "[" . date('Y-m-d H:i:s') . "] ".$msg."," .json_encode($ret).PHP_EOL, FILE_APPEND);
        $pp['msg']=$ret;
        db('test')->insert($pp);
    }
    /**
     * lilu
     * 一键上传店铺---短信提醒
     */
    // public function send_message(){
    //     $user=Session::get('user_info');
    //      //获取店铺的信息
    //      $store_name=DB::table('applet')->where('id',$user[0]['store_id'])->value('name');
    //      $phone = '13922830809';
    //     //  $phone = '13502882637';
    //      $content = $store_name."一键上传店铺代码，请尽快完成上传";
    //      $account='chacang';
    //      $password="123qwe";
    //      $re=phone($account,$password,$phone,$content);   //发送短信实时提醒  
    //      if($re){
    //             return ajax_success('发送成功');
    //         }else{
    //             return ajax_error('发送失败');
    //      }
    // }

     /*
        * 为授权的小程序帐号上传小程序代码
        * @params int $template_id : 模板ID
        * @params json $ext_json : 小程序配置文件，json格式
        * @params string $user_version : 代码版本号
        * @params string $user_desc : 代码描述
     * */
    public function send_message($template_id = 3, $user_version = 'v1.0.0', $user_desc = "秒答营业厅")
    {
        //判断access_token是否过期，重新获取
        $store_id=Session::get('store_id');
        $appid=db('miniprogram')->where('store_id',$store_id)->value('appid');
        $timeout=$this->is_timeout($appid);
        $ext_json = json_encode('{"extEnable": true,"extAppid": "'.$appid.'","ext":{"appid": "'.$appid.'"}}');
        $url = "https://api.weixin.qq.com/wxa/commit?access_token=".$timeout['authorizer_access_token'];
        $data = '{"template_id":"'.$template_id.'","ext_json":'.$ext_json.',"user_version":"'.$user_version.'","user_desc":"'.$user_desc.'"}';
        $ret2 = $this->https_post($url,$data);
        $ret = json_decode($ret2,true);
        $p['msg']=$ret2;
        db('test')->insert($p);
        if($ret['errcode'] == 0) {
            return ajax_success('上传成功');
        } else {
            return ajax_error('上传失败');
        }
    }
    /**
     * lilu
     * 获取体验码
     */
    public function get_qrcode($path = '')
    {
        //判断access_token是否过期，重新获取
        $store_id=Session::get('store_id');
        $appid=db('miniprogram')->where('store_id',$store_id)->value('appid');
        $timeout=$this->is_timeout($appid);
            if($path){
                $url = "https://api.weixin.qq.com/wxa/get_qrcode?access_token=".$timeout['authorizer_access_token']."&path=".urlencode($path);
            } else {
                $url = "https://api.weixin.qq.com/wxa/get_qrcode?access_token=".$timeout['authorizer_access_token'];
            }
            $ret2 = $this->https_get2($url);
            $ret = json_decode($ret2,true);
            $p['msg']=$ret2.'体验码';
            db('test')->insert($p);
            if($ret['errcode']) {
                return ajax_success('获取失败');
            } else {
                //304支持
                    // if (0 && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
                    // {
                    //     header('Cache-Control: public');
                    //     header('Last-Modified:' . $_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304);
                    //     exit();
                    // }
                    
                    header('Cache-Control: public');
                    // header('Last-Modified: ' . $_SERVER['REQUEST_TIME']);/
                    header('Content-Type: image/jpeg');
                    //这就是1张图 Content-Type: image/jpeg 
                    echo file_get_contents($url);
                    // halt(file_get_contents($url));
                    // echo  '<img src="data:'.file_get_contents($url).'">';
                        // return ajax_success('获取成功',["url"=>$url]);
            }
    }
    // public function serverIp(){
    //     if(isset($_SERVER)){
    //         if($_SERVER['SERVER_ADDR']){
    //             $server_ip=$_SERVER['SERVER_ADDR'];
    //         }else{
    //             $server_ip=$_SERVER['LOCAL_ADDR'];
    //         }
    //     }else{
    //         $server_ip = getenv('SERVER_ADDR');
    //     }
    //     return $server_ip;
    // }
    
    
    /**
     * lilu
     * 提交审核
     */
    public function publish($tag = "微信小程序" ,$title = "微信小程序")
    {
        //判断access_token是否过期，重新获取
        $store_id=Session::get('store_id');
        $appid=db('miniprogram')->where('store_id',$store_id)->value('appid');
        $timeout=$this->is_timeout($appid);
        $first_class = '';$second_class = '';$first_id = 0;$second_id = 0;
        $address = "pages/index/index";
        $category = $this->getCategory($timeout['authorizer_access_token']);
        if(!empty($category)) {
            $first_class = $category[0]['first_class'] ? $category[0]['first_class'] : '' ;
            $second_class = $category[0]['second_class'] ? $category[0]['second_class'] : '';
            $first_id = $category[0]['first_id'] ? $category[0]['first_id'] : 0;
            $second_id = $category[0]['second_id'] ? $category[0]['second_id'] : 0;
        }
        $getpage = $this->getPage($timeout['authorizer_access_token']);
        if(!empty($getpage) && isset($getpage[0])) {
            $address = $getpage[0];
        }
        $url = "https://api.weixin.qq.com/wxa/submit_audit?access_token=".$timeout['authorizer_access_token'];
        $data = '{
                "item_list":[{
                    "address":"'.$address.'",
                    "tag":"'.$tag.'",
                    "title":"'.$title.'",
                    "first_class":"'.$first_class.'",
                    "second_class":"'.$second_class.'",
                    "first_id":"'.$first_id.'",
                    "second_id":"'.$second_id.'"
                }]
            }';
        $ret = json_decode($this->https_post($url,$data),true);
        if($ret['errcode'] == 0) {
            return ajax_success('提交成功');
        } else {
            return ajax_error('提交失败');
        }
    }
    /*
     * lilu
     * 发布已通过审核的小程序
     * */
    public function release()
    {
        //判断access_token是否过期，重新获取
        $store_id=Session::get('store_id');
        $appid=db('miniprogram')->where('store_id',$store_id)->value('appid');
        $timeout=$this->is_timeout($appid);
        $url = "https://api.weixin.qq.com/wxa/release?access_token=".$timeout['authorizer_access_token'];
        $data = '{}';
        $ret = json_decode($this->https_post($url,$data),true);
        if($ret['errcode'] == 0) {
            return ajax_success('发布成功');
        } else {
            return ajax_error('发布失败');
        }
    }
    /**
     * lilu
     * 解除绑定
     */
    public function relieve()
    {
        //判断access_token是否过期，重新获取
        $store_id=Session::get('store_id');
        $re=db('miniprogram')->where('store_id',$store_id)->delete();
        if($re) {
            return ajax_success('解绑成功');
        } else {
            return ajax_error('解绑失败');
        }
    }
    /*
     * 获取授权小程序帐号的可选类目
     * */
    private function getCategory($authorizer_access_token)
    {
        $url = "https://api.weixin.qq.com/wxa/get_category?access_token=".$authorizer_access_token;
        $ret = json_decode($this->https_get($url),true);
        if($ret['errcode'] == 0) {
            return $ret['category_list'];
        } else {
            $this->errorLog("获取授权小程序帐号的可选类目操作失败",$ret);
            return false;
        }
    }
    /*
     * 获取小程序的第三方提交代码的页面配置
     * */
    private function getPage($authorizer_access_token)
    {
        $url = "https://api.weixin.qq.com/wxa/get_page?access_token=".$authorizer_access_token;
        $ret = json_decode($this->https_get($url),true);
        if($ret['errcode'] == 0) {
            return $ret['page_list'];
        } else {
            $this->errorLog("获取小程序的第三方提交代码的页面配置失败",$ret);
            return false;
        }

    }
   

     

}