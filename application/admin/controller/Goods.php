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

class Goods extends Controller{

    public $goods_status = [0,1];
    /**
     * [商品列表]
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     * 陈绪
     */
    public function index(Request $request){
        $datemins = $request->param("datemin");
        $datemaxs = $request->param("datemax");
        $search_keys = $request->param("search_key");
        $search_bts = $request->param("search_bt");
        $datemin = isset($datemins) ? $datemins : false;
        $datemax = isset($datemaxs) ? $datemaxs : false;
        $search_key = isset($search_keys) ? $search_keys : '%';
        $search_bt = isset($search_bts) ? $search_bts : false;
        if($request->isPost()) {
            if ($datemin && $datemax) {
               $good = db("goods")->where('create_time','>',strtotime($datemin))->where('create_time','<',strtotime($datemax))->paginate(5);
            }

            if ($search_key) {
                $good = db("goods")->where("goods_name","like","%".$search_key."%")->paginate(5);

            }else {
                $good = db("goods")->paginate(5);
            }

            return view("goods_index", [
                'good' => $good,
                'search_key' => $search_key,
                'datemax' => $datemax,
                'datemin' => $datemin
            ]);
        }else{
            $goods = db("goods")->paginate(10);
            return view("goods_index",["goods"=>$goods]);
        }

    }

    public function add($pid=0){
        $goods_list = [];
        if($pid == 0){
            $goods_list = getSelectList("goods_type");
        }
        return view("goods_add",["goods_list"=>$goods_list]);
    }



    /**
     * [商品添加]
     * 陈绪
     * @param Request $request
     */
    public function save(Request $request)
    {
        if ($request->isPost()) {
            $goods_data = $request->param();
            $goods_standard_name = implode(",",$goods_data["goods_standard_name"]);
            $goods_standard_value = implode(",",$goods_data["goods_standard_value"]);
            $goods_data["goods_standard_name"] = $goods_standard_name;
            $goods_data["goods_standard_value"] = $goods_standard_value;
            $goods_delivery = implode(",",$goods_data["goods_delivery"]);
            $goods_data["goods_delivery"] = $goods_delivery;
            //图片添加
            $show_images = $request->file("goods_show_images");
            if(!empty($show_images)) {
                $show_image = $show_images->move(ROOT_PATH . 'public' . DS . 'uploads');
                $goods_data["goods_show_images"] = str_replace("\\", "/", $show_image->getSaveName());
            }

            $bool = db("goods")->insert($goods_data);
            if ($bool) {
                //取出图片在存到数据库
                $goods_images = [];
                $goodsid = db("goods")->getLastInsID();
                $file = request()->file('goods_images');
                if(!empty($file)) {
                    foreach ($file as $key => $value) {
                        $info = $value->move(ROOT_PATH . 'public' . DS . 'upload');
                        $goods_url = str_replace("\\", "/", $info->getSaveName());
                        $goods_images[] = ["goods_images" => $goods_url, "goods_id" => $goodsid];
                    }
                }
                $booldata = model("goods_images")->saveAll($goods_images);
                if ($booldata) {
                    $this->redirect(url('admin/Goods/index'));
                } else {
                    $this->redirect(url('admin/Goods/add'));
                }
            }
        }
    }


    /**
     * [商品修改]
     * 陈绪
     */
    public function edit(Request $r,$id){
        $goods = db("goods")->where("id",$id)->select();
        $goods_type = db("goods_type")->where("id",$goods[0]["goods_type_id"])->field("name,id")->select();
        $goods_images = db("goods_images")->where("goods_id",$id)->select();
        return view("goods_edit",["goods"=>$goods,"goods_type"=>$goods_type,"goods_images"=>$goods_images]);
    }


    /**
     * [图片删除]
     * 陈绪
     */
    public function images(Request $request){
        if($request->isPost()){
            $id = $request->only(['id'])['id'];
            $image_url = db("goods_images")->where("id",$id)->field("goods_images,goods_quality_img")->find();
            if($image_url['goods_images'] != null){
                unlink(ROOT_PATH . 'public' . DS . 'upload/'.$image_url['goods_images']);
            }

            if($image_url['goods_quality_img'] != null){
                unlink(ROOT_PATH . 'public' . DS . 'upload/'.$image_url['goods_quality_img']);
            }
            $bool = db("goods_images")->where("id",$id)->delete();
            if($bool){
                return ajax_success("删除成功");
            }else{
                return ajax_error("删除失败");
            }
        }
    }


