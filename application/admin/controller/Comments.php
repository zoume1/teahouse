<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2018/10/26
 * Time: 19:17
 */
namespace  app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;

class  Comments extends  Controller{

	/**
	 * [评论管理显示]
	 * 郭杨
	 */
    public function index(){

		$comments_index = db("mament")->paginate(4);
        return view('comments_index',['comments_index'=>$comments_index]);
	}
	


	 public function add(){
		return view('comments_add');
	 }


	/**
	 * [评论管理编辑]
	 * 郭杨
	 */
	 public function edit($id){
		$comments = db("mament")->where('id',$id)->field('comment,id,reply_content')->select();
		dump($comments);
		return view('comments_edit',['comments'=>$comments]);
	 }
 
	/**
	 * [评论管理保存]
	 * 郭杨
	 */
	public function save(Request $request){
		if ($request->isPost()) {
			$comment_data = $request->param();
			$res = $request->only(["id"])["id"];
			
			$bool = db("mament")->where('id', $res)->update($comment_data);
            if ($bool) {
                $this->success("编辑成功", url("admin/Comments/index"));
            } else {
                $this->error("编辑失败", url("admin/Comments/edit"));
            }
		 
		}
	 }

	/**
     * [评论管理状态修改]
     * 郭杨
     */
	public function status(Request $request){
        if($request->isPost()) {
			$status = $request->only(["statuse"])["statuse"];
			
            if($status == 0) {
				$id = $request->only(["id"])["id"];
                $bool = db("mament")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Comments/index"));
                } else {
                    $this->error("修改失败", url("admin/Comments/index"));
                }
            }
            if($status == 1){
                $id = $request->only(["id"])["id"];
                $bool = db("mament")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Comments/index"));
                } else {
                    $this->error("修改失败", url("admin/Comments/index"));
                }
            }
        }
    }


	/**
     * [评论管理组删除]
     * 郭杨
     */
    public function delete($id)
    {
        $bool = db("mament")->where("id", $id)->delete();
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
}