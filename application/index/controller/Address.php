<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/26 0026
 * Time: 14:23
 */
namespace  app\index\controller;

use think\Controller;
use think\Db;
use think\Session;
use  think\Request;
class Address extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:所有地址列表数据返回
     **************************************
     */
    public function member_address_information(Request $request){
        if($request->isPost()){
            $post_open_id = $request->only(['open_id'])['open_id'];
            $user_id =Db::name("member")
                ->field("member_id")
                ->where("member_openid", $post_open_id)
                ->find();
            $member_id =$user_id["member_id"];
            $data =Db::name("user_address")
                ->where('user_id',$member_id)
                ->order("id","desc")
                ->select();
            if(!empty($data)){
                return ajax_success('地址列表信息',$data);
            }else{
                return ajax_error('没有填写地址记录',['status'=>0]);
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:收货地址添加
     **************************************
     * @param Request $request
     */
    public function member_address_adds(Request $request){
        if($request->isPost()){
            $post_open_id = $request->only(['open_id'])['open_id'];
            $user_id =Db::name("member")
                ->field("member_id")
                ->where("member_openid", $post_open_id)
                ->find();
            $member_id =$user_id["member_id"];
            $harvester = $request->only('harvester')['harvester'];
            $harvester_phone_num = $request->only('harvester_phone_num')['harvester_phone_num'];
            $address_name = $request->only('address_name')['address_name'];
            $address =explode(",",$address_name);
            $status = $request->only('status')['status'];
            $harvester_real_address =$request->only('harvester_real_address')['harvester_real_address'];
            $data =[
                "user_id"=> $member_id ,
                "harvester"=>$harvester,
                "harvester_phone_num"=>$harvester_phone_num,
                "address_name"=> $address,
                "status"=> $status,
                "harvester_real_address"=>$harvester_real_address
            ];
            $bool_id =Db::name("user_address")->insertGetId($data);
            if($bool_id){
                if($status==1){
                    Db::name('user_address')
                        ->where('user_id',$member_id)
                        ->where('id','NEQ',$bool_id)
                        ->update(['status'=>-1]);
                }
                return ajax_success("添加成功",$bool_id);
            }else{
                return ajax_error("添加失败",['status'=>0]);
            }


        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:收货地址删除
     **************************************
     * @param Request $request
     */
    public function member_address_del(Request $request){
        if($request->isPost()){
            $post_open_id = $request->only(['open_id'])['open_id'];
            $user_id =Db::name("member")
                ->field("member_id")
                ->where("member_openid", $post_open_id)
                ->find();
            $member_id =$user_id["member_id"];
            $id =$request->only('id')['id'];
            if($id){
                $bool =Db::name('user_address')
                    ->where("user_id",$member_id)
                    ->where("id",":id")
                    ->bind(["id"=>[$id,\PDO::PARAM_INT]])
                    ->delete();
                if($bool){
                    return ajax_success('删除成功',['status'=>1]);
                }else{
                    return ajax_error('删除失败',['status'=>0]);
                }
            }else{
                return ajax_error('这条地址信息不正确',['status']);
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:编辑地址数据返回
     **************************************
     * @param Request $request
     */
    public function member_address_edit_information(Request $request){
        if($request->isPost()){
            $id = $request->only(['id'])['id'];
            $data =Db::name("user_address")->where('id',$id)->find();
            if(!empty($data)){
                return ajax_success('地址信息返回成功',$data);
            }else{
                return ajax_error('地址信息返回失败',['status'=>0]);
            }
        }
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:收货地址编辑操作
     **************************************
     * @param Request $request
     */
    public function member_address_edit(Request $request){
        if($request->isPost()){
            $post_open_id = $request->only(['open_id'])['open_id'];
            $user_id =Db::name("member")
                ->field("member_id")
                ->where("member_openid", $post_open_id)
                ->find();
            $member_id =$user_id["member_id"];
            $id = $request->only('id')['id'];
            $harvester = $request->only('harvester')['harvester'];
            $harvester_phone_num = $request->only('harvester_phone_num')['harvester_phone_num'];
            $address_name = $request->only('address_name')['address_name'];
            $status = $request->only('status')['status'];
            $harvester_real_address =$request->only('harvester_real_address')['harvester_real_address'];
            $data =[
                "harvester"=>$harvester,
                "harvester_phone_num"=>$harvester_phone_num,
                "address_name"=>$address_name,
                "status"=> $status,
                "harvester_real_address"=>$harvester_real_address
            ];
            $bool_id =Db::name("user_address")
                ->where('id',$id)
                ->update($data);
            if($bool_id){
                if($status==1){
                    Db::name('user_address')
                        ->where('user_id',$member_id)
                        ->where('id','NEQ',$id)
                        ->update(['status'=>-1]);
                }
                return ajax_success("编辑成功",$bool_id);
            }else{
                return ajax_error("编辑失败",['status'=>0]);
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:设置默认地址
     **************************************
     * @param Request $request
     */
    public function member_address_status(Request $request){
        if($request->isPost()){
            $post_open_id = $request->only(['open_id'])['open_id'];
            $user_id =Db::name("member")
                ->field("member_id")
                ->where("member_openid", $post_open_id)
                ->find();
            $member_id =$user_id["member_id"];
            $id =$request->only('id')['id'];
            if(!empty($id)){
                $bool=  Db::name('user_address')
                    ->where("user_id",$member_id )
                    ->where("id","EQ",$id)
                    ->update(['status'=>1]);
                if($bool){
                    Db::name('user_address')
                        ->where("user_id",$member_id )
                        ->where("id","NEQ",$id)
                        ->update(['status'=>-1]);
                    return ajax_success("设置成功",['status'=>1]);
                }else{
                    return ajax_error('设置失败',['status'=>0]);
                }

            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:购买页面默认地址返回或者选择其他地址
     **************************************
     */
    public function member_default_address_return(Request $request)
    {
        if ($request->isPost()) {
            $post_open_id = $request->only(['open_id'])['open_id'];
            $user_id =Db::name("member")
                ->field("member_id")
                ->where("member_openid", $post_open_id)
                ->find();
            $member_id =$user_id["member_id"];
            if(empty( $member_id)){
                exit(json_encode(array("status"=>2,"info"=>"请登录")));
            }
            $address_id =Session::get("address_id");
            if(!empty($address_id)){
                $is_address =Db::name("user_address")
                    ->where("user_id", $member_id)
                    ->where("id",$address_id)
                    ->find();
                if(!empty($is_address)){
                    Session::delete("address_id");
                    return ajax_success('收货地址成功返回', $is_address);
                }else{
                    Session::delete("address_id");
                    exit(json_encode(array("status"=>0,"info"=>"请先选择收货地址")));
                }
            }
            $is_address_status = Db::name('user_address')
                ->where('user_id',  $member_id)
                ->order("id","desc")
                ->find();
            if (!empty($is_address_status)) {
                $is_address = Db::name('user_address')
                    ->where('user_id',  $member_id)
                    ->where("status", 1)
                    ->find();
                if(!empty($is_address)){
                    return ajax_success('收货地址成功返回', $is_address);
                }else{
                    return ajax_success('收货地址成功返回', $is_address_status);
                }
            } else {
                exit(json_encode(array("status"=>0,"info"=>"请先设置收货地址")));
            }
        }
    }


}