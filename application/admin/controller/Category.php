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

class Category extends Controller
{


    /**
     * [活动分类显示]
     * 陈绪
     */
    public function index()
    {
        $category = db("goods_type")->paginate(5);
        return view("category_index", ["category" => $category]);
    }



    /**
     * [活动分类添加]
     * 陈绪
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function add($pid = 0)
    {
        $goods_list = [];
        if ($pid == 0) {
            $goods_list = postSelectList("goods_type");
        }
        return view("category_add", ["goods_list" => $goods_list]);
    }



    /**
     * [活动分类分组入库]
     * 陈绪
     */
    public function save(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();
            $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
            $data["icon_image"] = str_replace("\\", "/", $show_images->getSaveName());
            $bool = db("goods_type")->insert($data);
            if ($bool) {
                $this->success("添加成功", url("admin/Category/index"));
            } else {
                $this->error("添加失败", url("admin/Category/add"));
            }
        }
    }



    /**
     * [活动分类分组修改]
     * [陈绪]
     */
    public function edit($pid = 0, $id)
    {
        $category = db("goods_type")->where("id", $id)->select();
        $category_name = db("goods_type")->where("id", $category[0]["pid"])->field("name,id")->select();
            $goods_list = postSelectList("goods_type");
        return view("category_edit", ["category" => $category, "goods_lists" => $goods_list]);
    }

    /**
     * [图片删除]
     * 陈绪
     */
    public function images(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(['id'])['id'];
            $image_url = db("goods_type")->where("id", $id)->field("icon_image")->find();
            if ($image_url['icon_image'] != null) {
                unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $image_url['icon_image']);
            }
            $bool = db("goods_type")->where("id", $id)->field("icon_image")->update(["icon_image" => null]);
            if ($bool) {
                return ajax_success("删除成功");
            } else {
                return ajax_error("删除失败");
            }
        }
    }

    /**
     * [活动分类分组更新]
     * [陈绪]
     * @param Request $request
     * @param $id
     */
    public function updata(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();

            $show_images = $request->file("icon_image");
            if ($show_images) {
                $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["icon_image"] = str_replace("\\", "/", $show_images->getSaveName());
            }
            $bool = db("goods_type")->where('id', $request->only(["id"])["id"])->update($data);

            if ($bool) {
                $this->success("编辑成功", url("admin/Category/index"));
            } else {
                $this->error("编辑失败", url("admin/Category/edit"));
            }
        }
    }


    /**
     * [活动分类分组删除]
     * [陈绪]
     */
    public function del($id)
    {
        $bool = db("goods_type")->where("id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/Category/index"));
        } else {
            $this->error("删除失败", url("admin/Category/edit"));
        }
    }


    /**
     * [活动分类分组ajax显示]
     * 陈绪
     * @param int $pid
     * @return
     */
    public function ajax_add($pid = 0)
    {
        $goods_list = [];
        if ($pid == 0) {
            $goods_list = getSelectList("goods_type");
        }
        return ajax_success("获取成功", $goods_list);
    }

    /**
     * [活动分类分组批量删除]
     * 陈绪
     * @param int $pid
     * @return
     */
    public function dels(Request $request)
    {
        if ($request->isPost()) {
            $id = $_POST['id'];
            if (is_array($id)) {
                $where = 'id in(' . implode(',', $id) . ')';
            } else {
                $where = 'id=' . $id;
            }

            $list = Db::name('goods_type')->where($where)->delete();
            if ($list !== false) {
                return ajax_success('成功删除!', ['status' => 1]);
            } else {
                return ajax_error('删除失败', ['status' => 0]);
            }
        }
    }




    /**
     * [活动分类分组状态修改]
     * 陈绪
     */
    public function status(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods_type")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Category/index"));
                } else {
                    $this->error("修改失败", url("admin/Category/index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods_type")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Category/index"));
                } else {
                    $this->error("修改失败", url("admin/Category/index"));
                }
            }
        }
    }



}

