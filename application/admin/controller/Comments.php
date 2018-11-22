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
	 * [活动分类显示]
	 * 郭杨
	 */
    public function index(){
		$comments_index = db("mament")->paginate(4);
        return view('comments_index');
    }
	 public function add(){
		return view('comments_add');
	 }
	 public function edit(){
		return view('comments_edit');
	 }
 


}