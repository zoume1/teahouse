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
use think\paginator\driver\Bootstrap;
use think\Session;
use think\View;


class  Material extends  Controller{


    /**
     **************GY*******************
     * @param Request $request
     * Notes:视频直播
     **************************************
     * @return \think\response\View
     */
    public function direct_seeding(){
        return view("direct_seeding");
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:视频直播添加编辑设备
     **************************************
     */
    public  function  direct_seeding_add(){
        return  view("direct_seeding_add");
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:直播分类
     **************************************
     */
    public function direct_seeding_classification(){
        $store_id = Session::get("store_id");
        $direct_data = Db::name("direct_seeding") 
                ->where("store_id",$store_id)
                ->select();
        $url = 'admin/Material/direct_seeding_classification';
        $pag_number = 20;
        $data = paging_data($direct_data,$url,$pag_number);
        return view("direct_seeding_classification",["data"=>$data]);
    }

    /**
     **************GY******************* 
     * @param Request $request
     * Notes:直播分类添加编辑
     **************************************
     * @return \think\response\View
     */
    public function direct_seeding_classification_add(Request $request){
        if($request->isPost()){

        }
        return view("direct_seeding_classification_add");
        
    }


    /**
     **************GY******************* 
     * @param Request $request
     * Notes:直播分类保存
     **************************************
     * @return \think\response\View
     */
    public function direct_seeding_classification_save(Request $request){
        if($request->isPost()){
            $store_id = Session::get("store_id");
            $data = input();
            $data['status'] = isset($data['status'])?$data['status']:0;
            $data['store_id'] = $store_id;
            $show_images = $request->file("icon_image");
            if ($show_images) {
                $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["icon_image"] = str_replace("\\", "/", $show_images->getSaveName());
            }
            $bool = Db::name("direct_seeding")->insert($data);
            if ($bool) {
                $this->success("添加成功", url("admin/Material/direct_seeding_classification"));
            } else {
                $this->error("添加失败", url("admin/Material/direct_seeding_classification"));
            }
        }
    
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:防伪溯源
     **************************************
     * @return \think\response\View
     */
    public function anti_fake(){
        return view("anti_fake");
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:温湿传感
     **************************************
     * @return \think\response\View
     */
    public function interaction_index(){
        return view("interaction_index");
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:温湿传感添加编辑
     **************************************
     * @return \think\response\View
     */
    public function interaction_add(){
        return view("interaction_add");
    }

    
 }