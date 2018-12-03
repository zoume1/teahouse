<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/25/025
 * Time: 14:13
 */

namespace app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;

class GoodsType extends Controller{


    /**
     * [商品分类显示]
     * 陈绪
     */
    public function index(){
        $category = db("category")->where("status","<>","0")->paginate(10);
        return view("goods_type_index",["category"=>$category]);
    }



    /**
     * [商品分类添加]
     * 陈绪
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function add($pid = 0){
        $goods_cate = [];
        $goods_list = [];
        if($pid == 0){
            $goods_list = getSelectList("category");
        }else{
            $goods_cate = db("category")->where("id",$pid)->field()->select();
        }
        return view("goods_type_add",["goods_list"=>$goods_list,"goods_cate"=>$goods_cate]);
    }



    /**
     * [商品分组入库]
     * 陈绪
     */
    public function save(Request $request){
        if($request->isPost()){
            $data = $request->param();
            unset($data["taglocation"]);
            unset($data["tags"]);
            $show_images = $request->file("type_images");
            if(!empty($show_images)){
                $type_images = $show_images->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["type_images"] = str_replace("\\","/",$type_images->getSaveName());
            }
            $bool = db("category")->insert($data);
            if($bool){
                $this->success("添加成功",url("admin/GoodsType/index"));
            }else{
                $this->error("添加失败",url("admin/GoodsType/add"));
            }
        }
    }



    /**
     * [商品分组修改]
     * [陈绪]
     */
    public function edit($pid=0,$id){
        $category = db("category")->where("id",$id)->select();
        $category_name = db("category")->where("id",$category[0]["pid"])->field("name,id")->select();
        if($pid == 0){
            $goods_list = getSelectList("category");
        }
        return view("goods_type_edit",["category"=>$category,"category_name"=>$category_name,"goods_lists"=>$goods_list]);
    }



    /**
     * [商品分组更新]
     * [陈绪]
     * @param Request $request
     * @param $id
     */
    public function updata(Request $request){
        if($request->isPost()) {
            $data = $request->only(["name", "status", "sort_number", "pid"]);
            $show_images = $request->file("type_images");
            if(!empty($show_images)){
                $type_images = $show_images->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["type_images"] = str_replace("\\","/",$type_images->getSaveName());
            }
            $bool = db("category")->where('id', $request->only(["id"])["id"])->update($data);
            if ($bool) {
                $this->success("编辑成功", url("admin/GoodsType/index"));
            } else {
                $this->error("编辑失败", url("admin/GoodsType/edit"));
            }
        }
    }


    /**
     * [商品分组删除]
     * [陈绪]
     */
    public function del($id){
        $bool = db("category")->where("id",$id)->delete();
        if($bool){
            $this->success("删除成功",url("admin/GoodsType/index"));
        }else{
            $this->error("删除失败",url("admin/GoodsType/edit"));
        }
    }


    /**
     * [商品分组ajax显示]
     * 陈绪
     * @param int $pid
     * @return
     */
    public function ajax_add($pid = 0){
        $goods_list = [];
        if($pid == 0){
            $goods_list = getSelectList("category");
        }
        return ajax_success("获取成功",$goods_list);
    }




    /**
     * 图片删除
     * 陈绪
     * @param Request $request
     * @return string|void
     */
    public function images(Request $request){
        $id = $request->only(['id'])['id'];
        $images = db("category")->where("id",$id)->field("type_images")->find();
        $bool = db("category")->where("id",$id)->update(['type_images'=>null]);
        unlink(ROOT_PATH . 'public' . DS . 'uploads/'.$images['type_images']);
        if($bool){
            return ajax_success("更新成功");
        }
    }



    /**
     * 商品分组状态修改
     * 陈绪
     */
    public function status(Request $request){
        if($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("category")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Category/index"));
                } else {
                    $this->error("修改失败", url("admin/GoodsType/index"));
                }
            }
            if($status == 1){
                $id = $request->only(["id"])["id"];
                $bool = db("category")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/GoodsType/index"));
                } else {
                    $this->error("修改失败", url("admin/GoodsType/index"));
                }
            }
        }
    }



}

