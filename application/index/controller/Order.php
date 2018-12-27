<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27 0027
 * Time: 15:20
 */

namespace  app\index\controller;

use think\Controller;
use think\Request;
use think\Db;

class  Order extends  Controller
{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:立即购买过去购物清单数据返回
     **************************************
     */
    public function order_return(Request $request)
    {
        if ($request->isPost()) {
            $goods_id = $request->only("goods_id")["goods_id"];
            $special_id = $request->only("guige")["guige"];
            $number = $request->only("num")["num"];
            if (empty($goods_id)) {
                return ajax_error("商品信息有误，请返回重新提交", ["status" => 0]);
            }
            $goods_data = Db::name("goods")->where("id", $goods_id)->find();
            //判断是为专用还是通用
            //专用规格
            if (!empty($special_id)) {
                if ($goods_data["goods_standard"] == 1) {
                    $data[0]["goods_info"] = $goods_data;
                    $data[0]["special_info"] = Db::name("special")
                        ->where("id", $special_id)
                        ->find();
                    $data[0]["number"] =$number;
                }
            } else {
                //通用规格
                if ($goods_data["goods_standard"] == 0) {
                    $data[0]["goods_info"] = $goods_data;
                    $data[0]["special_info"] = null;
                    $data[0]["number"] =$number;
                }

            }
            if(!empty($data)){
                return ajax_success("数据返回",$data);
            }else{
                return ajax_error("没有数据",["status"=>0]);
            }

        }
    }
}