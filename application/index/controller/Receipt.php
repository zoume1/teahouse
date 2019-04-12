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
            if(!empty($data)){
                $bool = db("member_receipt")->insert($data);
                return ajax_success('发送成功');
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
            $data["create_time"] = $time;
            if(!empty($data)){
                $bool = db("member_receipt")->insert($data);
                return ajax_success('发送成功');
            } else {
                return ajax_error("发送失败");
            }
        }

    }

}