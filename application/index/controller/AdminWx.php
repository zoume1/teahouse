<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/26 0026
 * Time: 11:27
 */
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;


class  AdminWx extends Controller{


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:后台套餐订购订单微信扫码支付回调
     **************************************
     */
    public function set_meal_notify(Request $request){
        if($request->isPost()){
            $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
            $xml_data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $val = json_decode(json_encode($xml_data), true);
            if($val["result_code"] == "SUCCESS" && $val["return_code"] =="SUCCESS" ){
                $enter_all_data=Db::name("set_meal_order")
                    ->where("order_number",$val["out_trade_no"])
                    ->find();
                $year =Db::name("enter_all")->where("id",$enter_all_data['enter_all_id'])->value("year");
                //进行逻辑处理
                //1、先判断是否上一单是否到期和是否存在
                //2、判断如果是升级过来的话需要进行删除已付款的订单
                $is_set_order =Db::name("set_meal_order")
                    ->where("store_id",$enter_all_data["store_id"])
                    ->where("audit_status",1)
                    ->find();

                if($is_set_order){
                    //这是套餐升级的情况
                    $data["pay_time"] =time();//支付时间
                    $data["pay_type"] =1;//支付类型（1扫码支付,2汇款支付，3余额支付）
                    $data["pay_status"] =1;//到账状态（1为已到账，-1未到账，2待审核）
                    $data["start_time"] =time();//开始时间
                    $data["end_time"] =strtotime("+$year  year");//开始时间
                    $data["explains"] ="微信扫码支付直接通过";//审核说明
                    $data["status"] =1; //订单状态（-1为未付款，1已付款）
                    $data["audit_status"] =1; //订单审核状态（1审核通过，-1审核不通过,0待审核）
                    $res =Db::name("set_meal_order")
                        ->where("order_number",$is_set_order["order_number"])
                        ->update($data);
                    if($res){
                        //把新生成的套餐订单删掉
                        //鲁文兵改
                         Db::name("set_meal_order")->where("order_number",$val["out_trade_no"])->update($data);
                           //鲁文兵修改if(!$is_set){}
                           
                            $is_uniacid =Db::table("ims_sudu8_page_base")
                                ->where("uniacid",$enter_all_data["store_id"])
                                ->find();
                            if(!$is_uniacid){
                                $insert_data =[
                                    "uniacid"=>$enter_all_data["store_id"],
                                    "index_style"=>"header",
                                    "copyimg"=>"",
                                    "base_color_t"=>"",
                                    "tabnum_new"=>5,
                                    "homepage"=>2,
                                ];
                                Db::table("ims_sudu8_page_base")->insert($insert_data);
                            }
                            $array =[
                                "go_home"=>1,
                                "uniacid"=>$enter_all_data["store_id"],
                                "kp"=>"/diypage/resource/images/diypage/default/default_start.jpg",
                                "kp_is"=>2,
                                "kp_url"=>"",
                                "kp_urltype"=>"",
                                "kp_m"=>2,
                                "tc"=>"/diypage/resource/images/diypage/default/tcgg.jpg",
                                "tc_is"=>2,
                                "tc_url"=>"",
                                "tc_urltype"=>"",
                                "foot_is"=>2,
                                "pid"=>0,
                                "store_id"=>$enter_all_data["store_id"],
                            ];
                            Db::table("ims_sudu8_page_diypageset")->insert($array);
                            //添加首页
                            $arr=[
                                "uniacid"=>$enter_all_data["store_id"],
                                "index"=>1,
                                "page"=>'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:23:"后台页面名称11111";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                                "items"=>"",
                                "tpl_name"=>"首页"
                            ];
                            $diy_id[0] = Db::table("ims_sudu8_page_diypage")->insertGetId($arr);
                            //添加系统推荐模板
                            $arrs=[
                                "uniacid"=>$enter_all_data["store_id"],
                                "index"=>0,
                                "page"=>'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:23:"后台页面名称11111";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                                "items"=>'a:9:{s:14:"M1556441265605";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#eeeeee";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:2:"20";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:0:"";}s:2:"id";s:3:"ssk";}s:14:"M1556442497229";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:9:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:3:{s:14:"C1556442497229";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/0a798157280c216842778b14703d2174.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1556442497230";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/4e24ab5a4e1eaf6c8a9e2cb44925715e.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}s:14:"M1556442727577";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/130a87d7c2de0d0271bca1477b81c5e8.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1556442901109";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"30";}s:4:"data";a:4:{s:14:"C1556442901109";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/21e8d6a0a0a9b02bddfe1f8c7dd3291d.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:15:"我的分享码";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901110";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/29c68a53ed8082397dce5c06f6bbefde.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"商品分类";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901111";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/6708933e84c6252df819a7bfe46be951.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:9:"购物车";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901112";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/f2a6a4efdf216a9530e009948310ba79.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"公司介绍";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1556447643377";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:2:"10";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:2:"10";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#d9d9d9";}s:4:"data";a:1:{s:14:"C1556447643377";a:5:{s:4:"text";s:6:"商品";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:6:"更多";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";}s:14:"M1556447629116";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447629116";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447629117";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447629118";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447629119";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1556447710765";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"2";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:9:"block one";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447710765";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447710766";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447710767";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447710768";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1556447741843";a:6:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:11:"block three";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447741843";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447741844";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447741845";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447741846";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";s:5:"index";s:3:"NaN";}s:14:"M1556447763411";a:5:{s:3:"max";s:1:"5";s:4:"icon";s:23:"iconfont2 icon-fuwenben";s:6:"params";a:1:{s:7:"content";s:164:"PHAgc3R5bGU9InRleHQtYWxpZ246IGNlbnRlcjsiPuaZuuaFp+iMtuS7k+aPkOS+m+aKgOacr+aUr+aMgTwvcD48cCBzdHlsZT0idGV4dC1hbGlnbjogY2VudGVyOyI+d3d3LnpoaWh1aWNoYWNhbmcuY29tPC9wPg==";}s:5:"style";a:3:{s:10:"background";s:7:"#ffffff";s:7:"padding";s:2:"10";s:9:"margintop";s:2:"10";}s:2:"id";s:8:"richtext";}s:14:"M1556447842556";a:7:{s:4:"icon";s:21:"iconfont2 icon-caidan";s:6:"isfoot";s:1:"1";s:3:"max";s:1:"1";s:6:"params";a:8:{s:8:"navstyle";s:1:"0";s:8:"textshow";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:20:{s:11:"pagebgcolor";s:7:"#f9f9f9";s:7:"bgcolor";s:7:"#ffffff";s:9:"bgcoloron";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:11:"iconcoloron";s:7:"#f1415b";s:9:"textcolor";s:7:"#666666";s:11:"textcoloron";s:7:"#666666";s:11:"bordercolor";s:7:"#cccccc";s:13:"bordercoloron";s:7:"#ffffff";s:14:"childtextcolor";s:7:"#666666";s:12:"childbgcolor";s:7:"#f4f4f4";s:16:"childbordercolor";s:7:"#eeeeee";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:11:"paddingleft";s:1:"0";s:10:"paddingtop";s:1:"0";s:8:"iconfont";s:2:"28";s:8:"textfont";s:2:"12";s:3:"bdr";s:1:"0";s:8:"bdrcolor";s:7:"#cccccc";}s:4:"data";a:4:{s:14:"C1556447842557";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-x-shouye2";s:4:"text";s:6:"首页";}s:14:"M1556448352088";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-x-caidan5";s:4:"text";s:6:"首页";}s:14:"C1556447842558";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-2.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:11:"icon-x-gwc2";s:4:"text";s:9:"购物车";}s:14:"C1556447842560";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-4.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:13:"icon-x-geren2";s:4:"text";s:12:"联系我们";}}s:2:"id";s:8:"footmenu";}}',
                                "tpl_name"=>"系统推荐"
                            ];
                            $diy_id[1]=Db::table("ims_sudu8_page_diypage")->insertGetId($arrs);
                            $new_array =[
                                "uniacid"=>$enter_all_data["store_id"],
                                "pageid"=>implode(',',$diy_id),
                                "template_name"=>"综合商城模板",
                                "thumb"=>"/diypage/template_img/template_shop/cover.png",
                                "create_time"=>time(),
                                "status"=>1,
                                "store_id"=>$enter_all_data["store_id"]
                            ];
                            $bool=Db::table("ims_sudu8_page_diypagetpl")->insertGetId($new_array);
                       
                              
                       //审核通过则对店铺进行开放，修改店铺的权限（普通访客）为商家店铺
                        Db::table("tb_admin")
                            ->where("store_id",$enter_all_data["store_id"])
                            ->where("is_own",1)
                            ->update(["role_id"=>7]);
                        echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                    }else {
                        return "fail";
                    }
                }else{
                    //这是新加入套餐的情况
                    $data["pay_time"] =time();//支付时间
                    $data["pay_type"] =1;//支付类型（1扫码支付，2汇款支付，3余额支付）
                    $data["pay_status"] =1;//到账状态（1为已到账，-1未到账，2待审核）
                    $data["start_time"] =time();//开始时间
                    $data["end_time"] =strtotime("+$year  year");//开始时间
                    $data["explains"] ="微信扫码支付直接通过";//审核说明
                    $data["status"] =1; //订单状态（-1为未付款，1已付款）
                    $data["audit_status"] =1; //订单审核状态（1审核通过，-1审核不通过,0待审核）
                    $result =Db::name("set_meal_order")
                        ->where("order_number",$val["out_trade_no"])
                        ->update($data);
                    Db::name("set_meal_order")->where("order_number",$val["out_trade_no"])->delete();
                    if($result){
                        //审核通过则对店铺进行开放，修改店铺的权限（普通访客）为商家店铺
                        Db::table("tb_admin")
                            ->where("store_id",$enter_all_data["store_id"])
                            ->where("is_own",1)
                            ->update(["role_id"=>7]);
                        //审核通过的时候先判断是否有小程序模板，没有的话则进行添加，有的话则不需要
                        $is_set = Db::table("ims_sudu8_page_diypageset")
                            ->where("store_id",$enter_all_data["store_id"])
                            ->find();
                            //鲁文兵修改if(!$is_set){}
                   
                            $is_uniacid =Db::table("ims_sudu8_page_base")
                                ->where("uniacid",$enter_all_data["store_id"])
                                ->find();
                            if(!$is_uniacid){
                                $insert_data =[
                                    "uniacid"=>$enter_all_data["store_id"],
                                    "index_style"=>"header",
                                    "copyimg"=>"",
                                    "base_color_t"=>"",
                                    "tabnum_new"=>5,
                                    "homepage"=>2,
                                ];
                                Db::table("ims_sudu8_page_base")->insert($insert_data);
                            }
                            $array =[
                                "go_home"=>1,
                                "uniacid"=>$enter_all_data["store_id"],
                                "kp"=>"/diypage/resource/images/diypage/default/default_start.jpg",
                                "kp_is"=>2,
                                "kp_url"=>"",
                                "kp_urltype"=>"",
                                "kp_m"=>2,
                                "tc"=>"/diypage/resource/images/diypage/default/tcgg.jpg",
                                "tc_is"=>2,
                                "tc_url"=>"",
                                "tc_urltype"=>"",
                                "foot_is"=>2,
                                "pid"=>0,
                                "store_id"=>$enter_all_data["store_id"],
                            ];
                            Db::table("ims_sudu8_page_diypageset")->insert($array);
                            //添加首页
                            $arr=[
                                "uniacid"=>$enter_all_data["store_id"],
                                "index"=>1,
                                "page"=>'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:23:"后台页面名称11111";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                                "items"=>"",
                                "tpl_name"=>"首页"
                            ];
                            $diy_id[0] = Db::table("ims_sudu8_page_diypage")->insertGetId($arr);
                            //添加系统推荐模板
                            $arrs=[
                                "uniacid"=>$enter_all_data["store_id"],
                                "index"=>0,
                                "page"=>'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:23:"后台页面名称11111";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                                "items"=>'a:9:{s:14:"M1556441265605";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#eeeeee";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:2:"20";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:0:"";}s:2:"id";s:3:"ssk";}s:14:"M1556442497229";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:9:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:3:{s:14:"C1556442497229";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/0a798157280c216842778b14703d2174.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1556442497230";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/4e24ab5a4e1eaf6c8a9e2cb44925715e.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}s:14:"M1556442727577";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/130a87d7c2de0d0271bca1477b81c5e8.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1556442901109";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"30";}s:4:"data";a:4:{s:14:"C1556442901109";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/21e8d6a0a0a9b02bddfe1f8c7dd3291d.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:15:"我的分享码";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901110";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/29c68a53ed8082397dce5c06f6bbefde.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"商品分类";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901111";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/6708933e84c6252df819a7bfe46be951.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:9:"购物车";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901112";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/f2a6a4efdf216a9530e009948310ba79.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"公司介绍";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1556447643377";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:2:"10";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:2:"10";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#d9d9d9";}s:4:"data";a:1:{s:14:"C1556447643377";a:5:{s:4:"text";s:6:"商品";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:6:"更多";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";}s:14:"M1556447629116";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447629116";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447629117";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447629118";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447629119";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1556447710765";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"2";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:9:"block one";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447710765";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447710766";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447710767";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447710768";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1556447741843";a:6:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:11:"block three";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447741843";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447741844";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447741845";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447741846";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";s:5:"index";s:3:"NaN";}s:14:"M1556447763411";a:5:{s:3:"max";s:1:"5";s:4:"icon";s:23:"iconfont2 icon-fuwenben";s:6:"params";a:1:{s:7:"content";s:164:"PHAgc3R5bGU9InRleHQtYWxpZ246IGNlbnRlcjsiPuaZuuaFp+iMtuS7k+aPkOS+m+aKgOacr+aUr+aMgTwvcD48cCBzdHlsZT0idGV4dC1hbGlnbjogY2VudGVyOyI+d3d3LnpoaWh1aWNoYWNhbmcuY29tPC9wPg==";}s:5:"style";a:3:{s:10:"background";s:7:"#ffffff";s:7:"padding";s:2:"10";s:9:"margintop";s:2:"10";}s:2:"id";s:8:"richtext";}s:14:"M1556447842556";a:7:{s:4:"icon";s:21:"iconfont2 icon-caidan";s:6:"isfoot";s:1:"1";s:3:"max";s:1:"1";s:6:"params";a:8:{s:8:"navstyle";s:1:"0";s:8:"textshow";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:20:{s:11:"pagebgcolor";s:7:"#f9f9f9";s:7:"bgcolor";s:7:"#ffffff";s:9:"bgcoloron";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:11:"iconcoloron";s:7:"#f1415b";s:9:"textcolor";s:7:"#666666";s:11:"textcoloron";s:7:"#666666";s:11:"bordercolor";s:7:"#cccccc";s:13:"bordercoloron";s:7:"#ffffff";s:14:"childtextcolor";s:7:"#666666";s:12:"childbgcolor";s:7:"#f4f4f4";s:16:"childbordercolor";s:7:"#eeeeee";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:11:"paddingleft";s:1:"0";s:10:"paddingtop";s:1:"0";s:8:"iconfont";s:2:"28";s:8:"textfont";s:2:"12";s:3:"bdr";s:1:"0";s:8:"bdrcolor";s:7:"#cccccc";}s:4:"data";a:4:{s:14:"C1556447842557";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-x-shouye2";s:4:"text";s:6:"首页";}s:14:"M1556448352088";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-x-caidan5";s:4:"text";s:6:"首页";}s:14:"C1556447842558";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-2.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:11:"icon-x-gwc2";s:4:"text";s:9:"购物车";}s:14:"C1556447842560";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-4.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:13:"icon-x-geren2";s:4:"text";s:12:"联系我们";}}s:2:"id";s:8:"footmenu";}}',
                                "tpl_name"=>"系统推荐"
                            ];
                            $diy_id[1]=Db::table("ims_sudu8_page_diypage")->insertGetId($arrs);
                            $new_array =[
                                "uniacid"=>$enter_all_data["store_id"],
                                "pageid"=>implode(',',$diy_id),
                                "template_name"=>"综合商城模板",
                                "thumb"=>"/diypage/template_img/template_shop/cover.png",
                                "create_time"=>time(),
                                "status"=>1,
                                "store_id"=>$enter_all_data["store_id"]
                            ];
                            $bool=Db::table("ims_sudu8_page_diypagetpl")->insertGetId($new_array);
                            
                        echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                    }else{
                        return "fail";
                    }
                }

            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:后台套餐订购订单支付宝扫码支付回调
     **************************************
     */
    public function set_meal_notify_alipay()
    {
        include EXTEND_PATH . "/lib/payment/alipay/alipay.class.php";
        $obj_alipay = new \alipay();
        if (!$obj_alipay->verify_notify()) {
            //验证未通过
            echo "fail";
            exit();
        } else {
            //这里可以做一下你自己的订单逻辑处理
            $pay_time = time();
            $data['pay_time'] = $pay_time;
            //原始订单号
            $out_trade_no = input('out_trade_no');
            //支付宝交易号
            $trade_no = input('trade_no');
            //交易状态
            $trade_status = input('trade_status');
            if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                $condition['order_number'] = $out_trade_no;
                $enter_all_data = Db::name("set_meal_order")
                    ->where($condition)
                    ->find();
                $year = Db::name("enter_all")->where("id", $enter_all_data['enter_all_id'])->value("year");
                //进行逻辑处理
                //1、先判断是否上一单是否到期和是否存在
                //2、判断如果是升级过来的话需要进行删除已付款的订单
                $is_set_order = Db::name("set_meal_order")
                    ->where("store_id", $enter_all_data["store_id"])
                    ->where("audit_status", 1)
                    ->find();
                if ($is_set_order) {
                    //这是套餐升级的情况
                    $data["pay_time"] = time();//支付时间
                    $data["pay_type"] = 1;//支付类型（1扫码支付,2汇款支付，3余额支付）
                    $data["pay_status"] = 1;//到账状态（1为已到账，-1未到账，2待审核）
                    $data["start_time"] = time();//开始时间
                    $data["end_time"] = strtotime("+$year  year");//开始时间
                    $data["explains"] = "支付宝扫码支付直接通过";//审核说明
                    $data["status"] = 1; //订单状态（-1为未付款，1已付款）
                    $data["audit_status"] = 1; //订单审核状态（1审核通过，-1审核不通过,0待审核）
                    $res = Db::name("set_meal_order")
                        ->where("order_number", $is_set_order["order_number"])
                        ->update($data);
                   
                    if ($res){
                        //把刚下套餐订单删掉
                           //$result= Db::name("set_meal_order")->where($condition)->delete();
                       $result = Db::name("set_meal_order")->where()->update($data);
                        if ($result) {
                            //进行角色转化
                            //审核通过则对店铺进行开放，修改店铺的权限（普通访客）为商家店铺
                            Db::table("tb_admin")
                                ->where("store_id",$enter_all_data["store_id"])
                                ->where("is_own",1)
                                ->update(["role_id"=>7]);
                            return "success";
                        } else {
                            return "fail";
                        }
                    } else {
                            return "fail";
                    }
                } else {
                    //这是新加入套餐的情况
                    $data["pay_time"] = time();//支付时间
                    $data["pay_type"] = 1;//支付类型（1扫码支付，2汇款支付，3余额支付）
                    $data["pay_status"] = 1;//到账状态（1为已到账，-1未到账，2待审核）
                    $data["start_time"] = time();//开始时间
                    $data["end_time"] = strtotime("+$year  year");//开始时间
                    $data["explains"] = "支付宝扫码支付直接通过";//审核说明
                    $data["status"] = 1; //订单状态（-1为未付款，1已付款）
                    $data["audit_status"] = 1; //订单审核状态（1审核通过，-1审核不通过,0待审核）
                    $result = Db::name("set_meal_order")
                        ->where($condition)
                        ->update($data);
                    if ($result) {
                        //进行角色转化
                        //审核通过则对店铺进行开放，修改店铺的权限（普通访客）为商家店铺
                        Db::table("tb_admin")
                            ->where("store_id",$enter_all_data["store_id"])
                            ->where("is_own",1)
                            ->update(["role_id"=>7]);
                        //审核通过的时候先判断是否有小程序模板，没有的话则进行添加，有的话则不需要
                        $is_set = Db::table("ims_sudu8_page_diypageset")
                            ->where("store_id",$enter_all_data["store_id"])
                            ->find();

                        if(!$is_set){
                            $is_uniacid =Db::table("ims_sudu8_page_base")
                                ->where("uniacid",$enter_all_data["store_id"])
                                ->find();
                            if(!$is_uniacid){
                                $insert_data =[
                                    "uniacid"=>$enter_all_data["store_id"],
                                    "index_style"=>"header",
                                    "copyimg"=>"",
                                    "base_color_t"=>"",
                                    "tabnum_new"=>5,
                                    "homepage"=>2,
                                ];
                                Db::table("ims_sudu8_page_base")->insert($insert_data);
                            }
                            $array =[
                                "go_home"=>1,
                                "uniacid"=>$enter_all_data["store_id"],
                                "kp"=>"/diypage/resource/images/diypage/default/default_start.jpg",
                                "kp_is"=>2,
                                "kp_url"=>"",
                                "kp_urltype"=>"",
                                "kp_m"=>2,
                                "tc"=>"/diypage/resource/images/diypage/default/tcgg.jpg",
                                "tc_is"=>2,
                                "tc_url"=>"",
                                "tc_urltype"=>"",
                                "foot_is"=>2,
                                "pid"=>0,
                                "store_id"=>$enter_all_data["store_id"],
                            ];
                            Db::table("ims_sudu8_page_diypageset")->insert($array);
                            //添加首页
                            $arr=[
                                "uniacid"=>$enter_all_data["store_id"],
                                "index"=>1,
                                "page"=>'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:23:"后台页面名称11111";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                                "items"=>"",
                                "tpl_name"=>"首页"
                            ];
                            $diy_id[0] = Db::table("ims_sudu8_page_diypage")->insertGetId($arr);
                            //添加系统推荐模板
                            $arrs=[
                                "uniacid"=>$enter_all_data["store_id"],
                                "index"=>0,
                                "page"=>'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:23:"后台页面名称11111";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                                "items"=>'a:9:{s:14:"M1556441265605";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#eeeeee";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:2:"20";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:0:"";}s:2:"id";s:3:"ssk";}s:14:"M1556442497229";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:9:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:3:{s:14:"C1556442497229";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/0a798157280c216842778b14703d2174.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1556442497230";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/4e24ab5a4e1eaf6c8a9e2cb44925715e.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}s:14:"M1556442727577";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/130a87d7c2de0d0271bca1477b81c5e8.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1556442901109";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"30";}s:4:"data";a:4:{s:14:"C1556442901109";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/21e8d6a0a0a9b02bddfe1f8c7dd3291d.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:15:"我的分享码";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901110";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/29c68a53ed8082397dce5c06f6bbefde.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"商品分类";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901111";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/6708933e84c6252df819a7bfe46be951.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:9:"购物车";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901112";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/f2a6a4efdf216a9530e009948310ba79.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"公司介绍";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1556447643377";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:2:"10";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:2:"10";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#d9d9d9";}s:4:"data";a:1:{s:14:"C1556447643377";a:5:{s:4:"text";s:6:"商品";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:6:"更多";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";}s:14:"M1556447629116";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447629116";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447629117";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447629118";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447629119";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1556447710765";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"2";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:9:"block one";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447710765";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447710766";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447710767";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447710768";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1556447741843";a:6:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:11:"block three";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447741843";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447741844";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447741845";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447741846";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";s:5:"index";s:3:"NaN";}s:14:"M1556447763411";a:5:{s:3:"max";s:1:"5";s:4:"icon";s:23:"iconfont2 icon-fuwenben";s:6:"params";a:1:{s:7:"content";s:164:"PHAgc3R5bGU9InRleHQtYWxpZ246IGNlbnRlcjsiPuaZuuaFp+iMtuS7k+aPkOS+m+aKgOacr+aUr+aMgTwvcD48cCBzdHlsZT0idGV4dC1hbGlnbjogY2VudGVyOyI+d3d3LnpoaWh1aWNoYWNhbmcuY29tPC9wPg==";}s:5:"style";a:3:{s:10:"background";s:7:"#ffffff";s:7:"padding";s:2:"10";s:9:"margintop";s:2:"10";}s:2:"id";s:8:"richtext";}s:14:"M1556447842556";a:7:{s:4:"icon";s:21:"iconfont2 icon-caidan";s:6:"isfoot";s:1:"1";s:3:"max";s:1:"1";s:6:"params";a:8:{s:8:"navstyle";s:1:"0";s:8:"textshow";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:20:{s:11:"pagebgcolor";s:7:"#f9f9f9";s:7:"bgcolor";s:7:"#ffffff";s:9:"bgcoloron";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:11:"iconcoloron";s:7:"#f1415b";s:9:"textcolor";s:7:"#666666";s:11:"textcoloron";s:7:"#666666";s:11:"bordercolor";s:7:"#cccccc";s:13:"bordercoloron";s:7:"#ffffff";s:14:"childtextcolor";s:7:"#666666";s:12:"childbgcolor";s:7:"#f4f4f4";s:16:"childbordercolor";s:7:"#eeeeee";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:11:"paddingleft";s:1:"0";s:10:"paddingtop";s:1:"0";s:8:"iconfont";s:2:"28";s:8:"textfont";s:2:"12";s:3:"bdr";s:1:"0";s:8:"bdrcolor";s:7:"#cccccc";}s:4:"data";a:4:{s:14:"C1556447842557";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-x-shouye2";s:4:"text";s:6:"首页";}s:14:"M1556448352088";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-x-caidan5";s:4:"text";s:6:"首页";}s:14:"C1556447842558";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-2.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:11:"icon-x-gwc2";s:4:"text";s:9:"购物车";}s:14:"C1556447842560";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-4.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:13:"icon-x-geren2";s:4:"text";s:12:"联系我们";}}s:2:"id";s:8:"footmenu";}}',
                                "tpl_name"=>"系统推荐"
                            ];
                            $diy_id[1]=Db::table("ims_sudu8_page_diypage")->insertGetId($arrs);
                            $new_array =[
                                "uniacid"=>$enter_all_data["store_id"],
                                "pageid"=>implode(',',$diy_id),
                                "template_name"=>"综合商城模板",
                                "thumb"=>"/diypage/template_img/template_shop/cover.png",
                                "create_time"=>time(),
                                "status"=>1,
                                "store_id"=>$enter_all_data["store_id"]
                            ];
                            Db::table("ims_sudu8_page_diypagetpl")->insertGetId($new_array);
                        }
                        return "success";
                    } else {
                        return "fail";
                    }
                }
            }

        }
    }




}