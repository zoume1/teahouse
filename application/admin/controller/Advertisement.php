<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/17/025
 * Time: 14:13
 */

namespace app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use think\Session;
use app\admin\controller\Qiniu;
use think\paginator\driver\Bootstrap;

class Advertisement extends Controller
{

    /**
     * [活动管理显示]
     * 郭杨
     */
    public function index()
    {

        $store_id = Session::get("store_id");
        $accessories = db("teahost")->where("store_id","EQ",$store_id)->order("order_ing desc")->select();
        foreach ($accessories as $key => $value) {
            if ($value["pid"]) {
                $res = db("goods_type")->where("id", $value['pid'])->field("name")->find();
                $accessories[$key]["named"] = $res["name"];
            }
        }
        $all_idents = $accessories;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $accessories = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Advertisement/index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $accessories->appends($_GET);
        $this->assign('access', $accessories->render());
        $goods = [];
        $goods = getSelectListes("goods_type");
        
        return view("accessories_business_advertising", ["accessories" => $accessories,"goods"=>$goods]);
    }



    /**
     * [活动管理添加]
     * 郭杨
     *
     */
    public function accessories_business_add($pid = 0)
    {
        $teahost_name = [];
        if ($pid == 0) {
            $teahost_name = getSelectListes("goods_type");
        }

        return view("accessories_business_add", ["teahost_name" => $teahost_name]);
    }



    /**
     * [活动管理分组入库]
     * 郭杨
     */
    public function accessories_business_save(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();
            $store_id = Session::get("store_id");
            $participats = isset($data["participats"])?$data["participats"]:0;//活动人数
            //活动日期
            $start_time = isset($data["start_time"])?$data["start_time"]:null;
            $end_time = isset($data["end_time"])?$data["end_time"]:null;
            $day_start_time = isset($data["day_start_time"])?$data["day_start_time"]:'00:00';
            $day_end_time = isset($data["day_end_time"])?$data["day_end_time"]:'00:00';
            $data["store_id"] = $store_id;
            //如果需要预约,且有人数限制

            if(!empty($start_time) && !empty($end_time) ){
                $day_start_time = $day_start_time.':00';
                $day_end_time = $day_end_time.':00';
                $one_time = $data["start_time"] .''. $day_start_time;
                $two_time = $data["end_time"] .''. $day_end_time;
                $data["start_time"] = strtotime($data["start_time"]);
                $data["end_time"] = strtotime($data["end_time"]);
                $data["one_time"] = strtotime($one_time);
                $data["two_time"] = strtotime($two_time);
                $day_number = diffBetweenTwoDays($data["start_time"],$data["end_time"]);
                $data["day_number"] = $day_number;
                for($i = 0;$i <= $day_number;$i++){
                    $day_array[$i] = $participats;
                }
                $data["day_array"] = implode(",",$day_array);
                
            } else {
                $this->error("活动时间不能为空", url("admin/Advertisement/accessories_business_add"));
            }
            $address = [$data["address_city2"], $data["address_city3"], $data["address_street"]];
            $addressed = [$data["address_city1"], $data["address_city2"], $data["address_city3"], $data["address_street"]];
            $data["addressed"] = implode(",", $addressed);
            $data["address"] = implode("", $address);
            
            foreach ($data as $k => $v) {
                if (in_array($v, $addressed)) {
                    unset($data[$k]);
                }
            }
            
           $qiniu=new Qiniu();
           //获取店铺七牛云的配置项
           $peizhi=Db::table('applet')->where('store_id',$store_id)->find();
           $images='classify_image';
           $rr=$qiniu->uploadimg($peizhi['accesskey'],$peizhi['secretkey'],$peizhi['bucket'],$peizhi['domain'],$images);
           if(empty($rr)){
             
           }else{
            $data["classify_image"] =  $rr[0];
           }

            $bool = db("teahost")->insert($data);
            if ($bool) {
                $this->success("添加成功", url("admin/Advertisement/index"));
            } else {
                $this->error("添加失败", url("admin/Advertisement/accessories_business_add"));
            }
        }
    }



    /**
     * [活动管理分组修改]
     * 郭杨
     */
    public function accessories_business_edit($pid = 0, $id)
    {

        $teahost = db("teahost")->where("id", $id)->select();
        $teahost[0]['start_time'] = date("Y-m-d H:i",$teahost[0]['start_time']);
        $teahost[0]['end_time'] = date("Y-m-d H:i",$teahost[0]['end_time']);
        $teahost_names = [];
        if ($pid == 0) {
            $teahost_names = getSelectListes("goods_type");
        }

        $city_address = explode(",", $teahost[0]["addressed"]);
        return view("accessories_business_edit", ["teahost" => $teahost, "teahost_names" => $teahost_names, "city_address" => $city_address]);
    }


    /**
     * [活动管理分组更新]
     * 郭杨
     * @param Request $request
     * @param $id
     */
    public function accessories_business_updata(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();
            $store_id = Session::get("store_id");


            $participats = isset($data["participats"])?$data["participats"]:0;//活动人数
            //活动日期
            // $start_time = isset($data["start_time"])?$data["start_time"]:null;
            // $end_time = isset($data["end_time"])?$data["end_time"]:null;
            // $day_start_time = isset($data["day_start_time"])?$data["day_start_time"]:'00:00';
            // $day_end_time = isset($data["day_end_time"])?$data["day_end_time"]:'00:00';
            

            // $day_start_time = $day_start_time.':00';
            // $day_end_time = $day_end_time.':00';
            // $one_time = $data["start_time"] . $day_start_time;
            // $two_time = $data["end_time"] . $day_end_time;
            // $data["start_time"] = strtotime($data["start_time"]);
            // $data["end_time"] = strtotime($data["end_time"]);
            // $data["one_time"] = strtotime($one_time);
            // $data["two_time"] = strtotime($two_time);
            $qiniu=new Qiniu();
            //获取店铺七牛云的配置项
            $peizhi=Db::table('applet')->where('store_id',$store_id)->find();
            $images='classify_image';
            $rr=$qiniu->uploadimg($peizhi['accesskey'],$peizhi['secretkey'],$peizhi['bucket'],$peizhi['domain'],$images);
            if(empty($rr)){
              
            }else{
             $data["classify_image"] =  $rr[0];
            }


            
            $address = [$data["address_city2"], $data["address_city3"], $data["address_street"]];
            $addressed = [$data["address_city1"], $data["address_city2"], $data["address_city3"], $data["address_street"]];
            $data["addressed"] = implode(",", $addressed);
            $data["address"] = implode("", $address);
            

            foreach ($data as $k => $v) {
                if (in_array($v, $addressed)) {
                    unset($data[$k]);
                }
            }
          
            $bool = db("teahost")->where('id', $request->only(["id"])["id"])->update($data);
            if ($bool) {
                $this->success("编辑成功", url("admin/Advertisement/index"));
            } else {
                $this->error("编辑成功", url("admin/Advertisement/index"));
            }
        }
    }


    /**
     * [活动管理分组删除]
     * 郭杨
     */
    public function accessories_business_del($id)
    {
        $bool = db("teahost")->where("id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/Advertisement/index"));
        } else {
            $this->error("删除失败", url("admin/Advertisement/index"));
        }
    }


    /**
     * [活动分类分组ajax显示]
     * 郭杨
     * @param int $pid
     * 
     */
