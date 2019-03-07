<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/7 0007
 * Time: 15:03
 */
namespace  app\index\controller;

use think\Controller;
use think\Request;
use think\Db;

class  Api extends  Controller{
    /**
     **************李火生*******************
     * 快递100接口
     **************************************
     */
    public function express_hundred(Request $request)
    {
        if ($request->isPost()) {
            $order_id =$request->only(['by_order_id'])["by_order_id"];
            if(!empty($order_id)) {
                $express =Db::name('order')
                    ->field('courier_number,express_name')
                    ->where('id',$order_id)
                    ->find();
                if(!empty($express)){
                    $express_type =$express['express_name'];
                    $express_num =$express['courier_number'];
                    if(!empty($express_num)) {
                        $codes =$express_num;
                        //参数设置
                        $post_data = array();
                        $post_data["customer"] = config("express_hundred.customer");
                        $key = config("express_hundred.key");
                        $post_data["param"] = '{"com":"'.$express_type.'","num":"' . $codes . '"}';
                        $url = 'http://poll.kuaidi100.com/poll/query.do';
                        $post_data["sign"] = md5($post_data["param"] . $key . $post_data["customer"]);
                        $post_data["sign"] = strtoupper($post_data["sign"]);
                        $o = "";
                        foreach ($post_data as $k => $v) {
                            $o .= "$k=" . urlencode($v) . "&";        //默认UTF-8编码格式
                        }
                        $post_data = substr($o, 0, -1);
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                        $result = curl_exec($ch);
                        $data = str_replace("\"", '"', $result);
                        if(!empty($data)){
                            return ajax_success("物流数据返回成功",$data);
                        }else{
                            return ajax_error("暂无物流信息");
                        }
//                        $data = json_decode($data,true);
                    }
                }


            }
        }
    }

}