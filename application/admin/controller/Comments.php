<?php

/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2018/10/26
 * Time: 19:17
 */
namespace app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use think\paginator\driver\Bootstrap;

class Comments extends Controller
{

    /**
     * [评论管理显示]
     * 陈绪
     */
    public function index()
    {
        $comments_index = db("comment")->select();
        $comments_set = db("comment_set")->select();
        foreach ($comments_index as $key=>$value){
            if(!empty($value["comment_set_id"]) && $value["status"] == 1){
                $comments_character_integral = db("comment_set")->where("id",$value["comment_set_id"])->value("character_integral");
                $comments_approve = db("comment_set")->where("id",$value["comment_set_id"])->value("approve");
                $comments_integral = $comments_character_integral + $comments_approve;
                $comments_index[$key]["comments_integral"] = $comments_integral;
                $comments_index[$key]["activity_name"] = db("teahost")->where("id",$value["teahost_id"])->value("activity_name");
                db("comment")->where("id",$value["id"])->update(["comment_integral"=>$comments_integral]);

            }else if(!empty($value["comment_set_id"])){
                $comments_character_integral = db("comment_set")->where("id",$value["comment_set_id"])->value("character_integral");
                $comments_index[$key]["comments_integral"] = $comments_character_integral;
                $comments_index[$key]["activity_name"] = db("teahost")->where("id",$value["teahost_id"])->value("activity_name");
                db("comment")->where("id",$value["id"])->update(["comment_integral"=>$comments_character_integral]);
            }else if (!empty($comments_set) && $value["comment_set_id"] == null){
                db("comment")->where("comment_set_id",null)->update(["comment_set_id"=>$comments_set[0]["id"]]);
                $comments_index[$key]["activity_name"] = db("teahost")->where("id",$value["teahost_id"])->value("activity_name");
                $comments_character_integral = db("comment_set")->where("id",$value["comment_set_id"])->value("character_integral");
                $comments_index[$key]["comments_integral"] = $comments_character_integral;
                if($value["status"] == 1){
                    $comments_approve = db("comment_set")->where("id",$value["comment_set_id"])->value("approve");
                    $comments_integral = $comments_character_integral + $comments_approve;
                    $comments_index[$key]["comments_integral"] = $comments_integral;
                }
            }else{
                $comments_index[$key]["activity_name"] = db("teahost")->where("id",$value["teahost_id"])->value("activity_name");
            }

        }
        $all_idents = $comments_index;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $comments_index = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Comments/index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $comments_index->appends($_GET);
        $this->assign('comment', $comments_index->render());
        return view('comments_index',["comments_index"=>$comments_index]);
    }


    /**
     * [评论积分设置]
     * 郭杨
     */
    public function add()
    {
        $comment = db("comment_set")->select();
        if(!empty($comment)){
            $this->assign(["comment"=>$comment]);
        }
        return view('comments_add');
    }


    /**
     * [评论积分设置保存]
     * 郭杨
     */
    public function preserve(Request $request)
    {
        $comment_datas = $request->param();
        $bool = db("comment_set")->insert($comment_datas);
        if ($bool) {
            $this->success("成功", url("admin/Comments/index"));
        } else {
            $this->error("失败", url("admin/Comments/index"));
        }
    }





    /**
     * [评论管理保存]
     * 郭杨
     */
    public function updata(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $comment_data = $request->param();
            unset($comment_data["id"]);
            $bool = db("comment_set")->where('id', $id)->update($comment_data);
            if ($bool) {
                $this->success("编辑成功", url("admin/Comments/index"));
            } else {
                $this->error("编辑失败", url("admin/Comments/edit"));
            }

        }
    }



    /**
     * [评论管理组删除]
     * 郭杨
     */
    public function delete($id)
    {
        $bool = db("comment")->where("id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/Comments/index"));
        } else {
            $this->error("删除失败", url("admin/Comments/index"));
        }
    }

    /**
     * [评论管理组批量删除]
     * 郭杨
     */
    public function deletes(Request $request)
    {
        if ($request->isPost()) {
            $id = $_POST['id'];
            if (is_array($id)) {
                $where = 'id in(' . implode(',', $id) . ')';
            } else {
                $where = 'id=' . $id;
            }

            $list = Db::name('mament')->where($where)->delete();
            if ($list !== false) {
                return ajax_success('成功删除!', ['status' => 1]);
            } else {
                return ajax_error('删除失败', ['status' => 0]);
            }
        }
    }


    /**
     * [评论管理组模糊删除]
     * 郭杨
     */
    public function search()
    {
        $comment_commodity = input('search_key');  //评论商品
        $comment_name = input('search_keys');      //用户名

        if ((!empty($comment_commodity)) || (!empty($comment_name))) {
            $actived = db("mament")->where("goods_comment", "like", "%" . $comment_commodity . "%")->where("user_account", "like", "%" . $comment_name . "%")->paginate(4);

        } else {
            $comments_inde = db("mament")->paginate(4);
            return view('comments_index', ['comments_index' => $comments_inde]);
        }
        if (!empty($actived)) {
            return view('comments_index', ['comments_index' => $actived]);
        }
    }
}