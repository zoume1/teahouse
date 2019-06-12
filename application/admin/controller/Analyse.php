<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;
use think\Request;
use think\Controller;
use think\Session;
use think\Db;
use think\paginator\driver\Bootstrap;

class  Analyse extends  Controller{
    
    /**
     * [增值商品]
     * 郭杨
     */    
    public function analyse_index(){
        $analyse_data = db("analyse_goods")->where("label",1)->select();
        if(!empty($analyse_data)){
            foreach ($analyse_data as $key => $value) {
                    if($analyse_data[$key]["goods_standard"] == "1")
                    {
                        $max[$key] = db("analyse_special")->where("goods_id", $analyse_data[$key]['id'])->max("price");//最高价格
                        $min[$key] = db("analyse_special")->where("goods_id", $analyse_data[$key]['id'])->min("price");//最低价格
                        $analyse_data[$key]["goods_repertory"] = db("analyse_special")->where("goods_id", $analyse_data[$key]['id'])->sum("stock");//库存
                        $analyse_data[$key]["max_price"] = $max[$key];
                        $analyse_data[$key]["min_price"] = $min[$key];
                    }               
                }
            }   
        $url = 'admin/Analyse/analyse_index';
        $pag_number = 20;
        $analyse = paging_data($analyse_data,$url,$pag_number);
        return view("analyse_index",["analyse"=>$analyse]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:总控增值订单
     **************************************
     */
    public function  analyse_order(){

        return view("analyse_order");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:总控增值退款维权
     **************************************
     * @return \think\response\View
     */
    public function  analyse_after_sale(){
        return view("analyse_after_sale");
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
                    $bool = db("analyse_goods")->insert($goods_data);
                    if ($bool && (!empty($show_images))) {
                        $this->success("添加成功", url("admin/Analyse/analyse_index"));
                    } else {
                        $this->success("添加失败", url('admin/Analyse/analyse_index'));
                    }
                }
                if ($goods_data["goods_standard"] == "1") {
                    $goods_special = [];
                    $goods_special["goods_name"] = $goods_data["goods_name"];
                    $goods_special["store_id"] = $goods_data["store_id"];
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
                    $goods_special["status"] = $goods_data["status"];
                    
    
                    if (isset($goods_data["goods_text"])) {
                        $goods_special["goods_text"] = $goods_data["goods_text"];
                    } else {
                        $goods_special["goods_text"] = null;
                        $goods_data["goods_text"] = null;
                    }

                    $goods_special["goods_show_images"] = $goods_data["goods_show_images"];
                    $goods_special["goods_show_image"] = $goods_data["goods_show_image"];
                    $result = implode(",", $goods_data["lv1"]);
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
                        $this->success("添加成功", url("admin/Analyse/analyse_index"));
                    } else {
                        $this->success("添加失败", url('admin/Analyse/analyse_index'));
                    }
                }
            }     
                 return view("analyse_add");
    }

