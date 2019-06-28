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
use think\Session;
use think\Request;
use think\paginator\driver\Bootstrap;

class  Limitations extends  Controller{

    /**
     * [限时限购显示]
     * GY
     */
    public function limitations_index() 
    {
        $store_id = Session::get("store_id");
        $limit = db("limited")
            ->where("store_id","EQ",$store_id)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        
        return view('limitations_index',["limit"=>$limit]);
    }


    /**
     * [限时限购编辑]
     * GY
     */
    public function limitations_edit($id)
    {
        $store_id = Session::get("store_id");
        $scope2 = db("member_grade")
        ->where("store_id","EQ",$store_id)
        ->field("member_grade_name")
        ->select();   //会员等级
        $limited = db("limited")->where("id", $id)->find();
        $scope=json_decode($limited['limit_condition'],true); 
        foreach($scope2 as $k=>$v)
        {
            $ture=0;
            foreach($scope['scope']['scope_type'] as $k2 =>$v2){
                 if($v2==$v['member_grade_name']){
                    $scope2[$k]['check']='1';
                    $ture=1;
                    break;
                 }
            }
            if($ture=='0'){
                $scope2[$k]['check']='0';
            }
        }
        halt($scope);
        return view('limitations_edit', ["limit" => $limited, "scope" => $scope,'scope2'=>$scope2]);

    }
    /**
     * [限时限购添加商品]
     * GY
     */
    public function limitations_add()
    {
        $store_id = Session::get("store_id");
        $scope = db("member_grade")
        ->where("store_id","EQ",$store_id)
        ->field("member_grade_name")
        ->select();
        return view('limitations_add', ["scope" => $scope]);
    }

    /**
     * 李禄
     * [限时限购保存商品]
     */
     public function limitations_save(Request $request)
     {
        if ($request->isPost()) {
             $data = $request->param();    //获取参数
             $store_id = Session::get("store_id");    //获取店铺id 
            $data["stroe_id"] = $store_id;
            if(array_key_exists('type',$data) || array_key_exists('status',$data) || array_key_exists('limit_status',$data))
            {    //限购和秒杀至少一个(面向范围可以为空)
                 if(array_key_exists('type',$data))
                 {   //限购
                    $map['scope_type']=$data['type'];     
                    
                 }else{
                     $map['scope_type']='0';      //面向所有的会员
                 }
                 if(array_key_exists('limit_status',$data))    //限购设置
                    {
                        if($data['number']=='0')
                        {
                            $map4['limit_status']='1';
                            $map4['number']='-1';   //不限购数量
                        }else{
                            $map4['number']=$data['number'];
                            $map4['limit_status']='1';
                        }
                    }else{
                        $map4['limit_status']='0';
                        $map4['number']='0';
                    }
                 if(array_key_exists('status',$data))  //开启秒杀
                 {    
                    $map2['miao_status']='1';
                    $map2['start_time']=strtotime($data['start_time']);
                    $map2['end_time']=strtotime($data['end_time']);
                 }else{
                    $map2['miao_status']='0';
                    $map2['start_time']='0';
                    $map2['end_time']='0';
                 }
                 $pp['scope']=$map;
                 $map3['label']=$data['label'];
                 $pp['limit']=$map4;
                 $pp['miao']=$map2;
                 $pp['label']=$map3;
                 $pp=json_encode($pp);      //限时限购的条件
            }else{
                $this->error('限购设置和开启秒杀至少选中一个');
            }
             //判断是否为编辑
            if(array_key_exists('id',$data))  
            {
                $pp2['limit_condition']=$pp;
                $res2=db('limited')->where('id',$data['id'])->update($pp2);
                if($res2)
                {
                    $this->success('保存成功');
                }
            }
            if (!empty($data["goods_id"])) {
                foreach ($data["goods_id"] as $key => $value) {
                    $goods[$key] = db("goods")->where("id", $data["goods_id"][$key])->where("store_id","EQ",$store_id)->field("id,goods_number,goods_show_images,goods_name,goods_standard,goods_repertory")->find();
                }
            }
            if (!empty($goods)) {
                foreach ($goods as $key => $value) {
                    $goods[$key]["goods_id"] = $goods[$key]["id"];
                    if ($goods[$key]["goods_standard"] == 1) {
                        $goods[$key]["goods_repertory"] = db("special")->where("goods_id", $goods[$key]["id"])->sum("stock");
                        $goods[$key]["goods_show_images"] = explode(",", $goods[$key]["goods_show_images"])[0];
                    } else {
                        $goods[$key]["goods_show_images"] = explode(",", $goods[$key]["goods_show_images"])[0];
                    }
                    unset($goods[$key]["id"]);
                    $goods[$key]['limit_condition']=$pp;
                    if($map=='0')
                    {    //未开启限购
                        $goods[$key]['limit_number']=0;
                    }else{
                        $goods[$key]['limit_number']=$map4['number'];
                        
                    }
                    //时间限制
                    if($map2['miao_status']=='0')
                    {     //未开启秒杀
                        $goods[$key]['create_time']='0';
                        $goods[$key]['end_time']='0';
                    }else{
                        $goods[$key]['create_time']=strtotime($data['start_time']);
                        $goods[$key]['end_time']=strtotime($data['end_time']);
                    }
                    $goods[$key]['store_id']=$store_id;
                  
                }
                foreach ($goods as $k => $v) {
                    //判断商品是否已限时限购
                    $is_limit=db('limited')->where('goods_id',$data['goods_id'][$k])->find();
                    if($is_limit)
                    {
                        $rest = db("limited")->where('id',$is_limit['id'])->update($v);
                    }else
                    {
                        $rest = db("limited")->insert($v);
                    }
                }
            }
            if ($rest) {
                $this->success("添加成功", url("admin/Limitations/limitations_index"));
            } else {
                $this->error("添加失败", url("admin/Limitations/limitations_add"));
            }

        }
     }
   