/*    public function ajax_add($pid = 0){
        $goods_list = [];
        if($pid == 0){
            $goods_list = getSelectList("goods_type");
        }
        return ajax_success("获取成功",$goods_list);
    }*/

    
    /**
     * [活动分类图片删除]
     * 郭杨
     */
    public function accessories_business_images(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(['id'])['id'];
            $image_url = db("teahost")->where("id", $id)->field("classify_image")->find();
            $bool = db("teahost")->where("id", $id)->field("classify_image")->update(["classify_image" => null]);
            if ($bool) {
                return ajax_success("删除成功");
            } else {
                return ajax_error("删除失败");
            }
        }
    }


    /**
     * [活动管理分组批量删除]
     * 郭杨
     *  
     */
    public function accessories_business_dels(Request $request)
    {
        if ($request->isPost()) {
            $id = $_POST['id'];
            if (is_array($id)) {
                $where = 'id in(' . implode(',', $id) . ')';
            } else {
                $where = 'id=' . $id;
            }
            $list = Db::name('teahost')->where($where)->delete();
            if ($list !== false) {
                return ajax_success('成功删除!', ['status' => 1]);
            } else {
                return ajax_error('删除失败', ['status' => 0]);
            }
        }
    }




    /**
     * [活动管理推荐状态修改]
     * 郭杨
     */
    public function accessories_business_label(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("teahost")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Advertisement/index"));
                } else {
                    $this->error("修改失败", url("admin/Advertisement/index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("teahost")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Advertisement/index"));
                } else {
                    $this->error("修改失败", url("admin/Advertisement/index"));
                }
            }
        }
    }


    /**
     * [活动管理模糊搜索]
     * 郭杨
     */
    public function accessories_business_search()
    {
        $store_id = Session::get("store_id");
        $name = input('titles');   //活动名称
        $store_id = input('titles');   //活动名称
        $label = input('labely');  //活动标签
        $datetime = input('times'); //是否过期 0:过期 1:未过期
        $present_time = time();

        if ((!empty($name)) && (!empty($label)) || (!empty($datetime))) {

                $data = db('teahost')->where("activity_name", "like", "%" . $name . "%")->where("store_id","EQ",$store_id)->select();
                foreach ($data as $key => $value) {
                    if ($value["pid"]) {
                        $res = db("goods_type")->where("id", $value['pid'])->field("name")->find();
                        $data[$key]["named"] = $res["name"];
                    }
                }
                    
            $all_idents = $data;//这里是需要分页的数据
            $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
            $listRow = 5;//每页5行记录
            $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
            $data = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
                'var_page' => 'page',
                'path' => url('admin/Advertisement/index'),//这里根据需要修改url
                'query' => [],
                'fragment' => '',
            ]);
            $data->appends($_GET);
            $this->assign('access', $data->render());
            return view("accessories_business_advertising", ["accessories" => $data]);
        }
        if ((empty($name)) && (!empty($lable))) {
            if ($datetime == 0) {
                $data = db('teahost')->where("start_time", '<', '$present_time')->where("store_id","EQ",$store_id)->where("label", "like", "%" . $label . "%")->select();
                foreach ($data as $key => $value) {
                    if ($value["pid"]) {
                        $res = db("goods_type")->where("id", $value['pid'])->field("name")->find();
                        $data[$key]["named"] = $res["name"];
                    }
                }
            }
            if ($datetime == 1) {
                $data = db('teahost')->where("start_time", '>', '$present_time')->where("store_id","EQ",$store_id)->where("label", "like", "%" . $label . "%")->select();
                foreach ($data as $key => $value) {
                    if ($value["pid"]) {
                        $res = db("goods_type")->where("id", $value['pid'])->field("name")->find();
                        $data[$key]["named"] = $res["name"];
                    }
                }
            }
            
            $all_idents = $data;//这里是需要分页的数据
            $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
            $listRow = 5;//每页5行记录
            $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
            $data = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
                'var_page' => 'page',
                'path' => url('admin/Advertisement/index'),//这里根据需要修改url
                'query' => [],
                'fragment' => '',
            ]);
            $data->appends($_GET);
            $this->assign('access', $data->render());
            return view("accessories_business_advertising", ["accessories" => $data]);
        }
    }

    /**
     * [活动管理显示]
     * 郭杨
     */
    public function accessories_advertising_search()
    {

        $store_id = Session::get("store_id");
        $time = time();
        $titles = input('titles')?input('titles'):null;
        $pdd = input('ppd')?input('ppd'):null;
        $times = input('times')?input('times'):null;
        if($times == 1){
            $time_condition  = "start_time>{$time}";
        } elseif($times == 2){
            $time_condition  = "start_time<{$time}";
        }
        if((!empty($titles)) && (!empty($ppd)) && (!empty($times))){
            $accessories = db("teahost")
            ->where("store_id","EQ",$store_id)
            ->where("pid","EQ",$pdd)
            ->where($time_condition)
            ->where("activity_name", "like", "%" . $titles . "%")
            ->order("order_ing desc")
            ->select();
        } elseif((!empty($titles)) && (empty($ppd)) && (empty($times))){
            $accessories = db("teahost")
            ->where("store_id","EQ",$store_id)
            ->where("activity_name", "like", "%" . $titles . "%")
            ->order("order_ing desc")
            ->select();
        } elseif((empty($titles)) && (!empty($ppd)) && (empty($times))){
            $accessories = db("teahost")
            ->where("store_id","EQ",$store_id)
            ->where("pid","EQ",$pdd)
            ->order("order_ing desc")
            ->select();
        } elseif((empty($titles)) && (empty($ppd)) && (!empty($times))){
            $accessories = db("teahost")
            ->where("store_id","EQ",$store_id)
            ->where($time_condition)
            ->order("order_ing desc")
            ->select();
        } elseif((!empty($titles)) && (!empty($ppd)) && (empty($times))){
            $accessories = db("teahost")
            ->where("store_id","EQ",$store_id)
            ->where("pid","EQ",$pdd)
            ->where("activity_name", "like", "%" . $titles . "%")
            ->order("order_ing desc")
            ->select();
        } elseif((!empty($titles)) && (empty($ppd)) && (!empty($times))){
            $accessories = db("teahost")
            ->where("store_id","EQ",$store_id)
            ->where($time_condition)
            ->where("activity_name", "like", "%" . $titles . "%")
            ->order("order_ing desc")
            ->select();
        } elseif((empty($titles)) && (!empty($ppd)) && (!empty($times))){
            $accessories = db("teahost")
            ->where("store_id","EQ",$store_id)
            ->where($time_condition)
            ->where("pid", $ppd)
            ->order("order_ing desc")
            ->select();
        } elseif((empty($titles)) && (empty($ppd)) && (empty($times))){
            $accessories = db("teahost")
            ->where("store_id","EQ",$store_id)
            ->order("order_ing desc")
            ->select();
        }
        foreach ($accessories as $key => $value) {
            if ($value["pid"]) {
                $res = db("goods_type")->where("id", $value['pid'])->field("name")->find();
                $accessories[$key]["named"] = $res["name"];
            }
        }
        $all_idents = $accessories;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $accessories = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Advertisement/index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $accessories->appends($_GET);
        $this->assign('access', $accessories->render());
        $goods = [];
        $goods = getSelectListes("goods_type");
        return view("accessories_business_advertising", ["accessories" => $accessories,'goods'=>$goods]);
    }
}











