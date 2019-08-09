<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13 0013
 * Time: 17:31
 */
namespace app\admin\controller;

use think\Session;
use think\Controller;
use think\Db;
use think\Request;

class evaluate extends Controller{


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:评价管理页面
     **************************************
     * @return \think\response\View
     */
    public function evaluate_index(){
        $store_id = Session::get("store_id");
        $data_status = Db::name("order_evaluate")->where("store_id","EQ",$store_id)->find();
        $data =Db::name("order_evaluate")
        ->where("store_id","EQ",$store_id)
        ->order("create_time","desc")
        ->paginate(20 ,false, [
            'query' => request()->param(),
        ]);
        return view("evaluate_index",["data"=>$data,"data_status"=>$data_status]);
        }


public function record_index(){
    $record = db("order")->where("distribution",1)->where("status",2)->select();//方便测试，后期再加上订单条件(已付款)
    foreach($record as $key => $value) {
        $record[$key]["higher_level"] = db("member")->where("member_id", $record[$key]["member_id"])->value("inviter_id");//上一级member_id
        $record[$key]["phone_numbers"] = db("member")->where("member_id", $record[$key]["higher_level"])->value("member_phone_num");//上一级手机号（用户账号）
        $record[$key]["goods_number"] = db("goods")->where("id", $record[$key]["goods_id"])->value("goods_number");//商品编号
        if(empty($record[$key]["higher_level"])){
            $rest = db("distribution") -> where("id",1)->find();
            $record[$key]["commission"] = ($rest['grade']/100);
            $record[$key]["money"] = round(($rest['grade'] * $record[$key]['order_real_pay']/100),2);
            $record[$key]["integral"] = $rest['scale'];//积分比例
            $record[$key]["integrals"] = round($rest['scale'] * $record[$key]['order_real_pay']/100);//积分
        }
    }
    $all_idents = $record;//这里是需要分页的数据
    $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
    $listRow = 20;//每页20行记录
    $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
    $record = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
        'var_page' => 'page',
        'path' => url('admin/Distribution/record_index'),//这里根据需要修改url
        'query' => [],
        'fragment' => '',
    ]);
    $record->appends($_GET);
    $this->assign('record', $record->render());
    return view('record_index',["record"=>$record]);
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
 * [商品列表添加商品分销设置]
 * GY
 */
public function goods_addtwo($id)
{
    $rest = $id;
    return view('goods_addtwo',["rest"=>$rest]);
}



/**
 * [商品列表添加商品分销设置入库]
 * GY
 */
public function goods_savetwo(Request $request)
{
    if ($request->isPost()) {
        $data = $request->param();
        $store_id = Session::get("store_id");
        $goods = db("goods")->where("id",$data["restid"])->field("id,goods_number,goods_show_image,goods_name")->find();
        $bool = db("goods")->where("id",$data["restid"])->update(["distribution" => 1]);
        $goods["rank"] = implode(",",$data["rank"]);
        $goods["grade"] = implode(",",$data["grade"]);
        $goods["scale"] = implode(",",$data["scale"]);
        $goods["integral"] = implode(",",$data["integral"]);
        $goods["award"] = implode(",",$data["award"]);
        $goods["status"] = $data["status"];                   
        $goods["way"] = $data["way"];
        $goods["goods_id"] = $data["restid"]; 
        $goods["store_id"] = $store_id; 
        unset($goods["id"]);               
    
        $boole = db("commodity")->insert($goods);
     
        if ($boole) {
            $this->success("添加成功", url("admin/Distribution/goods_index"));
        } else {
            $this->error("添加失败", url("admin/Distribution/goods_add"));
        }
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
            $evaluate_id =trim(input('evaluate_id'));   //订单评论表id
            $business_repay =trim(input('business_repay'));
            if(!empty($business_repay)){
                $data =Db::name('order_evaluate')
                    ->update(['business_repay'=>$business_repay,'id'=>$evaluate_id]);
                if($data){
                    $this->success('回复成功',url('admin/Evaluate/evaluate_index'));
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
            $store_id = Session::get("store_id");
            if(!empty($goods_name) && (!empty($user_name))){
                $data =Db::name("order_evaluate")
                ->where("goods_name",$goods_name)
                ->where("user_name",$user_name)
                ->where("store_id","EQ",$store_id)
                ->order("create_time","desc")
                ->paginate(20 ,false, [
                    'query' => request()->param(),
                ]);
            }else if(!empty($goods_name) && empty($user_name)){
                $data =Db::name("order_evaluate")
                ->where("goods_name",$goods_name)
                ->where("store_id","EQ",$store_id)
                ->order("create_time","desc")
                ->paginate(20 ,false, [
                    'query' => request()->param(),
                ]);
            }else if(empty($goods_name) && (!empty($user_name))){
                $data =Db::name("order_evaluate")
                ->where("user_name",$user_name)
                ->where("store_id","EQ",$store_id)
                ->order("create_time","desc")
                ->paginate(20 ,false, [
                    'query' => request()->param(),
                ]);
            }else{
                $data =Db::name("order_evaluate")
                ->order("create_time","desc")
                ->where("store_id","EQ",$store_id)
                ->paginate(20 ,false, [
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
            $store_id = Session::get("store_id");
            $status =$request->only(["status"])["status"];//1为开启，-1为关闭
            $data =Db::name("order_evaluate")->where("store_id","EQ",$store_id)->select();
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