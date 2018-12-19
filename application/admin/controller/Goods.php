<?php

/**
 * Created by PhpStorm.
 * User: CHEN
 * Date: 2018/7/11
 * Time: 16:12
 */

namespace app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use app\admin\model\Good;
use app\admin\model\GoodsImages;
use think\Session;
use think\Loader;
use think\paginator\driver\Bootstrap;

class Goods extends Controller
{


    /**
     * [商品列表显示]
     * GY
     */
    public function index(Request $request)
    {
        $goods = db("goods")->order("id desc")->select();
        foreach ($goods as $key => $value) {
            if ($value["pid"]) {
                $res = db("wares")->where("id", $value['pid'])->field("name")->find();
                $goods[$key]["named"] = $res["name"];
                $goods[$key]["goods_show_images"] = explode(",", $goods[$key]["goods_show_images"])[0];
            }
        }
      
        $all_idents = $goods;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $goods = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Category/index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $goods->appends($_GET);
        $this->assign('listpage', $goods->render());
        return view("goods_index", ["goods" => $goods]);


    }



    // /**
    //  * 模糊查询
    //  * 陈绪
    //  * @param Request $request
    //  * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
    //  */
    // public function seach(Request $request){
    //     $search_keys = $request->param("search_key");
    //     $search_bts = $request->param("search_bt");

    //     $search_key = isset($search_keys) ? $search_keys : '%';
    //     $search_bt = isset($search_bts) ? $search_bts : false;

    //     if ($search_key) {
    //         $good = db("goods")->where("goods_name", "like", "%" . $search_key . "%")->paginate(5, false,['query' => request()->param()]);
    //     } else {
    //         $good = db("goods")->paginate(5,false,['query' => request()->param()]);
    //         $this->assign("good", $good);
    //     }
    //     return view("goods_index", [
    //         'good' => $good,
    //         'search_key' => $search_key,
    //     ]);

    // }


    /**
     * [商品列表添加组]
     * GY
     */
    public function add($pid = 0)
    {
        $goods_list = [];
        if ($pid == 0) {
            $goods_list = getSelectList("wares");
        }


       

        return view("goods_add", ["goods_list" => $goods_list]);
    }



