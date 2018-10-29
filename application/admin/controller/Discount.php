<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/26
 * Time: 11:30
 */
namespace app\admin\controller;
use think\Controller;
use think\Request;

class Discount extends Controller{

    public $status = [
        1=>"未使用",
        2=>"已使用",
        3=>"已失效"
    ];



    /**
     * 优惠券
     * 陈绪
     */
    public function index(){
        $discounts = db("discounts")->select();
        return view("discount_index",['discounts'=>$discounts]);

    }



    /**
     * 添加优惠券
     * 陈绪
     */
    public function add(){
        return view("discount_add");
    }



    /**
     * 优惠券入库
     * 陈绪
     */
    public function save(Request $request){
        $data = $request->param();
        $time = $request->only(['over_time'])['over_time'];
        $discounts_valid_images = $request->file('discounts_valid_images')->move(ROOT_PATH . 'public' . DS . 'uploads');
        $data["discounts_valid_images"] = str_replace("\\","/",$discounts_valid_images->getSaveName());
        $discounts_failure_images = $request->file('discounts_failure_images')->move(ROOT_PATH . 'public' . DS . 'uploads');
        $data["discounts_failure_images"] = str_replace("\\","/",$discounts_failure_images->getSaveName());
        $data['start_time'] = time();
        $data['over_time'] = strtotime("+1 $time");
        $data["discounts_name"] = "邀请码优惠券";
        $bool = db("discounts")->insert($data);
        if($bool){
            $this->success("添加成功",url("admin/Discount/index"));
        }else{
            $this->success("添加失败",url("admin/Discount/index"));
        }

    }


    /**
     * [优惠券更新]
     * 陈绪
     */
    public function edit($id){
        $discounts = db("discounts")->where("id",$id)->select();
        return view("discount_edit",["discounts"=>$discounts]);
    }


    /**
     * [优惠券更新]
     * 陈绪
     */
    public function updata(Request $request){
        $id = $request->only(['id'])['id'];
        $discounts = db("discounts")->where("id",$id)->find();
        $data = $request->param();
        $time = $request->only(['over_time'])['over_time'];
        $discounts_valid_images = $request->file('discounts_valid_images')->move(ROOT_PATH . 'public' . DS . 'uploads');
        $data["discounts_valid_images"] = str_replace("\\","/",$discounts_valid_images->getSaveName());
        $discounts_failure_images = $request->file('discounts_failure_images')->move(ROOT_PATH . 'public' . DS . 'uploads');
        $data["discounts_failure_images"] = str_replace("\\","/",$discounts_failure_images->getSaveName());
        $data['start_time'] = time();
        $data['over_time'] = strtotime("+1 $time");
        $data["discounts_name"] = "邀请码优惠券";
        $bool = db("discounts")->where("id",$id)->update($data);
        if($bool){
            unlink(ROOT_PATH . 'public' . DS . 'uploads/'.$discounts['discounts_valid_images']);
            unlink(ROOT_PATH . 'public' . DS . 'uploads/'.$discounts['discounts_failure_images']);
            $this->success("更新成功",url("admin/Discount/index"));
        }else{
            $this->error("更新成功",url("admin/Discount/index"));
        }


    }




    /**
     * [优惠券删除]
     * 陈绪
     */
    public function del($id){
        $discounts = db("discounts")->where("id",$id)->find();
        $bool = db("discounts")->where("id",$id)->delete();
        if($bool){
            unlink(ROOT_PATH . 'public' . DS . 'uploads/'.$discounts['discounts_valid_images']);
            unlink(ROOT_PATH . 'public' . DS . 'uploads/'.$discounts['discounts_failure_images']);
            $this->success("删除成功",url("admin/Discount/index"));
        }else{
            $this->error("删除失败",url("admin/Discount/index"));
        }
    }

}