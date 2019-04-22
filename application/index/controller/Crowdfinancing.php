<?php

/**
 * Created by Vscode.
 * User: admin
 * Date: 2019/4/14
 * Time: 15:21
 */
namespace app\index\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;


class Crowdfinancing extends Controller
{
    /**
     * [立即购买过去清单数据返回]
     * 郭杨
     */
    public function crowd_order_return(Request $request)
    {
        if($request->isPost()){
            $member_id = $request->only('member_id')['member_id'];
            $member_grade_id = db('member')->where('member_id',$member_id)->value('member_grade_id');
            $member_consumption_discount =Db::name('member_grade')  //会员折扣
            ->where('member_grade_id',$member_grade_id)
            ->find();
            $goods_id = $request->only('goods_id')['goods_id'];
            $number = $request->only('num')['num'];
            
            if(empty($goods_id)){
                return ajax_error('商品信息有误,请返回重新提交',['status'=>0]);
            }
            foreach($goods_id as $key => $value){
                $goods_data = Db::name("crowd_goods")->where("id", $goods_id[$key])->find();
                $info = Db::name("crowd_special")
                ->where("goods_id", $goods_id[$key])
                ->find();
                $data[$key]["goods_info"] = $goods_data;
                $data[$key]["special_info"] = $info;
                $data[$key]["grade_price"] = $member_consumption_discount["member_consumption_discount"] * $info["price"];
                $data[$key]["unit"] = $info['offer'];
                $data[$key]["number"] = $number[$key];
                $data[$key]["user_grade_image"] =$member_consumption_discount["member_grade_img"];
            }
            if(!empty($data)){
                return ajax_success("数据返回",$data);
            } else {
                return ajax_error("没有数据",["status"=>0]);
            }
        }
    }

}