    /**
     * [商品列表组添加]
     * GY
     * 
     */
    public function save(Request $request)
    {
        if ($request->isPost()) {

            $goods_data = $request->param();
            // if (!empty($goods_data["goods_standard_name"])) {
            //     $goods_standard_name = implode(",", $goods_data["goods_standard_name"]);
            //     $goods_standard_value = implode(",", $goods_data["goods_standard_value"]);
            //     $goods_data["goods_standard_name"] = $goods_standard_name;
            //     $goods_data["goods_standard_value"] = $goods_standard_value;
            // }
            //添加图片
            $list = [];
            $show_images = $request->file("goods_show_images");
            $imgs = $request-> file("imgs");

            if (!empty($show_images)) {
                foreach ($show_images as $ky => $vl) {
                    $show = $vl->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $list[] = str_replace("\\", "/", $show->getSaveName());
                }
                $goods_data["goods_show_images"] = implode(',', $list);

            }

            if($goods_data["goods_standard"] == "0"){
                $bool = db("goods")->insert($goods_data);
                if ($bool) {
                    $this->success("添加成功", url("admin/Goods/index"));
                } else {
                    $this->success("添加失败", url('admin/Goods/add'));
                }

            }

            if($goods_data["goods_standard"] == "1"){
                
                $goods_special = [];
                $goods_special["goods_name"] = $goods_data["goods_name"];
                $goods_special["produce"] = $goods_data["produce"];
                $goods_special["brand"] = $goods_data["brand"];
                $goods_special["goods_number"] = $goods_data["goods_number"];
                $goods_special["goods_standard"] = $goods_data["goods_standard"];
                $goods_special["goods_selling"] = $goods_data["goods_selling"];
                $goods_special["goods_sign"] = $goods_data["goods_sign"];
                $goods_special["goods_describe"] = $goods_data["goods_describe"];
                $goods_special["pid"] = $goods_data["pid"];
                $goods_special["sort_number"] = $goods_data["sort_number"];
                $goods_special["video_link"] = $goods_data["video_link"];
                $goods_special["goods_delivery"] = $goods_data["goods_delivery"];
                $goods_special["goods_franking"] = $goods_data["goods_franking"];
                $goods_special["templet_id"] = $goods_data["templet_id"];
                $goods_special["label"] = $goods_data["label"];
                $goods_special["status"] = $goods_data["status"];

                if(isset($goods_data["goods_text"])){
                $goods_special["goods_text"] = $goods_data["goods_text"];
                }else{
                    $goods_special["goods_text"] = "";
                    $goods_data["goods_text"] = "";
                }
                $goods_special["goods_show_images"] = $goods_data["goods_show_images"];

                $result = implode(",",$goods_data["lv1"]);
               
                $goods_id = db('goods')->insertGetId($goods_special);
                
                if (!empty($goods_data)) {
                    foreach ($goods_data as $kn => $nl) {
                        if(substr($kn,0,3) == "sss"){
                            $price[] = $nl["price"];
                            $stock[] = $nl["stock"];
                            $coding[] = $nl["coding"];
                            $cost[] = $nl["cost"];
                            if(isset($nl["status"])){
                            $status[] = $nl["status"];
                            }else{
                                $status[] = "0"; 
                            }
                        }
                    }
    
                }
               

                if (!empty($imgs)) {
                    foreach ($imgs as $k => $v) {
                        $shows = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                        $tab = str_replace("\\", "/", $shows->getSaveName());

                        if(is_array($goods_data)){
                            foreach($goods_data as $key => $value)
                            {


                                if(substr($key,0,3) == "sss"){                                   
                                    $str[] = substr($key,3);
                                    $values[$k]["name"] = $str[$k];                                    
                                    $values[$k]["price"] = $price[$k];
                                    $values[$k]["lv1"] = $result;
                                    $values[$k]["stock"] = $stock[$k];
                                    $values[$k]["coding"] = $coding[$k];                                  
                                    $values[$k]["status"] = $status[$k];                       
                                    $values[$k]["cost"] = $cost[$k];
                                    $values[$k]["images"] =$tab;
                                    $values[$k]["goods_id"] =$goods_id;
                                    
                                }
                               
                            }
                        }
                        
                    }

                }
                foreach($values as $kz => $vw){
                    $rest = db('special')->insert($vw);

                }
                if ($rest) {
                    $this->success("添加成功", url("admin/Goods/index"));
                } else {
                    $this->success("添加失败", url('admin/Goods/add'));
                }
            }

        }
    }


    /**
     * [商品列表组修改]
     * GY
     */
    public function edit(Request $request, $id)
    {
        $goods = db("goods")->where("id", $id)->select();
        $goods_standard = db("special")->where("goods_id", $id)->select();

        foreach ($goods as $key => $value) {               
            $goods[$key]["goods_show_images"] = explode(',', $goods[$key]["goods_show_images"]);
        }

        foreach ($goods_standard as $k => $v) {               
            $goods_standard[$k]["title"]  = explode('_',  $v["name"]);
            //$goods_standard[$k]["lv1"]  = explode(',',  $v["lv1"]);
            $res = explode(',',  $v["lv1"]);
        }
    //    dump($goods_standard);
    //    dump($res);

    //     halt($goods);
        $goods_list = getSelectList("wares");
        return view("goods_edit", ["goods" => $goods,"goods_list" => $goods_list,"res" => $res,"goods_standard" => $goods_standard]);
    }


