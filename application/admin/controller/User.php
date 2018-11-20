<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/25
 * Time: 11:22
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\Request;

class User extends Controller{
    /**
     **************李火生*******************
     * @return \think\response\View
     * 会员首页
     **************************************
     */
    public function index(){
        $user_data =Db::name('member')->paginate(5);
        return view('index',['user_data'=>$user_data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:客户账户启用状态编辑
     **************************************
     */
    public function  status(Request $request){
        if($request->isPost()){
            $data =$_POST;
            if(!empty($data)){
                $bool =Db::name('member')->where('member_id',$data['id'])->update(['member_status'=>$data['status']]);
                if($bool){
                    return ajax_success('修改成功',['status'=>1]);
                }else{
                    return ajax_error('修改失败',['status'=>0]);
                }
            }
        }
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
       $grade_data = Db::name('member_grade')->paginate(5);
        return view('grade',['grade_data'=>$grade_data]);
    }
	/**
	**************邹梅*******************
	* @return \think\response\View
	* 会员等级编辑
	**************************************
	*/
    public function grade_edit($id =null){
        $term_data =Db::name('term')->select();
        if($this->request->isPost()){
            $data =$this->request->post();
            $data['create_time'] =time();
            $file =$this->request->file("member_grade_img");
            if($file){
                $datas = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                $images_url = str_replace("\\","/",$datas->getSaveName());
                $data['member_grade_img'] =$images_url;
            }
            if($id>0){
                $res =Db::name('member_grade')->where('member_grade_id',$id)->update($data);
            }else{
                $res =Db::name('member_grade')->insertGetId($data);
            }
            if($res>0){
                $this->success('编辑成功','admin/User/grade');
            }else{
                $this->error('编辑失败');
            }
        }
        if($id > 0){
            $info =Db::name('member_grade')->where("member_grade_id",$id)->find();
            dump($info);
            $this->assign('info',$info);
        }
        return view('grade_edit',['term_data'=>$term_data]);
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