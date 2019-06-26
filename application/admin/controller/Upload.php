<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Upload extends Controller
{
    public function index(){
        // if(check_login()){
             $user_id=Session::get('user_id');
             if(!$user_id)
             {
                $this->redirect('Login/index');
             }
             $role_id=db('admin')->where('id',$user_id)->find();
             if($role_id['role_id']==7){
        	// if(powerget()){
        		$id = $role_id['store_id'];     //小程序id
        		$res = Db::table('applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $commitData = [
                    // 'siteroot' => "https://".$_SERVER['HTTP_HOST']."/api/Wxapp2/",//'https://duli.nttrip.cn/api/Wxapps/',
                    // 'siteroot' => "https://xcx.siring.com.cn/api/Wxapps",//'https://duli.nttrip.cn/api/Wxapps/',
                    'siteroot' => "https://teahouse.siring.com.cn/api/",//'https://duli.nttrip.cn/api/Wxapps/',
                    'uip' => $_SERVER['REMOTE_ADDR'] ,
                    'appid' => $res['appID'],
                    'site_name' => $res['name'],
                    'uniacid' => $id,
                    'version' => '2.05',//当前小程序的版本
                    'tominiprogram' => unserialize($res['tominiprogram'])
                ];

                $params = http_build_query($commitData);
                $url = "http://wx.hdewm.com/uploadApi.php?".$params;
                $response = $this->_requestGetcurl($url);

                $result = json_decode($response,true);
                if(isset($result['data']) && $result['data'] != ""){
                    $this->assign("code_uuid",$result['code_uuid']);
                    $this->assign("code_token",$result['data']['code_token']);
                }
                $this->assign("appid",$res['appID']);
                $this->assign("projectname",$res['name']);
                $this->assign("id",$id);
                $this->assign("url",$url);
        	}else{
        		$usergroup = Session::get('usergroup');
        		if($usergroup==1){
        			$this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
        		}
        		if($usergroup==2){
        			$this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
        		}
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
        	}
            return $this->fetch('index');
        // }else{
        //     $this->redirect('Login/index');
        // }
    }


    public function wx_login(){
        // if(check_login()){
        //     if(powerget()){
            $user_id=Session::get('user_id');
            if(!$user_id)
            {
               $this->redirect('Login/index');
            }
            $role_id=db('admin')->where('id',$user_id)->find();
            if($role_id['role_id']==15){
           // if(powerget()){
                $id = input("appletid");
                $version = input('version');
                $desc = input('desc');
                $res = Db::table('applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $commitData = [
                    // 'siteroot' => "https://".$_SERVER['HTTP_HOST']."/api/",//'https://duli.nttrip.cn/api/Wxapps/',
                    'siteroot' => "https://teahouse.siring.com.cn/api/",//'https://duli.nttrip.cn/api/Wxapps/',
                    'uip' => $_SERVER['REMOTE_ADDR'] ,
                    'appid' => $res['appID'],
                    'site_name' => $res['name'],
                    'uniacid' => $id,
                    'version' => '2.05',//当前小程序的版本
                    'tominiprogram' => unserialize($res['tominiprogram'])
                ];
                $params = http_build_query($commitData);
                $url = "http://wx.hdewm.com/uploadApi.php?".$params;
                $response = $this->_requestGetcurl($url);

                $result = json_decode($response,true);
                if(isset($result['data']) && $result['data'] != ""){
                    $this->assign("code_uuid",$result['code_uuid']);
                    $this->assign("code_token",$result['data']['code_token']);
                }
                $this->assign("appid",$res['appID']);
                $this->assign("projectname",$res['name']);
                $this->assign("id",$id);
                $this->assign("url",$url);
                $this->assign('version', $version);
                $this->assign('desc', $desc);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
            }
            return $this->fetch('index');
        // }else{
        //     $this->redirect('Login/index');
        // }
    }


    public function checkscan(){
        $token = input("token");
        $last = input("last");
        $url = "http://wx.hdewm.com/uploadApi.php?do=checkscan&code_token=".$token."&last=".$last;
        $response = $this->_requestGetcurl($url);
        echo $response;
    }
    public function checklogin(){
        $uniacid = input("uniacid");
        $appid = input('appid');
        $name = input('name');
        if(strpos(ROOT_HOST,'https')===false){
            $host = "https".substr(ROOT_HOST,4);
        }
        $url = "http://122.114.217.68:8008/?type=get&op=open&appid=".$appid."&projectname=".$name."&url=".$host."/api/Wxapp2/&uniacid=".$uniacid;
        $result = json_decode($this->_requestGetcurl($url),true);
        if(isset($result['status']) && (int)$result['status'] == 1){
            return 1;
        }else{
            return 0;
        }
    }
    public function wxxcxinfo(){
        $store_id=Session::get('store_id');
        $uniacid = $store_id;
        $status = input("status");
        $token = input("token");
        $scan_token = input("scan_token");
        $code_uuid = input("code_uuid");
        // $this->assign("code_uuid",$code_uuid);
        $this->assign("code_uuid",'');
        // $this->assign("scan_token",$scan_token);
        $this->assign("scan_token",'');
        $res = Db::table('applet')->where("id",$uniacid)->find();
        if(!$res){ 
            $this->error("找不到对应的小程序！");
            exit;
        }
        $this->assign('applet',$res);
        // if($status){
        //     $this->assign('applet',$res);
        //     return $this->fetch("wxxcxinfo");
        // }else{
        //     $this->error("登录有误，需重新登录",$this->redirect('Wxreview/index'));
        // }
        return $this->fetch("wxxcxinfo");
    }
    public function yulan(){
        $uniacid = input("uniacid");
        $res = Db::table('applet')->where("id",$uniacid)->find();
        if(!$res){ 
            $this->error("找不到对应的小程序！");
            exit;
        }
        $url = "http://122.114.217.68:8008/?type=get&op=preview&appid=".$res['appID'];
        $result = $this->_requestGetcurl($url);
        if(strpos($result,'错误 需要重新登录')===true){
            return 1;
        }else if($result){
            return "data:image/jpeg;base64,".$result;
        }
    }
    public function upload(){
        $uniacid = input("uniacid");
        $desc = input("desc");
        $version = input("version");
        $res = Db::table('applet')->where("id",$uniacid)->find();
        $url = "http://122.114.217.68:8008/?type=get&op=upload&appid=".$res['appID']."&version=".$version."&desc=".$desc;
        $result = json_decode($this->_requestGetcurl($url),true);
        if(isset($result['error']) &&  $result['error']== "错误 需要重新登录"){
            return 1;
        }else if($result){
            return 2;
        }
    }
    /*
     * 新版本预览
     * */
    public function preview(){
        $token = input('token');
        $uuid = input("uuid");
        $url = "http://wx.hdewm.com/uploadApi.php?do=preview&code_token=".$token."&code_uuid=".$uuid;
        $response = $this->_requestGetcurl($url);
        echo $response;
    }
    /*新版本的代码提交*/
    public function commitcode(){
        $token = input("token");
        $uuid = input("uuid");
        $version = input("version");
        $desc = input('desc');
        $data = [
            'user_version' => $version,'user_desc' => $desc,'code_token' => $token,'code_uuid' => $uuid
        ];
        $params = http_build_query($data);
        $url = "http://wx.hdewm.com/uploadApi.php?do=commitcode&".$params;
        $response = json_decode($this->_requestGetcurl($url));
        // var_dump($response);
        // var_dump(1);
        // exit;
        return $response;
    }
        
    public function _requestGetcurl($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    //跳转小程序
    public function tominiprogram(){
        $uniacid = input('appletid');
        $res = Db::table('applet')->where("id",$uniacid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        
        if($res['tominiprogram']){
            $tominiprogram = unserialize($res['tominiprogram']);
        }else{
            $tominiprogram = '';
        }

        $this->assign('tominiprogram', $tominiprogram);
        return $this->fetch('tominiprogram');
    }

    //添加页面
    public function add_appid(){
        $uniacid = input('appletid');
        $res = Db::table('applet')->where("id",$uniacid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        return $this->fetch('add_appid');
    }


    //保存
    public function save_appid(){
        $uniacid = input('appletid');
        $appid = input('appid');
        if(!$appid){
            $this->error('请输入小程序APPID!');
            exit;
        }

        $res = Db::table('applet') ->where('id', $uniacid) ->find();

        if($res['tominiprogram']){

            $tominiprogram = unserialize($res['tominiprogram']);
            if(in_array($appid, $tominiprogram)){
                $this->error('该小程序已存在!');
            }else{
                if(count($tominiprogram) < 10){
                    array_push($tominiprogram, $appid);
                    $data['tominiprogram'] = serialize($tominiprogram);
                    $r = Db::table('applet') ->where('id', $uniacid) ->update($data);
                    if($r){
                        $this->success('添加成功!', Url('wxreview/tominiprogram').'?appletid='.$uniacid);
                    }else{
                        $this->error('发生未知错误, 操作失败, 请稍后再试!');
                    }
                }else{
                    $this->error('跳转小程序最多设置10个!', Url('wxreview/tominiprogram').'?appletid='.$uniacid);
                }
            }
            
        }else{
            $data['tominiprogram'] = serialize(array($appid));
            $r = Db::table('applet') ->where('id', $uniacid) ->update($data);
            if($r){
                $this->success('添加成功!', Url('wxreview/tominiprogram').'?appletid='.$uniacid);
            }else{
                $this->error('发生未知错误, 操作失败, 请稍后再试!');
            }
        }
    }

    //删除
    public function del(){
        $uniacid = input('appletid');
        $appid = input('appid');

        $res = Db::table('applet') ->where('id', $uniacid) ->field('tominiprogram') ->find();
        $tominiprogram = unserialize($res['tominiprogram']);

        $tominiprogram = array_diff($tominiprogram, [$appid]);

        $data['tominiprogram'] = serialize($tominiprogram);
        $r = Db::table('applet') ->where('id', $uniacid) ->update($data);
        if($r){
            $this->success('删除成功!');
        }else{
            $this->error('发生未知错误, 操作失败, 请稍后再试!');
        }
    }
}