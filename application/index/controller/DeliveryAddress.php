<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/13 0013
 * Time: 10:41
 */
namespace app\index\controller;


use think\Controller;
use think\Request;
use think\Db;

class DeliveryAddress extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:下单页面点击到店自提数据返回
     **************************************
     */
    public function delivery_address_return(Request $request){
        if($request->isPost()){
            $address_id =$request->only(['id'])['id'];
            if(!empty($address_id)){
                $is_address =Db::name("extract_address")
                    ->where("id",$address_id)
                    ->where("status",1)
                    ->find();
                if(!empty($is_address)){
                    return ajax_success('自提地址成功返回', $is_address);
                }else{
                    exit(json_encode(array("status"=>0,"info"=>"暂无到店自提地址")));
                }
            }else{
                $is_address =Db::name("extract_address")
                    ->where("status",1)
                    ->find();
                if(!empty($is_address)){
                    return ajax_success('自提地址成功返回', $is_address);
                }else{
                    exit(json_encode(array("status"=>0,"info"=>"暂无到店自提地址")));
                }
            }

        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:下单页面到店自提所有数据返回
     **************************************
     */
    public function delivery_address_all_return(Request $request){
        if($request->isPost()){
            $data =Db::name("extract_address")->where("status",1)->select();
            if(!empty($data)){
                return ajax_success('自提地址成功返回', $data);
            }else{
                exit(json_encode(array("status"=>0,"info"=>"暂无到店自提地址")));
            }
        }
    }


}