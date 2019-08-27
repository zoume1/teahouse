<?php

/**
 * Created by PhpStorm.
 * User: CHEN
 * Date: 2018/7/11
 * Time: 16:12
 */

namespace app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use app\admin\model\Good;
use app\admin\model\GoodsImages;
use think\Session;
use think\Loader;
use think\paginator\driver\Bootstrap;

use app\common\model\dealer\Apply ;
use app\common\model\dealer\Referee as RefereeModel;
use app\common\model\dealer\User;
use app\common\model\dealer\Order;
use app\api\model\UpdateLine;


class Goods extends Controller
{


    /**
     * [商品列表显示]
     * GY
     */
    public function index(Request $request)
    {
        // $data = [
        //     'member_id'=>1144,
        //     'id'=>2133,
        //     'parts_order_number'=>'ZY201907231129002145',
        //     'goods_id'=>[243,265,295],
        //     'store_id'=>6,
        //     'order_amount'=>[200,100,100],
        //     'goods_money'=>400,
        //     'status'=>2,
            
        // ];


        // halt(Order::createOrder($data));
        // $order_type = Db::name("order")->where("parts_order_number",$data["parts_order_number"])->find();
        // $order = GoodsOrder::getOrderInforMation($order_type);
        // $model = OrderModel::grantMoney($order);
        // // halt(Order::grantMoney($data));
        // $store_id = Session::get('store_id');
        // $member_id = 1122;
        // $member_data = Db::name("member")->where('member_id','=',$member_id)->find();
        // $apply = new Apply;
        // $rest = $apply->submit($member_data);
        // $inviter_id = 0;
        
        // RefereeModel::createRelation($member_id, $inviter_id,$store_id);


        $store_id = Session::get('store_id');
        $goods = db("goods")->where("store_id",'EQ',$store_id)->order("sort_number desc")->select();
        $goods_list = getSelectListes("wares");
        foreach ($goods as $key => $value) {
            if ($value["pid"]) {
                $res = db("wares")->where("id", $value['pid'])->field("name")->find();
                if($goods[$key]["goods_standard"] == "1")
                {
                    $max[$key] = db("special")->where("goods_id", $goods[$key]['id'])->max("price");//最高价格
                    $min[$key] = db("special")->where("goods_id", $goods[$key]['id'])->min("price");//最低价格
                    $goods[$key]["goods_repertory"] = db("special")->where("goods_id", $goods[$key]['id'])->sum("stock");//库存
                    $goods[$key]["goods_volume"] = db("special")->where("goods_id", $goods[$key]['id'])->sum("volume");//库存
                    $goods[$key]["max_price"] = $max[$key];
                    $goods[$key]["min_price"] = $min[$key];
                }
                $goods[$key]["named"] = $res["name"];               
                $goods[$key]["goods_show_images"] = explode(",", $goods[$key]["goods_show_images"])[0];
            }
        }

        $all_idents = $goods;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $goods = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Goods/index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $goods->appends($_GET);
        $this->assign('listpage', $goods->render());
        return view("goods_index", ["goods" => $goods,"goods_list" => $goods_list]);


    }



    /**
     * [商品列表添加组]
     * GY
     */
    public function add($pid = 0)
    {
        $goods_list = [];
        if ($pid == 0) {
            $goods_list = getSelectListes("wares");
        }
        $store_id = Session::get("store_id");
        $da_change = Db::table("tb_set_meal_order")
       ->where("store_id", $store_id)
       ->where("audit_status",1)
       ->value('enter_all_id'); 
        if(!empty($da_change)){
            if($da_change <= 6){
                $da_change = 1;
            }
            if(  ($da_change > 6) && ($da_change <= 17)){
                $da_change = 2;
            }
            if( $da_change > 17){
                $da_change = 3;
            }
        }
        $expenses = db("express")->field("id,name")->select();
        $scope = db("member_grade")->where("store_id","EQ",$store_id)->field("member_grade_name")->select();
        return view("goods_add", ["goods_list" => $goods_list,"scope"=>$scope,"expenses"=>$expenses,'da_change'=>$da_change]);
    }



