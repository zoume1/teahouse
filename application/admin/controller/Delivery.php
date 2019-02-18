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
        $data_status =Db::name("extract_address")->find();
        return view("delivery_index",["data"=>$data,"data_status"=>$data_status]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:买家上门自提功能开启关闭
     **************************************
     * @param Request $request
     */
    public function delivery_status(Request $request){
        if($request->isPost()){
            $status =$request->only(["status"])["status"];//1为开启，-1为关闭
            $data =Db::name("extract_address")->select();
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $bool =Db::name("extract_address")
                        ->where("id",$value["id"])
                        ->update(["status"=>$status]);
                }
                if($bool){
                    $this->success("更新成功",url("admin/Delivery/delivery_index"));
                }else{
                    $this->success("没有修改任何东西",url("admin/Delivery/delivery_index"));
                }
            }
        }
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
                "phone_num"=>$data["phone_num"],
                "status"=>1
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
        $delivery_data =Db::name("extract_address")->where("id",$id)->find();
        $string =explode(",",$delivery_data["extract_address"]);
        $num =count($string);
        if($num ==2){
            $string[3] =" ";
        }
        if($this->request->isPost()) {
            $data = $_POST;
            if (empty($data["extract_name"])) {
                $this->error("请填写自提点名称");
            }
            if (empty($data["extract_address"][0])) {
                $this->error("请填写城市");
            }
            if (empty($data["extract_real_address"])) {
                $this->error("请填写详细地址");
            }
            if (empty($data["phone_num"])) {
                $this->error("请填写手机号");
            }
            $extract_address = implode(",", $data["extract_address"]);
            $datas = [
                "extract_name" => $data["extract_name"],
                "extract_address" => $extract_address,
                "extract_real_address" => $data["extract_real_address"],
                "phone_num" => $data["phone_num"]
            ];
            $res = Db::name("extract_address")->where("id", $id)->update($datas);
            if ($res) {
                $this->success("修改成功", 'admin/Delivery/delivery_index');
            } else {
                $this->error("没有修改任何东西,请重试");
            }
        }
        return view("delivery_edit",["data"=>$delivery_data,"string"=>$string]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:上门自提删除
     **************************************
     * @param $id
     */
    public function del($id){
        $bool = db("extract_address")->where("id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/Delivery/delivery_index"));
        } else {
            $this->error("删除失败", url("admin/Delivery/delivery_index"));
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:上门自提批量删除
     **************************************
     * @param $id
     */
    public function dels($id){
        $bool = db("extract_address")->where("id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/Delivery/delivery_index"));
        } else {
            $this->error("删除失败", url("admin/Delivery/delivery_index"));
        }
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