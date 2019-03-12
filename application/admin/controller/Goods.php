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

class Goods extends Controller
{


    /**
     * [商品列表显示]
     * GY
     */
    public function index(Request $request)
    {
        $goods = db("goods")->order("id desc")->select();
        $goods_list = getSelectList("wares");
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
     * [商品列表添加组]
     * GY
     */
    public function add($pid = 0)
    {
        $goods_list = [];
        if ($pid == 0) {
            $goods_list = getSelectList("wares");
        }
        $scope = db("member_grade")->field("member_grade_name")->select();

        return view("goods_add", ["goods_list" => $goods_list,"scope"=>$scope]);
    }



    /**
     * [商品列表组保存]
     * GY
     * 
     */
    public function save(Request $request)
    {
        
        if ($request->isPost()) {
            $goods_data = $request->param();           
            $show_images = $request->file("goods_show_images");
            $imgs = $request->file("imgs");
            $list = [];
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
            
            
            if(empty($goods_data["num"][1]) && empty($goods_data["unit"][0])){ //存空
                
                $goods_data["num"] = array();
                $goods_data["unit"] = array();
            } else {
                $goods_data["num"] = implode(",",$goods_data["num"]);
                $goods_data["unit"] = implode(",",$goods_data["unit"]);
            }
            
            if ($goods_data["goods_standard"] == "0") {
                $bool = db("goods")->insert($goods_data);
                if ($bool) {
                    $this->success("添加成功", url("admin/Goods/index"));
                } else {
                    $this->success("添加失败", url('admin/Goods/add'));
                }
            }
            if ($goods_data["goods_standard"] == "1") {
                $goods_special = [];
                $goods_special["goods_name"] = $goods_data["goods_name"];
                $goods_special["produce"] = $goods_data["produce"];
                $goods_special["brand"] = $goods_data["brand"];
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
                            $num[] = implode(",",$goods_data[$kn]);
                        }
                        if(substr($kn,strrpos($kn,"_")+1) == "unit"){
                            $unit[] = implode(",",$goods_data[$kn]);
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
                                    $values[$k]["status"] = $status[$k];
                                    $values[$k]["save"] = $save[$k];
                                    $values[$k]["cost"] = $cost[$k];
                                    $values[$k]["line"] = $line[$k];
                                    $values[$k]["num"] = $num[$k];
                                    $values[$k]["unit"] = $unit[$k];
                                    $values[$k]["images"] = $tab;
                                    $values[$k]["goods_id"] = $goods_id;
                                }
                            }
                        }
                    }
                }
               halt($values);
                foreach ($values as $kz => $vw) {
                    $rest = db('special')->insert($vw);
                }
                if ($rest) {
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
        $goods = db("goods")->where("id", $id)->select();
        $scope = db("member_grade")->field("member_grade_name")->select();
        $goods_standard = db("special")->where("goods_id", $id)->select();
        foreach ($goods as $key => $value) {
            if(!empty($goods[$key]["goods_show_images"])){
            $goods[$key]["goods_show_images"] = explode(',', $goods[$key]["goods_show_images"]);
            $goods[$key]["scope"] = explode(',', $goods[$key]["scope"]);
        }
     }
        foreach ($goods_standard as $k => $v) {
            $goods_standard[$k]["title"] = explode('_', $v["name"]);
            $res = explode(',', $v["lv1"]);
        }
        $goods_list = getSelectList("wares");
        $restel = $goods[0]["goods_standard"];
        if ($restel == 0) {
            return view("goods_edit", ["goods" => $goods, "goods_list" => $goods_list,"scope" => $scope]);
        } else {
            return view("goods_edit", ["goods" => $goods, "goods_list" => $goods_list, "res" => $res, "goods_standard" => $goods_standard,"scope" => $scope]);
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
                $res = Db::name('goods')->where("id", $tid['pid'])->update(['goods_show_images' => $new_imgs_url]);
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
            $id = $request->only(["id"])["id"];
            $goods_data = $request->param();
            $show_images = $request->file("goods_show_images");
            if(!empty($goods_data["scope"])){
                $goods_data["scope"] = implode(',', $goods_data["scope"]);
            } else {
                $goods_data["scope"] = "";
            }
            
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
                    $goods_data["goods_show_images"] = NULL;
                    $goods_data["goods_show_image"] = NULL;
                }
            } 
              
            $bool = db("goods")->where("id", $id)->update($goods_data);
            if ($bool) {
                $this->success("更新成功", url("admin/Goods/index"));
            } else {
                $this->success("更新失败", url('admin/Goods/index'));
            }

        }

    }


    /**
     * [商品列表组首页推荐]
     * 陈绪
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
            $list = Db::name('goods')->where($where)->delete();
            if ($list !== false) {
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
            $id = $request->only(["id"])["id"];
            $value = $request->only(["value"])["value"];
            $key = $request->only(["key"])["key"];
            $valuet = db("special")->where("id", $id)->update([$key => $value]);

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

            if (!empty($id)) {
                $ture = db("special")->where("id", $id)->update(["status" => $status]);
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

        if((empty($goods_number)) && (!empty($pid))){
            $goods = db("goods")
                    ->where("pid",$pid)
                    ->order("id desc")
                    ->select();
        } else if ((!empty($goods_number)) && (empty($pid))) {
            $goods = db("goods")
                    ->where("goods_number",$goods_number)
                    ->order("id desc")
                    ->select();
        } else if ((!empty($goods_number)) && (!empty($pid))) {
            $goods = db("goods")
            ->where("goods_number",$goods_number)
            ->where("pid",$pid)
            ->order("id desc")
            ->select();
        } else {
            $goods = db("goods")->order("id desc")->select();
        }
      
        $goods_list = getSelectList("wares");
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
     * [众筹商品显示]
     * 郭杨
     */    
    public function crowd_index(){     
        return view("crowd_index");
    }



    /**
     * [众筹商品添加]
     * 郭杨
     */    
    public function crowd_add(){     
        return view("crowd_add");
    }


    /**
     * [众筹商品编辑]
     * 郭杨
     */    
    public function crowd_edit(){     
        return view("crowd_edit");
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