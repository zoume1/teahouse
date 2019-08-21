<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/4 0004
 * Time: 16:07
 */
namespace  app\index\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use app\index\controller\Login as LoginPass;
class  Store extends  Controller{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:创建店铺
     **************************************
     */
    public function  store_add(Request $request){
        if($request->isPost()){
            $user_id =Session::get("user");
            $store_code = new LoginPass;
            $is_business =$request->only(["is_business"])["is_business"];
            $id_card =$request->only(["id_card"])["id_card"];
            $contact_name =$request->only(["contact_name"])["contact_name"];
            $address_data =$request->only(["address_data"])["address_data"];
            $address_real_data =$request->only(["address_real_data"])["address_real_data"];
            $store_introduction =$request->only(["store_introduction"])["store_introduction"];
            $business_name =$request->only(["business_name"])["business_name"];
            $licence_no =$request->only(["licence_no"])["licence_no"];
            $store_name =$request->only(["store_name"])["store_name"];
        
            if(empty($id_card) || empty($contact_name) || empty($address_data) ||empty($address_real_data) ||empty($store_introduction || empty($store_name)) ){
                    return ajax_error("请注意填写完所有资料");
            }
            if($is_business ==2){
                if(empty($business_name) || empty($licence_no)){
                    return ajax_error("请填写企业信息");
                }
            }
            $card_positive =$request->only(["card_positive"])["card_positive"]; //身份证正面
            if(empty($card_positive)){
                return ajax_error("请上传身份证正面图");
            }
            $card_side_file = $request->only(["card_side"])["card_side"];//身份证反面
            if(empty($card_side_file)){
                return ajax_error("请上传身份证反面图");
            }
            $card_positive_images = base64_upload_flie($card_positive);//身份证正面
            $card_side_file =base64_upload_flie($card_side_file) ; //身份证反面
            $phone_number = db("pc_user")->where("id",$user_id)->find();//获取手机号
            $data = [
                "is_business"=>$is_business,
                "id_card"=>$id_card,
                "contact_name"=>$contact_name,
                "address_data"=>$address_data,
                "address_real_data"=>$address_real_data,
                "card_positive"=>$card_positive_images,
                "card_side"=>$card_side_file,
                "store_introduction"=>$store_introduction,
                "business_name"=>$business_name,
                "licence_no"=>$licence_no,
                "user_id"=>$user_id,
                "phone_number"=>$phone_number['phone_number'],
                "share_store_id" => $phone_number['invite_id'], //上级邀请user_id
                'share_code'=>$phone_number['my_invitation'],   //本店邀请码
                'highe_share_code'=>$phone_number['invitation'],   //上级分享码
                //店铺状态(1审核通过,-1审核不通过,2审核中）
                "status"=>1,
                "store_name"=>$store_name,
            ];

            $bool = Db::name("store")->insertGetId($data);
            if($bool > 0){
                    $user_data =Db::table("tb_pc_user")
                        ->field("phone_number,password")
                        ->where("id",$user_id)
                        ->find();
                    //审核通过则在后台添加一个登录账号，不通过则不添加
                   $is_set = Db::name("admin")->where("store_id",$bool)->find();
                   if(!$is_set){
                     //先判断该店铺是否已经添加过admin表
                     //插入到后台
                     $array =[
                         "account"=>$user_data['phone_number'], //手机号
                         "passwd"=>$user_data['password'],//登录密码
                         "sex"=>1,
                         "stime"=>date("Y-m-d H:i:s"),
                         "role_id"=>8,//普通访客
                         "phone"=>$user_data['phone_number'],
                         "status"=>0,//0可以登录后台，1被禁用
                         "name"=>$user_data['phone_number'],
                         "store_id"=>$bool,
                         "is_own"=>1, //1为商家，0为商家下面的员工或者admin
                     ];
                     Db::name("admin")->insertGetId($array);
                 } else {
                    return ajax_error("网络错误，请重新提交");
                 }
                
                $mobile = $user_data['phone_number'];
                $content = "【智慧茶仓】尊敬的用户您好！您的店铺申请成功，请及时登陆网站，选择套餐，完成店铺入驻";
                $output = sendMessage($content,$mobile);
                return ajax_success("您的资料已提交,请耐心等待审核");
            }else{
                return ajax_error("网络错误，请重新提交");
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺信息
     **************************************
     */
    public function  store_return(Request $request){
        if($request->isPost()){
            $id =$request->only(["id"])["id"];
            $data =Db::name("store")->where("id",$id)->find();
            if(!empty($data)){
                return ajax_success("店铺数据返回成功",$data);
            }else{
                return ajax_error("没有这个店铺信息");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺重新编辑
     **************************************
     */
    public function store_edit(Request $request){
        if($request->isPost()){
            $user_id =Session::get("user");
            $id =$request->only(["id"])["id"];
            $is_business =$request->only(["is_business"])["is_business"];
            $id_card =$request->only(["id_card"])["id_card"];
            $contact_name =$request->only(["contact_name"])["contact_name"];
            $address_data =$request->only(["address_data"])["address_data"];
            $address_real_data =$request->only(["address_real_data"])["address_real_data"];
            $store_introduction =$request->only(["store_introduction"])["store_introduction"];
            $business_name =$request->only(["business_name"])["business_name"];
            $licence_no =$request->only(["licence_no"])["licence_no"];
            $card_positive_images = null;
            $card_side_file = null;
            if(empty($id_card) || empty($contact_name) || empty($address_data) ||empty($address_real_data) ||empty($store_introduction) ){
                return ajax_error("请注意填写完所有资料");
            }
            if($is_business ==2){
                if(empty($business_name) || empty($licence_no)){
                    return ajax_error("请填写企业信息");
                }
            }
            $card_positive =$request->only(["card_positive"])["card_positive"]; //身份证正面
            $card_side = $request->only(["card_side"])["card_side"];//身份证反面
            if(!empty($card_positive_file)){
                $card_positive_images = base64_upload_flie($card_positive);//身份证正面
                $ole_positive_url =Db::name("store")->where("id",$id)->value("card_positive");
            }
            if(!empty( $card_side)){
                $card_side_file =base64_upload_flie( $card_side) ; //身份证反面
                $ole_side_url =Db::name("store")->where("id",$id)->value("card_side");
            }
            //修改图片需要把之前的图片删除
            if(!empty($card_positive_file)&& !empty($card_side_file)){
                $data = [
                    "is_business"=>$is_business,
                    "id_card"=>$id_card,
                    "contact_name"=>$contact_name,
                    "address_data"=>$address_data,
                    "address_real_data"=>$address_real_data,
                    "card_positive"=>$card_positive_images,
                    "card_side"=>$card_side_file,
                    "store_introduction"=>$store_introduction,
                    "business_name"=>$business_name,
                    "licence_no"=>$licence_no,
                    //店铺状态(1审核通过,-1审核不通过,2审核中）
                    "status"=>2
                ];
            }else if(!empty($card_positive_file) && empty($card_side_file) ){
                $data = [
                    "is_business"=>$is_business,
                    "id_card"=>$id_card,
                    "contact_name"=>$contact_name,
                    "address_data"=>$address_data,
                    "address_real_data"=>$address_real_data,
                    "card_positive"=>$card_positive_images,
                    "store_introduction"=>$store_introduction,
                    "business_name"=>$business_name,
                    "licence_no"=>$licence_no,
                    //店铺状态(1审核通过,-1审核不通过,2审核中）
                    "status"=>2
                ];
            }else if(empty($card_positive_file) && !empty($card_side_file) ){
                $data = [
                    "is_business"=>$is_business,
                    "id_card"=>$id_card,
                    "contact_name"=>$contact_name,
                    "address_data"=>$address_data,
                    "address_real_data"=>$address_real_data,
                    "card_side"=>$card_side_file,
                    "store_introduction"=>$store_introduction,
                    "business_name"=>$business_name,
                    "licence_no"=>$licence_no,
                    //店铺状态(1审核通过,-1审核不通过,2审核中）
                    "status"=>2
                ];
            }else {
                $data = [
                    "is_business"=>$is_business,
                    "id_card"=>$id_card,
                    "contact_name"=>$contact_name,
                    "address_data"=>$address_data,
                    "address_real_data"=>$address_real_data,
                    "store_introduction"=>$store_introduction,
                    "business_name"=>$business_name,
                    "licence_no"=>$licence_no,
                    //店铺状态(1审核通过,-1审核不通过,2审核中）
                    "status"=>2
                ];
            }
            $bool = Db::name("store")->where("id",$id)->where("user_id",$user_id)->update($data);
            if($bool){
                //删除图片
                if($card_positive_images != null){
                    unlink(ROOT_PATH . 'public' . DS . 'uploads/'.$ole_positive_url);
                }
                if($card_side_file != null){
                    unlink(ROOT_PATH . 'public' . DS . 'uploads/'.$ole_side_url);
                }
                return ajax_success("您的资料已提交,请耐心等待审核");
            }else{
                return ajax_error("网络错误，请重新提交");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:所有的店铺信息返回
     **************************************
     */
    public function store_all_data(Request $request){
        if($request->isPost()){
            $user_id =Session::get("user");
            $data =Db::name("store")
                ->where("status","NEQ",3)
                ->where("user_id",$user_id)
                ->field('id,store_name,store_number,user_id,status,business_name,store_introduction')
                ->select();
            foreach($data as $key => $value){
              $rest[$key] = Db::name("set_meal_order")
              ->where("store_id","EQ",$data[$key]['id'])
              ->where("pay_status",'EQ',1)
              ->find();
              if($rest[$key]){
                $time = time();
                $data[$key]['goods_name'] = $rest[$key]['goods_name'];
                $data[$key]['time'] = round(($rest[$key]["end_time"]-$time)/86400);

                if($data[$key]['time'] < 0){
                    $data[$key]['time'] = 0;
                }
                
                if($data[$key]['time'] == 0){
                    $role_id = 8;
                    $boole = Db::name('admin')->where("store_id",$data[$key]['id'])->update(['role_id'=>$role_id]);
                }
                $data[$key]['meal_id'] = $rest[$key]['id'];
              if($rest[$key]['enter_all_id'] <= 6)
                    $data[$key]['version'] = 1;
              } 
              if( ($rest[$key]['enter_all_id'] > 6) && ($rest[$key]['enter_all_id'] <= 17 )){
                    $data[$key]['version'] = 2;
              } 
              if($rest[$key]['enter_all_id'] > 17){
                   $data[$key]['version'] = 3;
              }
            }
            
            if(!empty($data)){
                return ajax_success("所有店铺信息返回成功",$data);
            }else{
                return ajax_error("没有店铺信息");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺放弃
     **************************************
     */
    public function store_give_up(Request $request){
        if($request->isPost()){
            $id =$request->only(["id"])["id"];
            $bool =Db::name("store")->where("id",$id)->update(["status"=>3]);
            if($bool){
                return ajax_success("放弃成功");
            }else{
                return ajax_error("请重启请求");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:pc端店铺指向后台页面
     **************************************
     */
    public function store_goto_admin(Request $request){
        if($request->isPost()){
            $id =$request->only(["id"])['id'];//店铺的id
            $status = Db::name("store")->where("id",$id)->find();
            if( empty($status['share_store_id']) && empty($status['highe_share_code'])){
                $restul = Db::name("pc_user")->where("id",$status['user_id'])->find();
                if(!empty($restul["invite_id"]) && !empty($restul["invitation"])){
                    $bool = Db::name("store")->where("id",$id)->update(['share_store_id'=>$restul['invite_id'],'highe_share_code'=>$restul['invitation']]);
                }
            }
            if($status['status']==-1){
                return ajax_error("店铺审核不通过，不能进入后台");
            }elseif ($status['status']==2){
                return ajax_error("店铺审核中，不能进入后台");
            }elseif ($status['status']==3){
                return ajax_error("店铺已放弃，不能进入后台");
            }else{
                //后台使用
                $userInfo = db("admin")
                    ->where("store_id",$id)
                    ->where("status","<>",1)
                    ->select();
                if($userInfo){
                    Session("user_id", $userInfo[0]["id"]);
                    Session("user_info", $userInfo);
                }
                //进行记录是哪个店铺
                Session("store_id", $id);
             return ajax_success("成功匹配,可以跳转后台");
            }
        }
    }


    /**
     **************GY*******************
     * @param Request $request
     * Notes:店铺logo
     **************************************
     */
    public function  store_logo_index(Request $request){
        if($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $data = Db::name("store")->where("id",$store_id)->value('store_logo');
            if(!empty($data)){
                return ajax_success("店铺数据返回成功",$data);
            }else{
                return ajax_error("这个店铺没logo");
            }
        }
    }



}
