<?php
/**
 * Created by Vscode
 * User: Administrator
 * Date: 2019/4/11 0021
 * Time: 14:35
 */
namespace  app\index\controller;
use think\Controller;
use think\Request;
use think\Db;

class Receipt extends Controller
{

    /**
     * [发票添加企业新户名]
     * 郭杨
     */
    public function bill(Request $request){
        if($request->isPost()){
            $time = time();
            $data = $request->param();
            $data["create_time"] = $time;
            $data["label"] = 1;
            $member_id = $data["member_id"];
            if(!empty($data)){
                $where = "update tb_member_receipt set label = 0 where type = 1 and member_id = $member_id";
                $rest = Db::query($where);
                $bool = db("member_receipt")->insert($data);
                if($bool){
                    return ajax_success('发送成功');
                } else {
                    return ajax_error("发送失败");
                }
            } else {
                return ajax_error("发送失败");
            }
        }

    }



    /**
     * [所有发票状态]
     * 郭杨
     */
    public function receipt_status(Request $request){
        if($request->isPost()){
            $status = db("receipt")->where("id",1)->field("status")->find();
            if(!empty($status)){
                return ajax_success('发送成功',$status);
            } else {
                return ajax_error("发送失败");
            }
        }

    }

    /**
     * [发票添加个人新户名]
     * 郭杨
     */
    public function people(Request $request){
        if($request->isPost()){
            $time = time();
            $data = $request->param();
            $member_id = $data["member_id"];
            $data["create_time"] = $time;
            $data["label"] = 1;

            if(!empty($data)){
                $where = "update tb_member_receipt set label = 0 where type = 2 and member_id = $member_id ";
                $rest = Db::query($where);
                $bool = db("member_receipt")->insert($data);
                if($bool){
                    return ajax_success('发送成功');
                } else {
                    return ajax_error("发送失败");
                }
            } else {
                return ajax_error("发送失败");
            }
        }
    }

    /**
     * [企业户名列表]
     * 郭杨
     */
    public function corporation(Request $request){
        if($request->isPost()){ 
            $member_id = $request->only(["member_id"])["member_id"];  
            $data = db("member_receipt")->where("type",1)->where("member_id",$member_id)->field("id,member_id,type,company,company_number,status,label")->select();       
            if(!empty($data)){ 
                return ajax_success('发送成功',$data);
            } else {
                return ajax_error("发送失败");
            }
        }

    }


    /**
     * [个人户名列表]
     * 郭杨
     */
    public function individual(Request $request){
        if($request->isPost()){  
            $member_id = $request->only(["member_id"])["member_id"]; 
            $data = db("member_receipt")->where("type",2)->where("member_id",$member_id)->field("id,member_id,type,name,user_phone,email,label")->select();       
            if(!empty($data)){ 
                return ajax_success('发送成功',$data);
            } else {
                return ajax_error("发送失败");
            }
        }

    }

    /**
     * [默认个人户名]
     * 郭杨
     */
    public function approve_individual(Request $request){
        if($request->isPost()){  
            $member_id = $request->only(["member_id"])["member_id"]; 
            $data = db("member_receipt")->where("type",2)->where("member_id",$member_id)->where("label",1)->field("id,member_id,type,name,user_phone,email,label")->select();       
            if(!empty($data)){ 
                return ajax_success('发送成功',$data);
            } else {
                return ajax_error("发送失败");
            }
        }

    }


    /**
     * [默认公司户名]
     * 郭杨
     */
    public function approve_corporation(Request $request){
        if($request->isPost()){  
            $member_id = $request->only(["member_id"])["member_id"]; 
            $data = db("member_receipt")->where("type",1)->where("member_id",$member_id)->where("label",1)->field("id,member_id,type,company,company_number,status,label")->select();       
            if(!empty($data)){ 
                return ajax_success('发送成功',$data);
            } else {
                return ajax_error("发送失败");
            }
        }

    }
}