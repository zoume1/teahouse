<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

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

class  Control extends  Controller{
    
    /**
     * [总控店铺]
     * 郭杨
     */    
    public function control_index(){ 
        $control_meale = db("enter_meal")->paginate(20,false, [
            'query' => request()->param(),
        ]);   
        return view("control_index",["control_meale"=>$control_meale]);
    }

    
    /**
     * [入驻套餐]
     * 郭杨
     */    
    public function control_meal_index(){
        $control_meal = db("enter_meal")->paginate(20,false, [
            'query' => request()->param(),
        ]);
        return view("control_meal_index",["control_meal"=>$control_meal]);
    }


    /**
     * [添加入驻套餐]
     * 郭杨
     */    
    public function control_meal_add(Request $request){ 
        if($request -> isPost()){
            $meal = $request->param();
            $year = $meal["year"]; 
            $min_cost = $meal["cost"];
            $favourable = $meal["favourable_cost"];
            foreach($min_cost as $key => $value){
                if(!$value){
                    unset($min_cost[$key]);
                }
                $cost[] = $value; 
            }             
            foreach($favourable as $ke => $val){
                if(!$val){
                    unset($favourable[$key]);
                }
                $favourable_cost[] = $val; 
            } 
            $min = min($min_cost);                 //套餐原价最低价
            $favour_min = min($favourable);        //套餐原价最低优惠券

            $enter = array(
                "name" => $meal["name"],
                "price" => $min,
                "favourable_price" => $favour_min,
                "sort_number" => $meal["sort_number"],
                "year" => 1,
                "status" => $meal["status"],
                "cost" => implode(",",$cost),
                "favourable_cost" => implode(",",$favourable_cost),
            );
            $enter_id = db("enter_meal")->insertGetId($enter);

            foreach($year as $k => $v){
                $values[$k]['year'] = $v;
                $values[$k]['cost'] = $cost[$k];
                $values[$k]['favourable_cost'] = $favourable_cost[$k];
                $values[$k]['enter_id'] = $enter_id;
            }
            
            foreach($values as $kk => $vv){
                $bool = db("enter_all")->insert($vv);
            }
            if ($enter_id || $bool) {
                $this->success("添加成功", url("admin/Control/control_meal_index"));
            } else {
                $this->success("添加失败", url('admin/Control/control_meal_index'));
            }
        }
            
        
        return view("control_meal_add");
    }

