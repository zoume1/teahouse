<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/17/025
 * Time: 14:13
 */

namespace app\admin\controller;
use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;

class Advertisement extends Controller{

    /**
     * [活动管理显示]
     * 陈绪
     */
    public function index(){
        $accessories = db("teahost")->paginate(5);
        return view("accessories_business_advertising",["accessories"=>$accessories]);
    }



    /**
     * [活动管理添加]
     * 陈绪
     *
     */
    public function accessories_business_add($pid = 0){
        $list = [];
        // if($pid == 0) {
        //     $list = postSelectList("teahost");
        // }
        
        return view("accessories_business_add",["list"=>$list]);
    }



    /**
     * [活动分类分组入库]
     * 陈绪
     */
    public function save(Request $request){
        if($request->isPost()){
            $data = $request->param();
            unset($data["taglocation"]);
            unset($data["tags"]);
            $show_images = $request->file("classify_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
            $data["classify_image"] = str_replace("\\","/",$show_images->getSaveName());
            $bool = db("teahost")->insert($data);
            if($bool){
                $this->success("添加成功",url("admin/Advertisement/index"));
            }else{
                $this->error("添加失败",url("admin/Advertisement/accessories_business_add"));
            }
        }
    }



    /**
     * [活动分类分组修改]
     * [陈绪]
     */
    public function accessories_business_edit($id){

        $teahost = db("teahost")->where("id",$id)->select();
        $teahost_name = db("teahost")->field("class_name,id")->select();
        dump($teahost_name);
        
        return view("accessories_business_edit",["teahost"=>$teahost,"teahost_name"=>$teahost]);
    }


    /**
     * [活动分类分组更新]
     * [陈绪]
     * @param Request $request
     * @param $id
     */
    public function updata(Request $request){
        if($request->isPost()) {
            $data = $request->only(["name", "status", "sort_number", "pid","rank"]);
            $show_images = $request->file("type_images")->move(ROOT_PATH . 'public' . DS . 'uploads');
            $data["type_images"] = str_replace("\\", "/", $show_images->getSaveName());
            $bool = db("goods_type")->where('id', $request->only(["id"])["id"])->update($data);
            if ($bool) {
                $this->success("编辑成功", url("admin/Advertisement/index"));
            } else {
                $this->error("编辑失败", url("admin/Advertisement/accessories_business_add"));
            }
        }
    }


    /**
     * [活动分类分组删除]
     * [陈绪]
     */
    /*public function del($id){
        $bool = db("goods_type")->where("id",$id)->delete();
        if($bool){
            $this->success("删除成功",url("admin/Category/index"));
        }else{
            $this->error("删除失败",url("admin/Category/edit"));
        }
    }*/


    /**
     * [活动分类分组ajax显示]
     * 陈绪
     * @param int $pid
     * @return
     */
/*    public function ajax_add($pid = 0){
        $goods_list = [];
        if($pid == 0){
            $goods_list = getSelectList("goods_type");
        }
        return ajax_success("获取成功",$goods_list);
    }*/

    /**
     * [活动分类分组批量删除]
     * 陈绪
     * @param int $pid
     * @return
     */
/*    public function dels(Request $request){
        if($request->isPost()){
            $id =$_POST['id'];
            if(is_array($id)){
                $where ='id in('.implode(',',$id).')';
            }else{
                $where ='id='.$id;
            }
            $list =  Db::name('goods_type')->where($where)->delete();
            if($list!==false)
            {
                return ajax_success('成功删除!',['status'=>1]);
            }else{
                return ajax_error('删除失败',['status'=>0]);
            }
        }
    }*/




    /**
     * [活动分类分组状态修改]
     * 陈绪
     */
/*    public function status(Request $request){
        if($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods_type")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Category/index"));
                } else {
                    $this->error("修改失败", url("admin/Category/index"));
                }
            }
            if($status == 1){
                $id = $request->only(["id"])["id"];
                $bool = db("goods_type")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Category/index"));
                } else {
                    $this->error("修改失败", url("admin/Category/index"));
                }
            }
        }
    }*/



}

