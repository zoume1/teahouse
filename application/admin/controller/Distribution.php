<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3 0003
 * Time: 18:21
 */

namespace app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use think\paginator\driver\Bootstrap;
use think\Session;

class Distribution extends Controller
{

    /**
     * [分销设置显示]
     * GY
     */
    public function setting_index()
    {
        $store_id = Session::get("store_id");
        $distribution = db("distribution") ->where("store_id","EQ",$store_id)-> select();

        $setting = Db::name("setting")->where("store_id",$store_id)->find();
        if(empty($setting)){
            $data = Db::name("setting")->where("store_id",6)->find();
            unset($data['id']);
            $data['store_id'] = $store_id;
            $bool = Db::name("setting")->insert($data);
        }

        if(empty($distribution)){
            $rest = db("distribution") ->where("store_id","EQ",$store_id)-> select();
            foreach($rest as $key => $value){
                unset($rest[$key]['id']);
                $rest[$key]['store_id'] = $store_id;
            }

            foreach($rest as $k => $v){
                $bool[] = db("distribution")->insert($v);
            }
        }
        

     
        return view("setting_index",["distribution" =>$distribution ]);
    }



    /**
     * [分销设置编辑]
     * GY
     */
    public function setting_edit($id)
    {
        $setting = db("distribution")->where("id", $id)->select();
        return view("setting_edit", ["setting" => $setting]);
    }




