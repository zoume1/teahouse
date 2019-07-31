<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;


class StoreHouse extends Controller{
    
    /**
     * [仓库管理]
     * 郭杨
     */    
    public function store_house(){
        $store_id = Session::get("store_id");
        $store = config("store_id");
        $store_data = db("store_house")->where("store_id","EQ",$store_id)->select();
        if(!empty($store_data)){
            foreach($store_data as $key => $value){
                $store_data[$key]["max"] = max(explode(',',$store_data[$key]['cost']));
                $store_data[$key]["min"] = min(explode(',',$store_data[$key]['cost']));
            }
        } else {
            $store_data = db("store_house")->where("store_id","EQ",$store)->select();
            foreach($store_data as $k => $value){
                unset($store_data[$k]['id']);
                $store_data[$k]['store_id'] = $store_id;
            }

            foreach($store_data as $ke => $val){
                $bool = db("store_house")->insert($val);
            }

            $store_data = db("store_house")->where("store_id","EQ",$store_id)->select();
        }
        $url = 'admin/StoreHouse/store_house';
        $pag_number = 20;
        $store = paging_data($store_data,$url,$pag_number);
        return view("store_house",["store"=>$store]);
    } 

    
    /**
     * [仓库管理添加]
     * 郭杨
     */    
    public function store_house_add(Request $request){
        if($request->isPost()){
            $data = $request->param();
            $store_id = Session::get("store_id");
            $data["type"] = isset($data["type"])?$data["type"]:0;
            $data["unit"] = implode(",",$data["unit"]);
            $data["cost"] = implode(",",$data["cost"]);
            $data["store_id"] = $store_id;

            if($data["label"] = 1){
                $where = "update tb_store_house set label = 0 where store_id = $store_id";
                $rest = Db::query($where);
            }

            $res =Db::name("store_house")->insert($data);           
            if($res){
                $this -> success("添加成功","admin/StoreHouse/store_house");
            } else {
                $this -> success("添加失败","admin/StoreHouse/store_house");
            }
        }
        return view("store_house_add");
    }

    /**
     * [仓库管理编辑]
     * 郭杨
     */    
    public function store_house_update(Request $request){
        if($request->isPost()){
            $store_id = Session::get("store_id");
            $data = $request -> param();
            $data["type"] = isset($data["type"])?$data["type"]:0;
            $data["unit"] = implode(",",$data["unit"]);
            $data["cost"] = implode(",",$data["cost"]);
            if($data["label"] = 1){
                $where = "update tb_store_house set label = 0 where store_id = $store_id";
                $rest = Db::query($where);
            }
     
            $bool = db("store_house")->where('id', $request->only(["id"])["id"])->update($data);

            if($bool){
                $this->success("更新成功",url("admin/StoreHouse/store_house"));
            } else {
                $this->error("更新失败", url("admin/StoreHouse/store_house"));
            }
                  
        }
    }


    /**
     * [仓库管理编辑]
     * 郭杨
     */    
    public function store_house_edit($id){
        $house = db("store_house")->where("id",$id)->select();
        return view("store_house_edit",["house"=>$house]);
    }