    /**
     * [商品删除]
     * 陈绪
     */
    public function del(Request $request){
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $image_url = db("goods_images")->where("goods_id", $id)->field("goods_images,goods_quality_img,id")->select();
            $goods_images = db("goods")->where("id", $id)->select();
            unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $goods_images[0]['goods_show_images']);
            unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $goods_images[0]['goods_parts_big_img']);
            unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $goods_images[0]['goods_spec_img']);
            unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $goods_images[0]['goods_parts_img']);
            foreach ($image_url as $value) {
                if ($value['goods_images'] != null) {
                    unlink(ROOT_PATH . 'public' . DS . 'upload/' . $value['goods_images']);
                }
                if ($value['goods_quality_img'] != null) {
                    unlink(ROOT_PATH . 'public' . DS . 'upload/' . $value['goods_quality_img']);
                }
                db("goods_images")->where("id", $value['id'])->delete();
            }
            $bool = db("goods")->where("id", $id)->delete();
            if ($bool) {
                return ajax_error("删除成功");
            } else {
                return ajax_error("删除失败");
            }
        }
    }


    /**
     * [产品更新]
     * 陈绪
     * @param Request $request
     */
    public function updata(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $goods_data = $request->only([
                "goods_name",
                "sort_number",
                "goods_type_id",
                "goods_new_money",
                "goods_parts",
                "goods_status",
                "goods_bottom_money",
                "goods_num"
            ]);
            $sign = $request->only(["goods_sign"])["goods_sign"];
            $goods_data["goods_sign"] = implode(",", $sign);
            $goods_data["goods_number"] = "GB" . date("YmdHis") . uniqid() . $request->only(["goods_number"])["goods_number"];
            //图片添加
            $show_images = $request->file("goods_show_images");
            $show_image = $show_images->move(ROOT_PATH . 'public' . DS . 'uploads');
            $goods_data["goods_show_images"] = str_replace("\\", "/", $show_image->getSaveName());

            $goods_parts_big_img = $request->file("goods_parts_big_img")->move(ROOT_PATH . 'public' . DS . 'uploads');
            $goods_data["goods_parts_big_img"] = str_replace("\\", "/", $goods_parts_big_img->getSaveName());

            $goods_spec_img = $request->file("goods_spec_img")->move(ROOT_PATH . 'public' . DS . 'uploads');
            $goods_data["goods_spec_img"] = str_replace("\\", "/", $goods_spec_img->getSaveName());

            $goods_parts_img = $request->file("goods_parts_img")->move(ROOT_PATH . 'public' . DS . 'uploads');
            $goods_data["goods_parts_img"] = str_replace("\\", "/", $goods_parts_img->getSaveName());

            $goods_data["create_time"] = time();
            $bool = db("goods")->where("id", $id)->update($goods_data);
            if($bool){
                //取出图片在存到数据库
                $goods_images = [];
                $goodsid = db("goods")->getLastInsID();
                $file = request()->file('goods_images');
                foreach ($file as $key => $value) {
                    $info = $value->move(ROOT_PATH . 'public' . DS . 'upload');
                    $goods_url = str_replace("\\", "/", $info->getSaveName());
                    $goods_images[] = ["goods_images" => $goods_url, "goods_id" => $id];
                }

                $goods_quality_img = $request->file("goods_quality_img");
                foreach ($goods_quality_img as $val) {
                    $goods_quality_imgs = $val->move(ROOT_PATH . 'public' . DS . 'upload');
                    $goods_quality_imgs_url = str_replace("\\", "/", $goods_quality_imgs->getSaveName());
                    $goods_images[] = ["goods_quality_img" => $goods_quality_imgs_url, "goods_id" => $id];
                }
                $booldata = model("goods_images")->saveAll($goods_images);
                if ($booldata) {
                    $this->redirect(url('admin/Goods/index'));
                } else {
                    $this->redirect(url('admin/Goods/add'));
                }
            }
        }

    }


    /**
     * [商品状态]
     * 陈绪
     */
    public function status(Request $request){

        if ($request->isPost()){
            $goods_id = $request->only(['id'])['id'];
            $goods_status["goods_status"] = $this->goods_status[0];
            $bool = db("goods")->where("id",$goods_id)->update($goods_status);
            if ($bool){
                return ajax_success("更新成功");
            }else{
                return ajax_error("更新失败");
            }
        }

    }


    /**
     * [商品上架]
     * 陈绪
     * @param Request $request
     * @return
     */
    public function putaway(Request $request){
        if ($request->isPost()){
            $goods_id = $request->only(['id'])['id'];
            $goods_status["goods_status"] = $this->goods_status[1];
            $bool = db("goods")->where("id",$goods_id)->update($goods_status);
            if ($bool){
                return ajax_success("更新成功");
            }else{
                return ajax_error("更新失败");
            }
        }
    }



    /**
     * [商品批量删除]
     * 陈绪
     */
    public function batches(Request $request){
        if($request->isPost()) {
            $id = $request->only(["ids"])["ids"];
            foreach ($id as $value) {
                $goods_url = db("goods")->where("id", $value)->find();
                $goods_images = db("goods_images")->where("goods_id", $value)->select();
                if($goods_url['goods_show_images'] != null){
                    unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $goods_url['goods_show_images']);
                    unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $goods_url['goods_parts_big_img']);
                    unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $goods_url['goods_spec_img']);
                    unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $goods_url['goods_parts_img']);
                }
                foreach ($goods_images as $val) {
                    if ($val['goods_images'] != null) {
                        unlink(ROOT_PATH . 'public' . DS . 'upload/' . $val['goods_images']);
                    }
                    if ($val['goods_quality_img'] != null) {
                        unlink(ROOT_PATH . 'public' . DS . 'upload/' . $val['goods_quality_img']);
                    }
                    GoodsImages::destroy($val['id']);
                }
                $bool = Good::destroy($value);
            }
            if ($bool) {
                return ajax_success("删除成功");
            } else {
                return ajax_error("删除失败");
            }

        }
    }




    /**
     * 商品付费详情
     * 陈绪
     */
    public function pay(){

        return view("goods_pay");

    }




    /**
     * 商品确认付费
     * 陈绪
     */
    public function affirm(){

        return view("affirm_pay");

    }



}