     /**
     * 李禄
     * [限时限购保存商品]
     */
     public function limitations_save_do(Request $request)
     {
        if ($request->isPost()) {
             $data = $request->param();    //获取参数
             $store_id = Session::get("store_id");    //获取店铺id 
            $data["stroe_id"] = $store_id;
           
            if(array_key_exists('aa',$data) || array_key_exists('status',$data))
            {    //限购和秒杀至少一个
                 if(array_key_exists('aa',$data))
                 {   //限购
                    $map['scope']='1';
                    $map['scope_type']=$data['type'];
                    if(array_key_exists('limit_status',$data))
                    {
                        if($data['number']=='0')
                        {
                            $map['limit_status']='1';
                            $map['number']='-1';   //不限购数量
                        }else{
                            $map['number']=$data['number'];
                            $map['limit_status']='1';
                        }
                    }else{
                        $map['limit_status']='0';
                        $map['number']='0';
                    }
                 }else{
                     $map='0';
                 }
                 if(array_key_exists('status',$data))
                 {    //开启秒杀
                    $map2['miao_status']='1';
                    $map2['start_time']=strtotime($data['start_time']);
                    $map2['end_time']=strtotime($data['end_time']);
                 }else{
                    $map2['miao_status']='0';
                    $map2['start_time']='0';
                    $map2['end_time']='0';
                 }
                 $map3['label']=$data['label'];
                 $pp['limit']=$map;
                 $pp['miao']=$map2;
                 $pp['label']=$map3;
                 $pp=json_encode($pp);      //限时限购的条件
            }else{
                $this->error('限购设置和开启秒杀至少选中一个');
            }
            $pp2['limit_condition']=$pp;
            $res2=db('limited')->where('id',$data['id'])->update($pp2);   //更改限制条件
            if(array_key_exists('status',$data))
            {
                $op['create_time']=strtotime($data['start_time']);
                $op['end_time']=strtotime($data['end_time']);
                $res3=db('limited')->where('id',$data['id'])->update($op);
            }else{
                $op['create_time']=0;
                $op['end_time']=0;
                $res3=db('limited')->where('id',$data['id'])->update($op);
            }
            if(array_key_exists('aa',$data))
            {
                $op2['limit_number']=$data['number'];
                $res4=db('limited')->where('id',$data['id'])->update($op2);
            }
            $this->success('保存成功',url('admin/Limitations/limitations_index'));
        }
     }

