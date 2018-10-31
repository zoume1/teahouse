<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/25
 * Time: 11:22
 */
namespace  app\admin\controller;

use think\Controller;

class User extends Controller{
    /**
     **************李火生*******************
     * @return \think\response\View
     * 会员首页
     **************************************
     */
    public function index(){
        return view('index');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 会员编辑
     **************************************
     */
    public function edit(){
        return view('edit');
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 会员等级
     **************************************
     */
    public function  grade(){
        return view('grade');
    }
	/**
	**************邹梅*******************
	* @return \think\response\View
	* 会员等级编辑
	**************************************
	*/
	public function  grade_edit(){
		return view('grade_edit');
	}
	/**
	**************邹梅*******************
	* @return \think\response\View
	* 会员等级添加
	**************************************
	*/
	public function  grade_add(){
		return view('grade_add');
	}

}