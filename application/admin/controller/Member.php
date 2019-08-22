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
            ->where("tb_dealer_user.show_status",'=',1) 
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
                $mboole = db("dealer_user")->where("user_id",$data['member_id'])->update(['show_status'=>1]);
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
            return view('member_add');
        }
    }


    /**
     * [分销成员编辑页面]
     * GY
     */
    public function member_update(Request $request){
        if ($request->isPost()){
            $store_id = Session::get('store_id');
            $data = $request->param();
            $rest["grade"] = implode(",",$data["grade"]);
            $rest["scale"] = implode(",",$data["scale"]);
            $rest["integral"] = implode(",",$data["integral"]);
            $rest["award"] = implode(",",$data["award"]);
            $rest["rank"] = implode(",",$data["rank"]);
            $rest["status"] = $data["status"];
      
            $bool = db("leaguer")->where('id','=',$data['id'])->update($rest);
            $boole = db("dealer_user")->where('user_id','=',$data['member_id'])->update(['status'=>$data['status']]);

            if ($bool) {
                $this->success("更新成功", url("admin/Member/member_index"));
            } else {
                $this->error("更新失败", url("admin/Member/member_index"));
            }
        }
    }

    /**
     * [分销新成员开关]
     * 郭杨
     */
    public function member_status(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("dealer_user")->where("user_id", $id)->update(["status" => 0]);
                $rest = db("leaguer")->where("member_id",$id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Member/member_index"));
                } else {
                    $this->error("修改失败", url("admin/Member/member_index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("dealer_user")->where("user_id", $id)->update(["status" => 1]);
                $rest = db("leaguer")->where("member_id",$id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Member/member_index"));
                } else {
                    $this->error("修改失败", url("admin/Member/member_index"));
                }
            }
        }
    }


}