    /**
     * [商品列表组图片删除]
     * GY
     */
    public function images(Request $request)
    {
        if ($request->isPost()) {
            $tid = $request->param();
            $id = $tid["id"];
        
            if (!empty($id)) {
                $image = db("goods")->where("id", $tid['pid'])->field("goods_show_images")->find();
                
                $se = explode(",", $image["goods_show_images"]);

                foreach ($se as $key => $value) {
                
                    if ($value == $id) {
                        unlink(ROOT_PATH . 'public' . DS . 'uploads/'.$value);
                    
                    }else{
                        $new_image[] =$value;
                    }
                }

                if(!empty($new_image)){
                    $new_imgs_url =implode(',',$new_image);
                    $res = Db::name('goods')->where("id", $tid['pid'])->update(['goods_show_images'=>$new_imgs_url]);
                }else{
                    $res = Db::name('goods')->where("id", $tid['pid'])->update(['goods_show_images'=>NULL]);
                }
                if($res){
                    return ajax_success('删除成功');
                }else{
                    return ajax_success('删除失败');
                }

            }  
 
        }
    }



    /**
     * [商品列表组删除]
     * GY
     */
    public function del(Request $request)
    {
        $id = $request->only(["id"])["id"];
        $image_url = db("goods_images")->where("goods_id", $id)->field("goods_images,id")->select();
        $bool = db("goods")->where("id", $id)->delete();
        if ($bool) {
            foreach ($image_url as $value) {
                if ($value['goods_images'] != null) {
                    unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $value['goods_images']);
                }
                $bool_data = db("goods_images")->where("id", $value['id'])->delete();
            }
            if ($bool) {
                $this->success("删除成功", url("admin/Goods/index"));
            } else {
                $this->success("删除失败", url('admin/Goods/add'));
            }

        }
    }



    /**
     * [商品列表组更新]
     * GY
     * 
     */
    public function updata(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $goods_data = $request->param();

            
            if (!empty($goods_data["goods_standard_name"])) {
                $goods_standard_name = implode(",", $goods_data["goods_standard_name"]);
                $goods_standard_value = implode(",", $goods_data["goods_standard_value"]);
                $goods_data["goods_standard_name"] = $goods_standard_name;
                $goods_data["goods_standard_value"] = $goods_standard_value;
            }

            $show_images = $request->file("goods_show_images");
            
            $list = [];
            if (!empty($show_images)) {
                foreach ($show_images as $k => $v) {
                    $show = $v->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $list[] = str_replace("\\", "/", $show->getSaveName());
                }         
                $liste = implode(',', $list);          
                $image = db("goods")->where("id", $id)->field("goods_show_images")->find();                
                $exper =  $image["goods_show_images"];              
                $montage =  $exper.",".$liste;                       
                $goods_data["goods_show_images"] = $montage;               
            }

            if(empty($show_images))
            {
                $image = db("goods")->where("id", $id)->field("goods_show_images")->find();                
                $exper =  $image["goods_show_images"]; 
                $goods_data["goods_show_images"] = $exper;

            }

            
            $commodity = db("commodity") -> where("shop_number",$goods_data["goods_number"])->field("zero,first,second,third,grade,shop_price")-> find();
            if(!($goods_data["goods_show_images"] == $commodity["shop_price"]))
            {
                $res = array("zero" ,"first" ,"second","third");
                $pdd = explode(",",$commodity["grade"]); 

                foreach ($pdd as $key => $value)
                {
                    $pdd[$key] = str_replace('%','',$value);
                }

                $array = array_combine($res,$pdd);
                foreach ($array as $k => $v)
                {
                    $array[$k] = ($v * $goods_data["goods_new_money"])/100;
                }
                
                $array["shop_price"] = $goods_data["goods_new_money"];
                $boole = db("commodity") -> where("shop_number",$goods_data["goods_number"]) ->update($array);
        }
            halt($goods_data);
            $bool = db("goods")->where("id", $id)->update($goods_data);
            if ($bool) {
                $this->success("更新成功", url("admin/Goods/index"));
            } else {
                $this->success("更新失败", url('admin/Goods/edit'));
            }


        }

    }


    /**
     * [商品列表组首页推荐]
     * 陈绪
     */
    public function status(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Goods/index"));
                } else {
                    $this->error("修改失败", url("admin/Goods/index"));
                }
            }
        }

    }




    /**
     * [商品列表组批量删除]
     * 陈绪
     */
    public function dels(Request $request)
    {
        if ($request->isPost()) {
            $id = $_POST['id'];
            if (is_array($id)) {
                $where = 'id in(' . implode(',', $id) . ')';
            } else {
                $where = 'id=' . $id;
            }
            $list = Db::name('goods')->where($where)->delete();
            if ($list !== false) {
                return ajax_success('成功删除!', ['status' => 1]);
            } else {
                return ajax_error('删除失败', ['status' => 0]);
            }
        }

    }





    /**
     * 商品查看
     * 陈绪
     */
    public function look(Request $request, $id)
    {

        $goods = db("goods")->where("id", $id)->select();
        foreach ($goods as $key => $value) {
            $goods[$key]["goods_standard_name"] = explode(",", $value["goods_standard_name"]);
            $goods_standard_value = explode(",", $value["goods_standard_value"]);
            $goods_standard_value = array_chunk($goods_standard_value, 8);
            $goods[$key]["goods_standard_value"] = $goods_standard_value;
            $goods[$key]["goods_images"] = db("goods_images")->where("goods_id", $value["id"])->select();

        }
        $goods_standard_name = array();
        foreach ($goods as $k => $val) {
            foreach ($val["goods_standard_name"] as $k_1 => $v_2) {
                $goods_standard_name[$k_1] = array(
                    "goods_standard_name" => $val["goods_standard_name"][$k_1],
                    "goods_standard_value" => $val["goods_standard_value"][$k_1]
                );
            }
        }
        $goods_list = getSelectList("goods_type");
        $goods_brand = getSelectList("brand");
        $year = db("year")->select();
        if ($request->isPost()) {
            $car_series = db("car_series")->distinct(true)->field("brand")->select();
            $car_brand = db("car_series")->field("series,brand")->select();
            return ajax_success("获取成功", array("car_series" => $car_series, "car_brand" => $car_brand));
        }
        return view("goods_look", ["year" => $year, "goods_brand" => $goods_brand, "goods_standard_name" => $goods_standard_name, "goods" => $goods, "goods_list" => $goods_list, "goods_brand" => $goods_brand]);
    }



    /**
     * 特殊规格名添加
     * 陈绪
     */
    public function name(Request $request)
    {

        if ($request->isPost()) {
            $standard_name = $request->only(["goods_name"])["goods_name"];
            $standard = db("goods_standard")->where("standard_name", $standard_name)->select();
            if (empty($standard)) {
                $goods_name_bool = db("goods_standard")->insert(["standard_name" => $standard_name]);
                if ($goods_name_bool) {
                    $goods_name = db("goods_standard")->order("id desc")->select();
                    return ajax_success("成功", $goods_name);
                } else {
                    return 2;
                }

            } else {
                return ajax_error("已存在");
            }
        }

    }



    /**
     * 商品特殊规格名显示
     * 陈绪
     */
    public function standard_name(Request $request)
    {

        if ($request->isPost()) {
            $goods_name = db("goods_standard")->order("id desc")->select();

            if ($goods_name) {
                return ajax_success("获取成功", $goods_name);
            } else {
                return ajax_error("失败");
            }

        }

    }




    /**
     * 专用商品属性入库
     * 陈绪
     */
    public function property_name(Request $request)
    {

        if ($request->isPost()) {
            $property_name = $request->only(["property_name"])["property_name"];
            $property = db("goods_property_name")->where("property_name", $property_name)->select();
            if (empty($property)) {
                $bool = db("goods_property_name")->insert(["property_name" => $property_name]);
                if ($bool) {
                    $goods_property_name = db("goods_property_name")->order("id desc")->select();
                    return ajax_success("成功", $goods_property_name);
                } else {
                    return 2;
                }
            } else {
                return ajax_error("已存在");
            }
        }

    }





    /**
     * 专用商品属性显示
     * 陈绪
     */
    public function property_show(Request $request)
    {

        if ($request->isPost()) {
            $property_name = db("goods_property_name")->order("id desc")->select();
            if ($property_name) {
                return ajax_success("获取成功", $property_name);
            } else {
                return ajax_error("失败");
            }

        }

    }




    /**
     * 角色检测
     * 陈绪
     */
    public function role_name(Request $request)
    {

        if ($request->isPost()) {
            $user_id = Session::get("user_id");
            $admin = db("admin")->where("id", $user_id)->select();
            return ajax_success("获取成功", array("admin" => $admin));
        }

    }




    /**
     * 商品提交订单
     * 陈绪
     */
    public function alipay(Request $request)
    {

        $config = array(
            //应用ID,您的APPID。
            'app_id' => "2018082761132725",

            //商户私钥，您的原始格式RSA私钥
            'merchant_private_key' => "MIIEpAIBAAKCAQEAyC9iRV5kLDbVK619EtISgMN5Gz0bOdFAfSojUzefVhKUrEJ6j48d1Awrg98yudp22kUs0zboMkVTYDT1l9ux5xj/p39JhqjjIl44oZsGFjSmu9/2HxaZ4UjfTJXkaGwJqyY0fSY2f+cE5YjoRYq5XhqijzF0BoKoH64pQNWxqp6f3wss2FKp707KV/oLAArqkqFcWfyylMsncdxV59Lo0mtJ7cIEOezng4es3KDdHmLT5kq3j0hl0kfIjdGuDR0cWnlcolHUoIOKVGSlSHn+WnFlZ20/fkfF+hdadUcG42tywCBVT40ugX1LmmdCI4hAnxLxeQ7bFkhrnpDWcW7KWQIDAQABAoIBAQCBQK730TFmpuTOtc669y6BOzUX1EWe+C/mYO28Dn7vqUGbU7UkuihtQIpcNCHhhGAXIHEH0zzrMH3b8XXdXjmo2ChBstr7elJlX2a7WYf9kHNTfRDCE+q5Xj7niSSYE6HOgvWDFMg9nyE3P0WRmTeEvjfVsv2SMoxxIBd8yD1Vxr3Gbg+gT8zWDrqXQ1Ap1gg5jNS14CFE3uKKwQ4n5JZWnIQ+jw3LZcpk9Eb/mrQ9kbnU7g0ikx8sYJpTiP7lAlb3dq1tdUmRV8+HfWYC/a8MbZtO6UyDWvms5Lb5g4we7FCmBAkG+zv62PxG9sQAvrQoSwKTOj/7LSeTgJsT97QNAoGBAPuQUNZEhVODVhCCISg84TGi0BozU64PqegJXFxbR++hQC2EsN6L2Mk2ftpd+J/9XRD0ffcBMea+H4N7ui4Y+OHoED/8d76dTX06PWfAYYJMu/o65c3IBSBiwgREuRo38a20CZ8hKr8LVpLXbtCB8WJ1kp5QeqqSPpwnjFncyBorAoGBAMu3Hokjze+FPpeFQ3tYVt9G/VSAhRMVAb5ZQClQH9plpVM9aMukp8jiaeSBg7d5RzNRGRU5ouKQ1AVs3jkgvVzUWRMKM+VkW4lzAhEkM766egpzngs9z4YXHcBW1bPJQap2TVLRcFmueDsVABXF5XZSgAwenBhtvmZ9X/UDCD+LAoGBALmXaOwLNUm9lVsshgXHlGQoN9t8jnnV+IXFkixY86NolY5/XHVzOwaHe+LifTCbnXOKzPvUF9qh3WIFf//OUJ9ps8NhIX6xUp/WvcKzfbzBm9Uqaqv8qzuPYJABm4YqS9TZBFgwAfdcCAzhf1G47Dq1fuvpd/YrWqGd07/gUIhtAoGAHDSkg7RzZQB75BrNdxyKGqwHk1WgFz5HWYWd/ppbbq+4LkhIZDnOCWBf7QWJqTOfihlmcavjQ59t27pxIlPIJDw6gQpemRpGGkfUN29dwsCq+Rt8/G14eEZnFiRvvk7VSrbKifb5qVEg0H1d36Xg2Xsew47Ragh33lTpnlDnKXUCgYBIuk9VU3DkITWsy+xiQbN4eQqbiFB7BA55xIjwPqK8K+0PVzRyObUEF6m9KSz2mEB1CHwr1fHj8qzJ/0CgKUeCONm5crLEGCGMbGUzMloGmVLSJz6+4xT8mwKOv/BcpTqkDLx+8HBaJppJnjWn0OmHLNa1JhAaVuef8eheH546kw==",

            //异步通知地址
            'notify_url' => "http://localhost/automobile/public/admin/goods_pay_code",

            //同步跳转
            'return_url' => "http://localhost/automobile/public/admin/goods_pay_code",

            //编码格式
            'charset' => "UTF-8",

            //签名方式
            'sign_type' => "RSA2",

            //支付宝网关
            'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

            //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
            'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyC9iRV5kLDbVK619EtISgMN5Gz0bOdFAfSojUzefVhKUrEJ6j48d1Awrg98yudp22kUs0zboMkVTYDT1l9ux5xj/p39JhqjjIl44oZsGFjSmu9/2HxaZ4UjfTJXkaGwJqyY0fSY2f+cE5YjoRYq5XhqijzF0BoKoH64pQNWxqp6f3wss2FKp707KV/oLAArqkqFcWfyylMsncdxV59Lo0mtJ7cIEOezng4es3KDdHmLT5kq3j0hl0kfIjdGuDR0cWnlcolHUoIOKVGSlSHn+WnFlZ20/fkfF+hdadUcG42tywCBVT40ugX1LmmdCI4hAnxLxeQ7bFkhrnpDWcW7KWQIDAQAB",

        );

        //Loader::import("Alipay.wappay.buildermodel.AlipayTradeWapPayContentBuilder");
        //Loader::import('Alipay.wappay.service.AlipayTradeService');
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = date("YmdHis") . uniqid();

        //订单名称，必填
        $subject = $_POST['WIDsubject'];
        //付款金额，必填
        $total_amount = $_POST['WIDtotal_amount'];

        //商品描述，可空
        $body = $_POST['WIDbody'];
        //超时时间
        $timeout_express = "1m";
        include('../extend/AliPay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php');

        $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);
        include('../extend/AliPay/wappay/service/AlipayTradeService.php');

        $payResponse = new \AlipayTradeService($config);
        $result = $payResponse->wapPay($payRequestBuilder, $config['return_url'], $config['notify_url']);
        Session("goods_id", $body);
        return;


    }


    /**
     * 回调地址
     * 陈绪
     * @param Request $request
     */
    public function pay_code(Request $request)
    {

        if ($request->isGet()) {
            $id = Session::get("goods_id");
            $goods_id = explode(",", $id);
            foreach ($goods_id as $value) {
                $bool = db("goods")->where("id", $value)->update(["goods_status" => 1, "putaway_status" => 1]);
            }
            if ($bool) {
                $this->success("上架成功", url("admin/Goods/index"));
            } else {
                $this->error("上架失败", url("admin/Goods/index"));
            }
        }
    }




    /**
     * 专用适用车型编辑显示
     * 陈绪
     */
    public function edit_show(Request $request)
    {

        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $goods = db("goods")->where("id", $id)->field("dedicated_vehicle,goods_car_year,goods_car_displacement,goods_car_series")->select();
            foreach ($goods as $key => $value) {

                $goods[$key]["goods_car_year"] = explode(",", $value["goods_car_year"]);
                $goods[$key]["goods_car_displacement"] = explode(",", $value["goods_car_displacement"]);
                $goods[$key]["goods_car_series"] = explode(",", $value["goods_car_series"]);

            }
            if ($goods) {
                return ajax_success("获取成功", $goods);
            } else {
                return ajax_error("获取失败");
            }
        }

    }


}