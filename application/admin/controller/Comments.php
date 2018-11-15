<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/26
 * Time: 19:17
 */
namespace  app\admin\controller;

use think\Controller;

class  Comments extends  Controller{
    public function index(){
        return view('comments_index');
    }
	 public function add(){
		return view('comments_add');
	 }
	 public function edit(){
		return view('comments_edit');
	 }
 


}