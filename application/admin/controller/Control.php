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
            $min = min($min_cost);        //套餐原价最低价
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
        $order =Db::table('tb_set_meal_order')
            ->field("tb_set_meal_order.*,tb_store.phone_number,tb_store.contact_name,tb_store.is_business,tb_store.address_real_data,tb_store.status store_status")
            ->join("tb_store","tb_set_meal_order.store_id=tb_store.id",'left')
            ->where("is_del",1)
            ->order("tb_set_meal_order.create_time","desc")
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        $enter_meal = db("enter_meal")->field("name")->select();
        return view("control_order_index",["order"=>$order,"enter_meal"=>$enter_meal]);
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
        $store_id =Db::table("tb_set_meal_order")
            ->where("id",$id)
            ->value("store_id");
        $store_info =Db::table("tb_store")
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
        $store_order = Db::table('tb_set_meal_order')
            ->field("tb_set_meal_order.*,tb_store.phone_number,tb_store.contact_name,tb_store.is_business,tb_store.address_real_data,tb_store.status store_status,tb_store.address_data,tb_store.id_card,tb_store.card_positive,tb_store.store_introduction,tb_store.store_qq,tb_store.explain")
            ->join("tb_store","tb_set_meal_order.store_id=tb_store.id",'left')
            ->where("is_del",1)
            ->where("store_id",$store_id)
            ->select();
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
            $id = $request->only(["id"])["id"];
            $data = $request->param();
            $is_pay = db("set_meal_order")->where("id", $id)->field("pay_type,store_id")->find();
            if(!$is_pay["pay_type"]){
                $this->error("此订单未付款不能审核操作");
            }
            $bool = db("set_meal_order")->where("id", $id)->update($data);
            if ($bool) {
                //审核通过则对店铺进行开放，修改店铺的权限（普通访客）为商家店铺
                if($data["audit_status"] ==1){
                    Db::table("tb_admin")
                        ->where("store_id",$is_pay["store_id"])
                        ->where("is_own",1)
                        ->update(["role_id"=>7]);
                    //审核通过的时候先判断是否有小程序模板，没有的话则进行添加，有的话则不需要
                    $is_set = Db::table("ims_sudu8_page_diypageset")->where("store_id",$is_pay["store_id"])->find();
                    if(!$is_set){
                        $is_uniacid =Db::table("ims_sudu8_page_base")->where("uniacid",$is_pay["store_id"])->find();
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
                        $arr=[
                            "uniacid"=>$is_pay["store_id"],
                            "index"=>1,
                            "page"=>'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:23:"后台页面名称11111";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                            "items"=>"",
                            "tpl_name"=>"首页"
                        ];
                       $diy_id = Db::table("ims_sudu8_page_diypage")->insertGetId($arr);
                        $new_array =[
                            "uniacid"=>$is_pay["store_id"],
                            "pageid"=>$diy_id,
                            "template_name"=>"综合商城模板",
                            "thumb"=>"/diypage/template_img/template_shop/cover.png",
                            "create_time"=>time(),
                            "status"=>1,
                            "store_id"=>$is_pay["store_id"]
                        ];
                        Db::table("ims_sudu8_page_diypagetpl")->insertGetId($new_array);
                    }
                }
                $this->success("审核成功", url("admin/Control/control_order_index"));
            } else {
                $this->error("审核失败,请编辑后再提交");
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

 }