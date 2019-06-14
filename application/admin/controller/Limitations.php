<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3 0003
 * Time: 18:21
 */

namespace  app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Session;
use think\Request;
use think\paginator\driver\Bootstrap;

class  Limitations extends  Controller{

    /**
     * [限时限购显示]
     * GY
     */
    public function limitations_index() 
    {
        $store_id = Session::get("store_id");
        $limit = db("limited")
            ->where("store_id","EQ",$store_id)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        
        return view('limitations_index',["limit"=>$limit]);
    }


    /**
     * [限时限购编辑]
     * GY
     */
    public function limitations_edit($id)
    {
        $store_id = Session::get("store_id");
        $limited = db("limited")->where("id", $id)->value('limit_id');
        $limit = db("limit")->where("id",$limited)->select();
        foreach ($limit as $k => $v) {
            $limit[$k]["scope"] = explode(",", $limit[$k]["scope"]);
        }

        $scope = db("member_grade")->where("store_id","EQ",$store_id)->field("member_grade_name")->select();
        return view('limitations_edit', ["limit" => $limit, "scope" => $scope]);

    }

    /**
     * [限时限购编辑]
     * GY
     */
     public function limitations_weave(Request $request)
     {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $limite = db("limited")->where("limit_id", $id)->select();
            if (!empty($limite) && !empty($id)) {
                return ajax_success("获取成功", $limite);
            } else {
                return ajax_error("获取失败");
            }

        }
 
     }


    /**
     * [限时限购添加商品]
     * GY
     */
    public function limitations_add()
    {
        $store_id = Session::get("store_id");
        $scope = db("member_grade")
        ->where("store_id","EQ",$store_id)
        ->field("member_grade_name")
        ->select();
        return view('limitations_add', ["scope" => $scope]);
    }

    /**
     * [限时限购保存商品]
     * GY
     */
     public function limitations_save(Request $request)
     {
        if ($request->isPost()) {
            $data = $request->param();
            $store_id = Session::get("store_id");
            $data["scope"] = implode(",", $data["scope"]);
            $data["stroe_id"] = $store_id;

            if (!empty($data["goods_id"])) {
                foreach ($data["goods_id"] as $key => $value) {
                    $goods[$key] = db("goods")->where("id", $data["goods_id"][$key])->where("store_id","EQ",$store_id)->field("id,goods_number,goods_show_images,goods_name,goods_standard,goods_repertory")->find();
                }
                unset($data["goods_id"]);
            }

            $limit_id = db("limit")->insertGetId($data);
            if (!empty($goods)) {
                foreach ($goods as $key => $value) {
                    $goods[$key]["goods_id"] = $goods[$key]["id"];
                    $goods[$key]["limit_id"] = $limit_id;
                    $goods[$key]["number"] = $data["number"];
                    $goods[$key]["time"] = $data["time"];
                    $goods[$key]["label"] = $data["label"];
                    $goods[$key]["scope"] =  $data["scope"];
                   


                    if ($goods[$key]["goods_standard"] == 1) {
                        $goods[$key]["goods_repertory"] = db("special")->where("goods_id", $goods[$key]["id"])->sum("stock");
                        $goods[$key]["goods_show_images"] = explode(",", $goods[$key]["goods_show_images"])[0];
                    } else {
                        $goods[$key]["goods_show_images"] = explode(",", $goods[$key]["goods_show_images"])[0];
                    }
                    unset($goods[$key]["id"]);
                }

                foreach ($goods as $k => $v) {
                    $rest = db("limited")->insert($v);
                }
            }
            if ($limit_id || $rest) {
                $this->success("添加成功", url("admin/Limitations/limitations_index"));
            } else {
                $this->error("添加失败", url("admin/Limitations/limitations_add"));
            }

        }
     }

     /**
     * [限时限购更新]
     * GY
     */
    public function limitations_update(Request $request)
    {

        if ($request->isPost()) {
            $data = $request->param();
            $data["scope"] = implode(",", $data["scope"]);
            $id = $request->only(["id"])["id"];
            if (!empty($data["goods_id"])) {
                foreach ($data["goods_id"] as $key => $value) {
                    $goodes[$key] = db("goods")->where("id", $data["goods_id"][$key])->field("id,goods_number,goods_show_images,goods_name,goods_standard,goods_repertory")->find();

                }
                unset($data["goods_id"]);
            }
            unset($data["id"]);
            unset($data["goods_number"]);
            $bool = db("limit")->where('id', $id)->update($data);
            $boole = db("limited")->where('limit_id', $id)->update($data);
            if (!empty($goodes)) {
                foreach ($goodes as $key => $value) {
                    $goodes[$key]["goods_id"] = $goodes[$key]["id"];
                    $goodes[$key]["limit_id"] = $request->only(["id"])["id"];
                    $goodes[$key]["number"] = $data["number"];
                    $goodes[$key]["time"] = $data["time"];
                    $goodes[$key]["label"] = $data["label"];
                    $goodes[$key]["scope"] = $data["scope"];

                    if ($goodes[$key]["goods_standard"] == 1) {
                        $goodes[$key]["goods_repertory"] = db("special")->where("goods_id", $goodes[$key]["id"])->sum("stock");
                        $goodes[$key]["goods_show_images"] = explode(",", $goodes[$key]["goods_show_images"])[0];
                    } else {
                        $goodes[$key]["goods_show_images"] = explode(",", $goodes[$key]["goods_show_images"])[0];
                    }
                    unset($goodes[$key]["id"]);
                }
                
                foreach ($goodes as $k => $v) {
                    $rest = db("limited")->insert($v);
                }
            }

            if ($bool || $rest ) {
                $this->success("编辑成功", url("admin/Limitations/limitations_index"));
            } else {
                $this->error("编辑失败", url("admin/Limitations/limitations_index"));
            }
        }
    }


     /**
     * [限时限购删除商品]
     * GY
     */
     public function limitations_delete($id)
     {
        $bool = db("limited")->where("id", $id)->delete();

        if ($bool ) {
            $this->success("删除成功", url("admin/Limitations/limitations_index"));
        } else {
            $this->error("删除失败", url("admin/Limitations/limitations_index"));
        }
     }

     /**
     * [限时限购搜索商品]
     * GY
     */
     public function limitations_search()
     {
         return view('limitations_add');
     }





}