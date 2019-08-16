<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/110026
 * Time: 17:23
 */
namespace  app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use think\Session;
use think\paginator\driver\Bootstrap;

class Member extends Controller{

    /**
     * [分销成员页面]
     * GY
     */
    public function member_index(){
        $store_id = Session::get('store_id');
        $member =Db::name('dealer_user')
            ->field("tb_dealer_user.*,tb_member.member_phone_num")
            ->join("tb_member","tb_dealer_user.referee_id = tb_member.member_id",'left')
            ->where("tb_dealer_user.wxapp_id",$store_id)
            ->where("tb_dealer_user.status",'=',1) 
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
      
        return view('member_index',["member"=>$member]);
    }


    /**
     * [分销成员添加]
     * GY
     */
    public function member_add(){
        return view('member_add');
    }

    /**
     * [分销成员保存入库]
     * GY
     */
    public function member_save(Request $request){
        if ($request->isPost()){
            $store_id = Session::get('store_id');
            $data = $request->param();
            $rest = db("member") 
             ->where("member_phone_num",$data["member_name"])
             ->where("store_id",$store_id)
             ->field("member_id,inviter_id,member_phone_num,leaguer_id")
             ->find();
            if($rest['leaguer_id'] > 0){
                $this->error("该用户已经是分销成员，请勿重复添加", url("admin/Member/member_index"));
            }
            if(!empty($rest)){
                $data["member_id"] = $rest["member_id"];
                $data["inviter_id"] = $rest["inviter_id"];
                $data["member_phone_num"] = $rest["member_phone_num"];
                $data["grade"] = implode(",",$data["grade"]);
                $data["scale"] = implode(",",$data["scale"]);
                $data["integral"] = implode(",",$data["integral"]);
                $data["award"] = implode(",",$data["award"]);
                $data["rank"] = implode(",",$data["rank"]);
                $data["store_id"] = $store_id;
                $member = db("leaguer")->insertGetId($data);
                $mbool = db("member")->where("member_id",$data['member_id'])->update(['leaguer_id'=>$member]);
                if ($member) {
                    $this->success("添加成功", url("admin/Member/member_index"));
                } else {
                    $this->error("添加失败", url("admin/Member/member_index"));
                }

            }else{
                $this->error("没有该用户,请仔细核对后添加", url("admin/Member/member_index"));
            }

            return view('member_add');
        }
    }

    /**
     * [分销成员编辑页面]
     * GY
     */
    public function member_edit($user_id){
        $data = db('leaguer')->where("member_id",'=',$user_id)->select();
        if(!empty($data)){
            $data[0]["grade"] = explode(",", $data[0]["grade"]);
            $data[0]["award"] = explode(",", $data[0]["award"]);
            $data[0]["scale"] = explode(",", $data[0]["scale"]);
            $data[0]["integral"] = explode(",", $data[0]["integral"]);
            return view('member_edit',['data'=>$data]);
        } else {
            return view('member_edit');
        }
    }


    /**
     * [分销成员编辑页面]
     * GY
     */
    public function member_update(){
        return view('member_edit');
    }



}