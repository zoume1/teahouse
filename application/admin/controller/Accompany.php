<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/12/23
 */
namespace  app\admin\controller;
use think\Db;
use think\paginator\driver\Bootstrap;
use think\Session;
use think\Request;
use app\admin\model\Goods;  
use app\admin\model\Accompany as Accompanyes;
use app\admin\model\AccompanySetting ;


class  Accompany extends  Controller{
    
    /**
     * [送存商品页面]
     * 郭杨
     */    
    public function accompany_index(){
        $search = input();
        $data = Accompanyes::accompany_index($search);
        return view("accompany_index",['data'=>$data]);
    }

    /**
     * [送存商品添加]
     * 郭杨
     */    
    public function accompany_add(Request $request){
        $store_id =  Session :: get('store_id');
        if($request -> isPost()){
            $data =  Request::instance()->param();
            $rest =(new Accompanyes())->accompany_add($data);
            if($rest){
                $this->success("添加成功", url("admin/Accompany/accompany_index"));
            }
            $this->success("添加失败", url('admin/Accompany/accompany_index'));
        }
        //送存仓储
        $store_name = Db::name("store_house")->where("store_id",$store_id)->select(); 
        //面向会员
        $scope = Db::name("member_grade")->where("store_id","EQ",$store_id)->field("member_grade_id,member_grade_name")->select();

        return view("accompany_add",['store_name'=>$store_name,'scope' =>$scope]);
    }

    /**
     * [送存商品详情]
     * 郭杨
     */    
    public function accompany_edit($id){
        $store_id =  Session :: get('store_id');
        $scope = Db::name("member_grade")->where("store_id","EQ",$store_id)->field("member_grade_id,member_grade_name")->select();
        $data = Accompanyes::detail($id);
        $data['start_time'] = date('Y-m-s h:i',$data['start_time']);
        $data['end_time'] = date('Y-m-s h:i',$data['end_time']);
        $data['scope'] = json_decode($data['scope']);
        $setting = AccompanySetting::detail(['accompany_id' => $id]);
        if(!empty($setting)){
            if($setting['min_price'] == 0){
                $setting['min_price'] = null;
            }
            if($setting['min_number'] == 0){
                $setting['min_number'] = null;
            }
        }
        return view("accompany_edit",['scope'=>$scope,'data'=>$data,'setting'=>$setting]);
    }

    /**
     * [商品编码搜索商品]
     */
    public function serach_accompany(Request $request){
        if($request -> isPost()){
            $goods_number = $request->only(['goods_number'])['goods_number'];
            return Goods::accompany_goods($goods_number);
        }
    }

    /**
     * [送存商品删除]
     * 郭杨
     */    
    public function accompany_del($id){

        $rest = Db::name('accompany')->where('id','=',$id)->update(['is_del'=>1]);
        if($rest){
            $this->success("删除成功", url("admin/Accompany/accompany_index"));
        }
        $this->success("删除失败", url('admin/Accompany/accompany_index'));
    }


    /**
     * [送存商品批量码下载]
     * 郭杨
     */    
    public function accompany_download(Request $request){
        if($request -> isPost()) {
            $id = $request->only(['id'])['id'];
            $zip = Goods::addFileToZip($id);
            return jsonSuccess('发送成功',$zip);
        }

    }

    
}