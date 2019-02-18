<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/6
 * Time: 15:59
 */

namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Loader;
use think\Session;
use think\Cache;
use think\Request;

class Login extends Controller{


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:微信小程序授权登录
     **************************************
     */
    public function wechatlogin()
    {
        $get = input('get.');
        //获取session_key
//      $params['appid'] = 'wxaa091b014a6fa464';//公司
        $params['appid'] = 'wx301c1368929fdba8';//客户公司
//        $params['appid'] = 'wxe81efe5d23e83c7d';
//        $params['appid'] = 'wxee81c196c106311f';
//        $params['secret'] = '7b19ad668d1e24ca3b0323fcdb97236e';//公司
        $params['secret'] = '94477ab333493c79f806f948f036f1e3';//客户公司
//        $params['secret'] = '055128687ca3e2eb2756307cd03a5544';
//        $params['secret'] = 'b1aafb5fc38e091481432ccfe5712dfc';
        $params['js_code'] = define_str_replace($get['code']);
        $params['grant_type'] = 'authorization_code';
        $http_key = httpCurl('https://api.weixin.qq.com/sns/jscode2session', $params, 'GET');
        $session_key = json_decode($http_key, true);
        if (!empty($session_key['session_key'])) {
            $appid = $params['appid'];
            $encryptedData = urldecode($get['encryptedData']);
            $iv = define_str_replace($get['iv']);
            $errCode = decryptData($appid,$session_key['session_key'],$encryptedData, $iv);
            $register_login = db("recommend_integral")->where("id","1")->value("register_integral");//授权通过即送积分
            if(!empty($errCode )){
                $is_register =Db::name('member')->where('member_openid',$errCode['openId'])->find();
                if(empty($is_register)){
                    $data['member_openid'] =$errCode['openId'];
                    $data['member_head_img'] =$errCode['avatarUrl'];
                    $data['member_name'] =$errCode['nickName'];
                    $data['member_create_time'] =time();
                    $data['member_grade_create_time'] =time();
                    $data['member_grade_id']=1;
                    $data['member_status']=1;
                    $data['member_integral_wallet'] = $register_login;
                    $grade_name =Db::name('member_grade')->field('member_grade_name')->where('member_grade_id',1)->find();
                    $data['member_grade_name'] =$grade_name['member_grade_name'];
                    $bool = Db::name('member')->insertGetId($data);
                if($register_login > 0){
                    //插入积分记录
                    $integral_data = [
                        "member_id" => $bool,
                        "integral_operation" => $register_login,//获得积分
                        "integral_balance" => $register_login,//积分余额
                        "integral_type" => 1, //积分类型（1获得，-1消费）
                        "operation_time" => date("Y-m-d H:i:s"), //操作时间
                        "integral_remarks" => "成功注册送" . $register_login . "积分",
                    ];
                    Db::name("integral")->insert($integral_data);
                }
                    if($bool){
                        $member_grade_info =Db::name("member_grade")
                            ->field("member_grade_name,member_grade_img,member_grade_id")
                            ->where("member_grade_id",1)
                            ->find();
                        $info_data =[
                            "member_grade_info"=>$member_grade_info,
                            "openid"=>$errCode['openId'],
                            "member_id"=>$bool
                        ];
                        return ajax_success('返回数据成功',$info_data);
                    }else{
                        return ajax_success('返回数据失败',['status'=>0]);
                    }
                }else{
                    //已注册则进行登录
                    $member_grade_info =Db::name("member_grade")
                        ->field("member_grade_name,member_grade_img,member_grade_id")
                        ->where("member_grade_id",$is_register["member_grade_id"])
                        ->find();
                    $data =[
                        "member_grade_info"=>$member_grade_info,
                        "openid"=>$errCode['openId'],
                        "member_id"=>$is_register["member_id"]
                    ];
                    return ajax_error('该用户已经注册过，请不要重复注册',$data);
                }
            }else{
                return ajax_error('没有数据',['status'=>0]);
            }
        } else {
            return ajax_error('获取session_key失败',['status'=>0]);
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:登陆操作
     **************************************
     * @param Request $request
     */
    public function dolog(Request $request){
        if($request->isPost()){
            $data = $_GET;
            $user_mobile =$data['account'];
            $password =$data['passwd'];
            if(empty($user_mobile)){
                return  ajax_error('手机号不能为空',$user_mobile);
            }
            if(empty($password)){
                return ajax_error('密码不能为空',['status'=>0]);
            }
            $res = Db::name('pc_user')->field('password')->where('phone_number',$user_mobile)->find();
            $datas =[
                'phone_number'=> $user_mobile,
            ];
            if(password_verify($password , $res["password"])){
                if($res){
                    $ress =Db::name('pc_user')->where('phone_number',$user_mobile)->where('status',1)->field("id")->find();
                    if($ress)
                    {
                        Session::set("user",$ress["id"]);
                        Session::set('member',$datas);
                        return ajax_success('登录成功',$datas);
                    }else{
                        ajax_error('此用户已被管理员设置停用',$datas);
                    }
                }
            }else{
                return ajax_error('密码错误',['status'=>0]);
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:退出操作
     **************************************
     */
    public function logout(Request $request){
        if($request->isPost()){
            Session('member',null);
            Session::delete("user");//用户推出
            return ajax_success('退出成功',['status'=>1]);
        }
    }


}