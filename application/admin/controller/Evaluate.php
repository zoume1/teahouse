<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13 0013
 * Time: 17:31
 */
namespace app\admin\controller;


use think\Controller;
use think\Db;
use think\Request;

class Evaluate extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:评价管理页面
     **************************************
     * @return \think\response\View
     */
    public function evaluate_index(){
        $data_status =Db::name("order_evaluate")->find();
        $data =Db::name("order_evaluate")->order("create_time","desc")->paginate(20 ,false, [
            'query' => request()->param(),
        ]);

        return view("evaluate_index",["data"=>$data,"data_status"=>$data_status]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:评价编辑
     **************************************
     * @return \think\response\View
     */
    public function evaluate_edit($id){
        $evaluate_details =Db::name('order_evaluate')->where('id',$id)->find();//评价信息
        $evaluate_images =Db::name('order_evaluate_images')->where('evaluate_order_id',$id)->select();
        return view("evaluate_edit",['evaluate_details'=>$evaluate_details,'evaluate_images'=>$evaluate_images]);
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:配件商订单评价状态删除功能
     **************************************
     * @param $id
     */
    public function  evaluate_del($id){
        $res =Db::name('order_evaluate')->where('id',$id)->delete();
        if($res){
            //如果存在图片则连图片一块删除
            $url_string = Db::name("order_evaluate_images")->where("evaluate_order_id",$id)->field("images")->select();
            if(!empty($url_string)){
                foreach ($url_string as $k=>$v){
                    if(!empty($v["images"])){
                        unlink(ROOT_PATH . 'public' . DS . 'uploads/'.$v["images"]);
                    }
                }
            }
            $this->success('删除成功','admin/Evaluate/evaluate_index');
        }else{
            $this->error('删除失败','admin/Evaluate/evaluate_index');
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:配件商订单评价状态批量删除功能
     **************************************
     * @param $id
     */
    public function  evaluate_dels(Request $request){
        if($request->isPost()) {
            $id =$_POST['id'];
            if(is_array($id)){
                $where ='id in('.implode(',',$id).')';
                $wheres ='evaluate_order_id in('.implode(',',$id).')';
            }else{
                $where ='id='.$id;
                $wheres ='evaluate_order_id='.$id;
            }

            $res = Db::name('order_evaluate')->where($where)->delete();
            if ($res) {
                //如果存在图片则连图片一块删除
                $url_string = Db::name("order_evaluate_images")->where($wheres)->field("images")->select();
                if (!empty($url_string)) {
                    foreach ($url_string as $k => $v) {
                        if (!empty($v["images"])) {
                            unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $v["images"]);
                        }
                    }
                }
                $this->success('删除成功', 'admin/Evaluate/evaluate_index');
            } else {
                $this->error('删除失败', 'admin/Evaluate/evaluate_index');
            }
        }
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:服务商订单评价商家回复
     **************************************
     */
    public  function evaluate_repay(Request $request){
        if($request->isPost()){
            $evaluate_id =trim(input('evaluate_id'));
            $business_repay =trim(input('business_repay'));
            if(!empty($business_repay)){
                $data =Db::name('order_service_evaluate')
                    ->update(['business_repay'=>$business_repay,'id'=>$evaluate_id]);
                if($data){
                    $this->success('回复成功');
                }else{
                    $this->error('回复失败');
                }
            }

        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:评价搜索
     **************************************
     * @param Request $request
     */
    public function evaluate_search(){
            $goods_name =trim(input('goods_name'));
            $user_name =trim(input('user_name'));
            if(!empty($goods_name) && (!empty($user_name))){
                $data =Db::name("order_evaluate")->where("goods_name",$goods_name)->where("user_name",$user_name)->order("create_time","desc")->paginate(20 ,false, [
                    'query' => request()->param(),
                ]);
            }else if(!empty($goods_name) && empty($user_name)){
                $data =Db::name("order_evaluate")->where("goods_name",$goods_name)->order("create_time","desc")->paginate(20 ,false, [
                    'query' => request()->param(),
                ]);
            }else if(empty($goods_name) && (!empty($user_name))){
                $data =Db::name("order_evaluate")->where("user_name",$user_name)->order("create_time","desc")->paginate(20 ,false, [
                    'query' => request()->param(),
                ]);
            }else{
                $data =Db::name("order_evaluate")->order("create_time","desc")->paginate(20 ,false, [
                    'query' => request()->param(),
                ]);
            }
        return view("evaluate_index",["data"=>$data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:评价功能开启关闭
     **************************************
     * @param Request $request
     */
    public function evaluate_status(Request $request){
        if($request->isPost()){
            $status =$request->only(["status"])["status"];//1为开启，-1为关闭
            $data =Db::name("order_evaluate")->select();
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $bool =Db::name("order_evaluate")
                        ->where("id",$value["id"])
                        ->update(["is_show"=>$status]);
                }
                if($bool){
                    $this->success("更新成功",url("admin/Evaluate/evaluate_index"));
                }else{
                    $this->success("没有修改任何东西",url("admin/Evaluate/evaluate_index"));
                }
            }
        }
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:评价积分设置
     **************************************
     * @return \think\response\View
     */
    public function evaluate_setting(){
        return view("evaluate_setting");
    }

}