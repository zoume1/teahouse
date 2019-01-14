<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/110026
 * Time: 17:23
 */
namespace  app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use think\paginator\driver\Bootstrap;

class Member extends Controller{

    /**
     * [分销成员页面]
     * GY
     */
    public function member_index(){
        $member = db("member") -> select();
        foreach($member as $key => $value) {
            $member[$key]["higher_inviter"] = db("member")->where("member_id", $member[$key]["member_id"])->value("inviter_id");//上一级member_id
            $member[$key]["phone_numbers"] = db("member")->where("member_id", $member[$key]["higher_inviter"])->value("member_phone_num");//上一级手机号（用户账号)
            $member[$key]["count_money"] = db("order")->where("member_id", $member[$key]["member_id"])->where("distribution",1)->where("status",2)->sum("order_real_pay");
            $member[$key]["count_money"] = round($member[$key]["count_money"],2);
            $member[$key]["one_number"] = db("member")->where("rank_one", $member[$key]["member_id"])->count("rank_one");
            $member[$key]["two_number"] = db("member")->where("rank_two", $member[$key]["member_id"])->count("rank_two");
            $member[$key]["three_number"] = db("member")->where("rank_three", $member[$key]["member_id"])->count("rank_three");
            if($member[$key]["one_number"] == 0){ //说明该用户未参与分销活动
                unset($member[$key]);
            }           
            
        }
               
        $all_idents = $member;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $member = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Distribution/member_index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $member->appends($_GET);
        $this->assign('member',$member->render());
        return view('member_index',["member"=>$member]);
    }


    /**
     * [分销成员添加]
     * GY
     */
    public function member_add(){
        return view('member_add');
    }

    /**
     * [分销成员保存入库]
     * GY
     */
    public function member_save(Request $request){
        if ($request->isPost()){
            $data = $request->param();
            $rest = db("member") -> where("member_name",$data["member_name"])->field("member_id,inviter_id,member_phone_num")->find();
            if(!empty($rest)){
                $data["member_id"] = $rest["member_id"];
                $data["inviter_id"] = $rest["inviter_id"];
                $data["member_phone_num"] = $rest["member_phone_num"];
                $data["grade"] = implode(",",$data["grade"]);
                $data["scale"] = implode(",",$data["scale"]);
                $data["integral"] = implode(",",$data["integral"]);
                $data["award"] = implode(",",$data["award"]);
                $member = db("leaguer")->insert($data);
                if ($member) {
                    $this->success("添加成功", url("admin/Distribution/member_index"));
                } else {
                    $this->error("添加失败", url("admin/Distribution/member_index"));
                }

            }else{
                $this->error("没有该用户,请仔细核对后添加", url("admin/Distribution/member_index"));
            }

            return view('member_add');
        }
    }

    /**
     * [分销成员编辑页面]
     * GY
     */
    public function member_edit(){
        return view('member_edit');
    }



}