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
     * [增值物流商品添加]
     * 郭杨
     */    
    public function analyse_add(Request $request){
            if ($request->isPost()) {
                $goods_data = $request->param(); 
                $show_images = $request->file("goods_show_images");
                $imgs = $request->file("imgs");
                $list = [];
    
                if (!empty($show_images)) {              
                    foreach ($show_images as $k=>$v) {
                        $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $list[] = str_replace("\\", "/", $info->getSaveName());
                    }            
                    $goods_data["goods_show_image"] =  $list[0];
                    $goods_data["goods_type"] = 1;     //商品类型
                    $goods_data["goods_show_images"] = implode(',', $list);
                }
                          
               
                if ($goods_data["goods_standard"] == "0") {
                    halt($goods_data);
                    $bool = db("goods")->insert($goods_data);
                    if ($bool && (!empty($show_images))) {
                        $this->success("添加成功", url("admin/Goods/index"));
                    } else {
                        $this->success("添加失败", url('admin/Goods/add'));
                    }
                }
                if ($goods_data["goods_standard"] == "1") {
                    $goods_special = [];
                    $goods_special["goods_name"] = $goods_data["goods_name"];
                    $goods_special["produce"] = $goods_data["produce"];
                    $goods_special["goods_type"] = $goods_data["goods_type"];
                    $goods_special["brand"] = $goods_data["brand"];
                    $goods_special["goods_number"] = $goods_data["goods_number"];
                    $goods_special["goods_standard"] = $goods_data["goods_standard"];
                    $goods_special["goods_selling"] = $goods_data["goods_selling"];
                    $goods_special["goods_sign"] = $goods_data["goods_sign"];
                    $goods_special["goods_describe"] = $goods_data["goods_describe"];
                    $goods_special["product_type"] = $goods_data["product_type"];
                    $goods_special["sort_number"] = $goods_data["sort_number"];
                    $goods_special["video_link"] = $goods_data["video_link"];
                    $goods_special["goods_delivery"] = $goods_data["goods_delivery"];
                    $goods_special["goods_franking"] = $goods_data["goods_franking"];
                    $goods_special["templet_id"] = $goods_data["templet_id"];
                    $goods_special["label"] = $goods_data["label"];
                    
    
                    if (isset($goods_data["goods_text"])) {
                        $goods_special["goods_text"] = $goods_data["goods_text"];
                    } else {
                        $goods_special["goods_text"] = null;
                        $goods_data["goods_text"] = null;
                    }

                    $goods_special["goods_show_images"] = $goods_data["goods_show_images"];
                    $goods_special["goods_show_image"] = $goods_data["goods_show_image"];
                    $result = implode(",", $goods_data["lv1"]);
                    halt($goods_data);
                    $goods_id = db('analyse_goods')->insertGetId($goods_special);
                    
                    if (!empty($goods_data)) {
                        foreach ($goods_data as $kn => $nl) {
                            if (substr($kn, 0, 3) == "sss") {
                                $price[] = $nl["price"];
                                $stock[] = $nl["stock"];
                                $coding[] = $nl["coding"];
                                $cost[] = $nl["cost"];
                                $line[] = $nl["line"];
                                if (isset($nl["status"])) {
                                    $status[] = $nl["status"];
                                } else {
                                    $status[] = "0";
                                }
                                if (isset($nl["save"])) {
                                    $save[] = $nl["save"];
                                } else {
                                    $save[] = "0";
                                }
                            }
                                
                        }
    
                }
                    if (!empty($imgs)) {
                        foreach ($imgs as $k => $v) {
                            $shows = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                            $tab = str_replace("\\", "/", $shows->getSaveName());
    
                            if (is_array($goods_data)) {
                                foreach ($goods_data as $key => $value) {
                                    if (substr($key, 0, 3) == "sss") {
                                        $str[] = substr($key, 3);
                                        $values[$k]["name"] = $str[$k];
                                        $values[$k]["price"] = $price[$k];
                                        $values[$k]["lv1"] = $result;
                                        $values[$k]["stock"] = $stock[$k];
                                        $values[$k]["coding"] = $coding[$k];
                                        $values[$k]["save"] = $save[$k];
                                        $values[$k]["cost"] = $cost[$k];
                                        $values[$k]["line"] = $line[$k];                                    
                                        $values[$k]["images"] = $tab;
                                        $values[$k]["goods_id"] = $goods_id;                                       
                                    }
                                }
                            }
                        }
                    }
    
                    foreach ($values as $kz => $vw) {
                        $rest = db('analyse_special')->insertGetId($vw);
                    }    
                    if ($rest && (!empty($show_images))) {
                        $this->success("添加成功", url("admin/Goods/index"));
                    } else {
                        $this->success("添加失败", url('admin/Goods/add'));
                    }
                }
            }     
                     return view("analyse_add");
    }

    /**
     * [增值虚拟商品添加]
     * 郭杨
     */    
    public function analyse_invented(){     
        return view("analyse_invented");
    }

    /**
     * [增值商品编辑]
     * 郭杨
     */    
    public function analyse_edit(){     
        return view("analyse_edit");
    }



    /**
     * [增值商品编辑更新]
     * 郭杨
     */    
    public function analyse_update(){     
        
    }



    /**
     * [增值商品删除]
     * 郭杨
     */    
    public function analyse_delete(){     
        
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