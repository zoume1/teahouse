<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/21
 * Time: 15:23
 */

namespace app\admin\controller;
use think\Controller;
use think\Request;

class Agency extends Controller{


    /**
     * 代理
     * 陈绪
     */
    public function index(){
        $agency = db("agency")->paginate(5);
        return view("agency_index",['agency'=>$agency]);
    }



    /**
     * 代理添加页面
     * 陈绪
     */
    public function add(){
        return view("agency_add");
    }




    /**
     * 代理入库
     * 陈绪
     */
    public function save(Request $request){
        $agency = $request->param();
        $images = $request->file("images")->move(ROOT_PATH . 'public' . DS . 'uploads');
        $agency["images"] = str_replace("\\", "/", $images->getSaveName());
        $bool = db("agency")->insert($agency);
        if($bool){
            $this->redirect(url("admin/Agency/index"));
        }else{
            $this->error("添加失败",url("admin/Agency/add"));
        }

    }


    /**
     * 代理编辑
     * 陈绪
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function edit($id){
        $agency = db("agency")->where("id",$id)->select();
        return view("agency_edit",["agency"=>$agency]);

    }



    /**
     * 代理编辑更新
     * 陈绪
     */
    public function updata(Request $request){
        $id = $request->only(['id'])['id'];
        $agency = $request->param();
        $images = $request->file("images")->move(ROOT_PATH . 'public' . DS . 'uploads');
        $agency["images"] = str_replace("\\", "/", $images->getSaveName());
        unset($agency['id']);
        $bool = db("agency")->where('id',$id)->update($agency);
        if($bool){
            $this->redirect(url("admin/Agency/index"));
        }else{
            $this->error("添加失败",url("admin/Agency/edit"));
        }

    }




    /**
     * 代理删除
     * 陈绪
     */
    public function del($id){
        $images = db("agency")->where("id",$id)->field("images")->find();
        $bool = db("agency")->where("id",$id)->delete();
        if($bool){
            unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $images['images']);
            $this->redirect(url("admin/Agency/index"));
        }

    }





}