    /**
     * [增值虚拟商品添加]
     * 郭杨
     */    
    public function analyse_invented(Request $request){
        if($request->isPost()){
                $goods_data = $request->param(); 
                $store_id = Session::get("store_id");
                $show_images = $request->file("goods_show_images");
                $imgs = $request->file("imgs");
                $list = [];
    
                if (!empty($show_images)) {              
                    foreach ($show_images as $k=>$v) {
                        $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $list[] = str_replace("\\", "/", $info->getSaveName());
                    }            
                    $goods_data["goods_show_image"] =  $list[0];
                    $goods_data["goods_type"] = 2;     //商品类型
                    $goods_data["store_id"] = $store_id; //店铺id
                    $goods_data["goods_show_images"] = implode(',', $list);
                }
                
                if ($goods_data["goods_standard"] == "0") {
                    $bool = db("analyse_goods")->insert($goods_data);
                    if ($bool && (!empty($show_images))) {
                        $this->success("添加成功", url("admin/Analyse/analyse_index"));
                    } else {
                        $this->success("添加失败", url('admin/Analyse/analyse_index'));
                    }
                }
                if ($goods_data["goods_standard"] == "1") {
                    $goods_special = [];
                    $goods_special["goods_name"] = $goods_data["goods_name"];
                    $goods_special["store_id"] = $goods_data["store_id"];
                    $goods_special["goods_type"] = $goods_data["goods_type"];
                    $goods_special["goods_number"] = $goods_data["goods_number"];
                    $goods_special["goods_standard"] = $goods_data["goods_standard"];
                    $goods_special["goods_selling"] = $goods_data["goods_selling"];
                    $goods_special["goods_sign"] = $goods_data["goods_sign"];
                    $goods_special["product_type"] = $goods_data["product_type"];
                    $goods_special["sort_number"] = $goods_data["sort_number"];
                    $goods_special["video_link"] = $goods_data["video_link"];
                    $goods_special["label"] = $goods_data["label"];
                    $goods_special["status"] = $goods_data["status"];
                    
                       
                    if (isset($goods_data["goods_text"])) {
                        $goods_special["goods_text"] = $goods_data["goods_text"];
                    } else {
                        $goods_special["goods_text"] = null;
                        $goods_data["goods_text"] = null;
                    }

                    $goods_special["goods_show_images"] = $goods_data["goods_show_images"];
                    $goods_special["goods_show_image"] = $goods_data["goods_show_image"];
                    $result = implode(",", $goods_data["lv1"]);
                    $goods_id = db('analyse_goods')->insertGetId($goods_special);
                    
                    if (!empty($goods_data)) {
                        foreach ($goods_data as $kn => $nl) {
                            if (substr($kn, 0, 3) == "sss") {
                                $price[] = $nl["price"];
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
                        $this->success("添加成功", url("admin/Analyse/analyse_index"));
                    } else {
                        $this->success("添加失败", url('admin/Analyse/analyse_index'));
                    }
                }
           }     
            return view("analyse_invented");
    }

    /**
     * [增值商品编辑]
     * 郭杨
     */    
    public function analyse_edit($id){
        $analyse = db("analyse_goods")->where("id", $id)->select();
        $goods_standard = db("analyse_special")->where("goods_id", $id)->select();
        
        foreach ($analyse as $key => $value) {
            if(!empty($analyse[$key]["goods_show_images"])){
                $analyse[$key]["goods_show_images"] = explode(',', $analyse[$key]["goods_show_images"]);
          }
       }
       foreach ($goods_standard as $k => $v) {
            $goods_standard[$k]["title"] = explode('_', $v["name"]);
            $res = explode(',', $v["lv1"]);         
      }
        $restel = $analyse[0]["goods_standard"]; //判断是否为通用或特殊
        $goods_type = $analyse[0]["goods_type"]; //商品类型
        if(($restel == 1) && ($goods_type == 1)){
            return view("analyse_edit",["analyse"=>$analyse,"goods_standard" => $goods_standard]);
        } else {
            return view("analyse_edit",["analyse"=>$analyse,"goods_standard" => $goods_standard]);
        }
        
    }


    /**
     * [增值商品图片删除]
     * GY
     */
    public function analyse_images(Request $request)
    {
        if ($request->isPost()) {
            $tid = $request->param();
            $id = $tid["id"];
            $image = db("analyse_goods")->where("id", $tid['pid'])->field("goods_show_images")->find();
            if (!empty($image["goods_show_images"])) {
                $se = explode(",", $image["goods_show_images"]);
                foreach ($se as $key => $value) {
                    if ($value == $id) {
                        unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $value);
                    } else {
                        $new_image[] = $value;
                    }
                }
            }
            if (!empty($new_image)) {
                $new_imgs_url = implode(',', $new_image);
                $res = Db::name('goods')->where("id", $tid['pid'])->update(['goods_show_images' => $new_imgs_url]);
            } else {
                $res = Db::name('goods')->where("id", $tid['pid'])->update(['goods_show_images' => NULL,'goods_show_image' => NULL]);
            }
            if ($res) {
                return ajax_success('删除成功');
            } else {
                return ajax_success('删除失败');
            }
        }
    }

    /**
     * [增值商品编辑更新]
     * 郭杨
     */    
    public function analyse_update(Request $request){     
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $goods_data = $request->param();       
            $show_images = $request->file("goods_show_images");
          
            $list = [];
            if (!empty($show_images)) {
                foreach ($show_images as $k => $v) {
                    $show = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $list[] = str_replace("\\", "/", $show->getSaveName());
                }               
                    $liste = implode(',', $list);
                    $image = db("analyse_goods")->where("id", $id)->field("goods_show_images")->find();
                if(!empty($image["goods_show_images"]))
                {
                    $exper = $image["goods_show_images"];
                    $montage = $exper . "," . $liste;
                    $goods_data["goods_show_images"] = $montage;
                } else {                   
                    $montage = $liste;
                    $goods_data["goods_show_image"] = $list[0];
                    $goods_data["goods_show_images"] = $montage;
                }
            } else {
                    $image = db("analyse_goods")->where("id", $id)->field("goods_show_images")->find();
                if(!empty($image["goods_show_images"])){
                    $goods_data["goods_show_images"] = $image["goods_show_images"];
                } else {
                    $goods_data["goods_show_images"] = null;
                    $goods_data["goods_show_image"] = null;
                }
            } 
            
            $bool = db("analyse_goods")->where("id", $id)->update($goods_data);
            if ($bool ){
                $this->success("更新成功", url("admin/Analyse/analyse_index"));
            } else {
                $this->success("更新失败", url('admin/Analyse/analyse_index'));
            }

        }
    }



