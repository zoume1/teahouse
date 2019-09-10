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
use think\Db;
use think\Request;
use think\Session;
class Capital extends Controller{
    /**
     **************李火生*******************
     * @return \think\response\View
     * 会员储值首页
     **************************************
     */
    public function index(){
        $store_id =Session::get("store_id");
        $recharge_data = Db::name('recharge_full_setting')->where("store_id","EQ",$store_id)->paginate(20 ,false, [
            'query' => request()->param(),
        ]);
        return view('index',['recharge_data'=>$recharge_data]);
    }
	/**
	**************李火生*******************
	* @return \think\response\View
	* 会员储值编辑（添加也写在这里了）
	**************************************
	*/
	public function edit($id=null){
        if($id > 0){
            $store_id =Session::get("store_id");
            $data =Db::name('recharge_full_setting')->where('recharge_setting_id',$id)->find();
            $this->assign('data',$data);
        }
        if($this->request->isPost()){
            $data =$this->request->post();
            if($id > 0){
                $data['update_time'] = time();
                $res =Db::name('recharge_full_setting')->where('recharge_setting_id',$id)->update($data);
            }else{
                $data['create_time'] = time();
                $data['store_id'] = $store_id;
                $res =Db::name('recharge_full_setting')->insertGetId($data);
            }
            if($res>0){
                $this->success('编辑成功','admin/Capital/index');
            }else{
                $this->error('编辑失败');
            }
        }
		return view('edit');
	}
	/**
	**************李火生*******************
	* @return \think\response\View
	* 会员储值添加
	**************************************
	*/
	public function add(){
		return view('add');
    
    }
    /**
     * lilu
     * 资金管理添加处理
     */
	public function capital_adddo(){
        $input=input();
        if(!array_key_exists('status',$input)){
        $input['status']=0;
        }
        $store_id=Session::get('store_id');
        $input['store_id']=$store_id;
        $input['create_time']=time();
        $re=db('recharge_full_setting')->insert($input);
        if($re){
            $this->success('新增成功','admin/Capital/index');
        }else{
            $this->error('新增失败','admin/Capital/index');
        }
	}
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员储值删除
     **************************************
     * @param $id
     */
    public function del($id){
        $bool= Db::name('recharge_full_setting')->where('recharge_setting_id',$id)->delete();
        if($bool){
            $this->success('删除成功','admin/Capital/index');
        }else{
            return ajax_error('删除失败','admin/Capital/index');
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员储值状态值修改
     **************************************
     * @param Request $request
     */
    public function  status(Request $request){
        if($request->isPost()){
            $data =$_POST;
            if(!empty($data)){
                $bool =Db::name('recharge_full_setting')
                    ->where('recharge_setting_id',$data['id'])
                    ->update(['status'=>$data['status']]);
                if($bool){
                    return ajax_success('修改成功',['status'=>1]);
                }else{
                    return ajax_error('修改失败',['status'=>0]);
                }
            }
        }
    }




}