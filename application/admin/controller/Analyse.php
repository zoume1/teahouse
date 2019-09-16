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
use app\admin\controller\Qiniu;


class  Analyse extends  Controller{
    
    /**
     * [增值商品]
     * 郭杨
     */    
    public function analyse_index(){     
        $analyse_data = db("analyse_goods")->order('sort_number desc')->select();
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
     * [增值商品列表组批量删除]
     * GY
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
     **************李火生*******************
     * @param Request $request
     * Notes:总控增值订单
     **************************************
     */
    public function  analyse_order(){
        $store_id = Session::get("store_id");
        if($store_id == 0){
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'>',1)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time desc")
            ->select();
        } else {
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'>',1)
            ->where("a.store_id",'=',$store_id)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time desc")
            ->select();
        }
        $url = 'admin/Analyse/analyse_order';
        $pag_number = 20;
        $data = paging_data($rest_data,$url,$pag_number);
        return view("analyse_order",["data"=>$data]);
    }

        /**
     **************李火生*******************
     * @param Request $request
     * Notes:总控增值订单待发货
     **************************************
     */
    public function  analyse_waiting(){
        $store_id = Session::get("store_id");
        if($store_id == 0){
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'=',2)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time")
            ->select();
        } else {
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'=',2)
            ->where("a.store_id",'=',$store_id)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time")
            ->select();
        }
        $url = 'admin/Analyse/analyse_order';
        $pag_number = 20;
        $data = paging_data($rest_data,$url,$pag_number);
        return view("analyse_order",["data"=>$data]);
    }

         /**
     **************李火生*******************
     * @param Request $request
     * Notes:总控增值订单已发货
     **************************************
     */
    public function  analyse_delivered(){
        $store_id = Session::get("store_id");
        if($store_id == 0){
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'=',4)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time")
            ->select();
        } else {
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'=',4)
            ->where("a.store_id",'=',$store_id)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time")
            ->select();
        }
        $url = 'admin/Analyse/analyse_order';
        $pag_number = 20;
        $data = paging_data($rest_data,$url,$pag_number);
        return view("analyse_order",["data"=>$data]);
    }


         /**
     **************李火生*******************
     * @param Request $request
     * Notes:总控增值订单待收货
     **************************************
     */
    public function  analyse_received(){
        $store_id = Session::get("store_id");
        if($store_id == 0){
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'=',4)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time")
            ->select();
        } else {
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'=',4)
            ->where("a.store_id",'=',$store_id)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time")
            ->select();
        }
        $url = 'admin/Analyse/analyse_order';
        $pag_number = 20;
        $data = paging_data($rest_data,$url,$pag_number);
        return view("analyse_order",["data"=>$data]);
    }


         /**
     **************李火生*******************
     * @param Request $request
     * Notes:总控增值订单待收货
     **************************************
     */
    public function  analyse_served(){
        $store_id = Session::get("store_id");
        if($store_id == 0){
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'=',12)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time")
            ->select();
        } else {
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'=',12)
            ->where("a.store_id",'=',$store_id)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time")
            ->select();
        }
        $url = 'admin/Analyse/analyse_order';
        $pag_number = 20;
        $data = paging_data($rest_data,$url,$pag_number);
        return view("analyse_order",["data"=>$data]);
    }


         /**
     **************李火生*******************
     * @param Request $request
     * Notes:总控增值订单待收货
     **************************************
     */
    public function  analyse_ok (){
        $store_id = Session::get("store_id");
        if($store_id == 0){
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'=',8)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time")
            ->select();
        } else {
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'=',8)
            ->where("a.store_id",'=',$store_id)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time")
            ->select();
        }
        $url = 'admin/Analyse/analyse_order';
        $pag_number = 20;
        $data = paging_data($rest_data,$url,$pag_number);
        return view("analyse_order",["data"=>$data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:总控增值退款维权
     **************************************
     * @return \think\response\View
     */
    public function  analyse_after_sale(){
       
        //获取增值订单的售后记录
        $adder_order_list=db('adder_after_sale')->order('operation_time desc')->select();
        foreach($adder_order_list as $k=>$v){
            //获取店铺名称
            $adder_order_list[$k]['store_name']=db('store')->where('id',$v['store_id'])->value('store_name');
        }
        //分页处理
        $all_idents = $adder_order_list;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $adder_order_list = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Analyse/analyse_after_sale'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $adder_order_list->appends($_GET);
        $this->assign('access', $adder_order_list->render());
        return view("analyse_after_sale",['data'=>$adder_order_list]);
    }


    /**
     * [增值物流商品添加]
     * 郭杨
     */    
    public function analyse_add(Request $request){
            if ($request->isPost()) {
                $goods_data = $request->param(); 
                // $show_images = $request->file("goods_show_images");
                // $imgs = $request->file("imgs");
                //测试七牛上传图片
                $qiniu=new Qiniu();
                //获取店铺七牛云的配置项
                $accesskey = 'Rf_gkgGeg_lYnq30jPAa725UQax5JYYqt_D-BbMZ';
                $secrectkey = 'P7MWrpaKYM65h1qCIM0GW-uFkkNgbhkGvM5oKqeB';
                $bucket = 'goods';
                 $domain='teahouse.siring.cn';
                 $images='goods_show_images';
                 $rr=$qiniu->uploadimg($accesskey,$secrectkey,$bucket,$domain,$images);
                if (!empty($rr)) {              
                    $goods_data["goods_show_image"] =  $rr[0];
                    $goods_data["goods_type"] = 1;     //商品类型
                    $goods_data["goods_show_images"] = implode(',', $rr);
                }
                if ($goods_data["goods_standard"] == "0") {
                    $bool = db("analyse_goods")->insert($goods_data);
                    if ($bool || (!empty($rr))) {
                        $this->success("添加成功", url("admin/Analyse/analyse_index"));
                    } else {
                        $this->error("添加失败", url('admin/Analyse/analyse_index'));
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
                    $qiniu=new Qiniu();
                    //获取店铺七牛云的配置项
                    $accesskey = 'Rf_gkgGeg_lYnq30jPAa725UQax5JYYqt_D-BbMZ';
                    $secrectkey = 'P7MWrpaKYM65h1qCIM0GW-uFkkNgbhkGvM5oKqeB';
                    $bucket = 'goods';
                     $domain='teahouse.siring.cn';
                     $images='imgs';
                     $rr2=$qiniu->uploadimg($accesskey,$secrectkey,$bucket,$domain,$images);
                    if (!empty($rr2)) {
                        foreach ($rr2 as $k => $v) {
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
                                        $values[$k]["images"] = $v;
                                        $values[$k]["goods_id"] = $goods_id;                                       
                                    }
                                }
                            }
                        }
                    }
    
                    foreach ($values as $kz => $vw) {
                        $rest = db('analyse_special')->insertGetId($vw);
                    }    
                    if ($rest || (!empty($rr2))) {
                        $this->success("添加成功", url("admin/Analyse/analyse_index"));
                    } else {
                        $this->error("添加失败", url('admin/Analyse/analyse_index'));
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
                // $show_images = $request->file("goods_show_images");
                // $imgs = $request->file("imgs");
                // $list = [];
                $qiniu=new Qiniu();
                //获取店铺七牛云的配置项
                $accesskey = 'Rf_gkgGeg_lYnq30jPAa725UQax5JYYqt_D-BbMZ';
                $secrectkey = 'P7MWrpaKYM65h1qCIM0GW-uFkkNgbhkGvM5oKqeB';
                $bucket = 'goods';
                 $domain='teahouse.siring.cn';
                 $images='goods_show_images';
                 $rr=$qiniu->uploadimg($accesskey,$secrectkey,$bucket,$domain,$images);
    
                if (!empty($rr)) {              
                         
                    $goods_data["goods_show_image"] =  $rr[0];
                    $goods_data["goods_type"] = 2;     //商品类型
                    $goods_data["goods_show_images"] = implode(',', $rr);
                }
                
                if ($goods_data["goods_standard"] == "0") {
                    $bool = db("analyse_goods")->insert($goods_data);
                    if ($bool || (!empty($rr))) {
                        $this->success("添加成功", url("admin/Analyse/analyse_index"));
                    } else {
                        $this->error("添加失败", url('admin/Analyse/analyse_index'));
                    }
                }
                if ($goods_data["goods_standard"] == "1") {
                    $goods_special = [];
                    $goods_special["goods_name"] = $goods_data["goods_name"];
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
                   $qiniu=new Qiniu();
                   //获取店铺七牛云的配置项
                   $accesskey = 'Rf_gkgGeg_lYnq30jPAa725UQax5JYYqt_D-BbMZ';
                   $secrectkey = 'P7MWrpaKYM65h1qCIM0GW-uFkkNgbhkGvM5oKqeB';
                   $bucket = 'goods';
                    $domain='teahouse.siring.cn';
                    $images='imgs';
                    $rr2=$qiniu->uploadimg($accesskey,$secrectkey,$bucket,$domain,$images);
                    if (!empty($rr2)) {
                        foreach ($rr2 as $k => $v) {
                           
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
                                        $values[$k]["images"] = $v;
                                        $values[$k]["goods_id"] = $goods_id;                                       
                                    }
                                }
                            }
                        }
                    }   
                    foreach ($values as $kz => $vw) {
                        $rest = db('analyse_special')->insertGetId($vw);
                    }    
                    if ($rest || (!empty($rr2))) {
                        $this->success("添加成功", url("admin/Analyse/analyse_index"));
                    } else {
                        $this->error("添加失败", url('admin/Analyse/analyse_index'));
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
                        // unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $value);
                        unset($se[$key]);
                    } else {
                        $new_image[] = $value;
                    }
                }
            }
            if (!empty($new_image)) {
                $new_imgs_url = implode(',', $new_image);
                $res = Db::name('analyse_goods')->where("id", $tid['pid'])->update(['goods_show_images' => $new_imgs_url,'goods_show_image' => $new_image[0]]);
            } else {
                $res = Db::name('analyse_goods')->where("id", $tid['pid'])->update(['goods_show_images' => NULL,'goods_show_image' => NULL]);
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
            //测试七牛上传图片
            $qiniu=new Qiniu();
            //获取店铺七牛云的配置项
            $accesskey = 'Rf_gkgGeg_lYnq30jPAa725UQax5JYYqt_D-BbMZ';
            $secrectkey = 'P7MWrpaKYM65h1qCIM0GW-uFkkNgbhkGvM5oKqeB';
            $bucket = 'goods';
             $domain='teahouse.siring.cn';
             $images='goods_show_images';
             $rr=$qiniu->uploadimg($accesskey,$secrectkey,$bucket,$domain,$images);
            if(empty($rr)){
                $image = db("analyse_goods")->where("id", $id)->field("goods_show_images")->find();
                if(!empty($image["goods_show_images"])){
                    $goods_data["goods_show_images"] = $image["goods_show_images"];
                } else {
                    $goods_data["goods_show_images"] = null;
                    $goods_data["goods_show_image"] = null;
                }
            }else{
                    $liste = implode(',', $rr);
                    $image = db("analyse_goods")->where("id", $id)->field("goods_show_images")->find();
                if(!empty($image["goods_show_images"]))
                {
                    $exper = $image["goods_show_images"];
                    $montage = $exper . "," . $liste;
                    $goods_data["goods_show_images"] = $montage;
                } else {                   
                    $montage = $liste;
                    $goods_data["goods_show_image"] = $rr[0];
                    $goods_data["goods_show_images"] = $montage;
                }
            }
            // $show_images = $request->file("goods_show_images");
            // $list = [];
            // if (!empty($show_images)) {  //有上传的图片
            //     foreach ($show_images as $k => $v) {
            //         $show = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
            //         $list[] = str_replace("\\", "/", $show->getSaveName());
            //     }               
            //         $liste = implode(',', $list); //上传的图片
            //         $image = db("analyse_goods")->where("id", $id)->field("goods_show_images")->find();//数据库中的图片
            //     if(!empty($image["goods_show_images"]))
            //     {
            //         //数据库中有图片
            //         $exper = $image["goods_show_images"];
            //         $montage = $exper . "," . $liste;
            //         $goods_data["goods_show_images"] = $montage;
            //     } else {                   
            //         $montage = $liste;
            //         $goods_data["goods_show_image"] = $list[0];
            //         $goods_data["goods_show_images"] = $montage;
            //     }
            // } else {
            //     $image = db("analyse_goods")->where("id", $id)->field("goods_show_images")->find();
            //     if(!empty($image["goods_show_images"])){
            //         $goods_data["goods_show_images"] = $image["goods_show_images"];
            //     } else {
            //         $goods_data["goods_show_images"] = null;
            //         $goods_data["goods_show_image"] = null;
            //     }
            // } 
            unset($goods_data['aa']);
            $bool = db("analyse_goods")->where("id", $id)->update($goods_data);
            if ($bool ){
                $this->success("更新成功", url("admin/Analyse/analyse_index"));
            } else {
                $this->success("更新成功", url('admin/Analyse/analyse_index'));
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
    // /**
    //  * [增值商品组批量删除]
    //  * 陈绪
    //  */
    // public function analyse_dels(Request $request)
    // {
    //     if ($request->isPost()) {
    //         $id = $request->only(["id"])["id"];
    //         if (is_array($id)) {
    //             $where = 'id in(' . implode(',', $id) . ')';
    //         } else {
    //             $where = 'id=' . $id;
    //         }
    //         $list = Db::name('analyse_goods')->where($where)->delete();
    //         if (empty($list)) {
    //             return ajax_success('成功删除!', ['status' => 1]);
    //         } else {
    //             return ajax_error('删除失败', ['status' => 0]);
    //         }
    //     }
    // }
    /**
     * [增值商品规格图片删除]
     * 郭杨
     */
    public function anaysle_photos(Request $request)
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
            // $imag = $request-> file("file") -> move(ROOT_PATH . 'public' . DS . 'uploads');
            $accesskey = 'Rf_gkgGeg_lYnq30jPAa725UQax5JYYqt_D-BbMZ';
            $secrectkey = 'P7MWrpaKYM65h1qCIM0GW-uFkkNgbhkGvM5oKqeB';
            $bucket = 'goods';
             $domain='teahouse.siring.cn';
            //测试七牛上传图片
            $qiniu=new Qiniu();
            //获取店铺七牛云的配置项
            $images='imgs';
            $rr=$qiniu->uploadimg($accesskey,$secrectkey,$bucket,$domain,$images);
            $images = $rr[0];
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
    /**
     * [线下提现申请编辑]
     * 郭杨
     */    
    public function control_withdraw_update(Request $request){   
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
                    $result = db('store')->where('id',$data['store_id'])->setDec('store_wallet',$data['real_money']);
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


        /**
     **************gy*******************
     * @param Request $request
     * Notes:这是处理回复
     **************************************
     * @param Request $request
     */
    public function store_notice_index(Request $request){
        if($request->isPost()){
            $order_id = $request->only("order_id")["order_id"];   //订单号
            $rest = Db::name("adder_order")->where("parts_order_number",$order_id)->find();
            $datas = Db::name("note_notification")
                ->where("order_id",$rest['id'])
                ->order("create_time","desc")
                ->select();
            $data =[
                "datas"=>$datas,
                "goods_type"=>$rest['goods_type'],
                "express_name"=>$rest['express_name'],
                "express_name_ch"=>$rest['express_name_ch'],
                "courier_number"=>$rest['courier_number']
            ];
            if(!empty($data)){
                return ajax_success("数据返回成功",$data);
            }else{
                return ajax_error("没有数据",["status"=>0]);
            }
        }
    }



        /**
     **************GY*******************
     * @param Request $request
     * Notes:增值订单的基本信息
     **************************************
     */
    public function adder_order_information_return(Request $request){
        if($request->isPost()){
            $order_id = $request->only(["order_id"])["order_id"];
            if(!empty($order_id)){
                $data = Db::name("adder_order")->where("parts_order_number",$order_id)->find();
                if(!empty($data)){
                    $data["store_name"] = Db::name("store")->where("id",$data["store_id"])->value("store_name");
                    $data["goods_franking"] = Db::name("analyse_goods")->where("id",$data["goods_id"])->value("goods_franking");
                    return ajax_success("数据返回成功",$data);
                }else{
                    return ajax_error("没有数据信息",["status"=>0]);
                }
            }
        }
    }

        /**
     **************GY*******************
     * @param Request $request
     * Notes:增值订单确认发货（填写订单编号）
     **************************************
     */
    public function  adder_order_confirm_shipment(Request $request){
        if($request->isPost()){
            $order_number =$request->only(["order_id"])["order_id"];
            $status =$request->only(["status"])["status"];
            $courier_number =$request->only(["courier_number"])["courier_number"];
            $express_name =$request->only(["express_name"])["express_name"];
            $express_name2 =$request->only(["express_name_ch"])["express_name_ch"];
            $data =[
                "status"=>$status,
                "courier_number"=>$courier_number,
                "express_name"=>$express_name,
                "express_name_ch"=>$express_name2,
            ];
            $bool = Db::name("adder_order")->where("parts_order_number",$order_number)->update($data);
            if($bool){
                return ajax_success("发货成功",["status"=>1]);
            }else{
                return ajax_error("发货失败",["status"=>0]);
            }

            
        }
    }

        /**
     **************gy*******************
     * @param Request $request
     * Notes:确认服务
     **************************************
     * @param Request $request
     */
    public function store_confirm_status(Request $request){
        if($request->isPost()){
            $order_id = $request->only("order_id")["order_id"];
            $status = $request->only("status")["status"];
            $rest = Db::name("adder_order")->where("parts_order_number",$order_id)->update(['status'=>$status]);
            if(!empty($rest)){
                return ajax_success("确认服务成功");
            }else{
                return ajax_error("确认服务失败");
            }
        }
    }

        /**
     **************GY*******************
     * @param Request $request
     * Notes:增值订单修改订单编号
     **************************************
     */
    public function adder_order_change(Request $request){
        if($request->isPost()){
            $order_id = $request->only(["order_id"])["order_id"];
            $courier_number = $request->only(["courier_number"])["courier_number"];
            if(!empty($order_id)){
                $data = Db::name("adder_order")->where("parts_order_number",$order_id)->update(['courier_number'=>$courier_number]);
                if(!empty($data)){
                    return ajax_success("修改成功",$data);
                }else{
                    return ajax_error("修改失败",["status"=>0]);
                }
            }
        }
    }

        /**
     * [增值商品搜索]
     * 郭杨
     */    
    public function analyse_search(){     
        $goods_number = input('goods_number')?input('goods_number'):null;
        if(!empty($goods_number)){
            $condition = " `goods_number` like '%{$goods_number}%' or `goods_name` like '%{$goods_number}%'";
            $analyse_data = db("analyse_goods")
            ->order('sort_number desc')
            ->where($condition)
            ->select();
        } else {
            $analyse_data = db("analyse_goods")
            ->order('sort_number desc')
            ->select();
        }
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
     **************gy*******************
     * @param Request $request
     * Notes:总控增值订单搜索
     **************************************
     */
    public function  analyse_order_search(){
        $search_a =input("search_a") ? input("search_a"):null;
        $time_min  =input("date_min") ? input("date_min"):null;
        $time_max  =input('date_max') ? strtotime(date('Y-m-d H:i:s',strtotime(input('date_max'))+1*24*60*60)):null;
        if(!empty($search_a) && !empty($time_min) && !empty($time_max)){
            $condition =" `parts_order_number` like '%{$search_a}%' or `parts_goods_name` like '%{$search_a}%' or `user_account_name` like '%{$search_a}%' or `user_phone_number` like '%{$search_a}%'";
            $time_condition  = "order_create_time>{$time_min} and order_create_time< {$time_max}";
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'>',1)
            ->where($condition)
            ->where($time_condition)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time desc")
            ->select();
        } elseif(!empty($search_a) && empty($time_min) && empty($time_max)){
            $condition =" `parts_order_number` like '%{$search_a}%' or `parts_goods_name` like '%{$search_a}%' or `user_account_name` like '%{$search_a}%' or `user_phone_number` like '%{$search_a}%'";
            $time_condition  = "order_create_time< {$time_max}";
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'>',1)
            ->where($condition)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time desc")
            ->select();
        } elseif(empty($search_a) && !empty($time_min) && empty($time_max)){
            $time_condition  = "order_create_time>{$time_min}";
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'>',1)
            ->where($time_condition)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time desc")
            ->select();
        } elseif(empty($search_a) && empty($time_min) && !empty($time_max)){
            $time_condition  = "order_create_time< {$time_max}";
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'>',1)
            ->where($time_condition)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time desc")
            ->select();
        } elseif(!empty($search_a) && !empty($time_min) && empty($time_max)){
            $condition =" `parts_order_number` like '%{$search_a}%' or `parts_goods_name` like '%{$search_a}%' or `user_account_name` like '%{$search_a}%' or `user_phone_number` like '%{$search_a}%'";
            $time_condition  = "order_create_time>{$time_min}";
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'>',1)
            ->where($condition)
            ->where($time_condition)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time desc")
            ->select();
        } elseif(!empty($search_a) && empty($time_min) && !empty($time_max)){
            $condition =" `parts_order_number` like '%{$search_a}%' or `parts_goods_name` like '%{$search_a}%' or `user_account_name` like '%{$search_a}%' or `user_phone_number` like '%{$search_a}%'";
            $time_condition  = "order_create_time< {$time_max}";
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'>',1)
            ->where($condition)
            ->where($time_condition)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time desc")
            ->select();
        } elseif(empty($search_a) && !empty($time_min) && !empty($time_max)){
            $time_condition  = "order_create_time>{$time_min} and order_create_time< {$time_max}";
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'>',1)
            ->where($time_condition)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time desc")
            ->select();
        } else {
            $rest_data = Db::table("tb_adder_order")
            ->alias('a')
            ->join('tb_store b','b.id = a.store_id','left')
            ->where("a.status",'>',1)
            ->field("a.*,b.store_name")
            ->order("a.order_create_time desc")
            ->select();
        }

        $url = 'admin/Analyse/analyse_order';
        $pag_number = 20;
        $data = paging_data($rest_data,$url,$pag_number);
        return view("analyse_order",["data"=>$data]);
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后页面数据返回---增值订单
     **************************************
     */
    public function  adder_after_sale_information(Request $request){
        if($request->isPost()){
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
            $data =Db::name("adder_after_sale")->where("id",$after_sale_id)->find();
            $data["reply"] =Db::name("adder_after_reply")->where("after_sale_id",$after_sale_id)->select();
            if(!empty($data)){
                return ajax_success("售后信息返回成功",$data);
            }else{
                return ajax_error("暂无售后信息");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后状态修改---增值订单
     **************************************
     */
    public function adder_after_sale_status(Request $request){
        if($request->isPost()){
            $status =$request->only(["status"])["status"];     //申请状态
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后记录id
            if($status ==5){   //拒绝申请
                $normal_time =Db::name("order_setting")->find();//订单设置的时间
                $normal_future_time =strtotime("+". $normal_time['after_sale_time']." day");
                $data =[
                    "status"=>$status,
                    "handle_time"=>time(),
                    "future_time"=>$normal_future_time,
                    "who_handle"=>3 , //1、用户自己撤销 2 、中途撤销 3、商家拒绝
                ];
            }else{      //收货中
                $data =[
                    "status"=>$status,
                    "handle_time"=>time()
                ];
            }
            $bool =Db::name("adder_after_sale")
                ->where("id",$after_sale_id)
                ->update($data);
            if($bool){
                return ajax_success("更改成功");
            }else{
                return ajax_error("更改失败");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后状态修改带快递信息
     **************************************
     */
    public function adder_after_sale_express_add(Request $request){
        if($request->isPost()){
            $status =$request->only(["status"])["status"];
            $sell_express_company =$request->only(["sell_express_company"])["sell_express_company"];
            $sell_express_number =$request->only(["sell_express_number"])["sell_express_number"];
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
            $data =[
                "status"=>$status,
                "sell_express_company"=>$sell_express_company,
                "sell_express_number"=>$sell_express_number
            ];
            $bool =Db::name("adder_after_sale")->where("id",$after_sale_id)->update($data);
            //初始订单已关闭
            $order_id=db('adder_after_sale')->where('id',$after_sale_id)->value('order_id');
            $order_number=db('order')->where('id',$order_id)->value('parts_order_number');   //获取订单号
            $re=db('order')->where('parts_order_number',$order_number)->select();
            foreach($re as $k=>$v){
                db('order')->where('id',$v['id'])->update(['status'=>0]);
            }
            if($bool){
                return ajax_success("更改成功");
            }else{
                return ajax_error("更改失败");
            }
        }
    }        /**
    * lilu
    * 退款维权---退款（余额）
    */
   public function adder_after_sale_refound(){
       //获取参数
       $input=input();
       if($input['status']=='5')
       {
           //商家拒绝
            //用户拒绝
            $data['who_handle']=3;
            $data['status']='5';
            $adder_order=db('adder_after_sale')->where('id',$input['after_sale_id'])->update($data);
            if($adder_order){
                //修改售后状态为已发货
                return ajax_success('商家拒绝收货，请及时和客服联系');
            }else{
                return ajax_error('商家拒绝收货，请及时和客服联系');
            }
       }else{
           //换货
           //获取订单信息
           $order_info=db('adder_after_sale')->where('id',$input['after_sale_id'])->find();
           if($order_info['is_return_goods']=='1'){
               //换货--修改订单的状态
               $data['buy_express_company']=$input['express'];
                $data['buy_express_number']=$input['danhao'];
                $data['status']=7;
               $re2=db('adder_after_sale')->where('id',$input['after_sale_id'])->update($data);   //店铺已退货
               if($re2){
                   return ajax_success('发货成功'); 
                }else{
                    return ajax_error('发货失败'); 
               }
           }else{
               //退款至会员余额
               $re=db('member')->where('member_id',$order_info['member_id'])->setInc('member_wallet',$input['money']);
               $money=db('member')->where('member_id',$order_info['member_id'])->value('member_wallet');
               //退款记录
               $map['user_id']=$order_info['member_id'];
               $map['wallet_operation']=$input['money'];
               $map['wallet_type']=1;
               $map['operation_time']=date('Y-m-d H:i:s',time());
               $map['wallet_remarks']='售后单号为'.$order_info['sale_order_number'].'退款成功';
               $map['wallet_img']='';
               $map['title']=date('Y-m-d H:i:s',time());
               $map['order_nums']=date('Y-m-d H:i:s',time());
               $map['pay_type']='小城序';
               $map['wallet_balance']=$money;
               $map['operation_linux_time']=time();
               db('wallet')->insert($map);
                //生成对账单记录
                $rr=create_captical_log($map['order_nums'],$map['user_id'],0,$map['wallet_operation'],6,$order_info['store_id']);
               //修改退款维权订单的状态
               $re2=db('adder_after_sale')->where('id',$input['after_sale_id'])->update(['status'=>'6']);
               if($re && $re2 ){
                    return ajax_success('退款成功');
                }else{
                    return ajax_error('退款失败');
               }
           }

           }

   }
   /**
    * lilu
    * 增值订单回复
    */
    public function adder_business_replay(Request $request){
        if($request->isPost()){
            $after_sale_id =$request->only(["after_sale_id"])["after_sale_id"];//售后id
            $content= $request->only(["content"])["content"]; //回复的内容
            $is_who =1;//谁回复（1卖家，2买家）
            $data =[
                "content" =>$content,
                "after_sale_id"=>$after_sale_id,
                "is_who"=>$is_who,
                "create_time" =>time()
            ];
            $id =Db::name("adder_after_reply")->insertGetId($data);
            if($id >0){
                return ajax_success("回复成功",$data);
            }else{
                return ajax_error("回复失败");
            }

        }
    }


 }