<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/10
 * Time: 14:10
 */
namespace app\rec\controller;
use app\rec\model\Store;
use app\rec\model\User as UserAll;
use think\Request;
use think\Validate;
use think\Controller;
use think\captcha\Captcha;
use think\Session;
use app\index\controller\Login as Loging;
use think\Loader;
//include('../extend/lib/SendApi.php');
//include('../extend/SampleCode/php/wxBizMsgCrypt.php');
class User extends Controller{
    /**
     * 获取验证码
     * @return array
     * @author fyk
     */
    public function code()
    {
        $request = Request::instance();
        $data = $request->param();
        // 验证
        $rules = [
            'phone' => 'require|regex:\d{11}',
        ];
        $msg = [
            'phone.require' => '请输入手机号',
            'phone.regex' => '手机号格式不正确',
        ];
        $valid = new Validate($rules, $msg);
        if (!$valid->check($data)) {
            return json(['code' => 0,'msg' => $valid->getError()]);
        }
        // 查询
        $user = db('pc_user') ->where('phone_number',$data['phone']) ->find();
        if (!$user) {
            // 手机号不存在
            $mobileCode = rand(100000, 999999);
            $mobile = $data['phone'];
            //存入session中
            if (strlen($mobileCode)> 0){
                Session::set('mobileCode',$mobileCode);
                Session::set('mobile',$mobile);
            }
            $content = "【智慧茶仓】尊敬的用户，您本次验证码为{$mobileCode}，十分钟内有效";
            $output = sendMessage($content,$mobile);

            $res = $output ? ['code' => 1,'msg' => '发送成功'] : ['code' => 0,'msg' => '发送失败'];

            return json($res);
        }else {
            return json(['code'=>0,'msg'=>'手机已注册, 请直接登录']);
        }


    }

    /**
     * 注册
     * @return array
     * @author fyk
     */
    public function register(){
        $request = Request::instance();
        $param = $request->param();

        $rules = [
            'phone_number' => 'require|regex:\d{11}|unique:user',
            'password'=>'require|alphaNum|confirm|length:6,16',
            'code'=>'require',
        ];
        $message = [
            'phone_number.require' => '请输入手机号',
            'phone_number.regex' => '手机号格式不正确',
            'phone_number.unique' => '手机号已存在',
            'password.require'=>'密码不能为空',
            'password.length' => '密码长度必须在6~16位之间',
            'password.confirm' => '两次密码输入不一致',
            'code.require'=>'验证码不能为空',
        ];
        //验证
        $validate = new Validate($rules,$message);
        if(!$validate->check($param)){
            return json(['code' => 0,'msg' => $validate->getError()]);
        }

        if (Session::get('mobileCode') != $param['code']) {
            return json(['code'=>1,'msg'=>$param['code']."验证码不正确"]);
        }
        //判断邀请码
        if(empty($param['invitation'])){
            $param['invitation'] = '';
        }else{
            $user = new UserAll();
            $shop_id = $user->shop($param['invitation']);

            if (empty($shop_id)) {
                return json(['code'=>0,'msg'=>"邀请码有误"]);
            }
        }

        $password = password_hash($param['password'],PASSWORD_DEFAULT);

        //调取生成店铺码
        $my_invitation = new Loging();
        $re_code = $my_invitation->memberCode();
        // 储存
        $user = new UserAll();
        $result = $user->add($param['phone_number'],$password,$param['invitation'],$re_code);

        $res = $result ? ['code' => 1,'msg' => '注册成功'] : ['code' => 0,'msg' => '注册失败'];

        return json($res);
    }

    /**
     * 登录
     * @return array
     * @author fyk
     */
    public function login()
    {
        $request = Request::instance();
        $data = $request->param();
        // 验证
        $rules = [
            'phone' => 'require|regex:\d{11}',
            'password' => 'alphaNum|require|length:6,16',
//            'captcha|验证码'=>'require|captcha'
        ];
        $msg = [
            'phone.require' => '请输入手机号',
            'phone.regex' => '手机号格式不正确',
            'password.require' => '密码不能为空',
            'password.length' => '密码长度必须在6~16位之间'
        ];
        $valid = new Validate($rules, $msg);
        if (!$valid->check($data)) {
            return json(['code' => 0,'msg' => $valid->getError()]);
        }
        // 查询
        $user = db('pc_user') ->where('phone_number',$data['phone']) ->find();
        //pp($user);die;
        if (!$user) {
            // 手机号不存在
            return json(['code'=>0,'msg'=>'账号或手机号不存在, 请先注册']);
        }else {
            // 判断密码是否正确
            if (password_verify($data['password'] ,$user['password'])) {
                //更新openID
                if(!empty($data['open_id'])){
                    db('pc_user') ->where('id',$user['id']) ->update(['openid'=>$data['open_id']]);
                }
                return json(['code'=>1,'msg'=>'登录成功','user_id'=>$user['id']]);
            }else {
                return json(['code'=>0,'msg'=>'密码错误']);
            }
        }
    }


