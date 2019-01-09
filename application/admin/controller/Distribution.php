<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3 0003
 * Time: 18:21
 */

namespace  app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use think\paginator\driver\Bootstrap;

class  Distribution extends  Controller{

    /**
     * [分销设置显示]
     * GY
     */
    public function setting_index()
    {
        $distribution = db("distribution") -> select();
        //刷新页面
        $members = db("member")->field("member_id,member_name,member_grade_id,member_grade_name,inviter_id,rank")->select();
        foreach($members as $k=>$v){
            if(!empty($v['inviter_id'])){ //判断是否有一级member_id
             $e = db("member")->where("member_id", $members[$k]['inviter_id'])->field("rank")->update(["rank" => 1]);//将上一级用户更新为1级
             $members[$k]['rank_one'] = db("member")->where("member_id", $v["inviter_id"])->value("inviter_id");//找到上一级的inviter_id 
                   
            if(!empty($members[$k]['rank_one'])){
             $r = db("member")->where("member_id", $members[$k]['rank_one'])->field("rank")->update(["rank" => 2]);  
             $members[$k]['rank_two'] = db("member")->where("member_id", $members[$k]['rank_one'])->value("inviter_id");
            
            if(!empty($members[$k]['rank_two'])){
             $s = db("member")->where("member_id", $members[$k]['rank_two'])->field("rank")->update(["rank" => 3]);
             $members[$k]['rank_two'] = db("member")->where("member_id", $members[$k]['rank_one'])->value("inviter_id");//上2级账号id          
            }
         }
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
        $setting = db("distribution") -> where("id",$id) -> select();    
        return view("setting_edit",["setting" => $setting]);
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

        $commodity = db("commodity") -> select();
        foreach ($commodity as $key => $value)
        {
            $commodity[$key]["grade"] = explode(",",$commodity[$key]["grade"]);
        }


        $all_idents = $commodity;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $commodity = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Category/index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $commodity->appends($_GET);
        $this->assign('commodity', $commodity->render());

        return view('goods_index',["commodity"=> $commodity]);

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

        $goods = db("commodity") -> where("id",$id) ->select();
        $goods[0]["grade"] = explode(",",$goods[0]["grade"]);
        $goods[0]["award"] = explode(",",$goods[0]["award"]);
        $goods[0]["scale"] = explode(",",$goods[0]["scale"]);
        $goods[0]["integral"] = explode(",",$goods[0]["integral"]);
    
        return view('goods_edit',["goods"=> $goods]);
    }


    
    /**
     * [分销商品编辑更新]
     * GY
     */
    public function goods_update(Request $request)
    {

        if ($request->isPost()) {
            $goods_data = $request->param();
            $goods_data["rank"] = implode(",",$goods_data["rank"]);
            $goods_data["grade"] = implode(",",$goods_data["grade"]);
            $goods_data["award"] = implode(",",$goods_data["award"]);
            $goods_data["scale"] = implode(",",$goods_data["scale"]);
            $goods_data["integral"] = implode(",",$goods_data["integral"]);

            $bool = db("commodity")->where('id', $request->only(["id"])["id"])->update($goods_data);
            if ($bool) {
                $this->success("编辑成功", url("admin/Distribution/goods_index"));
            } else {
                $this->error("编辑失败", url("admin/Distribution/goods_edit"));
            }
        }
    }



    /**
     * [分销商品添加入库]
     * GY
     */
    public function goods_save(Request $request)
    {
        if ($request->isPost())
         {
            $data = $request->param();
            $des = db("goods") -> where("goods_number",$data["shop_number"])->field("id,goods_name,goods_show_images,goods_new_money")-> find();
            $bool = db("goods")->where("goods_number",$data["shop_number"])->update(["distribution" => 1]);
            $reste = db("commodity")->where("goods_id",$des["id"])->find();
            $deny = explode(",",$des["goods_show_images"]);
            $data["picture"] = $deny[0];
            $data["shop_name"] = $des["goods_name"];
            $data["goods_id"] = $des["id"];
            
            $data["rank"] = implode(",",$data["rank"]);
            $data["grade"] = implode(",",$data["grade"]);
            $data["award"] = implode(",",$data["award"]);
            $data["scale"] = implode(",",$data["scale"]);
            $data["integral"] = implode(",",$data["integral"]);

            if(empty($data["shop_name"]))
            {
                $this->error("商品列表中没有该商品,请仔细核对后再添加", url("admin/Distribution/goods_add"));
            }
            if(!empty($reste))
            {
                $this->error("该商品已经加入分销设置,请仔细核对后再添加", url("admin/Distribution/goods_add"));
            }
         
            $bool = db("commodity")->insert($data);
            if ($bool) {
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
        $bool = db("commodity")->where("id", $id)->delete();
        $goods_id = db("commodity")->where("id", $id)->value("goods_id");
        $boole = db("goods")->where("id",$goods_id)->update(["distribution" => 0]);
        if ($bool && $boole) {
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
        $record = db("order")->where("distribution",1)->where("status",2)->select();//方便测试，后期再加上订单条件(已付款)
        foreach($record as $key => $value) {
            $record[$key]["higher_level"] = db("member")->where("member_id", $record[$key]["member_id"])->value("inviter_id");//上一级member_id
            $record[$key]["phone_numbers"] = db("member")->where("member_id", $record[$key]["higher_level"])->value("member_phone_num");//上一级手机号（用户账号）
            $record[$key]["goods_number"] = db("goods")->where("id", $record[$key]["goods_id"])->value("goods_number");//商品编号
        }


    
 
    
        
    //    $type = _tree_sort(recursionArre($members),'member_id');
 
    //    foreach ($type as $key => $value) {
    //     if (isset($value['child'])) {//是否有子集
    //         $bool = db("member")->where("member_id", $value["member_id"])->field("rank")->update(["rank" =>1]);//有下一级用户,更新为1级
            
    //         foreach($value['child'] as $k => $v){
    //             if (isset($value[$k]['child'])) {//是否有子集
    //                 $boole = db("member")->where("member_id", $value[$k]["member_id"])->field("rank")->update(["rank" => 1]);//有下一级用户,更新为1级
    //                 $one = db("member")->where("member_id", $value[$k]["rank_zero"])->field("rank")->update(["rank" => 2]);//上一级用户更新为2级
    //                 halt(2);
    //                 foreach($v as $q => $w){           
    //                     if (count($w['child'])) {//是否有子集
    //                         $w = db("member")->where("member_id", $v[$q]["member_id"])->field("rank")->update(["rank" => 1]);//有下一级用户,更新为1级
    //                         $e = db("member")->where("member_id", $v[$q]["rank_one"])->field("rank")->update(["rank" => 2]);//上一级用户更新为2级
    //                         $p = db("member")->where("member_id", $v[$q]["rank_two"])->field("rank")->update(["rank" => 3]);//上一级用户更新为3级

    //                         foreach($w as $e => $r){           
    //                             if (count($r['child'])) {//是否有子集
    //                                 $c = db("member")->where("member_id", $w[$e]["member_id"])->field("rank")->update(["rank" => 1]);//有下一级用户,更新为1级
    //                                 $v = db("member")->where("member_id", $w[$e]["rank_one"])->field("rank")->update(["rank" => 2]);//上一级用户更新为2级
    //                                 $b = db("member")->where("member_id", $w[$e]["rank_two"])->field("rank")->update(["rank" => 3]);//上一级用户更新为3级
    //                             }else {
    //                                 $c = db("member")->where("member_id", $r[$e]["member_id"])->field("rank")->update(["rank" => 0]);//没有
    //                             }
    //                         }
    //                     }else {
    //                         $w = db("member")->where("member_id", $v[$q]["member_id"])->field("rank")->update(["rank" => 0]);//没有
    //                     }
    //                 } 
    //             } 
    //         } 
    //     } else {
    //         $bool = db("member")->where("member_id", $value["member_id"])->field("rank")->update(["rank" => 0]);//没有
    //     }
    // }



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
     * [分销成员页面]
     * GY
     */
    public function member_index(){
        return view('member_index');
    }



    /**
     * [分销成员编辑页面]
     * GY
     */
    public function member_edit(){
        return view('member_edit');
    }





}