    /**
     * [商品列表组首页推荐]
     * 郭杨
     */
    public function analyse_status(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("analyse_goods")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Analyse/analyse_index"));
                } else {
                    $this->error("修改失败", url("admin/Analyse/analyse_index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("analyse_goods")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Analyse/analyse_index"));
                } else {
                    $this->error("修改失败", url("admin/Analyse/analyse_index"));
                }
            }
        }
    }


    /**
     * [增值商品列表组是否上架]
     * 陈绪
     */
    public function analyse_ground(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("analyse_goods")->where("id", $id)->update(["label" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Analyse/analyse_index"));
                } else {
                    $this->error("修改失败", url("admin/Analyse/analyse_index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("analyse_goods")->where("id", $id)->update(["label" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Analyse/analyse_index"));
                } else {
                    $this->error("修改失败", url("admin/Analyse/analyse_index"));
                }
            }
        }
    }


    /**
     * [增值商品删除]
     * 郭杨
     */    
    public function analyse_delete($id)
    {
        $bool = db("analyse_goods")-> where("id", $id)->delete();
        $boole = db("analyse_special")->where("goods_id",$id)->delete();
        if ($bool || $boole) {
            $this->success("删除成功", url("admin/Analyse/analyse_index"));
        } else {
            $this->success("删除失败", url('admin/Analyse/analyse_index'));
        }
       
    }


    /**
     * [增值商品组批量删除]
     * 陈绪
     */
    public function analyse_dels(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            if (is_array($id)) {
                $where = 'id in(' . implode(',', $id) . ')';
            } else {
                $where = 'id=' . $id;
            }
            $list = Db::name('analyse_goods')->where($where)->delete();
            if (empty($list)) {
                return ajax_success('成功删除!', ['status' => 1]);
            } else {
                return ajax_error('删除失败', ['status' => 0]);
            }
        }
    }

    /**
     * [增值商品规格图片删除]
     * 郭杨
     */
    public function analyse_photos(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            if (!empty($id)) {
                $photo = db("analyse_special")->where("id", $id)->update(["images" => null]);
            }
            if ($photo) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }


    /**
     * [增值商品规格值修改]
     * 郭杨
     */
    public function analyse_value(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $value = $request->only(["value"])["value"];
            $key = $request->only(["key"])["key"];
            $valuet = db("analyse_special")->where("id", $id)->update([$key => $value]);

            if (!empty($valuet)) {
                return ajax_success('更新成功!');
            } else {
                return ajax_error('更新失败');
            }
        }
    }




    /**
     * [增值商品规格图片添加]
     * 郭杨
     */
    public function analyse_addphoto(Request $request)
    {
        if ($request->isPost()) {
            $id = $request -> only(["id"])["id"];
            $imag = $request-> file("file") -> move(ROOT_PATH . 'public' . DS . 'uploads');
            $images = str_replace("\\", "/", $imag->getSaveName());

            if(!empty($id)){
                $bool = db("analyse_special")->where("id", $id)->update(["images" => $images]);
            }
             if ($bool) {
                 return ajax_success('添加图片成功!');
             } else {
                 return ajax_error('添加图片失败');
             }
        }
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

    /**
     * [线下充值申请编辑]
     * 郭杨
     */    
    public function control_charging_update(Request $request){   
        if($request->isPost()){
            $status =$request->only(["status"])["status"];
            $id = $request->only(["id"])["id"];
            if( empty($status) || empty($id)){
                return ajax_error("参数错误");
            }
            $bool = db("offline_recharge")->where('id',$id)->update(["status"=>$status]);
            if($bool){
                if($status == 2){
                    $data = db("offline_recharge")->where('id',$id)->find();
                    $result = db('store')->where('id',$data['store_id'])->setInc('store_wallet',$data['money']);
                    if($result){
                        return ajax_success("审核成功");
                    } else {
                        return ajax_error("审核失败");
                    }
                } else {
                    return ajax_success("审核成功");
                }
            } else {
                return ajax_error("审核失败");
            }
        }
    }


 }