<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/12 0012
 * Time: 15:41
 */
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;

class Delivery extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:配送设置
     **************************************
     */
    public function delivery_index(){
        $data =Db::name("extract_address")->paginate(20);
        return view("delivery_index",["data"=>$data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:上门自提添加(
     **************************************
     */
    public function delivery_add(Request $request){
        if($request->isPost()){
            $data =$_POST;
            if(empty($data["extract_name"])){
              $this->error("请填写自提点名称");
            }
            if(empty($data["extract_address"][0])){
                $this->error("请填写城市");
            }
            if(empty($data["extract_real_address"])){
                $this->error("请填写详细地址");
            }
            if(empty($data["phone_num"])){
                $this->error("请填写手机号");
            }
            $extract_address =implode(",",$data["extract_address"]);
            $datas =[
                "extract_name"=>$data["extract_name"],
                "extract_address"=>$extract_address,
                "extract_real_address" =>$data["extract_real_address"],
                "phone_num"=>$data["phone_num"]
            ];
            $res =Db::name("extract_address")->insert($datas);
            if($res){
                $this->success("添加成功",'admin/Delivery/delivery_index');
            }else{
                $this->error("失败,请重试");
            }
        }
        return view("delivery_add");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:上门自提编辑(
     **************************************
     */
    public function delivery_edit($id){
        if(empty($id)){
            $this->error("参数不正确");
        }
        $data =Db::name("extract_address")->where("id",$id)->find();
        $string =explode(",",$data["extract_address"]);
        $num =count($string);
        if($num ==2){
            $string[3] =" ";
        }
        return view("delivery_edit",["data"=>$data,"string"=>$string]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:快递发货
     **************************************
     */
    public function delivery_goods(){
        return view("delivery_goods");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:快递发货添加按重量
     **************************************
     */
    public function delivery_goods_add_weight(){
        return view("delivery_goods_add_weight");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:快递发货添加按件
     **************************************
     */
    public function delivery_goods_add_number(){
        return view("delivery_goods_add_number");
    }



}