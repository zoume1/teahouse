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
            if(isset($data["member_id"]) && isset($data["type"]) && isset($data["company"]) && isset($data["company_number"]) && isset($data["status"])){
                $data["create_time"] = $time;
                $data["label"] = 1;
                $member_id = $data["member_id"];
                
                if(!empty($data)){
                    $where = "update tb_member_receipt set label = 0 where type = 1 and member_id = $member_id";
                    $rest = Db::query($where);
                    $bool = db("member_receipt")->insertGetId($data);
                    if($bool){
                        return ajax_success('发送成功',['receipt_id'=>$bool]);
                    } else {
                        return ajax_error("发送失败");
                    }
                } else {
                    return ajax_error("发送失败");
                } 
            } else {
                return ajax_error("请检查参数是否正确");
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
            if(isset($data["member_id"]) && isset($data["type"]) && isset($data["company"]) && isset($data["company_number"])){
                $member_id = $data["member_id"];
                $data["create_time"] = $time;
                $data["label"] = 1;
                $data["status"] = 2;

                if(!empty($data)){
                    $where = "update tb_member_receipt set label = 0 where type = 2 and member_id = $member_id ";
                    $rest = Db::query($where);
                    $bool = db("member_receipt")->insertGetId($data);
                    if($bool){
                        return ajax_success('发送成功',['receipt_id'=>$bool]);
                    } else {
                        return ajax_error("发送失败");
                    }
                } else {
                    return ajax_error("发送失败");
                }
            } else {
                return ajax_error("请检查参数是否正确");
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
            $data = db("member_receipt")->where("type",2)->where("member_id",$member_id)->field("id,member_id,type,company,company_number,label")->select();       
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
            $data = db("member_receipt")->where("type",2)->where("member_id",$member_id)->where("label",1)->field("id,member_id,type,company,company_number,label")->select();       
            if(!empty($data)){ 
                return ajax_success('发送成功',$data);
            } else {
                return ajax_error("没有默认户名,请前往设置");
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
                return ajax_error("没有默认户名,请前往设置");
            }
        }

    }


    /**
     * [设为默认]
     * 郭杨
     */
    public function set_default(Request $request){
        if($request->isPost()){  
            $member_id = $request->only(["member_id"])["member_id"]; 
            $type = $request->only(["type"])["type"];
            $id =  $request->only(["id"])["id"];
            $where = "update tb_member_receipt set label = 0 where type = $type and member_id = $member_id ";
            $rest = Db::query($where);
            $data = db("member_receipt")->where("id",$id)->where("member_id",$member_id)->update(["label"=>1]);       
            if($data){ 
                return ajax_success('设置成功');
            } else {
                return ajax_error("设置失败");
            }
        }
    }


    
    /**
     * [删除户名]
     * 郭杨
     */
    public function bill_delete(Request $request){
        if($request->isPost()){  
            $member_id = $request->only(["member_id"])["member_id"]; 
            $id =  $request->only(["id"])["id"];
            $data = db("member_receipt")->where("id",$id)->where("member_id",$member_id)->delete();       
            if($data){ 
                return ajax_success('删除成功');
            } else {
                return ajax_error("删除失败");
            }
        }
    }


    /**
     * [查询发票费率]
     * 郭杨
     */
    public function proportion(Request $request){
        if($request->isPost()){  
            $receipt_id =  $request->only(["receipt_id"])["receipt_id"];
            $receipt_type = db("member_receipt")->where('id',$receipt_id)->value("status");
            $store_id = $request->only(['uniacid'])['uniacid'];
            if(!empty($receipt_id)){
                if($receipt_type == 1 ){  //普通发票
                    $proportion = db("receipt")->where("store_id",$store_id)->value('common');  
                } else {  //增值税发票
                    $proportion = db("receipt")->where("store_id",$store_id)->value('senior');
                }
                return ajax_success('发送成功',$proportion);
            } else {
                return ajax_error("参数错误");
            }
        }
    }
}