    /**
     * [入驻套餐首页显示]
     * 郭杨
     */
    public function control_meal_status(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("enter_meal")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Control/control_meal_index"));
                } else {
                    $this->error("修改失败", url("admin/Control/control_meal_index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("enter_meal")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Control/control_meal_index"));
                } else {
                    $this->error("修改失败", url("admin/Control/control_meal_index"));
                }
            }
        }
    }


    /**
     * [入驻套餐编辑保存]
     * 保存
     */
    public function control_meal_update(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $data = $request->param();
            $data["cost"] = implode(",",$data["cost"]);
            $data["favourable_cost"] = implode(",",$data["favourable_cost"]);

            $bool = db("enter_meal")->where("id",$id)->update($data);
            if ($bool) {
                $this->success("编辑成功", url("admin/Control/control_meal_index"));
            } else {
                $this->success("编辑失败", url('admin/Control/control_meal_index'));
            }
        }
    }



    /**
     * [入驻套餐首页显示]
     * 保存
     * 
     */
    public function control_meal_edit($id)
    {
        $meal = db("enter_meal")->where("id",$id)->find();
        $meal_edit = array(
            "id" => $meal["id"],
            "name" => $meal["name"],
            "sort_number"=> $meal["sort_number"],
            "status" => $meal["status"],
            "cost" => explode(",",$meal["cost"]),
            "favourable_cost" => explode(",",$meal["favourable_cost"])
        );
       
        return view("control_meal_edit",["meal_edit"=>$meal_edit]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:入驻订单
     **************************************
     * @return \think\response\View
     */
    public function control_order_index(){
        $order =Db::table('tb_meal_orders')
            ->field("tb_meal_orders.*,tb_store.phone_number,tb_store.contact_name,tb_store.is_business,tb_store.address_real_data,tb_store.status store_status")
            ->join("tb_store","tb_meal_orders.store_id=tb_store.id",'left')
            ->where("is_del",1)
            ->where("tb_meal_orders.pay_type","NEQ","NULL")
            ->order("tb_meal_orders.create_time","desc")
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);

        $enter_meal = db("enter_meal")->field("name")->select();
        
        $type_meal['0']['audit_status']='入驻审核不通过';
        $type_meal['1']['audit_status']='入驻审核';
        $type_meal['2']['audit_status']='入驻审核通过';
        

             
        return view("control_order_index",["order"=>$order,"enter_meal"=>$enter_meal,"type_meal"=>$type_meal]);
    }


    /**
     * [入驻订单店铺信息编辑]
     * 郭杨
     */    
    public function control_order_add($id){
        $store_order = db("store")
            ->where("id",$id)
            ->select();
        $store_order[0]["address_data"] = explode(",",$store_order[0]["address_data"]);
        return view("control_order_add",["store_order"=>$store_order]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:入驻订单套餐数据返回
     **************************************
     * @param $id
     */
    public function control_order_status($id){
        //先检查店铺审核状态(未审核不能点进来审核订单)
        $store_id =Db::table("tb_meal_orders")
            ->where("id",$id)
            ->value("store_id");

        $store_info = Db::table("tb_store")
            ->where("id",$store_id)
            ->where("status",1)
            ->value("id");
          
        if(!$store_info){
            $this->error("请先进行店铺审核操作");
        }
        $store_information =Db::table("tb_store")
            ->where("id",$store_id)
            ->where("store_del",1)
            ->value("id");
        if(!$store_information){
            $this->error("该店铺已被删除，无法进行以下操作");
        }
        $store_order = Db::table('tb_meal_orders')
            ->field("tb_meal_orders.*,tb_store.phone_number,tb_store.contact_name,tb_store.is_business,tb_store.address_real_data,tb_store.status store_status,tb_store.address_data,tb_store.id_card,tb_store.card_positive,tb_store.store_introduction,tb_store.store_qq,tb_store.explain,tb_store.card_side")
            ->join("tb_store","tb_meal_orders.store_id=tb_store.id",'left')
            ->where("is_del",1)
            ->where("tb_meal_orders.id",$id)
            ->where("tb_meal_orders.pay_type","NEQ","NULL")
            ->where("store_id",$store_id)
            ->select();
        $payment_data = Db::name("meal_pay_form")->where("meal_order_id","EQ",$id)->find();
        if(!empty($payment_data)){
            $store_order[0]['remittance_name'] = $payment_data['remittance_name'];
            $store_order[0]['remittance_account'] = $payment_data['remittance_account'];
            $store_order[0]['pay_time'] = $payment_data['pay_time'];
        }
        $store_order[0]["address_data"] = explode(",",$store_order[0]["address_data"]);
        return view("control_order_status",["store_order"=>$store_order]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:入驻订单编辑审核操作
     **************************************
     */
    public function control_order_status_update(Request $request){
        if($request -> isPost()) {
            $id = $request->only(["id"])["id"]; //审核的订单id
            $audit_status = isset($request->only(["audit_status"])["audit_status"])?$request->only(["audit_status"])["audit_status"]:null;
            $explains = $request->only(["explains"])["explains"];
            $data = $request->param();
            if(empty($audit_status)){
                $this->error("请选择审核状态");
            }
            //这是需要审核通过的订单
            $is_pay = db("meal_orders")
                ->where("id", $id)
                ->find();
            
            //未付款
            if(!$is_pay["pay_type"]){
                $this->error("此订单未付款不能审核操作");
            }
            if($is_pay["pay_type"] ==1){
                $this->error("扫码支付已自动审核通过");
            }
            //其他支付方式直接通过
            if(($is_pay["audit_status"] == 1)   && ($is_pay["pay_type"] != 2) ){
                $this->error("此订单已审核通过,请勿重复审核", url("admin/Control/control_order_index"));
            }
            ////汇款已审核的
            if(($is_pay["audit_status"] == 1)   && ($is_pay["pay_type"] = 2) ){
                $this->error("此订单已审核通过,请勿重复审核", url("admin/Control/control_order_index"));
            } else {
                //汇款未审核的后台进行审核
                //审核通过提交
                if($audit_status == 1){    
                //1、先判断是否上一单是否到期和是否存在
                //2、判断如果是升级过来的话需要进行删除已付款的订单
                    $is_set_order = Db::name("set_meal_order")
                    ->where("store_id",$is_pay["store_id"])
                    ->where("audit_status",'EQ',1)
                    ->select();
                    
                    //修改时间
                    $year = Db::name("enter_all")->where("id", $is_pay['enter_all_id'])->value("year");
                    $data["start_time"] = time(); //开始时间
                    if($year > 0){
                        $data["end_time"] = strtotime("+$year  year");//结束时间
                    } else {
                        $data["end_time"] = strtotime("+10  day");//结束时间
                    }
                    $data["explains"] = $explains; //审核说明
                    $data["status"] =1; //订单状态（-1为未付款，1已付款)
                    $data["apply"] = 1; //订单状态（-1为未付款，1已付款)
                    $data["audit_status"] = 1; //订单审核状态（2汇款审核通过，-1审核不通过,0待审核 )
                    $data['goods_name'] = $is_pay['goods_name']; //升级套餐名
                    $data['enter_all_id'] =  $is_pay['enter_all_id']; //套餐id
                    $data['pay_status'] =  1; //到账状态
                    $data['pay_time'] =  time(); //审核时间

                    //升级套餐
                    if($is_set_order){
                        $rest = Db::name("meal_orders")
                        ->where("order_number",$is_pay["order_number"])
                        ->update($data);
                        
                        
                        $res = Db::name("set_meal_order")
                        ->where("order_number",$is_set_order["order_number"])
                        ->update($data);
                       $delete_new_order = Db::name('set_meal_order')->where('order_number',$is_pay["order_number"])->delete();
                    
                       if($res){                           
                        //审核通过则对店铺进行开放，修改店铺的权限（普通访客）为商家店铺
                          if($is_pay['enter_all_id'] <= 6){
                              $role_id = 13;
                          }
                          if(  ($is_pay['enter_all_id'] > 6) && ($is_pay['enter_all_id'] <= 17)){
                              $role_id = 14;
                          }
                          if( $is_pay['enter_all_id'] > 17){
                              $role_id = 15;
                          }
                         $bool =  Db::table("tb_admin")
                                ->where("store_id",$is_pay["store_id"])
                                ->where("is_own",1)
                                ->update(["role_id"=>$role_id]);
                        if($bool){
                            $this->success("审核成功", url("admin/Control/control_order_index"));
                        } else {
                            $this->error("审核失败", url("admin/Control/control_order_index"));
                        }
                    } else {
                            $this->error("审核错误", url("admin/Control/control_order_index"));    
                    }
                } else {
                    //第一次购买
                    $rest = Db::name("meal_orders")
                    ->where("order_number",$is_pay["order_number"])
                    ->update($data);

                    
                    $res = Db::name("set_meal_order")
                    ->where("order_number",$is_pay["order_number"])
                    ->update($data);
                    if($res){                           
                        //审核通过则对店铺进行开放，修改店铺的权限（普通访客）为商家店铺
                          if($is_pay['enter_all_id'] <= 6){
                              $role_id = 13;
                          }
                          if(  ($is_pay['enter_all_id'] > 6) && ($is_pay['enter_all_id'] <= 17)){
                              $role_id = 14;
                          }
                          if( $is_pay['enter_all_id'] > 17){
                              $role_id = 15;
                          }
                                $boole =  Db::table("tb_admin")
                                    ->where("store_id",$is_pay["store_id"])
                                    ->where("is_own",1)
                                    ->update(["role_id"=>$role_id]);

                                    //审核通过的时候先判断是否有小程序模板，没有的话则进行添加，有的话则不需要
                                    $is_set = Db::table("ims_sudu8_page_diypageset")
                                    ->where("store_id",$is_pay["store_id"])
                                    ->find();

                                    
                                    if(!$is_set){
                                    $is_uniacid =Db::table("ims_sudu8_page_base")
                                        ->where("uniacid",$is_pay["store_id"])
                                        ->find();
                                    if(!$is_uniacid){
                                        $insert_data =[
                                            "uniacid"=>$is_pay["store_id"],
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
                                        "uniacid"=>$is_pay["store_id"],
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
                                        "store_id"=>$is_pay["store_id"],
                                    ];
                                    Db::table("ims_sudu8_page_diypageset")->insert($array);
                                    //添加首页
                                    $arr=[
                                        "uniacid"=>$is_pay["store_id"],
                                        "index"=>1,
                                        "page"=>'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:23:"后台页面名称11111";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                                        "items"=>"",
                                        "tpl_name"=>"首页"
                                    ];
                                    $diy_id[0] = Db::table("ims_sudu8_page_diypage")->insertGetId($arr);
                                    //添加系统推荐模板
                                    $arrs=[
                                        "uniacid"=>$is_pay["store_id"],
                                        "index"=>0,
                                        "page"=>'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:23:"后台页面名称11111";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                                        "items"=>'a:9:{s:14:"M1556441265605";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#eeeeee";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:2:"20";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:0:"";}s:2:"id";s:3:"ssk";}s:14:"M1556442497229";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:9:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:3:{s:14:"C1556442497229";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/0a798157280c216842778b14703d2174.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1556442497230";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/4e24ab5a4e1eaf6c8a9e2cb44925715e.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}s:14:"M1556442727577";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/130a87d7c2de0d0271bca1477b81c5e8.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1556442901109";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"30";}s:4:"data";a:4:{s:14:"C1556442901109";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/21e8d6a0a0a9b02bddfe1f8c7dd3291d.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:15:"我的分享码";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901110";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/29c68a53ed8082397dce5c06f6bbefde.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"商品分类";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901111";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/6708933e84c6252df819a7bfe46be951.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:9:"购物车";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901112";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/f2a6a4efdf216a9530e009948310ba79.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"公司介绍";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1556447643377";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:2:"10";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:2:"10";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#d9d9d9";}s:4:"data";a:1:{s:14:"C1556447643377";a:5:{s:4:"text";s:6:"商品";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:6:"更多";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";}s:14:"M1556447629116";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447629116";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447629117";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447629118";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447629119";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1556447710765";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"2";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:9:"block one";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447710765";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447710766";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447710767";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447710768";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1556447741843";a:6:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:11:"block three";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447741843";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447741844";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447741845";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447741846";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";s:5:"index";s:3:"NaN";}s:14:"M1556447763411";a:5:{s:3:"max";s:1:"5";s:4:"icon";s:23:"iconfont2 icon-fuwenben";s:6:"params";a:1:{s:7:"content";s:164:"PHAgc3R5bGU9InRleHQtYWxpZ246IGNlbnRlcjsiPuaZuuaFp+iMtuS7k+aPkOS+m+aKgOacr+aUr+aMgTwvcD48cCBzdHlsZT0idGV4dC1hbGlnbjogY2VudGVyOyI+d3d3LnpoaWh1aWNoYWNhbmcuY29tPC9wPg==";}s:5:"style";a:3:{s:10:"background";s:7:"#ffffff";s:7:"padding";s:2:"10";s:9:"margintop";s:2:"10";}s:2:"id";s:8:"richtext";}s:14:"M1556447842556";a:7:{s:4:"icon";s:21:"iconfont2 icon-caidan";s:6:"isfoot";s:1:"1";s:3:"max";s:1:"1";s:6:"params";a:8:{s:8:"navstyle";s:1:"0";s:8:"textshow";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:20:{s:11:"pagebgcolor";s:7:"#f9f9f9";s:7:"bgcolor";s:7:"#ffffff";s:9:"bgcoloron";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:11:"iconcoloron";s:7:"#f1415b";s:9:"textcolor";s:7:"#666666";s:11:"textcoloron";s:7:"#666666";s:11:"bordercolor";s:7:"#cccccc";s:13:"bordercoloron";s:7:"#ffffff";s:14:"childtextcolor";s:7:"#666666";s:12:"childbgcolor";s:7:"#f4f4f4";s:16:"childbordercolor";s:7:"#eeeeee";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:11:"paddingleft";s:1:"0";s:10:"paddingtop";s:1:"0";s:8:"iconfont";s:2:"28";s:8:"textfont";s:2:"12";s:3:"bdr";s:1:"0";s:8:"bdrcolor";s:7:"#cccccc";}s:4:"data";a:4:{s:14:"C1556447842557";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-x-shouye2";s:4:"text";s:6:"首页";}s:14:"M1556448352088";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-x-caidan5";s:4:"text";s:6:"首页";}s:14:"C1556447842558";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-2.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:11:"icon-x-gwc2";s:4:"text";s:9:"购物车";}s:14:"C1556447842560";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-4.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:13:"icon-x-geren2";s:4:"text";s:12:"联系我们";}}s:2:"id";s:8:"footmenu";}}',
                                        "tpl_name"=>"系统推荐"
                                    ];
                                    $diy_id[1]= Db::table("ims_sudu8_page_diypage")->insertGetId($arrs);
                                    $new_array =[
                                        "uniacid"=>$is_pay["store_id"],
                                        "pageid"=>implode(',',$diy_id),
                                        "template_name"=>"综合商城模板",
                                        "thumb"=>"/diypage/template_img/template_shop/cover.png",
                                        "create_time"=>time(),
                                        "status"=>1,
                                        "store_id"=>$is_pay["store_id"]
                                    ];
                                    $bool=Db::table("ims_sudu8_page_diypagetpl")->insertGetId($new_array);
                                    }


                            if($bool  && $boole){
                                $this->success("审核成功", url("admin/Control/control_order_index"));
                            } else {
                                $this->error("审核失败", url("admin/Control/control_order_index"));
                            }
                        } else {
                                $this->error("审核错误", url("admin/Control/control_order_index"));    
                        }
                    }
                } else {
                    //审核不通过
                    $data["explains"] = $explains; //审核说明
                    $data["audit_status"] = -1; //订单审核状态（2汇款审核通过，-1审核不通过,0待审核 )
                    

                    $res = Db::name("set_meal_order")
                    ->where("order_number",$is_pay["order_number"])
                    ->update($data);

                    $rest = Db::name("meal_orders")
                    ->where("order_number",$is_pay["order_number"])
                    ->update($data);

                    if($res  && $rest){
                        $this->success("审核成功", url("admin/Control/control_order_index"));
                    } else {
                        $this->error("审核失败", url("admin/Control/control_order_index"));
                    }
                }
            }
        } 
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:入驻资料店铺页面
     **************************************
     */
    public function control_store_return(){
        $order =Db::table('tb_store')
            ->field("phone_number,contact_name,is_business,address_real_data,status store_status,store_name,id")
            ->where("store_del",1)
            ->order('id',"desc")
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        return view("control_store_return",["order"=>$order]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:入驻订单店铺审核操作
     **************************************
     * @param Request $request
     */
    public function control_order_update(Request $request){
        if($request -> isPost()){
            $id = $request -> only(["id"])["id"];
            $data = $request -> param();
            $bool = db("store")->where("id",$id)->update($data);
            if($bool){
                if($data['status'] ==1){
                    $user_id =db("store")
                        ->where("id",$id)
                        ->value("user_id");
                    $user_data =Db::table("tb_pc_user")
                        ->field("phone_number,password")
                        ->where("id",$user_id)
                        ->find();
                    //审核通过则在后台添加一个登录账号，不通过则不添加
                 $is_set = Db::name("admin")->where("store_id",$id)->find();
                 if(!$is_set){
                     //先判断该店铺是否已经添加过admin表
                     //插入到后台
                     $array =[
                         "account"=>$user_data['phone_number'], //手机号
                         "passwd"=>$user_data['password'],//登录密码
                         "sex"=>1,
                         "stime"=>date("Y-m-d H:i:s"),
                         "role_id"=>8,//普通访客
                         "phone"=>$user_data['phone_number'],
                         "status"=>0,//0可以登录后台，1被禁用
                         "name"=>$user_data['phone_number'],
                         "store_id"=>$id,
                         "is_own"=>1, //1为商家，0为商家下面的员工或者admin
                     ];
                     Db::name("admin")->insertGetId($array);
                 }
                }
                $mobile = $user_data['phone_number'];
                $content = "尊敬的用户您好！您的店铺申请成功，请及时登陆网站，选择套餐，完成店铺入驻";
                $url = "http://120.26.38.54:8000/interface/smssend.aspx";
                $post_data = array("account" => "chacang", "password" => "123qwe", "mobile" => "$mobile", "content" => $content);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                $output = curl_exec($ch);
                curl_close($ch);

                $this->success("审核成功",url("admin/Control/control_order_index"));
            } else {
                $this->error("审核失败,请编辑后再提交",url("admin/Control/control_order_index"));
            }
             
        }
    }



    /**
     * [入驻订单搜索]
     * 郭杨
     */    
    public function control_order_search(){
        $contact_name = input("contact_name") ? input("contact_name"):null;
        $name = input("name") ? input("name"):null; 
        if((!empty($contact_name)) && (!empty($name))){
            $add_order = db("store")
                        ->where("contact_name",$contact_name)
                        ->where("enter_meal",$name)
                        ->paginate(20 ,false, [
                            'query' => request()->param(),
                        ]);

        } else if((empty($contact_name)) && (!empty($name))){
           $add_order = db("store")
                     ->where("enter_meal", "like","%" .$name ."%")
                     ->paginate(20 ,false, [
                      'query' => request()->param(),
                      ]);
                      
        } else if((!empty($contact_name)) && (empty($name))){
            $add_order = db("store")
                      ->where("contact_name", "like","%" .$contact_name ."%")
                      ->paginate(20 ,false, [
                       'query' => request()->param(),
                       ]);
        } else {
            $add_order = db("store")->paginate(20,false, [
                'query' => request()->param(),
            ]);
        }
        $enter_meal = db("enter_meal")->field("name")->select();
        return view("control_order_index",["order"=>$add_order,"enter_meal"=>$enter_meal]);
                     
    }


    /**
     * [店铺分析]
     * 郭杨
     */    
    public function control_store_index(){     
        return view("control_store_index");
    }


    /**
     * [增值商品运费模板]
     * 郭杨
     */    
    public function control_store_templet(){     
        return view("control_store_templet");
    }



    /**
     * [增值商品运费模板添加]
     * 郭杨
     */    
    public function control_templet_add(){     
        return view("control_templet_add");
    }


    /**
     * [增值商品运费模板删除]
     * 郭杨
     */    
    public function control_templet_delete(){     
        return view("control_templet_add");
    }


    /**
     * [增值商品运费模板编辑]
     * 郭杨
     */    
    public function control_templet_edit(){     
        return view("control_templet_edit");
    }



    /**
     * [增值商品运费模板更新]
     * 郭杨
     */    
    public function control_templet_update(){     
        return view("control_templet_add");
    }
    
    

    /**
     * [线下充值申请]
     * 郭杨
     */    
    public function control_online_charging(){     
        return view("control_online_charging");
    }


    /**
     * [线下充值申请编辑]
     * 郭杨
     */    
    public function control_charging_edit(){     
        return view("control_charging_edit");
    }


    /**
     * [提现申请]
     * 郭杨
     */    
    public function control_withdraw_deposit(){     
        return view("control_withdraw_deposit");
    }


    /**
     * [提现申请编辑]
     * 郭杨
     */    
    public function control_withdraw_edit(){     
        return view("control_withdraw_edit");
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:admin后台审核订单发票
     **************************************
     */
    public function store_examine_receipt(Request $request){
        if($request -> isPost()){
            $id = $request->only(['id'])['id']; //套餐id
            $store_data = Db::name("meal_orders")->where("id","EQ",$id)->find();
            
            $receipt = Db::name("store_receipt")
                ->where("store_id","EQ",$store_data['store_id'])
                ->where("meal_order_id","EQ",$id)
                ->find();
            $location = db("pc_store_address")->where("store_id",'EQ',$store_data['store_id'])->where("default","EQ",1)->find();
            $money = db("meal_orders")->where("id",'EQ',$id)->value("pay_money");
            if(!empty($receipt)){
                $receipt['location'] = $location;
                return ajax_success("发送成功",$receipt);
            } else {
                $data = array(
                    'apply'=>1, 
                    'money'=> $money,
                    'location'=>$location             
                );
                return ajax_success("发送成功",$data);
            }
        }
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:admin后台审核订单发票
     **************************************
     */
    public function admin_auditing_receipt(Request $request){
        if($request->isPost()){
            $id = $request->only(['id'])['id'];  //套餐id
            $store_data = Db::name("meal_orders")->where("id","EQ",$id)->find();    
            $receipt = Db::name("store_receipt")
                ->where("store_id","EQ",$store_data['store_id'])
                ->where("meal_order_id","EQ",$id)
                ->find();
            
            if($receipt['apply'] == 3){
                exit(json_encode(array("status" => 0, "info" => "已审核通过,不能重复开票")));
            } else {
                $bool = Db::name("meal_orders")->where("id","EQ",$id)->update(["apply"=>3]);
                $boole = Db::name("store_receipt")->where("meal_order_id","EQ",$id)->update(["apply"=>3]);
                if($bool && $boole){
                    return ajax_success("审核成功",$bool);
                } else {
                    exit(json_encode(array("status" => 2, "info" => "审核失败")));
                }
            }
        }
    }

 }