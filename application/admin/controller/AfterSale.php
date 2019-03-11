<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/6 0006
 * 售后
 * Time: 17:01
 */
namespace  app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;

class  AfterSale extends  Controller{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后官方回复
     **************************************
     */
    public function business_replay(Request $request){
        if($request->isPost()){
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
            $content= $request->only(["content"])["content"]; //回复的内容
            $is_who =1;//谁回复（1卖家，2买家）
            $data =[
                "content" =>$content,
                "after_sale_id"=>$after_sale_id,
                "is_who"=>$is_who,
                "create_time" =>time()
            ];
            $id =Db::name("after_reply")->insertGetId($data);
            if($id >0){
                return ajax_success("回复成功",$data);
            }else{
                return ajax_error("回复失败");
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后页面数据返回
     **************************************
     */
    public function  business_after_sale_information(Request $request){
        if($request->isPost()){
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
            $data =Db::name("after_sale")->where("id",$after_sale_id)->find();
            $data["images"] =Db::name("after_image")->where("after_sale_id",$after_sale_id)->select();
            $data["reply"] =Db::name("after_reply")->where("after_sale_id",$after_sale_id)->select();
            if(!empty($data)){
                return ajax_success("售后信息返回成功",$data);
            }else{
                return ajax_error("暂无售后信息");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后状态修改
     **************************************
     */
    public function after_sale_status(Request $request){
        if($request->isPost()){
            $status =$request->only(["status"])["status"];
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
            $bool =Db::name("after_sale")
                ->where("id",$after_sale_id)
                ->update(["status"=>$status,"handle_time"=>time()]);
            if($bool){
                return ajax_success("更改成功");
            }else{
                return ajax_error("更改失败");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后状态修改带快递信息
     **************************************
     */
    public function after_sale_express_add(Request $request){
        if($request->isPost()){
            $status =$request->only(["status"])["status"];
            $sell_express_company =$request->only(["sell_express_company"])["sell_express_company"];
            $sell_express_number =$request->only(["sell_express_number"])["sell_express_number"];
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
            $data =[
                "status"=>$status,
                "sell_express_company"=>$sell_express_company,
                "sell_express_number"=>$sell_express_number
            ];
            $bool =Db::name("after_sale")->where("id",$after_sale_id)->update($data);
            if($bool){
                return ajax_success("更改成功");
            }else{
                return ajax_error("更改失败");
            }
        }
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后状态修改带退钱操作
     **************************************
     */
    public function after_sale_money_add(Request $request){
        if($request->isPost()){
            $status =$request->only(["status"])["status"];
            $business_return_money =$request->only(["business_return_money"])["business_return_money"];
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
            $data =[
                "status"=>$status,
                "business_return_money"=>$business_return_money,
            ];
            $bool =Db::name("after_sale")->where("id",$after_sale_id)->update($data);
            if($bool){
                return ajax_success("更改成功");
            }else{
                return ajax_error("更改失败");
            }
        }
    }


}