    /**
     * [分销设置编辑入库]
     * GY
     */
    public function setting_updata(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();
            $bool = db("distribution")->where('id', $request->only(["id"])["id"])->update($data);
            if ($bool) {
                $this->success("编辑成功", url("admin/Distribution/setting_index"));
            } else {
                $this->error("编辑失败", url("admin/Distribution/setting_index"));
            }
        }
    }




    /**
     * [分销商品页面]
     * GY
     */
    public function goods_index()
    {

        $store_id = Session::get("store_id");
        $commodity = db("commodity")->where("store_id","EQ",$store_id)->select();
        foreach ($commodity as $key => $value) {
            $commodity[$key]["grade"] = explode(",", $commodity[$key]["grade"]);
        }


        $all_idents = $commodity;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $commodity = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Distribution/goods_index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $commodity->appends($_GET);
        $this->assign('commodity', $commodity->render());

        return view('goods_index', ["commodity" => $commodity]);

    }



    /**
     * [分销商品添加商品]
     * GY
     */
    public function goods_add()
    {
        return view('goods_add');
    }




    /**
     * [分销商品编辑]
     * GY
     */
    public function goods_edit($id)
    {

        $goods = db("commodity")->where("id", $id)->select();
        $goods[0]["grade"] = explode(",", $goods[0]["grade"]);
        $goods[0]["award"] = explode(",", $goods[0]["award"]);
        $goods[0]["scale"] = explode(",", $goods[0]["scale"]);
        $goods[0]["integral"] = explode(",", $goods[0]["integral"]);

        return view('goods_edit', ["goods" => $goods]);
    }

 


    /**
     * [分销商品编辑更新]
     * GY
     */
    public function goods_update(Request $request)
    {

        if ($request->isPost()) {
            $goods_data = $request->param();
            $goods_data["rank"] = implode(",", $goods_data["rank"]);
            $goods_data["grade"] = implode(",", $goods_data["grade"]);
            $goods_data["award"] = implode(",", $goods_data["award"]);
            $goods_data["scale"] = implode(",", $goods_data["scale"]);
            $goods_data["integral"] = implode(",", $goods_data["integral"]);

            $bool = db("commodity")->where('id', $request->only(["id"])["id"])->update($goods_data);
            if ($bool) {
                $this->success("编辑成功", url("admin/Goods/index"));
            } else {
                $this->error("编辑失败", url("admin/Goods/index"));
            }
        }
    }



    /**
     * [分销商品添加入库]
     * GY
     */
    public function goods_save(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();
            if(!empty($data["goods_id"])){
                foreach($data["goods_id"] as $key => $value)
                {
                    $goods[$key] = db("goods")->where("id",$data["goods_id"][$key])->field("id,goods_number,goods_show_image,goods_name,store_id")->find();
                    $bool = db("goods")->where("id",$data["goods_id"][$key])->update(["distribution" => 1]);
                    $goods[$key]["rank"] = implode(",",$data["rank"]);
                    $goods[$key]["grade"] = implode(",",$data["grade"]);
                    $goods[$key]["scale"] = implode(",",$data["scale"]);
                    $goods[$key]["integral"] = implode(",",$data["integral"]);
                    $goods[$key]["award"] = implode(",",$data["award"]);
                    $goods[$key]["status"] = $data["status"];                   
                    $goods[$key]["way"] = $data["way"];
                    $goods[$key]["goods_id"] = $goods[$key]["id"];
                    unset($goods[$key]["id"]);
                }
            }

         foreach($goods as $k => $v){
            $boole = db("commodity")->insert($v);
         }
            if ($boole) {
                $this->success("添加成功", url("admin/Distribution/goods_index"));
            } else {
                $this->error("添加失败", url("admin/Distribution/goods_add"));
            }
        }
    }


    /**
     * [分销商品组删除]
     * GY
     */
    public function goods_delete($id)
    {
        $goods_id = db("commodity")->where("id", $id)->value("goods_id");
        $boole = db("goods")->where("id",$goods_id)->update(["distribution" => 0]);
        $bool = db("commodity")->where("id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/Distribution/goods_index"));
        } else {
            $this->error("删除失败", url("admin/Distribution/goods_index"));
        }

    }

    /**
     * [分销记录页面]
     * GY
     */
    public function record_index(){
        $store_id = Session::get("store_id");
        $record =Db::table('tb_dealer_order')
            ->field("tb_dealer_order.*,tb_member.member_phone_num,tb_order.user_phone_number,user_account_name")
            ->join("tb_dealer_user","tb_dealer_order.user_id= tb_dealer_user.user_id",'left')
            ->join("tb_order","tb_order.id= tb_dealer_order.order_id",'left')
            ->join("tb_member","tb_dealer_user.referee_id = tb_member.member_id",'left')
            ->where("tb_dealer_order.wxapp_id",$store_id)
            ->where("tb_dealer_order.is_settled",'=',1) //已结算
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);

        return view('record_index',["record"=>$record]);
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
        
            $boole = db("commodity")->insertGetId($goods);
            $bool = db("goods")->where("id",$data["restid"])->update(["distribution_id" =>$boole]);
         
            if ($boole) {
                $this->success("添加成功", url("admin/Goods/index"));
            } else {
                $this->error("添加失败", url("admin/Goods/index"));
            }
        }
    }


    /**
     * [分销记录页面]
     * GY
     */
    public function record_search(){
        $store_id = Session::get("store_id");
        $search_a = input('search_name')?input('search_name'):null;
        if(!empty($search_a)){
            $condition =" `order_no` like '%{$search_a}%' or `user_account_name` like '%{$search_a}%' or `user_phone_number` like '%{$search_a}%'";
            $record =Db::table('tb_dealer_order')
            ->field("tb_dealer_order.*,tb_member.member_phone_num,tb_order.user_phone_number,user_account_name")
            ->join("tb_dealer_user","tb_dealer_order.user_id= tb_dealer_user.user_id",'left')
            ->join("tb_order","tb_order.id= tb_dealer_order.order_id",'left')
            ->join("tb_member","tb_dealer_user.referee_id = tb_member.member_id",'left')
            ->where("tb_dealer_order.wxapp_id",$store_id)
            ->where($condition)
            ->where("tb_dealer_order.is_settled",'=',1) //已结算
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        } else {
            $record =Db::table('tb_dealer_order')
            ->field("tb_dealer_order.*,tb_member.member_phone_num,tb_order.user_phone_number,user_account_name")
            ->join("tb_dealer_user","tb_dealer_order.user_id= tb_dealer_user.user_id",'left')
            ->join("tb_order","tb_order.id= tb_dealer_order.order_id",'left')
            ->join("tb_member","tb_dealer_user.referee_id = tb_member.member_id",'left')
            ->where("tb_dealer_order.wxapp_id",$store_id)
            ->where("tb_dealer_order.is_settled",'=',1) //已结算
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        }
        return view('record_index',["record"=>$record]);
    }

}