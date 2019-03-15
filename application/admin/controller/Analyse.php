<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;
use think\Request;
use think\Controller;
use think\Db;
use think\paginator\driver\Bootstrap;

class  Analyse extends  Controller{
    
    /**
     * [增值商品]
     * 郭杨
     */    
    public function analyse_index(){     
        return view("analyse_index");
    }

    
    /**
     * [增值商品添加]
     * 郭杨
     */    
    public function analyse_add(){     
        return view("analyse_add");
    }


    /**
     * [增值订单]
     * 郭杨
     */    
    public function analyse_order_index(){     
        return view("analyse_order_index");
    }


    /**
     * [退款维权]
     * 郭杨
     */    
    public function analyse_refund_index(){     
        return view("analyse_refund_index");
    }


    /**
     * [SEO优化]
     * 郭杨
     */    
    public function analyse_optimize_index(){  
        $optimize = db("seo_optimize")->where("id",1)->select();  
        return view("analyse_optimize_index",["optimize"=>$optimize]);
    }


    /**
     * [SEO优化更新]
     * 郭杨
     */    
    public function analyse_optimize_update(Request $request){
        if($request -> isPost()){
            $optimize_data = $request -> param();
            $bool = db("seo_optimize")->where("id",1)->update($optimize_data); 
            if($bool){
                $this->success('编辑成功', 'admin/Analyse/analyse_optimize_index');
            } else {
                $this ->error('编辑失败','admin/Analyse/analyse_optimize_index');
            }
            
        }  

    }


 }