     /**
     * [限时限购更新]
     * GY
     */
    public function limitations_update(Request $request)
    {

        if ($request->isPost()) {
            $data = $request->param();
            $data["scope"] = implode(",", $data["scope"]);
            $id = $request->only(["id"])["id"];
            if (!empty($data["goods_id"])) {
                foreach ($data["goods_id"] as $key => $value) {
                    $goodes[$key] = db("goods")->where("id", $data["goods_id"][$key])->field("id,goods_number,goods_show_images,goods_name,goods_standard,goods_repertory")->find();

                }
                unset($data["goods_id"]);
            }
            unset($data["id"]);
            unset($data["goods_number"]);
            $bool = db("limit")->where('id', $id)->update($data);
            $boole = db("limited")->where('limit_id', $id)->update($data);
            if (!empty($goodes)) {
                foreach ($goodes as $key => $value) {
                    $goodes[$key]["goods_id"] = $goodes[$key]["id"];
                    $goodes[$key]["limit_id"] = $request->only(["id"])["id"];
                    $goodes[$key]["number"] = $data["number"];
                    $goodes[$key]["time"] = $data["time"];
                    $goodes[$key]["label"] = $data["label"];
                    $goodes[$key]["scope"] = $data["scope"];

                    if ($goodes[$key]["goods_standard"] == 1) {
                        $goodes[$key]["goods_repertory"] = db("special")->where("goods_id", $goodes[$key]["id"])->sum("stock");
                        $goodes[$key]["goods_show_images"] = explode(",", $goodes[$key]["goods_show_images"])[0];
                    } else {
                        $goodes[$key]["goods_show_images"] = explode(",", $goodes[$key]["goods_show_images"])[0];
                    }
                    unset($goodes[$key]["id"]);
                }
                
                foreach ($goodes as $k => $v) {
                    $rest = db("limited")->insert($v);
                }
            }

            if ($bool || $rest ) {
                $this->success("编辑成功", url("admin/Limitations/limitations_index"));
            } else {
                $this->error("编辑失败", url("admin/Limitations/limitations_index"));
            }
        }
    }


     /**
     * [限时限购删除商品]
     * GY
     */
     public function limitations_delete($id)
     {
        $bool = db("limited")->where("id", $id)->delete();

        if ($bool ) {
            $this->success("删除成功", url("admin/Limitations/limitations_index"));
        } else {
            $this->error("删除失败", url("admin/Limitations/limitations_index"));
        }
     }

     /**
     * [限时限购搜索商品]
     * GY
     */
     public function limitations_search()
     {
         return view('limitations_add');
     }
     /**
      * lilu
      * 限时限购检索
      */
      /**
     * [优惠券搜索商品]
     * GY
     */
    public function coupon_search2(Request $request)
    {
        $goods_number = input("goods_number");
        $store_id = Session::get("store_id");
        $goods = db("goods")
                ->where('goods_number','like','%'.$goods_number.'%')
                ->where("store_id","EQ",$store_id)
                ->field("id,goods_number,goods_show_images,goods_name,goods_standard,goods_repertory,coupon_type")
                ->select();
        if(!empty($goods)){
            foreach ($goods as $key => $value) {
                //获取商品图片
                if ($goods[$key]["goods_standard"] == 1) {
                    $goods[$key]["goods_repertory"] = db("special")->where("goods_id", $goods[$key]["id"])->sum("stock");
                    $goods[$key]["goods_show_images"] = explode(",", $goods[$key]["goods_show_images"])[0];
                } else {
                    $goods[$key]["goods_show_images"] = explode(",", $goods[$key]["goods_show_images"])[0];
                }
                //判断商品是否已限时限购
                $is_limit=db('limited')->where('goods_id',$value['id'])->find();
                if($is_limit)
                {
                    $goods[$key]['is_limit']='已设限购活动未结束';
                }else
                {
                    $goods[$key]['is_limit']='未设限购活动';
                }
            }
            return ajax_success("获取成功", $goods);
        } else {
            // $id = $goods_number - 1000000;
            // $key = 0;
            // $crowd = db("crowd_goods")->where("id", $id)->field("id,goods_show_image,project_name,coupon_type")->find();
            // if(!empty($crowd)){
            // $crowd['goods_repertory'] = db("crowd_special")->where("goods_id",$crowd['id'])->sum("stock");
            // $crowd_goods[$key] = array(
            //     'id'=> $crowd['id'],
            //     'goods_number'=> $crowd['id'] +1000000,
            //     'goods_name'=> $crowd['project_name'],
            //     'goods_standard'=> 1,
            //     'goods_show_images'=> $crowd['goods_show_image'],
            //     'goods_repertory'=> $crowd['goods_repertory'],
            //     'coupon_type'=> $crowd['coupon_type']
            // );
            // return ajax_success("获取成功", $crowd_goods);
            // } else {
            //     return ajax_error("未找到该商品");
            // }
            return ajax_error('获取失败');
        }
    }






}