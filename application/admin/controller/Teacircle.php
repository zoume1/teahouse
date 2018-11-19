<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/26
 * Time: 19:17
 */
namespace  app\admin\controller;
use think\Db;
use think\Request;
use think\Controller;

class  Teacircle extends  Controller{

    public function index(){


    }

	 public function add(){
		return view('teacircle_add');
	 }
	 public function edit(){
		return view('teacircle_edit');
	 }
 


}