    /**
     * [仓库管理删除]
     * 郭杨
     */
    public function store_house_delete($id){
        $bool = db("store_house")->where("id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/StoreHouse/store_house"));
        } else {
            $this->error("删除失败", url("admin/StoreHouse/store_house"));
        }

    }

    /**
     * [仓库所有单位]
     * 郭杨
     */
    public function store_house_unit(){
        $unit = db("express")->where("store_id","EQ",79)->distinct(true)->field("name")->select();
        if(!empty($unit)){
            $liste = unit_list($unit);
            return ajax_success('传输成功', $liste);
        } else {
            return ajax_error('数据为空');
        }      
    }


    /**
     * [仓库编辑价格单位]
     * 郭杨
     */
    public function store_house_cost(Request $request){
        if($request->isPost()){
            $id = $request->only(["id"])["id"];
            $cost = db("store_house") -> where('id',$id) ->field("cost,unit,id")->find();
            $cost['cost'] = explode(",",$cost['cost']);
            $cost['unit'] = explode(",",$cost['unit']);
            if(!empty($cost)){
                return ajax_success('传输成功', $cost);
            } else {
                return ajax_error('数据为空');
            } 
        }     
    }


    /**
     * [仓库默认入仓编辑]
     * 郭杨
     */
    public function store_house_status(Request $request){
        $store_id = Session::get("store_id");
        $status = $request->only(["status"])["status"];
        if ($status == 0) {
            $id = $request->only(["id"])["id"];
            $bool = db("store_house")->where("id", $id)->update(["label" => 0]);
            if ($bool) {
                $this->redirect(url("admin/StoreHouse/store_house"));
            } else {
                $this->error("修改失败", url("admin/StoreHouse/store_house"));
            }
        }
        if ($status == 1) {
            $where = "update tb_store_house set label = 0 where store_id = $store_id";
            $rest = Db::query($where);
            $id = $request->only(["id"])["id"];
            $bool = db("store_house")->where("id", $id)->update(["label" => 1]);
            if ($bool) {
                $this->redirect(url("admin/StoreHouse/store_house"));
            } else {
                $this->error("修改失败", url("admin/StoreHouse/store_house"));
            }
        }
         
    }


    /**
     * [仓库入仓]
     * 郭杨
     */    
    public function stores_divergence()
    { 
        $store_id = Session::get("store_id");
        $store_order = db("house_order")
                    ->where("store_id","EQ",$store_id)
                    ->where("status",">",1)
                    ->field("id,parts_order_number,user_phone_number,parts_goods_name,user_account_name,store_name,store_number,order_create_time,end_time,store_house_id")
                    ->select();

        foreach($store_order as $key => $value){
            $store_order[$key]["store_number"] = str_replace(',', '', $store_order[$key]["store_number"]);
            $store_order[$key]["store_name"] = db("store_house")->where("id",$store_order[$key]["store_house_id"])->value('name');
        }    

        $url = 'admin/StoreHouse/stores_divergence';
        $pag_number = 20;
        $stores_divergence = paging_data($store_order,$url,$pag_number);
        return view("stores_divergence",["stores_divergence"=>$stores_divergence]);
    }

    /**
     * [仓库出仓]
     * 郭杨
     */
    public function stores_divergence_out(){
        $store_id = Session::get("store_id");
        $store_order = Db::table("tb_out_house_order")
                    ->field("tb_out_house_order.user_phone_number,user_account_name,out_order_number,house_charges,tb_house_order.parts_goods_name,end_time,tb_out_house_order.pay_time,tb_out_house_order.status,tb_store_house.name,tb_out_house_order.store_number,tb_out_house_order.id")
                    ->join("tb_house_order","tb_house_order.id = tb_out_house_order.house_order_id",'left')
                    ->join("tb_store_house","tb_store_house.id = tb_out_house_order.store_house_id",'left')
                    ->where("tb_out_house_order.store_id",$store_id)
                    ->where("tb_out_house_order.status",">",0)
                    ->select();

        foreach($store_order as $key => $value){
            $store_order[$key]["store_number"] = str_replace(',', '', $store_order[$key]["store_number"]);
        }    

        $url = 'admin/StoreHouse/stores_divergence_out';
        $pag_number = 20;
        $stores_divergences = paging_data($store_order,$url,$pag_number);
        return view("stores_divergence_out",["stores_divergences"=>$stores_divergences]);
    }


    /**
     * [仓库续费]
     * 郭杨
     */
    public function stores_series_index(){
        $store_id = Session::get("store_id");
        $store_order = Db::table("tb_series_house_order")
            ->field("tb_series_house_order.store_house_id,series_parts_number,series_price,tb_house_order.pay_time,store_number,end_time,user_account_name,parts_goods_name,tb_store_house.name")
            ->join("tb_house_order","tb_house_order.id = tb_series_house_order.store_house_id",'left')
            ->join("tb_store_house","tb_house_order.store_house_id = tb_store_house.id",'left')
            ->where("tb_series_house_order.store_id",$store_id)
            ->where("tb_series_house_order.pay_status",">",1)
            ->select();
        foreach($store_order as $key => $value){
        $store_order[$key]["store_number"] = str_replace(',', '', $store_order[$key]["store_number"]);
        }    

        $url = 'admin/StoreHouse/stores_series_index';
        $pag_number = 20;
        $stores = paging_data($store_order,$url,$pag_number);
        return view("stores_series_index",["stores"=>$stores]);
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:订单确认发货（填写订单编号）
     **************************************
     */
    public function  stores_order_confirm_shipment(Request $request){
        if($request->isPost()){
            $order_id =$request->only(["order_id"])["order_id"];
            $status =$request->only(["status"])["status"];
            $courier_number =$request->only(["courier_number"])["courier_number"];
            $express_name =$request->only(["express_name"])["express_name"];
            $express_name2 =$request->only(["express_name_ch"])["express_name_ch"];
            $data =[
                "status"=>$status,
                "courier_number"=>$courier_number,
                "express_name"=>$express_name,
                "express_name_ch"=>$express_name2,
            ];
            $order_number = Db::name("out_house_order")->where("id",$order_id)->value('out_order_number');
            $bool = Db::name("out_house_order")->where("id",$order_id)->update(['status'=>$status]);
            $boole = Db::name("order")->where("parts_order_number",$order_number)->update($data);
            if($bool){
                return ajax_success("发货成功",["status"=>1]);
            }else{
                return ajax_error("发货失败",["status"=>0]);
            }
        }
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:这是处理回复
     **************************************
     * @param Request $request
     */
    public function stores_notice_index(Request $request){
        if($request->isPost()){
            $order_id = $request->only("order_id")["order_id"];
            $datas = Db::name("note_notification")
                ->where("order_id",$order_id)
                ->order("create_time","desc")
                ->select();
            $order_type =Db::name("order")->where("id",$order_id)->value("order_type");
            $courier_number =Db::name("order")->where("id",$order_id)->value("courier_number");
            $express_name =Db::name("order")->where("id",$order_id)->value("express_name");
            $data =[
                "datas"=>$datas,
                "order_type"=>$order_type,
                "express_name"=>$express_name,
                "courier_number"=>$courier_number
            ];
            if(!empty($data)){
                return ajax_success("数据返回成功",$data);
            }else{
                return ajax_error("没有数据",["status"=>0]);
            }
        }
    }


    
 }