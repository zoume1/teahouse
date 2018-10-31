<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/25
 * Time: 11:37
 *
 */

namespace  app\admin\controller;

use think\Controller;

class Capital extends Controller{
    /**
     **************李火生*******************
     * @return \think\response\View
     * 资金管理首页
     **************************************
     */
    public function index(){
        return view('index');
    }
	/**
	**************邹梅*******************
	* @return \think\response\View
	* 资金管理编辑
	**************************************
	*/
	public function edit(){
		return view('edit');
	}
	/**
	**************邹梅*******************
	* @return \think\response\View
	* 资金管理添加
	**************************************
	*/
	public function add(){
		return view('add');
	}



}