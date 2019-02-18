<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21 0021
 * Time: 14:35
 */
namespace  app\index\controller;
use think\Controller;
use think\Request;
use think\Db;

class My extends Controller
{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的页面数据返回
     **************************************
     * @param Request $request
     */
    public function my_index(Request $request){
        if($request->isPost()){
            $post_open_id = $request->only(['open_id'])['open_id'];
            $my_data =Db::name('member')
                ->field('member_phone_num,member_openid,member_name,member_head_img,member_grade_name,member_wallet,member_integral_wallet,member_grade_id')
                ->where('member_openid',$post_open_id)
                ->find();
            $post_member_grade_img =Db::name('member_grade')->field('member_grade_img,member_background_color')->where('member_grade_id',$my_data['member_grade_id'])->find();
           $my_data['member_grade_img'] =$post_member_grade_img['member_grade_img'];
           $my_data['member_background_color'] =$post_member_grade_img['member_background_color'];
            if(!empty($my_data)){
                return ajax_success('用户信息返回成功',$my_data);
            }else{
                return ajax_error('用户信息返回失败');
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:返回给小程序的会员等级数据
     **************************************
     * @param Request $request
     */
    public function show_grade(Request $request)
    {
        if ($request->isPost()) {
            $post_open_id = $request->only(['open_id'])['open_id'];
            if (!empty($post_open_id)) {
                $member_information = Db::name('member')->where('member_openid', $post_open_id)->find();
                $data = [];
                $data['member_id'] = $member_information['member_id']; //会员码
                $data['member_grade_create_time'] = date('Y-m-d H:i:s', $member_information['member_grade_create_time']); // 创建等级的时间
                $domain_name = 'http://teahouse.siring.com.cn';//域名
                $member_id = $member_information['member_id'];   //所登录的id
                $reg = 'reg';  //注册地址
                $share_url = $domain_name . "/" . $reg . "/" . $member_id;
                $share_code ='http://b.bshare.cn/barCode?site=weixin&url='.$share_url;
                $data['share_url'] = $share_code; //生成的二维码
                $data['member_grade_name'] =$member_information['member_grade_name'];
                $data['member_grade_id'] =$member_information['member_grade_id'];
                $member_data = Db::name('member_grade')->where('introduction_display', 1)->select();
                foreach ($member_data as $k => $v) {
                    $grade['member_grade_id'] = $v['member_grade_id'];           //会员等级ID
                    $grade['member_grade_name'] = $v['member_grade_name'];       //等级名称
                    $grade['member_grade_img'] = $v['member_grade_img'];     //等级图标
                    $grade['member_finite_period'] = $v['member_finite_period'];//有效期（年）
                    $grade['first_year_pay_full'] = $v['first_year_pay_full'];  //首年消费满（万元
                    $grade['recharge_member_send'] = $v['recharge_member_send']; //充值送会员（万元）
                    $grade['recharge_integral_send'] = $v['recharge_integral_send']; //充值送积分
                    $grade['member_background_color'] =$v['member_background_color']; //颜色
                }
                $user['member_grade'] = $member_data;//会员等级信息
                $user['information'] = $data;        //用户的所有信息
                if (!empty($user)) {
                    return ajax_success('成功返回数据', $user);
                } else {
                    return ajax_error('没有数据', ['status' => 0]);
                }
            }
        }

    }


     //获取用户经销商信息 及生成推广二维码
     public function qrcode(Request $request)
     {
        if ($request->isGet()) {
         //拿到openid  查找用户表内是否有该用户  没有则拒绝生成二维码   有则查看是否已生成二维码   有生成则发送数据   没有则生成
         //$openid = $request->only(['open_id'])['open_id'];//得到用户openid
         $openid = "o_lMv5eXfyfY3oXzzi3iXJx35_SU";//得到用户openid
         $dealer =  Db::name("member")->where(['member_openid' => $openid])->value('dimension');
         
         if(empty($dealer)){
             if(1){
                 $appid = 'wx301c1368929fdba8';
                 $secret = '94477ab333493c79f806f948f036f1e3';//AppSecret(小程序密钥)
                 //halt($secret);
                 $url_access_token = 'http://teahouse.siring.com.cn/teahouse/?id=123'.$appid.'&secret='.$secret;
                 $json_access_token = $this -> sendCmd($url_access_token,array());
                 $arr_access_token = json_decode($json_access_token,true);
                 $access_token = $arr_access_token['access_token'];
                 halt($access_token);
                 if(!empty($access_token)) {
                     $url = 'http://teahouse.siring.com.cn/teahouse'.$access_token;
                    //halt($url);
                     $data = '{"path": "pages/my/my?uid='.$dealer.'", "width": 430}';
                     $result = $this -> sendCmd($url,$data);
                     $name = $openid.time();
                     file_put_contents('./upload/qrcode/code-'.$name.'.jpg',$result);
                     //存储二维码路径
                     $arr['dimension'] = '/upload/qrcode/code-'.$name.'.jpg';
                     halt($arr);
                     $res=Db::name("users")->where(['member_openid' => $openid])->update($arr);
                     return ajax_success('暂无数据',$arr);
                 } else {
                     $arr = array('code'=>0,'msg'=>'ACCESS TOKEN为空！');
                     return ajax_error('暂无数据',$arr);
                 }
             }else{
                return ajax_success('获取二维码');
             }
         }else{
             $arr['dimension'] = $dealer;
             //echo "<img src=/youchis$dealer>";
             return ajax_error('暂无数据',$arr);
         }

        }
     }
 
     /**
      * 发起请求
      * @param  string $url  请求地址
      * @param  string $data 请求数据包
      * @return   string      请求返回数据
      */
     function sendCmd($url,$data)
     {
         $curl = curl_init(); // 启动一个CURL会话
         curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测
         curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
         curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:')); //解决数据包大不能提交
         curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
         curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
         curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
         curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
         curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循
         curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
 
         $tmpInfo = curl_exec($curl); // 执行操作
         if (curl_errno($curl)) {
             echo 'Errno'.curl_error($curl);
         }
         curl_close($curl); // 关键CURL会话
         return $tmpInfo; // 返回数据
     }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:手机账号数据返回，没有则进行绑定添加手机号
     **************************************
     * @param Request $request
     */
     public function  user_phone_return(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $phone_number =Db::name("member")
                ->where("member_id", $member_id)
                ->value("member_phone_num");
            if(!empty($phone_number)){
                return ajax_success("手机号返回成功",$phone_number);
            }else{
                return ajax_error("请前往绑定手机号",["status"=>0]);
            }
        }
     }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:手机号绑定
     **************************************
     * @param Request $request
     */
     public function user_phone_bingding(Request $request){
         if($request->isPost()){
             $member_id =$request->only(["member_id"])["member_id"];
             $member_phone_num =$request->only(["member_phone_num"])["member_phone_num"];
             $code =$request->only(["code"])["code"];
             if (session('mobileCode') != $code || $member_phone_num != $_SESSION['mobile']) {
                 return ajax_error("验证码不正确");
             }
             $phone_number =Db::name("member")
                 ->where("member_id", $member_id)
                 ->update(["member_phone_num"=>$member_phone_num]);
             if(!empty($phone_number)){
                 return ajax_success("绑定成功",$phone_number);
             }else{
                 return ajax_error("请重试",["status"=>0]);
             }
         }
     }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:用户昵称数据返回
     **************************************
     */
     public function user_name_return(Request $request){
         if($request->isPost()){
             $member_id =$request->only(["member_id"])["member_id"];
             $data =Db::name("member")
                 ->where("member_id",$member_id)
                 ->value("member_name");
             if(!empty($data)){
                 return ajax_success("昵称数据返回成功",$data);
             }else{
                 return ajax_error("没有昵称信息",["status"=>0]);
             }
         }
     }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:用户昵称数据修改
     **************************************
     * @param Request $request
     */
     public  function user_name_update(Request $request){
         if($request->isPost()){
             $user_name =$request->only(["user_name"])["user_name"];
             $member_id =$request->only(["member_id"])["member_id"];
             $data =[
                 "member_name" =>$user_name
             ];
            $bool =Db::name("member")->where("member_id",$member_id)->update($data);
            if($bool){
                return ajax_success("修改成功",$data);
            }else{
                return ajax_error("修改失败",["status"=>0]);
            }
         }
     }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:用户头像数据返回
     **************************************
     */
    public function user_img_return(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $data =Db::name("member")
                ->where("member_id",$member_id)
                ->value("member_head_img");
            if(!empty($data)){
                return ajax_success("昵称数据返回成功",$data);
            }else{
                return ajax_error("没有昵称信息",["status"=>0]);
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:用户头像修改
     **************************************
     */
    public function user_img_update(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $member_img =$request->file("member_images");
            if(!empty($member_img)){
                $info = $member_img->move(ROOT_PATH . 'public' . DS . 'uploads');
                $images= str_replace("\\", "/", $info->getSaveName());

            }
            if(empty($images)){
                return ajax_error("上传失败");
            }
            $data =Db::name("member")
                ->where("member_id",$member_id)
                ->update(["member_images"=>$images]);
            if(!empty($data)){
                return ajax_success("昵称数据返回成功",$data);
            }else{
                return ajax_error("没有昵称信息",["status"=>0]);
            }
        }
    }

}