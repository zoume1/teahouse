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
               
        return view('bonus_index');
    }


    /**
     * [积分商城编辑]
     * GY
     */
    public function bonus_edit()
    {
        return view('bonus_edit');
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
    public function coupon_add(){
        return view('coupon_add');
    }



    /**
     * [优惠券保存入库]
     * GY
     */
    public function coupon_save(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();
            //halt($data);


            $bool = db("coupon")->insert($data);
            if ($bool) {
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
    public function coupon_edit(){
        return view('coupon_edit');
    }







}