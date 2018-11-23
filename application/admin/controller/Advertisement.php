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
use think\paginator\driver\Bootstrap;

class Advertisement extends Controller{

    /**
     * [活动管理显示]
     * 郭杨
     */
    public function index(){
        
        $accessories = db("teahost")->select();        
        foreach($accessories as $key => $value){
            if($value["pid"]){
                $res = db("goods_type")->where("id",$value['pid'])->field("name")->find();
                $accessories[$key]["named"] = $res["name"];
            }
        }
        $all_idents =$accessories ;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 5;//每页5行记录
        $showdata = array_slice($all_idents, ($curPage - 1)*$listRow, $listRow,true);// 数组中根据条件取出一段值，并返回
        $accessories = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path'     => url('admin/Advertisement/index'),//这里根据需要修改url
            'query'    =>  [],
            'fragment' => '',
        ]);
        $accessories->appends($_GET);
        $this->assign('access', $accessories->render());
        return view("accessories_business_advertising",["accessories"=>$accessories]);
    }



    /**
     * [活动管理添加]
     * 郭杨
     *
     */
    public function accessories_business_add($pid=0){ 
        $teahost_name = [];
        if ($pid == 0) {
            $teahost_name = getSelectList("goods_type");
        }  
       
        return view("accessories_business_add",["teahost_name"=>$teahost_name]);
    }



    /**
     * [活动分类分组入库]
     * 郭杨
     */
    public function accessories_business_save(Request $request){
        if($request->isPost()){
            $data = $request->param();
           // dump($data);
        
            $data["start_time"] = strtotime($data["start_time"]);
            $addressed = [$data["address_city1"],$data["address_city2"],$data["address_city3"],$data["address_street"]];
            $data["addressed"] = implode(",",$addressed);
            $data["address"] = implode("",$addressed);

            foreach( $data as $k=>$v){
                if(in_array($v,$addressed))
                {
                    unset($data[$k]);
                }
            }
          
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
     * 郭杨
     */
    public function accessories_business_edit($pid=0,$id){

        $teahost = db("teahost")->where("id",$id)->select();
        
        $teahost_names = [];
        if ($pid == 0) {
            $teahost_names = getSelectList("goods_type");
        }
        
        //halt($teahost_names);
        $city_address = explode(",",$teahost[0]["addressed"]);  
        return view("accessories_business_edit",["teahost"=>$teahost,"teahost_names"=>$teahost_names,"city_address"=>$city_address]);
    }


    /**
     * [活动分类分组更新]
     * 郭杨
     * @param Request $request
     * @param $id
     */
    public function accessories_business_updata(Request $request){
        if($request->isPost()) {
            $data = $request->param();
            //halt($data);
            $show_images = $request->file("classify_image");
            //halt($data);
            if ($show_images) {
                $show_images = $request->file("classify_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["classify_image"] = str_replace("\\","/",$show_images->getSaveName());
            }
            $data["start_time"] = strtotime($data["start_time"]);
            $addressed = [$data["address_city1"],$data["address_city2"],$data["address_city3"],$data["address_street"]];
            $data["addressed"] = implode(",",$addressed);
            $data["address"] = implode("",$addressed);

            foreach($data as $k=>$v){
                if(in_array($v,$addressed))
                {
                    unset($data[$k]);
                }
            }

            $bool = db("teahost")->where('id', $request->only(["id"])["id"])->update($data);
            if ($bool) {
                $this->success("编辑成功", url("admin/Advertisement/index"));
            } else {
                $this->error("编辑失败", url("admin/Advertisement/accessories_business_add"));
            }
        }
    }


    /**
     * [活动分类分组删除]
     * 郭杨
     */
    public function accessories_business_del($id){
        $bool = db("teahost")->where("id",$id)->delete();
        if($bool){
            $this->success("删除成功",url("admin/Advertisement/index"));
        }else{
            $this->error("删除失败",url("admin/Advertisement/accessories_business_edit"));
        }
    }


    /**
     * [活动分类分组ajax显示]
     * 郭杨
     * @param int $pid
     * 
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
     * 郭杨
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
     * 郭杨
     */
   public function accessories_business_label(Request $request){
        if($request->isPost()) {
            $status = $request->only(["label"])["label"];
            if($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("teahost")->where("id", $id)->update(["label" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Advertisement/index"));
                } else {
                    $this->error("修改失败", url("admin/Advertisement/index"));
                }
            }
            if($status == 1){
                $id = $request->only(["id"])["id"];
                $bool = db("teahost")->where("id", $id)->update(["label" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Advertisement/index"));
                } else {
                    $this->error("修改失败", url("admin/Advertisement/index"));
                }
            }
        }
    }



}