    /**
     * [商品列表组保存]
     * GY
     * 
     */
    public function save(Request $request)
    {
        
        if ($request->isPost()) {
            $store_id = Session::get("store_id");
            $goods_data = $request->param();
            $show_images = $request->file("goods_show_images");
            if(!empty($goods_data['goods_delivery'])){
                $goods_data['goods_delivery'] = json_encode($goods_data['goods_delivery']);
            } else {
                $goods_data['goods_delivery'] = null;
            }
            $imgs = $request->file("imgs");
            $list = [];
            unset($goods_data["aaa"]);
            if (!empty($show_images)) {              
                foreach ($show_images as $k=>$v) {
                    $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $list[] = str_replace("\\", "/", $info->getSaveName());
                }            
                $goods_data["goods_show_image"] =  $list[0];
                $goods_data["goods_show_images"] = implode(',', $list);
            }
            if(!empty($goods_data["scope"])){
                $goods_data["scope"] = implode(',', $goods_data["scope"]);
            } else {
                $goods_data["scope"] = "";
            }
            
        
            $goods_data["templet_id"] = isset($goods_data["templet_id"])?implode(",",$goods_data["templet_id"]):null;
            $goods_data["templet_name"] = isset($goods_data["templet_name"])?implode(",",$goods_data["templet_name"]):null;
            $goods_data["goods_sign"] = isset($goods_data["goods_sign"])?$goods_data["goods_sign"]:null;
                           
            if(empty($goods_data["num"][1]) && empty($goods_data["unit"][0])){ //存             
                $goods_data["num"] = array();
                $goods_data["unit"] = array();
            } else {
                $goods_data["element"] = unit_comment($goods_data["num"],$goods_data["unit"]);
                $goods_data["num"] = implode(",",$goods_data["num"]);
                $goods_data["unit"] = implode(",",$goods_data["unit"]);
            }
            $goods_data["store_id"] = $store_id;
            //暂时更改
            $goods_data["goods_sign"] = json_encode($goods_data["goods_sign"]); 
            $goods_data["server"] = json_encode($goods_data["server"]); 
            if ($goods_data["goods_standard"] == "0") {
                $bool = db("goods")->insertGetId($goods_data);
                if ($bool && (!empty($show_images))) {
                    $this->success("添加成功", url("admin/Goods/index"));
                } else {
                    $this->success("添加失败", url('admin/Goods/add'));
                }
            }
            if ($goods_data["goods_standard"] == "1") {
                $goods_special = [];
                $goods_special['server'] = $goods_data['server'];
                $goods_special["goods_name"] = $goods_data["goods_name"];
                $goods_special["produce"] = $goods_data["produce"];
                $goods_special["store_id"] = $goods_data["store_id"];
                $goods_special["brand"] = $goods_data["brand"];
                $goods_special["date"] = $goods_data["date"];
                $goods_special["goods_number"] = $goods_data["goods_number"];
                $goods_special["goods_standard"] = $goods_data["goods_standard"];
                $goods_special["goods_selling"] = $goods_data["goods_selling"];
                $goods_special["goods_sign"] = $goods_data["goods_sign"];
                $goods_special["goods_describe"] = $goods_data["goods_describe"];
                $goods_special["pid"] = $goods_data["pid"];
                $goods_special["sort_number"] = $goods_data["sort_number"];
                $goods_special["video_link"] = $goods_data["video_link"];
                $goods_special["goods_delivery"] = $goods_data["goods_delivery"];
                $goods_special["goods_franking"] = $goods_data["goods_franking"];
                $goods_special["templet_id"] = $goods_data["templet_id"];
                $goods_special["label"] = $goods_data["label"];
                $goods_special["status"] = $goods_data["status"];
                $goods_special["scope"] = $goods_data["scope"];
                $goods_special["templet_id"] = $goods_data["templet_id"];
                $goods_special["templet_name"] = $goods_data["templet_name"];

                if (isset($goods_data["goods_text"])) {
                    $goods_special["goods_text"] = $goods_data["goods_text"];
                } else {
                    $goods_special["goods_text"] = "";
                    $goods_data["goods_text"] = "";
                }
                if (isset($goods_data["text"])) {
                    $goods_special["text"] = $goods_data["text"];
                } else {
                    $goods_special["text"] = "";
                    $goods_data["text"] = "";
                }
                $goods_special["goods_show_images"] = $goods_data["goods_show_images"];
                $goods_special["goods_show_image"] = $goods_data["goods_show_image"];
                $result = implode(",", $goods_data["lv1"]);
                $goods_id = db('goods')->insertGetId($goods_special);
                
                if (!empty($goods_data)) {
                    foreach ($goods_data as $kn => $nl) {
                        if (substr($kn, 0, 3) == "sss") {
                            $price[] = $nl["price"];
                            $stock[] = $nl["stock"];
                            $coding[] = $nl["coding"];
                            $cost[] = $nl["cost"];
                            $line[] = $nl["line"];
                            $offer[] = $nl["offer"];
                            if (isset($nl["status"])) {
                                $status[] = $nl["status"];
                            } else {
                                $status[] = "0";
                            }
                            if (isset($nl["save"])) {
                                $save[] = $nl["save"];
                            } else {
                                $save[] = "0";
                            }
                        }

                        if(substr($kn,strrpos($kn,"_")+1) == "num"){
                            $num1[substr($kn,0,strrpos($kn,"_"))]["num"] = implode(",",$goods_data[$kn]);
                            $num[substr($kn,0,strrpos($kn,"_"))]["num"] = $goods_data[$kn];
                        } 
                        if(substr($kn,strrpos($kn,"_")+1) == "unit"){
                            $unit1[substr($kn,0,strrpos($kn,"_"))]["unit"] = implode(",",$goods_data[$kn]);
                            $unit[substr($kn,0,strrpos($kn,"_"))]["unit"] = $goods_data[$kn]; 
                        }                        
                    }
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $k => $v) {
                        $shows = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $tab = str_replace("\\", "/", $shows->getSaveName());

                        if (is_array($goods_data)) {
                            foreach ($goods_data as $key => $value) {
                                if (substr($key, 0, 3) == "sss") {
                                    $str[] = substr($key, 3);
                                    $values[$k]["name"] = $str[$k];
                                    $values[$k]["price"] = $price[$k];
                                    $values[$k]["lv1"] = $result;
                                    $values[$k]["stock"] = $stock[$k];
                                    $values[$k]["coding"] = $coding[$k];
                                    if(isset($num1)){
                                        if(array_key_exists($coding[$k],$num1)){
                                            $values[$k]["num"] = $num1[$coding[$k]]["num"]; 
                                        } else {
                                            $values[$k]["num"] = null;
                                        }
                                    } else {
                                            $values[$k]["num"] = null;
                                    }
                                    if(isset($unit1)){
                                        if(array_key_exists($coding[$k],$unit1)){
                                            $values[$k]["unit"] = $unit1[$coding[$k]]["unit"];
                                            $values[$k]["element"] = unit_comment($num[$coding[$k]]["num"],$unit[$coding[$k]]["unit"]);
                                        } else {
                                            $values[$k]["unit"] = null;
                                            $values[$k]["element"] = null;
                                        }
                                    } else {
                                            $values[$k]["unit"] = null;
                                            $values[$k]["element"] = null;
                                    }
                                    $values[$k]["status"] = $status[$k];
                                    $values[$k]["save"] = $save[$k];
                                    $values[$k]["cost"] = $cost[$k];
                                    $values[$k]["line"] = $line[$k];                                    
                                    $values[$k]["images"] = $tab;
                                    $values[$k]["goods_id"] = $goods_id;
                                    $values[$k]["offer"] = $offer[$k];
                                    
                                }
                            }
                        }
                    }
                }

                foreach ($values as $kz => $vw) {
                    $rest = db('special')->insertGetId($vw);
                }    
                if ($rest && (!empty($show_images))) {
                    $this->success("添加成功", url("admin/Goods/index"));
                } else {
                    $this->success("添加失败", url('admin/Goods/add'));
                }
            }
        }
    }


    /**
     * [商品列表组修改]
     * GY
     */
    public function edit(Request $request, $id)
    {
        $store_id = Session::get("store_id");
        $goods = db("goods")->where("id", $id)->select();
        $scope = db("member_grade")->where("store_id","EQ",$store_id)->field("member_grade_name")->select();
        $goods_standard = db("special")->where("goods_id", $id)->select();
        $expenses = db("express")->field("id,name")->select();
        foreach ($goods as $key => $value) {
            if(!empty($goods[$key]["goods_show_images"])){
            $goods[$key]["goods_show_images"] = explode(',', $goods[$key]["goods_show_images"]);
            $goods[$key]["scope"] = explode(',', $goods[$key]["scope"]);
            $goods[$key]["unit"] = explode(',', $goods[$key]["element"]);
            $goods[$key]["templet_name"] = explode(',', $goods[$key]["templet_name"]);
            $goods[$key]["templet_id"] = explode(',', $goods[$key]["templet_id"]);
            $goods[$key]["goods_delivery"] = json_decode($goods[$key]["goods_delivery"],true);
            $goods[$key]["goods_sign"] = json_decode($goods[$key]["goods_sign"],true);
            $goods[$key]["server"] = json_decode($goods[$key]["server"],true);
        }
     }

        $team = isset($goods[0]["templet_id"])?$goods[0]["templet_id"]:null;
        
        if(!empty($team)){
            foreach($team as $ke => $val){
                $temp[$ke] = db("express")->where("id",$team[$ke])->field("name,id")->find();
            }
        }
       
        foreach ($goods_standard as $k => $v) {
            $goods_standard[$k]["title"] = explode('_', $v["name"]);
            $res = explode(',', $v["lv1"]);         
        }
          
        
        $goods_list = getSelectListes("wares");
        $restel = $goods[0]["goods_standard"]; //判断是否为通用或特殊
        if ($restel == 0) {
            return view("goods_edit", ["goods" => $goods, "goods_list" => $goods_list,"scope" => $scope,"expenses"=>$expenses,"temp"=>$temp]);
        } else {
            return view("goods_edit", ["goods" => $goods, "goods_list" => $goods_list, "res" => $res, "goods_standard" => $goods_standard,"scope" => $scope,"expenses"=>$expenses,"temp"=>$temp]);
        }
    }


    /**
     * [商品列表组图片删除]
     * GY
     */
    public function images(Request $request)
    {
        if ($request->isPost()) {
            $tid = $request->param();
            $id = $tid["id"];
            $image = db("goods")->where("id", $tid['pid'])->field("goods_show_images")->find();
            if (!empty($image["goods_show_images"])) {
                $se = explode(",", $image["goods_show_images"]);
                foreach ($se as $key => $value) {
                    if ($value == $id) {
                        unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $value);
                    } else {
                        $new_image[] = $value;
                    }
                }
            }
            if (!empty($new_image)) {
                $new_imgs_url = implode(',', $new_image);
                $res = Db::name('goods')->where("id", $tid['pid'])->update(['goods_show_images' => $new_imgs_url,'goods_show_image' => $new_imgs_url[0]]);
            } else {
                $res = Db::name('goods')->where("id", $tid['pid'])->update(['goods_show_images' => NULL,'goods_show_image' => NULL]);
            }
            if ($res) {
                return ajax_success('删除成功');
            } else {
                return ajax_success('删除失败');
            }
        }
    }



    /**
     * [商品列表组删除]
     * GY
     */
    public function del(Request $request)
    {
        $id = $request->only(["id"])["id"];
        $bool = db("goods")-> where("id", $id)->delete();
        $boole = db("special")->where("goods_id",$id)->delete();
        $res = db("commodity")->where("goods_id",$id)->find();

        if($res) {
            db("commodity")->where("goods_id", $id)->delete();
        }

        if ($bool || $boole) {
            $this->success("删除成功", url("admin/Goods/index"));
        } else {
            $this->success("删除失败", url('admin/Goods/add'));
        }
    }



    /**
     * [商品列表组更新]
     * GY
     * 
     */
    public function updata(Request $request)
    {
        if ($request->isPost()) {
            $store_id = Session :: get("store_id");
            $id = $request->only(["id"])["id"];
            $goods_data = $request->param();
            unset($goods_data["aaa"]);
            if(!empty($goods_data["goods_sign"])){
                $goods_data["goods_sign"] = json_encode($goods_data["goods_sign"]);
            } 
            $goods_data["goods_member"] = isset($goods_data["goods_member"])?$goods_data["goods_member"]:0;
        
            if(!empty($goods_data["server"])){     
                $goods_data["server"] = json_encode($goods_data["server"]); 
            }
            $show_images = $request->file("goods_show_images");
            if(!empty($goods_data['goods_delivery'])){
                $goods_data['goods_delivery'] = json_encode(array_values($goods_data['goods_delivery']));
            } else {
                $goods_data['goods_delivery'] = null;
            }    
            if(!empty($goods_data["scope"])){
                $goods_data["scope"] = implode(',', $goods_data["scope"]);
            } else {
                $goods_data["scope"] = "";
            }
            $goods_data["templet_id"] = isset($goods_data["templet_id"])?implode(",",$goods_data["templet_id"]):null;
            $goods_data["templet_name"] = isset($goods_data["templet_name"])?implode(",",$goods_data["templet_name"]):null;
            $list = [];
            if (!empty($show_images)) {
                foreach ($show_images as $k => $v) {
                    $show = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $list[] = str_replace("\\", "/", $show->getSaveName());
                }               
                    $liste = implode(',', $list);
                    $image = db("goods")->where("id", $id)->field("goods_show_images")->find();
                if(!empty($image["goods_show_images"]))
                {
                    $exper = $image["goods_show_images"];
                    $montage = $exper . "," . $liste;
                    $goods_data["goods_show_images"] = $montage;
                } else {                   
                    $montage = $liste;
                    $goods_data["goods_show_image"] = $list[0];
                    $goods_data["goods_show_images"] = $montage;
                }
            } else {
                    $image = db("goods")->where("id", $id)->field("goods_show_images")->find();
                if(!empty($image["goods_show_images"])){
                    $goods_data["goods_show_images"] = $image["goods_show_images"];
                } else {
                    $goods_data["goods_show_images"] = null;
                    $goods_data["goods_show_image"] = null;
                }
            } 

            if($goods_data["goods_standard"] == 1){
                $special_id = db("special")->where("goods_id",$id)->field("id")->select();

                foreach($special_id as $pp => $qq){
                    $special[$pp] = $qq["id"];
                }

                foreach ($goods_data as $kn => $nl) {
                    if(substr($kn,strrpos($kn,"_")+1) == "num"){
                        $num1[substr($kn,0,strrpos($kn,"_"))]["num"] = implode(",",$goods_data[$kn]);
                        $num[substr($kn,0,strrpos($kn,"_"))]["num"] = $goods_data[$kn];
                    } 
                    if(substr($kn,strrpos($kn,"_")+1) == "unit"){
                        $unit1[substr($kn,0,strrpos($kn,"_"))]["unit"] = implode(",",$goods_data[$kn]);
                        $unit[substr($kn,0,strrpos($kn,"_"))]["unit"] = $goods_data[$kn]; 
                    } 
                    
                    if(is_array($nl)){
                        unset($goods_data[$kn]);                    
                    }
                }
           
             foreach($special as $tt => $yy){ 
                 if(isset($num1)){
                    if(array_key_exists($yy,$num1)){        
                    $bools[$tt] = db("special")->where("id",$yy)->update(["unit"=>$unit1[$yy]["unit"],"num"=>$num1[$yy]["num"],"element"=>unit_comment($num[$yy]["num"],$unit[$yy]["unit"])]);
                    } else {
                    $bools[$tt] = db("special")->where("id",$yy)->update(["unit"=>null,"num"=>null,"element"=>null]);
                    }
               } else {
                    $bools[$tt] = db("special")->where("id",$yy)->update(["unit"=>null,"num"=>null,"element"=>null]);
               }
            }

             foreach($bools as $xx => $cc){
                 if($cc = 1){
                     $rest = 1;
                 } else {
                    $rest = 0;
                 }
             }
             
             $bool = db("goods")->where("id", $id)->update($goods_data);
             if ($bool || $rest) {
                 $this->success("更新成功", url("admin/Goods/index"));
             } else {
                 $this->success("更新成功", url('admin/Goods/index'));
             }
             
        } else {

            if(empty($goods_data["num"][1]) && empty($goods_data["unit"][0])){ //存空
                
                $goods_data["num"] = array();
                $goods_data["unit"] = array();
            } else {
                $goods_data["element"] = unit_comment($goods_data["num"],$goods_data["unit"]);
                $goods_data["num"] = implode(",",$goods_data["num"]);
                $goods_data["unit"] = implode(",",$goods_data["unit"]);
            }
            $rest = new UpdateLine;
            $update_data = [
                'goods_line'=>$goods_data['goods_bottom_money'],
                'goods_id'=>$id,
                'store_id'=>$store_id
            ];
            $update_line = $rest->add($update_data);
        }
            
            $bool = db("goods")->where("id", $id)->update($goods_data);
            if ($bool) {
                $this->success("更新成功", url("admin/Goods/index"));
            } else {
                $this->success("更新成功", url('admin/Goods/index'));
            }

        }

    }



    /**
     * [商品列表运费模板编辑]
     * 郭杨
     */
    public function goods_templet(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $templet = db("goods")->where("id",$id)->field("templet_id,templet_name")->find();
            if(!empty($templet)){
                $templet_id = explode(",",$templet["templet_id"]);
                $templet["templet_id"] = $templet_id;
                foreach($templet_id as $ke => $val){
                    $temp[$ke] = db("express")->where("id",$val)->field("name,id")->find();
                }
                $rest["templet_unit"] = explode(",",$templet["templet_name"]);
                $rest["templet_name"] = $temp;
                return ajax_success('传输成功', $rest);
            } else {
                return ajax_error("数据为空");
            }
        }
    }

    /**
     * [众筹商品列表运费模板编辑]
     * 郭杨
     */
    public function crowd_templet(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $templet = db("crowd_goods")->where("id",$id)->field("templet_id,templet_name")->find();
            if(!empty($templet)){
                $templet_id = explode(",",$templet["templet_id"]);
                $templet["templet_id"] = $templet_id;
                foreach($templet_id as $ke => $val){
                    $temp[$ke] = db("express")->where("id",$val)->field("name,id")->find();
                }
                $rest["templet_unit"] = explode(",",$templet["templet_name"]);
                $rest["templet_name"] = $temp;
                return ajax_success('传输成功', $rest);
            } else {
                return ajax_error("数据为空");
            }
        }
    }



    /**
     * [商品列表组首页推荐]
     * 郭杨
     */
    public function status(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/index"));
                }
            }
        }
    }


    /**
     * [商品列表组是否上架]
     * 陈绪
     */
    public function ground(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods")->where("id", $id)->update(["label" => 0]);
                $rest = db("join")->where("goods_id",$id)->update(["label" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods")->where("id", $id)->update(["label" => 1]);
                $rest = db("join")->where("goods_id",$id)->update(["label" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/index"));
                }
            }
        }
    }

        /**
     * [商品列表组是否上架]
     * 陈绪
     */
    public function distribution_status(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods")->where("id", $id)->update(["distribution_status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods")->where("id", $id)->update(["distribution_status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/index"));
                }
            }
        }
    }



    /**
     * [商品列表组批量删除]
     * 陈绪
     */
    public function dels(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            if (is_array($id)) {
                $where = 'id in(' . implode(',', $id) . ')';
            } else {
                $where = 'id=' . $id;
            }
            $tb_name = 'goods';
            $list = Db::name('goods')->where($where)->delete();
            if (empty($list)) {
                return ajax_success('成功删除!', ['status' => 1]);
            } else {
                return ajax_error('删除失败', ['status' => 0]);
            }
        }
    }


    /**
     * [商品列表规格图片删除]
     * 郭杨
     */
    public function photos(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            if (!empty($id)) {
                $photo = db("special")->where("id", $id)->update(["images" => null]);
            }
            if ($photo) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }


    /**
     * [商品列表规格值修改]
     * 郭杨
     */
    public function value(Request $request)
    {
        if ($request->isPost()) {
            $store_id = Session::get('store_id');
            $id = $request->only(["id"])["id"];
            $value = $request->only(["value"])["value"];
            $key = $request->only(["key"])["key"];

            $valuet = db("special")->where("id", $id)->update([$key => $value]);
            if($key == "line"){
                $rest = new UpdateLine;
                $update_data = [
                    'goods_line'=>$value,
                    'goods_id'=>$id,
                    'store_id'=>$store_id
                ];
                $update_line = $rest->add($update_data);
            }
            if (!empty($valuet)) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }


    /**
     * [商品列表规格开关]
     * 郭杨
     */
    public function switches(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $status = $request->only(["status"])["status"];
            $name = $request->only(["name"])["name"];

            if (!empty($id)) {
                $ture = db("special")->where("id", $id)->update(["$name" => $status]);
            }
            if ($ture) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }


    /**
     * [商品列表规格图片添加]
     * 郭杨
     */
    public function addphoto(Request $request)
    {
        if ($request->isPost()) {
            $id = $request -> only(["id"])["id"];
            $imag = $request-> file("file") -> move(ROOT_PATH . 'public' . DS . 'uploads');
            $images = str_replace("\\", "/", $imag->getSaveName());

            if(!empty($id)){
                $bool = db("special")->where("id", $id)->update(["images" => $images]);
            }
             if ($bool) {
                 return ajax_success('添加图片成功!');
             } else {
                 return ajax_error('添加图片失败');
             }
        }
    }



    /**
     * [商品列表分销设置加载]
     * 郭杨
     */
    public function goods_promote($id)
    {
        if ($request->isPost()) {
            $id = $request -> only(["id"])["id"];
            $imag = $request-> file("file") -> move(ROOT_PATH . 'public' . DS . 'uploads');
            $images = str_replace("\\", "/", $imag->getSaveName());

            if(!empty($id)){
                $bool = db("special")->where("id", $id)->update(["images" => $images]);
            }
             if ($bool) {
                 return ajax_success('添加图片成功!');
             } else {
                 return ajax_error('添加图片失败');
             }
        }
    }


    /**
     * [商品列表搜索]
     * 郭杨
     */
    public function search()
    {
        $goods_number = input('goods_number');
        $pid = input('pid');
        $store_id = Session::get("store_id");
        if((empty($goods_number)) && (!empty($pid))){
            $goods = db("goods")
                    ->where("pid",$pid)
                    ->where("store_id","EQ",$store_id)
                    ->order("id desc")
                    ->select();
        } else if ((!empty($goods_number)) && (empty($pid))) {
            $condition =" `goods_number` like '%{$goods_number}%' or `goods_name` like '%{$goods_number}%'";
            $goods = db("goods")
                    ->where($condition)
                    ->where("store_id","EQ",$store_id)
                    ->order("id desc")
                    ->select();
        } else if ((!empty($goods_number)) && (!empty($pid))) {
            $condition =" `goods_number` like '%{$goods_number}%' or `goods_name` like '%{$goods_number}%'";
            $goods = db("goods")
            ->where($condition)
            ->where("store_id","EQ",$store_id)
            ->where("pid",$pid)
            ->order("id desc")
            ->select();
        } else {
            $goods = db("goods")->where("store_id","EQ",$store_id)->order("id desc")->select();
        }
      
        $goods_list = getSelectListes("wares");
        foreach ($goods as $key => $value) {
            if ($value["pid"]) {
                $res = db("wares")->where("id", $value['pid'])->field("name")->find();
                if($goods[$key]["goods_standard"] == "1")
                {
                    $max[$key] = db("special")->where("goods_id", $goods[$key]['id'])->max("price");//最高价格
                    $min[$key] = db("special")->where("goods_id", $goods[$key]['id'])->min("price");//最低价格
                    $goods[$key]["goods_repertory"] = db("special")->where("goods_id", $goods[$key]['id'])->sum("stock");//库存
                    $goods[$key]["max_price"] = $max[$key];
                    $goods[$key]["min_price"] = $min[$key];
                }
                $goods[$key]["named"] = $res["name"];               
                $goods[$key]["goods_show_images"] = explode(",", $goods[$key]["goods_show_images"])[0];
            }
        }

        $all_idents = $goods;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $goods = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Goods/index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $goods->appends($_GET);
        $this->assign('listpage', $goods->render());
        return view("goods_index", ["goods" => $goods,"goods_list" => $goods_list]);
    }



    /**
     * [普通商品多规格列表单位编辑]
     * 郭杨
     */
    public function offer(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $standard = db("goods")->where("id",$id)->value("goods_standard");
            if($standard == 1){
                $goods_standard = db("special")->where("goods_id", $id)->select();
                $offer = db("special")->where("goods_id", $id)->field("coding,id")->select();

                foreach($offer as $pp => $qq){
                    $offers[$pp] = $qq["coding"];
                    $specail_id[$pp] = $qq["id"];
                }

                foreach ($goods_standard as $k => $v) {
                    $goods_standard[$k]["title"] = explode('_', $v["name"]);
                    $res = explode(',', $v["lv1"]);      
                    $unit["unit"][] = explode(',', $v["unit"]);        
                    $num["num"][] = explode(',', $v["num"]);        
                }

                foreach($offers as $kk => $zz){
                    $rest1["unit"][$kk] = $unit["unit"][$kk];
                    $rest2["num"][$kk] = $num["num"][$kk];
                    $unit1[$kk]["unit"] =  $rest1["unit"][$kk];
                    $unit1[$kk]["num"] =  $rest2["num"][$kk];
                    $unit1[$kk]["number"] =  $offers[$kk];
                    $unit1[$kk]["id"] =  $specail_id[$kk];
                    
                             
                }
                
                if(!empty($unit1)){
                    return ajax_success('传输成功', $unit1);
                } else {
                    return ajax_error("数据为空");
                }

            } else {
                return ajax_error("该商品为统一规格商品");
            }
        }
    }
    


    /**
     * [普通商品多规格列表单位id查找]
     * 郭杨
     */
    public function standard(Request $request)
    {
        if ($request->isPost()) {
            $coding = $request->only(["coding"])["coding"];
            $id = $request->only(["id"])["id"];
            $special = db("special")->where("goods_id",$id)->where("coding",$coding)->value("id");
            if(!empty($special)){
                return ajax_success('传输成功', $special);
            } else {
                return ajax_error("数据为空");
            } 
        }             
    }


    /**
     * [众筹商品显示]
     * 郭杨
     */    
    public function crowd_index(){
        $store_id = Session::get("store_id");
        $crowd_data = db("crowd_goods")->where("store_id","EQ",$store_id)->order("sort_number desc")->select();
        if(!empty($crowd_data)){
            foreach ($crowd_data as $key => $value) {
                $min_price[$key] = db("crowd_special")->where("goods_id", $crowd_data[$key]['id'])->min("cost");//金额
                $min_stock[$key] = db("crowd_special")->where("goods_id", $crowd_data[$key]['id'])->min("stock");//数量
                $crowd_data[$key]["min_price"] = sprintf("%.2f", $min_price[$key]);
                $crowd_data[$key]["min_stock"] = $min_stock[$key];
            }
        }   

        $url = 'admin/Goods/crowd_index';
        $pag_number = 20;
        $crowd = paging_data($crowd_data,$url,$pag_number);     
        return view("crowd_index",["crowd"=>$crowd]);
    }



    /**
     * [众筹商品添加]
     * 郭杨
     */    
    public function crowd_add(Request $request){
        if($request->isPost()) {
            $goods_data = $request->param();
            $store_id = Session::get("store_id");
            $goods_text =  isset($goods_data["goods_text"]) ? $goods_data["goods_text"]:null;
            $team =  isset($goods_data["team"]) ? $goods_data["team"]:null;
            $text =  isset($goods_data["text"]) ? $goods_data["text"]:null;
            $result = isset($goods_data["lv1"]) ? $goods_data["lv1"]:null;
            $scope = isset($goods_data["scope"]) ? implode(",",$goods_data["scope"]):null;
            $goods_delivery = isset($goods_data["goods_delivery"]) ? json_encode($goods_data["goods_delivery"]):null;
            $goods_sign = isset($goods_data["goods_sign"]) ? json_encode($goods_data["goods_sign"]):null;
            $goods_data["templet_id"] = isset($goods_data["templet_id"])?implode(",",$goods_data["templet_id"]):null;
            $goods_data["templet_name"] = isset($goods_data["templet_name"])?implode(",",$goods_data["templet_name"]):null;
            $show_images = $request->file("goods_show_images");
            $number_days = intval($goods_data["number_days"]);
            $imgs = $request->file("imgs");
            $time = time();
            $end_time = strtotime(date('Y-m-d', strtotime ("+ $number_days day", $time)));
            $list = [];
            if (!empty($show_images)) {              
                foreach ($show_images as $k=>$v) {
                    $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $list[] = str_replace("\\", "/", $info->getSaveName());
                }            
                $goods_data["goods_show_image"] =  $list[0];
                $goods_data["goods_show_images"] = implode(',', $list);
                $goods_data["time"] = $time;
            }

            $goods = array(
                "project_name" => $goods_data["project_name"],
                "number_days" => $goods_data["number_days"],
                "goods_sign" => $goods_sign,
                "goods_describe" => $goods_data["goods_describe"],
                "pid" => $goods_data["pid"],
                "sort_number" => $goods_data["sort_number"],
                "time"=> $time,
                "end_time"=>$end_time,
                "company_name" => $goods_data["company_name"],
                "company_name1" => $goods_data["company_name"],
                "company_time" => $goods_data["company_time"],
                "goods_show_image" => $goods_data["goods_show_image"],
                "goods_show_images" => $goods_data["goods_show_images"],
                "goods_member" => $goods_data["goods_member"],
                "video_link" => $goods_data["video_link"],
                "goods_text" => $goods_text,
                "team" => $team,
                "text" => $text,
                "goods_delivery" => $goods_delivery,
                "goods_franking" => $goods_data["goods_franking"],
                "templet_id" => $goods_data["templet_id"],
                "templet_name" => $goods_data["templet_name"],
                "label" => $goods_data["label"],
                "status"=> $goods_data["status"],
                "scope"=> $scope,
                "store_id"=> $store_id
            );

            if(empty($result)){
                $this->error("请添加规格值", url('admin/Goods/crowd_add'));
            } else {
                $goods_id = db('crowd_goods')->insertGetId($goods);
                $goods_number_id = $goods_id + 1000000;
                $standard = implode(",", $result);
                if (!empty($goods_data)) {
                    foreach ($goods_data as $kn => $nl) {
                        if (substr($kn, 0, 3) == "sss") 
                        {
                            $stock[] = $nl["stock"];
                            $coding[] = $nl["coding"];
                            $story[] = $nl["story"];
                            $cost[] = $nl["cost"];
                            $offer[] = $nl["offer"];
                            $line[] = isset($nl["line"])?$nl["line"]:null;
                            $status[] = isset($nl["status"])? $nl["status"]:0;
                            $save[] = isset($nl["save"]) ? $nl["save"]:0; 
                        }
                        if(substr($kn,strrpos($kn,"_")+1) == "num")
                        {
                            $num1[substr($kn,0,strrpos($kn,"_"))]["num"] = implode(",",$goods_data[$kn]);
                            $num[substr($kn,0,strrpos($kn,"_"))]["num"] = $goods_data[$kn];
                        } 
                        if(substr($kn,strrpos($kn,"_")+1) == "unit")
                        {
                            $unit1[substr($kn,0,strrpos($kn,"_"))]["unit"] = implode(",",$goods_data[$kn]);
                            $unit[substr($kn,0,strrpos($kn,"_"))]["unit"] = $goods_data[$kn]; 
                        }                         
                    }
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $k => $v) {
                        $shows = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $tab = str_replace("\\", "/", $shows->getSaveName());

                        if (is_array($goods_data)) {
                            foreach ($goods_data as $key => $value) {
                                if (substr($key, 0, 3) == "sss") {
                                    $str[] = substr($key, 3);
                                    $values[$k]["name"] = $str[$k];
                                    $values[$k]["lv1"] = $standard;
                                    $values[$k]["stock"] = $stock[$k];
                                    $values[$k]["offer"] = $offer[$k];
                                    $values[$k]["coding"] = $coding[$k];
                                    if(isset($num1)){
                                        if(array_key_exists($coding[$k],$num1)){
                                            $values[$k]["num"] = $num1[$coding[$k]]["num"]; 
                                        } else {
                                            $values[$k]["num"] = null;
                                        }
                                    } else {
                                            $values[$k]["num"] = null;
                                    }
                                    if(isset($unit1)){
                                        if(array_key_exists($coding[$k],$unit1)){
                                            $values[$k]["unit"] = $unit1[$coding[$k]]["unit"];
                                            $values[$k]["element"] = unit_comment($num[$coding[$k]]["num"],$unit[$coding[$k]]["unit"]);
                                        } else {
                                            $values[$k]["unit"] = null;
                                            $values[$k]["element"] = null;
                                        }
                                    } else {
                                            $values[$k]["unit"] = null;
                                            $values[$k]["element"] = null;
                                    }
                                    $values[$k]["status"] = $status[$k];
                                    $values[$k]["story"] = $story[$k];
                                    $values[$k]["save"] = $save[$k];
                                    $values[$k]["cost"] = $cost[$k];
                                    $values[$k]["limit"] = $line[$k];                                    
                                    $values[$k]["images"] = $tab;
                                    $values[$k]["goods_id"] = $goods_id;                                   
                                }
                            }
                        }
                    }
                }
            }

            foreach ($values as $kz => $vw) {
                $rest = db('crowd_special')->insertGetId($vw);
            }
            $boolte = db('crowd_goods')->where('id',$goods_id)->update(['goods_number'=>$goods_number]);
            if ($rest || $goods_id) {
                $this->success("添加成功", url("admin/Goods/crowd_index"));
            } else {
                $this->error("添加失败", url('admin/Goods/crowd_index'));
            }
            
   
        }
        $store_id = Session::get("store_id");
        $scope = db("member_grade")->where("store_id","EQ",$store_id)->field("member_grade_name")->select();
        $expenses = db("express")->where("store_id","EQ",$store_id)->field("id,name")->select();
        $goods_list = getSelectListes("wares");      
        return view("crowd_add",["goods_list"=>$goods_list,"expenses"=>$expenses,"scope"=>$scope]);
    }


    /**
     * [众筹商品编辑]
     * 郭杨
     */    
    public function crowd_edit($id){
        $store_id = Session::get("store_id");
        $goods = db("crowd_goods") -> where("id",$id) -> select(); 
        $goods_standard = db("crowd_special")->where("goods_id", $id)->select();
        $goods_list = getSelectListes("wares");
        $expenses = db("express")->where("store_id","EQ",$store_id)->field("id,name")->select();
        $scope = db("member_grade")->where("store_id","EQ",$store_id)->field("member_grade_name")->select();


        foreach ($goods as $key => $value) {
            if(!empty($goods[$key]["goods_show_images"])){
            $goods[$key]["goods_show_images"] = explode(',', $goods[$key]["goods_show_images"]);
            $goods[$key]["scope"] = explode(',', $goods[$key]["scope"]);
            $goods[$key]["goods_delivery"] = json_decode($goods[$key]["goods_delivery"],true);
            $goods[$key]["goods_sign"] = json_decode($goods[$key]["goods_sign"],true);
        }
     }

     foreach ($goods_standard as $k => $v) {
            $goods_standard[$k]["title"] = explode('_', $v["name"]);
            $res = explode(',', $v["lv1"]);         
        }
    
        // halt($goods_standard);
        return view("crowd_edit", ["goods" => $goods, "goods_list" => $goods_list, "res" => $res, "goods_standard" => $goods_standard,"expenses"=>$expenses,"scope" => $scope]);
    }


    /**
     * [众筹商品列表组更新]
     * GY
     * 
     */
    public function crowd_update(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $time = time();
            $goods_data = $request->param();
            unset($goods_data["aaa"]);
            $show_images = $request->file("goods_show_images");
            $number_days = intval($goods_data["number_days"]);
            $goods_data["templet_id"] = isset($goods_data["templet_id"])?implode(",",$goods_data["templet_id"]):null;
            $goods_data["templet_name"] = isset($goods_data["templet_name"])?implode(",",$goods_data["templet_name"]):null;
            $end_time = strtotime(date('Y-m-d', strtotime ("+ $number_days day", $time)));           
            $list = [];
            if (!empty($show_images)) {
                foreach ($show_images as $k => $v) {
                    $show = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $list[] = str_replace("\\", "/", $show->getSaveName());
                }               
                    $liste = implode(',', $list);
                    $image = db("crowd_goods")->where("id", $id)->field("goods_show_images")->find();
                if(!empty($image["goods_show_images"]))
                {
                    $exper = $image["goods_show_images"];
                    $montage = $exper . "," . $liste;
                    $goods_data["goods_show_images"] = $montage;
                } else {                   
                    $montage = $liste;
                    $goods_data["goods_show_image"] = $list[0];
                    $goods_data["goods_show_images"] = $montage;
                }
            } else {
                    $image = db("crowd_goods")->where("id", $id)->field("goods_show_images")->find();
                if(!empty($image["goods_show_images"])){
                    $goods_data["goods_show_images"] = $image["goods_show_images"];
                } else {
                    $goods_data["goods_show_images"] = null;
                    $goods_data["goods_show_image"] = null;
                }
            } 
            $goods_data["end_time"] = $end_time;
            $special_id = db("crowd_special")->where("goods_id",$id)->field("id")->select();
            foreach($special_id as $pp => $qq){
                $special[$pp] = $qq["id"];
            }
            foreach ($goods_data as $kn => $nl) {
                if(substr($kn,strrpos($kn,"_")+1) == "num"){
                    $num1[substr($kn,0,strrpos($kn,"_"))]["num"] = implode(",",$goods_data[$kn]);
                    $num[substr($kn,0,strrpos($kn,"_"))]["num"] = $goods_data[$kn];
                } 
                if(substr($kn,strrpos($kn,"_")+1) == "unit"){
                    $unit1[substr($kn,0,strrpos($kn,"_"))]["unit"] = implode(",",$goods_data[$kn]);
                    $unit[substr($kn,0,strrpos($kn,"_"))]["unit"] = $goods_data[$kn]; 
                }    
                if(is_array($nl)){
                    unset($goods_data[$kn]);                    
                }
            }
            
            foreach($special as $tt => $yy){ 
                 if(isset($num1)){
                    if(array_key_exists($yy,$num1)){        
                    $bools[$tt] = db("crowd_special")->where("id",$yy)->update(["unit"=>$unit1[$yy]["unit"],"num"=>$num1[$yy]["num"],"element"=>unit_comment($num[$yy]["num"],$unit[$yy]["unit"])]);
                    } else {
                    $bools[$tt] = db("crowd_special")->where("id",$yy)->update(["unit"=>null,"num"=>null,"element"=>null]);
                    }
               } else {
                    $bools[$tt] = db("crowd_special")->where("id",$yy)->update(["unit"=>null,"num"=>null,"element"=>null]);
               }
            }

            foreach($bools as $xx => $cc){
                if($cc = 1){
                     $rest = 1;
                } else {
                    $rest = 0;
                }
            }
             $bool = db("crowd_goods")->where("id", $id)->update($goods_data);
             if ($bool || $rest) {
                 $this->success("更新成功", url("admin/Goods/crowd_index"));
             } else {
                 $this->success("更新失败", url('admin/Goods/crowd_index'));
             }                         
        }
    }



    /**
     * [众筹商品列表组删除]
     * GY
     */
    public function crowd_delete($id)
    {
        $bool = db("crowd_goods")-> where("id", $id)->delete();
        $boole = db("crowd_special")->where("goods_id",$id)->delete();

        if ($bool || $boole) {
            $this->success("删除成功", url("admin/Goods/crowd_index"));
        } else {
            $this->success("删除失败", url('admin/Goods/crowd_index'));
        }
    }

    /**
     * [众筹商品图片删除]
     * GY
     */
    public function crowd_images(Request $request)
    {
        if ($request->isPost()) {
            $tid = $request->param();
            $id = $tid["id"];
            $image = db("crowd_goods")->where("id", $tid['pid'])->field("goods_show_images")->find();
            if (!empty($image["goods_show_images"])) {
                $se = explode(",", $image["goods_show_images"]);
                foreach ($se as $key => $value) {
                    if ($value == $id) {
                        unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $value);
                    } else {
                        $new_image[] = $value;
                    }
                }
            }
            if (!empty($new_image)) {
                $new_imgs_url = implode(',', $new_image);
                $res = Db::name('crowd_goods')->where("id", $tid['pid'])->update(['goods_show_images' => $new_imgs_url]);
            } else {
                $res = Db::name('crowd_goods')->where("id", $tid['pid'])->update(['goods_show_images' => NULL,'goods_show_image' => NULL]);
            }
            if ($res) {
                return ajax_success('删除成功');
            } else {
                return ajax_success('删除失败');
            }
        }
    }



    /**
     * [众筹商品多规格列表单位编辑]
     * 郭杨
     */
    public function crowd_offer(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $goods_standard = db("crowd_special")->where("goods_id", $id)->select();
            $offer = db("crowd_special")->where("goods_id", $id)->field("coding,id")->select();

            foreach($offer as $pp => $qq){
                $offers[$pp] = $qq["coding"];
                $specail_id[$pp] = $qq["id"];
            }

            foreach ($goods_standard as $k => $v) {
                $goods_standard[$k]["title"] = explode('_', $v["name"]);
                $res = explode(',', $v["lv1"]);      
                $unit["unit"][] = explode(',', $v["unit"]);        
                $num["num"][] = explode(',', $v["num"]);        
            }

            foreach($offers as $kk => $zz){
                $rest1["unit"][$kk] = $unit["unit"][$kk];
                $rest2["num"][$kk] = $num["num"][$kk];
                $unit1[$kk]["unit"] =  $rest1["unit"][$kk];
                $unit1[$kk]["num"] =  $rest2["num"][$kk];
                $unit1[$kk]["number"] =  $offers[$kk];
                $unit1[$kk]["id"] =  $specail_id[$kk];                           
            }  

            if(!empty($unit1)){
                return ajax_success('传输成功', $unit1);
            } else {
                return ajax_error("数据为空");
            }
        }
    }


    /**
     * [增值商品多规格列表单位id查找]
     * 郭杨
     */
    public function crowd_standard(Request $request)
    {
        if ($request->isPost()) {
            $coding = $request->only(["coding"])["coding"];
            $id = $request->only(["id"])["id"];
            $special = db("crowd_special")->where("goods_id",$id)->where("coding",$coding)->value("id");
            if(!empty($special)){
                return ajax_success('传输成功', $special);
            } else {
                return ajax_error("数据为空");
            } 
        }             
    }

    /**
     * [商品列表组首页轮播推荐]
     * 郭杨
     */
    public function crowd_status(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("crowd_goods")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/crowd_index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/crowd_index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("crowd_goods")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/crowd_index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/_index"));
                }
            }
        }
    }


    /**
     * [众筹商品列表组是否上架]
     * GY
     */
    public function crowd_ground(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("crowd_goods")->where("id", $id)->update(["label" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/crowd_index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/crowd_index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("crowd_goods")->where("id", $id)->update(["label" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/crowd_index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/crowd_index"));
                }
            }
        }
    }


    /**
     * [众筹商品列表组批量删除]
     * GY
     */
    public function crowd_dels(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            if (is_array($id)) {
                $where = 'id in(' . implode(',', $id) . ')';
            } else {
                $where = 'id=' . $id;
            }
            $list = Db::name('crowd_goods')->where($where)->delete();
            if (empty($list)) {
                return ajax_success('成功删除!', ['status' => 1]);
            } else {
                return ajax_error('删除失败', ['status' => 0]);
            }
        }
    }


     /**
     * [众筹商品列表规格图片删除]
     * 郭杨
     */
    public function crowd_photos(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            if (!empty($id)) {
                $photo = db("crowd_special")->where("id", $id)->update(["images" => null]);
            }
            if ($photo) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }

    /**
     * [众筹商品列表规格值修改]
     * 郭杨
     */
    public function crowd_value(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $value = $request->only(["value"])["value"];
            $key = $request->only(["key"])["key"];
            $valuet = db("crowd_special")->where("id", $id)->update([$key => $value]);

            if (!empty($valuet)) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }

    /**
     * [众筹商品列表规格开关]
     * 郭杨
     */
    public function crowd_switches(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $status = $request->only(["status"])["status"];
            $name = $request->only(["name"])["name"];

            if (!empty($id)) {
                $ture = db("crowd_special")->where("id", $id)->update(["$name" => $status]);
            }
            if ($ture) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }


    /**
     * [众筹商品列表规格图片添加]
     * 郭杨
     */
    public function crowd_addphoto(Request $request)
    {
        if ($request->isPost()) {
            $id = $request -> only(["id"])["id"];
            $imag = $request-> file("file") -> move(ROOT_PATH . 'public' . DS . 'uploads');
            $images = str_replace("\\", "/", $imag->getSaveName());

            if(!empty($id)){
                $bool = db("crowd_special")->where("id", $id)->update(["images" => $images]);
            }
             if ($bool) {
                 return ajax_success('添加图片成功!');
             } else {
                 return ajax_error('添加图片失败');
             }
        }
    }


    /**
     * [商品列表搜索]
     * 郭杨
     */
    public function crowd_search()
    {
        $goods_number = input('project_name');
        $store_id = Session::get("store_id");
        if(!empty($goods_number)){
            $condition =" `goods_number` like '%{$goods_number}%' or `project_name` like '%{$goods_number}%'";
               $crowd_data = db("crowd_goods")
                    ->where($condition)
                    ->where("store_id", $store_id)
                    ->select();
            } else {
                $crowd_data = db("crowd_goods")->where("store_id", $store_id)->select();
            }
      
        if(!empty($crowd_data)){
            foreach ($crowd_data as $key => $value) {
                $min_price[$key] = db("crowd_special")->where("goods_id", $crowd_data[$key]['id'])->min("cost");//金额
                $min_stock[$key] = db("crowd_special")->where("goods_id", $crowd_data[$key]['id'])->min("stock");//数量
                $crowd_data[$key]["min_price"] = sprintf("%.2f", $min_price[$key]);
                $crowd_data[$key]["min_stock"] = $min_stock[$key];
            }
        }   

        $url = 'admin/Goods/crowd_index';
        $pag_number = 20;
        $crowd = paging_data($crowd_data,$url,$pag_number);     
        return view("crowd_index",["crowd"=>$crowd]);

    }






    /**
     * [专属定制商品显示]
     * 郭杨
     */    
    public function exclusive_index(){     
        return view("exclusive_index");
    }



    /**
     * [专属定制商品添加]
     * 郭杨
     */    
    public function exclusive_add(){     
        return view("exclusive_add");
    }


    /**
     * [专属定制商品编辑]
     * 郭杨
     */    
    public function exclusive_edit(){     
        return view("exclusive_edit");
    }
}