    /**
     * 验证码
     * @return array
     * @author fyk
     */
    public function vs_code(){
        //引用
        $captcha = new Captcha();
        $captcha->fontSize = 30;
        $captcha->length   = 4;
        $captcha->useNoise = false;
        return $captcha->entry();
    }

    /**
     * 忘记密码
     * @return array
     * @author fyk
     */
    public function forget(){
        $request = Request::instance();
        $param = $request->param();

        $rules = [
            'user_id' => 'require',
            'phone_number' => 'require|regex:\d{11}',
            'password'=>'require|length:6,16',
            'code'=>'require',
        ];
        $message = [
            'phone_number.require' => '请输入手机号',
            'phone_number.regex' => '手机号格式不正确',
            'password.require'=>'密码不能为空',
            'password.length' => '密码长度必须在6~16位之间',
            'code.require'=>'验证码不能为空',
        ];
        //验证
        $validate = new Validate($rules,$message);
        if(!$validate->check($param)){
            return json(['code' => 0,'msg' => $validate->getError()]);
        }

        if (Session::get('mobileCode') != $param['code']) {
            return json(['code'=>1,'msg'=>$param['code']."验证码不正确"]);
        }

        $password = password_hash($param['password'],PASSWORD_DEFAULT);

        // 储存
        $user = new UserAll();
        $result = $user->edit($param['phone_number'],$password);

        $res = $result ? ['code' => 1,'msg' => '修改密码成功'] : ['code' => 0,'msg' => '修改密码失败'];

        return json($res);
    }

    /**
     * 修改手机号
     * @return array
     * @author fyk
     */
    public function edit_phone(){
        $request = Request::instance();
        $param = $request->param();

        $rules = [
            'user_id' => 'require',
            'new_phone' =>'require|regex:\d{11}',
            'new_code'=>'require',
            'password'=>'require',
        ];
        $message = [
            'new_phone.require' => '请输入新手机号',
            'new_phone.regex' => '新手机号格式不正确',
            'new_code.require'=>'新手机验证码不能为空',
            'password.require'=>'原密码不能为空',
        ];
        //验证
        $validate = new Validate($rules,$message);
        if(!$validate->check($param)){
            return json(['code' => 0,'msg' => $validate->getError()]);
        }

        if (Session::get('mobileCode') != $param['new_code']) {
            return json(['code'=>0,'msg'=>$param['new_code']."验证码不正确"]);
        }
        $user = db('pc_user') ->where('id',$param['user_id']) ->find();

        if (password_verify($param['password'] ,$user['password'])) {
            // 储存
            $user = new UserAll();
            $result = $user->edit_tel($param['user_id'],$param['new_phone']);

            $res = $result ? ['code' => 1,'msg' => '修改手机号成功'] : ['code' => 0,'msg' => '修改手机号失败'];
            return json($res);

        }else {
            return json(['code'=>0,'msg'=>'密码错误']);
        }



    }

    /**
     * 我的店铺
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user_store()
    {

        $request = Request::instance();
        $param = $request->param();

        if(!$param['user_id'])returnJson(0,'用户ID不能为空');

        $data = userAll::where('id',$param['user_id'])
            ->field('id,phone_number,my_invitation,img')->find();
        //判断
        returnArray($data);

        $data['store_num'] = Store::store_num($param['user_id']); //店铺数
        $data['withdrawals'] = 100; //已提现金额
        $data['no_mention'] = 1000; //未提现金额
        $data['commission'] = 1100; //分销佣金

        $data ? returnJson(1,'获取成功',$data) : returnJson(0,'获取失败');

    }


}