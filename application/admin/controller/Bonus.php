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

class  Bonus extends  Controller{

    /**
     * [积分商城显示]
     * GY
     */
    public function bonus_index() 
    {
        $bonus = db("bonus_mall")->paginate(20);
             
        return view('bonus_index',["bonus" => $bonus]);
    }



    /**
     * [积分商城添加商品]
     * GY
     */
    public function bonus_add()
    {
        return view('bonus_add');
    }


    /**
     * [积分商城保存商品]
     * GY
     */
    public function bonus_save(Request $request)
    {
        if ($request->isPost()) {
            $goods_data = $request->param();
            $list = [];
            $show_images = $request->file("goods_show_images");


            if (!empty($show_images)) {
                foreach ($show_images as $ky => $vl) {
                    $show = $vl->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $list[] = str_replace("\\", "/", $show->getSaveName());
                }
                $goods_data["goods_show_images"] = implode(',', $list);
            }
//            halt($goods_data);
            $bool = db("bonus_mall")->insert($goods_data);
            if ($bool) {
                $this->success("添加成功", url("admin/Bonus/bonus_index"));
            } else {
                $this->success("添加失败", url('admin/Bonus/bonus_index'));
            }
        }

    }

    /**
     * [积分商城编辑商品]
     * GY
     */
    public function bonus_edit()
    {
        return view('bonus_edit');
    }


    /**
     * [积分商城更新商品]
     * GY
     */
    public function bonus_update()
    {
        return view('bonus_edit');
    }


    /**
     * [积分商城删除商品]
     * GY
     */
    public function bonus_delete()
    {
        return view('bonus_edit');
    }




    /**
     * [优惠券显示]
     * GY
     */
    public function coupon_index()
    {
        $coupon = db("coupon")->paginate(20);
        return view('coupon_index',["coupon" => $coupon]);
    }


    /**
     * [优惠券添加]
     * GY
     */
    public function coupon_add()
    {
        $scope = db("member_grade")->field("member_grade_name")->select();
        return view('coupon_add',["scope"=>$scope]);
    }



    /**
     * [优惠券保存入库]
     * GY
     */
    public function coupon_save(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();           
            // $data["start_time"] = strtotime($data["start_time"]);
            // $data["end_time"] = strtotime($data["end_time"]);
            $data["scope"] = implode(",",$data["scope"]);
            if(!empty($data["goods_id"])){
            foreach($data["goods_id"] as $key => $value)
            {
                $goods[$key] = db("goods")->where("id",$data["goods_id"][$key])->field("id,goods_number,goods_show_images,goods_name,goods_standard,goods_repertory")->find();
            }
            unset($data["goods_id"]);
            }
            $coupon_id = db("coupon")->insertGetId($data);
            if(!empty($goods)){
            foreach($goods as $key => $value){
                $goods[$key]["goods_id"] =  $goods[$key]["id"];
                $goods[$key]["coupon_id"] =  $coupon_id;

                if($goods[$key]["goods_standard"] == 1)
                {
                    $goods[$key]["goods_repertory"] = db("special")->where("goods_id",$goods[$key]["id"])->sum("stock");
                    $goods[$key]["goods_show_images"] = explode(",",$goods[$key]["goods_show_images"])[0];
                } else {
                    $goods[$key]["goods_show_images"] = explode(",",$goods[$key]["goods_show_images"])[0];
                }
                unset($goods[$key]["id"]);
            }

            foreach ($goods as $k => $v) {
                $rest = db("join")->insert($v);
            }
        }
            if ($coupon_id || $rest) {
                $this->success("添加成功", url("admin/Bonus/coupon_index"));
            } else {
                $this->error("添加失败", url("admin/Bonus/coupon_add"));
            }
        }
    }


    /**
     * [优惠券编辑]
     * GY
     */
    public function coupon_edit($id)
    {
        $coupons = db("coupon")->where("id", $id)->select();
        foreach($coupons as $k => $v)
        {
            $coupons[$k]["scope"] = explode(",",$coupons[$k]["scope"]);
        }
        
        $scope = db("member_grade")->field("member_grade_name")->select();
        return view('coupon_edit',["coupons"=>$coupons,"scope"=>$scope]);
        
        
    }


    /**
     * [优惠券编辑]
     * GY
     */
    public function coupon_weave(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $join = db("join")->where("coupon_id",$id)->select();  
            if (!empty($join) && !empty($id)) 
            {
                return ajax_success("获取成功", $join);
            } else {
                return ajax_error("获取失败优惠券失败");
            }
            
        }
              
    }



    /**
     * [优惠券更新]
     * GY
     */
    public function coupon_update(Request $request){
        
        if ($request->isPost()) {
            $data = $request->param();
            $data["scope"] = implode(",",$data["scope"]);
            
            if(!empty($data["goods_id"])){
            foreach($data["goods_id"] as $key => $value)
            {
                $goodes[$key] = db("goods")->where("id",$data["goods_id"][$key])->field("id,goods_number,goods_show_images,goods_name,goods_standard,goods_repertory")->find();

            }
            unset($data["goods_id"]);
        }
            unset($data["goods_number"]);
            if(!empty($goodes)){
            foreach($goodes as $key => $value){
                $goodes[$key]["goods_id"] =  $goodes[$key]["id"];
                $goodes[$key]["coupon_id"] =  $request->only(["id"])["id"];

                if($goodes[$key]["goods_standard"] == 1)
                {
                    $goodes[$key]["goods_repertory"] = db("special")->where("goods_id",$goodes[$key]["id"])->sum("stock");
                    $goodes[$key]["goods_show_images"] = explode(",",$goodes[$key]["goods_show_images"])[0];
                } else {
                    $goodes[$key]["goods_show_images"] = explode(",",$goodes[$key]["goods_show_images"])[0];
                }
                unset($goodes[$key]["id"]);
            }
            foreach ($goodes as $k => $v) {
                $rest = db("join")->insert($v);
            }
        }
            $bool = db("coupon")->where('id', $request->only(["id"])["id"])->update($data);

            if ($bool || $rest) {
                $this->success("编辑成功", url("admin/Bonus/coupon_index"));
            } else {
                $this->error("编辑失败", url("admin/Bonus/coupon_index"));
            }
        }
    }



    /**
     * [优惠券删除]
     * GY
     */
    public function coupon_del($id)
    {
        $bool = db("coupon")->where("id", $id)->delete();
        $boole = db("join")->where("coupon_id", $id)->delete();
        if ($bool && $boole) {
            $this->success("删除成功", url("admin/Category/index"));
        } else {
            $this->error("删除失败", url("admin/Category/index"));
        }
    }



    /**
     * [优惠券搜索商品]
     * GY
     */
    public function coupon_search(Request $request)
    {
        $goods_number = input("goods_number");
        $goods = db("goods")->where("goods_number",$goods_number)->field("id,goods_number,goods_show_images,goods_name,goods_standard,goods_repertory")->select();

        foreach($goods as $key => $value){        
            if($goods[$key]["goods_standard"] == 1)
            {
                $goods[$key]["goods_repertory"] = db("special")->where("goods_id",$goods[$key]["id"])->sum("stock");
                $goods[$key]["goods_show_images"] = explode(",",$goods[$key]["goods_show_images"])[0];
            } else {
                $goods[$key]["goods_show_images"] = explode(",",$goods[$key]["goods_show_images"])[0];
            }
        }

        if (!empty($goods) && !empty($goods_number)) {
            return ajax_success("获取成功", $goods);
        } else {
            return ajax_error("获取失败商品信息");
        }
    }





}