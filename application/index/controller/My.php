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
use think\Session;
use think\Cache;
use app\index\controller\Login as LoginPass;
use app\common\model\dealer\Apply as ApplyModel;
use app\common\model\dealer\Referee as RefereeModel;


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
                ->field('member_phone_num,member_openid,member_name,member_head_img,member_grade_name,member_wallet,member_integral_wallet,member_grade_id,member_recharge_money,dimension')
                ->where('member_openid',$post_open_id)
                ->find();
            $post_member_grade_img = Db::name('member_grade')
                ->field('member_grade_img,member_background_color')
                ->where('member_grade_id',$my_data['member_grade_id'])
                ->find();
           $my_data['member_grade_img'] =$post_member_grade_img['member_grade_img'];
           $my_data['member_background_color'] =$post_member_grade_img['member_background_color'];
           $my_data["member_wallet"] =$my_data["member_wallet"]+$my_data["member_recharge_money"];

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
            $store_id = $request->only(['uniacid'])['uniacid'];
            //判断用户分享码是否存在
            if (file_exists(ROOT_PATH . 'public' . DS . 'uploads'.DS.$store_id.'.txt')) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                $re=file_get_contents(ROOT_PATH . 'public' . DS . 'uploads'.DS.$store_id.'.txt');
            }else{
                 //获取用户的信息
                 $member_information = Db::name('member')->where('member_openid', $post_open_id)->find();
                //获取携带参数的小程序的二维码
                $page='pages/logs/logs';
                $qrcode=$this->mpcode($page,$member_information['member_id'],$store_id);
                //把qrcode文件写进文件中，使用的时候拿出来
                $dateFile =$store_id . "/";  //创建目录
                $new_file = ROOT_PATH . 'public' . DS . 'uploads'.DS.$store_id.'.txt';
                // if (!file_exists($new_file)) {
                //     //检查是否有该文件夹，如果没有就创建，并给予最高权限
                //     mkdir($new_file, 750);
                // }
                if (file_put_contents($new_file, $qrcode)) {
                    // return  $dateFile . $filename;  //返回文件名及路径
                    $re=file_get_contents(ROOT_PATH . 'public' . DS . 'uploads'.DS.$store_id.'.txt');
                } else {
                    return ajax_success('获取失败');
                }
            }
            if (!empty($post_open_id)) {
                //获取用户的信息
                $member_information = Db::name('member')->where('member_openid', $post_open_id)->find();
                $data = [];
                $data['member_id'] = $member_information['member_id']; //会员id
                $data['dimension'] = $member_information['dimension']; //会员码
                $data['member_grade_create_time'] = date('Y-m-d H:i:s', $member_information['member_grade_create_time']); //创建等级的时间
                $domain_name = 'http://teahouse.siring.com.cn';  //域名
                $member_id = $member_information['member_id'];   //所登录的id
                $reg = 'reg';  //注册地址
                // $share_url = $domain_name . "/" . $reg . "/" . $member_id;
                // $share_code ='http://b.bshare.cn/barCode?site=weixin&url='.$share_url;
                $share_code = $re;
                $data['share_url'] = $share_code; //生成的二维码
                $data['member_grade_name'] =$member_information['member_grade_name'];
                $data['member_grade_id'] =$member_information['member_grade_id'];
                //获取上级的推广码
                if($member_information['inviter_id']=='0'){
                    $qrcode_up=0;
                }else{
                    $qrcode_up=db('member')->where('member_id',$member_information['inviter_id'])->value('dimension');
                }
                $data['dimension_up'] =$qrcode_up;
                $member_data = Db::name('member_grade')
                            ->where('introduction_display', 1)
                            ->where('store_id', $store_id)
                            ->whereOr("member_grade_id",$member_information['member_grade_id'])
                            ->select();
                
                $user['member_grade'] = $member_data; //会员等级信息
                $user['information'] = $data;         //用户的所有信息
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
                 $url_access_token = config("domain.url").'/?id=123'.$appid.'&secret='.$secret;
                 $json_access_token = $this -> sendCmd($url_access_token,array());
                 $arr_access_token = json_decode($json_access_token,true);
                 $access_token = $arr_access_token['access_token'];
                 if(!empty($access_token)) {
                     $url = config("domain.url").$access_token;
                    //halt($url);
                     $data = '{"path": "pages/my/my?uid='.$dealer.'", "width": 430}';
                     $result = $this -> sendCmd($url,$data);
                     $name = $openid.time();
                     file_put_contents('./upload/qrcode/code-'.$name.'.jpg',$result);
                     //存储二维码路径
                     $arr['dimension'] = '/upload/qrcode/code-'.$name.'.jpg';
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
      * GY
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
            $phone_number = Db::name("member")
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
     * Notes:修改支付密码
     **************************************
     * @param Request $request
     */
     public function user_phone_bingding(Request $request){
         if($request->isPost()){
             $member_id = $request->only(["member_id"])["member_id"];
             $passwd = $request->only(["password"])["password"];
             $code =$request->only(["code"])["code"];
             $mobileCode = Cache::get('mobileCode');
             $mobile = Cache::get('mobile');
             if($mobileCode != $code ) {
                 return ajax_error("验证码不正确");
             }
             $phone_number = Db::name("member")
                 ->where("member_id", $member_id)
                 ->value("member_phone_num");
             if($phone_number){
                 Cache::rm('mobileCode');
                 Cache::rm('mobile');
                 $passwd =password_hash($passwd,PASSWORD_DEFAULT);
                $bool = Db::name("member")->where("member_id",$member_id)->update(['pay_password'=>$passwd]);
                return ajax_success("修改成功",$bool);
             }else{
                 return ajax_error("请重试",["status"=>0]);
             }
         }
     }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:绑定手机号
     **************************************
     * @param Request $request
     */
     public function user_phone_bangding(Request $request){
         if($request->isPost()){
             $member_id = $request->only(["member_id"])["member_id"];
             $phone = $request->only(["member_phone_num"])["member_phone_num"];
             $code =$request->only(["code"])["code"];
             $mobileCode = Cache::get('mobileCode');
             $mobile = Cache::get('mobile');
             if($mobileCode != $code ) {
                 return ajax_error("验证码不正确");
             }
            //  $phone_number = Db::name("member")
            //      ->where("member_id", $member_id)
            //      ->value("member_phone_num");
             if($member_id && $phone){
                 Cache::rm('mobileCode');
                 Cache::rm('mobile');
                //  $passwd =password_hash($passwd,PASSWORD_DEFAULT);
                $bool = Db::name("member")->where("member_id",$member_id)->update(['member_phone_num'=>$phone]);
                return ajax_success("绑定成功",$bool);
             }else{
                 return ajax_error("请重试",["status"=>0]);
             }
         }
     }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:修改手机号绑定
     **************************************
     * @param Request $request
     */
    public function user_phone_bingding_update(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $old_phone_num =$request->only(["old_phone_num"])["old_phone_num"];
            $old =Db::name("member")
                ->where("member_id",$member_id)
                ->value("member_phone_num");
            if( $old_phone_num  !=$old){
                return ajax_error("老账号不是原绑定的手机号");
            }
            $member_phone_num =$request->only(["member_phone_num"])["member_phone_num"];
            $code =$request->only(["code"])["code"];
            $mobileCode =Cache::get('mobileCode');
            $mobile =Cache::get('mobile');
            if($mobileCode != $code || $member_phone_num != $mobile) {
                return ajax_error("验证码不正确");
            }
            $phone_number =Db::name("member")
                ->where("member_id", $member_id)
                ->update(["member_phone_num"=>$member_phone_num]);
            if(!empty($phone_number)){
                Cache::rm('mobileCode');
                Cache::rm('mobile');
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


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:用户会员码返回
     **************************************
     */
    public function consumerCode(Request $request){
        if($request->isPost()){
            $member_id =$request->only(["member_id"])["member_id"];
            $code = Db::name("member")
                ->where("member_id",$member_id)
                ->find();
                $data['dimension']=$code['dimension'];    //自己的邀请码
                //获取上一级的会员邀请码
                if($code['inviter_id']=='0'){    //没有上级
                   $data['dimension_up']='';
                }else{
                    $dimension_up = Db::name("member")
                    ->where("member_id",$code['inviter_id'])
                    ->value('dimension');
                    $data['dimension_up']=$dimension_up;
                }
            if(!empty($code)){
                return ajax_success("邀请码返回成功",$data);
            }else{
                $member_code = new LoginPass;
                $new_code = $member_code -> memberCode();
                $bool = db("member")->where('member_id',$member_id)->update(["dimension"=>$new_code]);
                if($bool){
                    return ajax_success("邀请码返回成功",$new_code);
                } else {
                    return ajax_success("邀请码返回失败");
                }
            }
        }
    }
    /**
     * lilu
     * 生成小程序分享码
     */
    public function getAccesstoken($uniacid){
        // $store_id=Session::get('store_id');
        //获取小程序的信息
        $re=Db::table('applet')->where('store_id',$uniacid)->find();
        $appid = $re['appID'];                     /*小程序appid*/
        $srcret = $re['appSecret'];                   /*小程序秘钥*/
        $tokenUrl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$srcret;
        $getArr=array();
        $tokenArr=json_decode($this->send_post($tokenUrl,$getArr,"GET"),true);
        $access_token=$tokenArr['access_token'];
        return $access_token;
    }
    public function send_post($url, $post_data,$method='POST') {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => $method, //or GET
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
    public function api_notice_increment($url, $data){
        $ch = curl_init();
        $header=array('Accept-Language:zh-CN','x-appkey:114816004000028','x-apsignature:933931F9124593865313864503D477035C0F6A0C551804320036A2A1C5DF38297C9A4D30BB1714EC53214BD92112FB31B4A6FAB466EEF245710CC83D840D410A7592D262B09D0A5D0FE3A2295A81F32D4C75EBD65FA846004A42248B096EDE2FEE84EDEBEBEC321C237D99483AB51235FCB900AD501C07A9CAD2F415C36DED82','x-apversion:1.0','Content-Type:application/x-www-form-urlencoded','Accept-Charset: utf-8','Accept:application/json','X-APFormat:json');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        //         var_dump($tmpInfo);
        //        exit;
        if (curl_errno($ch)) {
            return false;
        }else{
            // var_dump($tmpInfo);
            return $tmpInfo;
        }
    }
    /*上面生成的是数量限制10万的二维码，下面重写数量不限制的码*/
    /*getWXACodeUnlimit*/
    /*码一，圆形的小程序二维码，数量限制一分钟五千条*/
    /*45009    调用分钟频率受限(目前5000次/分钟，会调整)，如需大量小程序码，建议预生成。
    41030    所传page页面不存在，或者小程序没有发布*/
    public function mpcode($page,$cardid,$uniacid){
        //参数----会员id
        $postdata['scene']=$cardid;
        // 宽度
        $postdata['width']=430;
        // 页面
        $postdata['page']=$page;     //扫码后进入的小程序页面
//        $postdata['page']="pages/postcard/postcard";
        // 线条颜色
        $postdata['auto_color']=false;
        //auto_color 为 false 时生效
        $postdata['line_color']=['r'=>'0','g'=>'0','b'=>'0'];
        // 是否有底色为true时是透明的
        $postdata['is_hyaline']=true;
        $post_data = json_encode($postdata);
        $access_token=$this->getAccesstoken($uniacid);
        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
        $result=$this->api_notice_increment($url,$post_data);
        $data='image/png;base64,'.base64_encode($result);
       
        return $data;
//        echo '<img src="data:'.$data.'">';
    }
    /*码二，正方形的二维码，数量限制调用十万条*/
    public function qrcodes(){
        $path="pages/logs/logs";
        // 宽度
        $postdata['width']=430;
        // 页面
        $postdata['path']=$path;
        $post_data = json_encode($postdata);
        $access_token=$this->getAccesstoken();
        $url="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token;
        $result=$this->api_notice_increment($url,$post_data);
        $data='image/png;base64,'.base64_encode($result);
        echo '<img src="data:'.$data.'">';
    }
   public function uploadOne($file)
    {
        header('Content-type:text/html;charset=utf-8');
        $base64_image_content = trim($file);
        //正则匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type = $result[2];//图片后缀
    
            $dateFile = date('Y-m-d', time()) . "/";  //创建目录
            $new_file = UPLOAD_BASE_PATH . $dateFile;
            if (!file_exists($new_file)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0700);
            }
    
            $filename = time() . '_' . uniqid() . ".{$type}"; //文件名
            $new_file = $new_file . $filename;
            
            //写入操作
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                return $dateFile . $filename;  //返回文件名及路径
            } else {
                return false;
            }
        }
    }
    /**
     * lilu
     * 分享返积分
     * member_id   
     * inviter_id     上级id
     * uniacid   店铺id
     */
    public function qr_back_points(){
        if($input['member_id']==$input['inviter_id']){
            return ajax_error('操作失败,非法操作');
        }
        //获取参数   inviter_id  上级id    member_integral_wallet    积分余额
        $input=input();
        //获取扫码获取参数的配置积分  recommend_integral
        $recommend_data = db('recommend_integral')->where("store_id","EQ",$input['uniacid'])->find();
        //给上级id增加积分
        $re = db('member')->where('member_id',$input['inviter_id'])->setInc('member_integral_wallet',$recommend_data['recommend_integral']);
        // $dimension=db('member')->where('member_id',$input['inviter_id'])->value('dimension');
        //给当前客户记录上级id
        $re2 = db('member')->where('member_id',$input['member_id'])->update(['inviter_id'=>$input['inviter_id']]);
        if($re && $re2){
            $member_data = Db::name("member")->where('member_id','=',$input['member_id'])->find();
            $apply = new ApplyModel;
            $rest = $apply->submit($member_data);
            RefereeModel::createRelation($input['member_id'], $input['inviter_id'],$member_data['store_id']);
 
             return ajax_success('操作成功');
        }else{
             return ajax_error('操作失败');

        }
    }
    /**
     * lilu
     * 获取首页的银行卡列表
     * uncacid
     * member_id
     */
    public function get_bank_list(){
        //获取参数信息
        $input=input();
        //获取银行卡信息
        $list=db('store_bank_icard')->where(['store_id'=>$input['uncacid'],'status'=>1])->select();
        if($list){
           return ajax_success('获取成功',$list);
        }else{
            return ajax_error('获取失败');

        }

    }


}