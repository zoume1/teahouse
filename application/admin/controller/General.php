<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */

namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\paginator\driver\Bootstrap;
use think\Session;

class  General extends  Base {
   
    private  $store_ids;

    public function _initialize()
    {
       $isset_store = Session::get("store_id");
       if($isset_store){
           $this->store_ids =$isset_store;
       }else{
           $this->success("该店铺信息不存在","admin/Home/index");
       }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺概况
     **************************************
     * @return \think\response\View
     */
    public function general_index(){
        $data =Db::table("tb_store")
            ->field("id,is_business,store_number,contact_name,id_card,store_logo,store_qq,phone_number,store_introduction,store_name")
            ->where("id",$this->store_ids)
            ->find();
        $goods_name=Db::table("tb_set_meal_order")
            ->where("store_id",$this->store_ids)
            ->where("is_del",1)
            ->where("status",1)
            ->where("pay_type","NEQ",-1)
            ->where("audit_status",1)
            ->value("goods_name");
        if($goods_name){
            $data["enter_meal"]  =$goods_name;
        }else{
            $data["enter_meal"] ="未购买套餐版本";
        }
        return view("general_index",["data"=>$data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺收货地址
     **************************************
     * @return \think\response\View
     */
    public function general_address(){
        $store_id =$this->store_ids ;
        $data =Db::name("pc_store_address")->where('store_id',$store_id)->select();
        $number_one = count($data);
        $number_two = 5-$number_one;
        return view("general_address",["data"=>$data,"number_one"=>$number_one,"number_two"=>$number_two]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:地址添加编辑
     **************************************
     */
    public function  general_address_add(Request $request){
        if($request->isPost()){
            $id =$request->only(["id"])["id"];
            $address = $request->only(["address"])["address"];//三级城市逗号隔开
            $street = $request->only(["street"])["street"];//详细地址
            $zip = $request->only(["zip"])["zip"];//邮政编号
            $phone = $request->only(["phone"])["phone"];//电话号码
            $name = $request->only(["name"])["name"];//收货人姓名
            $default =$request->only(["default"])["default"]; //设置默认收货地址（1为默认，0为非默认）
            $store_id =$this->store_ids; //店铺id
            $data =[
                "address"=>$address,
                "street"=>$street,
                "zip"=>$zip,
                "phone"=>$phone,
                "name"=>$name,
                "default"=>$default,
                "store_id"=>$store_id
            ];
            if($id){
                //地址编辑
                $bool =Db::name("pc_store_address")
                    ->where("store_id",$store_id)
                    ->where("id",$id)
                    ->update($data);
                if($bool){
                    if($default ==1){
                        Db::name("pc_store_address")
                            ->where("store_id",$store_id)
                            ->where("id","NEQ",$id)
                            ->update(["default"=>0]);
                    }
                    return ajax_success("修改成功");
                }else {
                    return ajax_error("修改失败");
                }
            }else{
                //地址添加
                $ids =Db::name("pc_store_address")->insertGetId($data);
                if($ids){
                    if($default ==1){
                        Db::name("pc_store_address")
                            ->where("store_id",$store_id)
                            ->where("id","NEQ",$ids)
                            ->update(["default"=>0]);
                    }
                    return ajax_success("添加成功");
                }else {
                    return ajax_error("添加失败");
                }
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺地址删除
     **************************************
     * @param Request $request
     */
    public function general_address_del(Request $request){
        if($request->isPost()){
            $id =$request->only('id')['id'];
            if($id){
                $bool =Db::name('pc_store_address')
                    ->where("id",":id")
                    ->bind(["id"=>[$id,\PDO::PARAM_INT]])
                    ->delete();
                if($bool){
                    return ajax_success('删除成功');
                }else{
                    return ajax_error('删除失败');
                }
            }else{
                return ajax_error('这条地址信息不正确');
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺地址编辑数据返回
     **************************************
     */
    public function general_address_edit_info(Request $request){
        if($request->isPost()){
            $id =$request->only(["id"])["id"];
            $data =Db::name("pc_store_address")->where('id',$id)->find();
            if(!empty($data)){
                return ajax_success('地址信息返回成功',$data);
            }else{
                return ajax_error('地址信息返回失败');
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺地址所有信息返回成功
     **************************************
     */
    public function  general_address_return_info(Request $request){
        $store_id =$this->store_ids ;
        if($request->isPost()){
            $data =Db::name("pc_store_address")
                ->where('store_id',$store_id)
                ->order("default","desc")
                ->select();
            if(!empty($data)){
                $list=array();
                foreach ($data as $key=>$value){
                    $list[$key]["prov"] =explode(" ",$value["address"])[0];
                    $list[$key]["city"] =explode(" ",$value["address"])[1];
                    $lists =count(explode(" ",$value["address"]));
                    if($lists=3){
                        $list[$key]["dist"] =explode(" ",$value["address"])[2];
                    }else{
                        $list[$key]["dist"] =NULL;
                    }
                    $list[$key]["street"] =$value["street"];
                    $list[$key]["zip"] =$value["zip"];
                    $list[$key]["name"] =$value["name"];
                    $list[$key]["phone"] =$value["phone"];
                    $list[$key]["default"] =$value["default"];
                    $list[$key]["id"]=$value['id'];
                }
                return ajax_success('所有地址信息返回成功',$list);
            }else{
                return ajax_error('所有地址信息返回失败');
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺地址默认值设置
     **************************************
     */
    public function general_address_status(Request $request){
        if($request->isPost()){
            $id =$request->only('id')['id'];
            $store_id =$this->store_ids; //店铺id
            if(!empty($id)){
                $bool=  Db::name('pc_store_address')
                    ->where("store_id",$store_id)
                    ->where("id","EQ",$id)
                    ->update(['default'=>1]);
                if($bool){
                    Db::name('pc_store_address')
                        ->where("store_id",$store_id)
                        ->where("id","NEQ",$id)
                        ->update(['default'=>0]);
                    return ajax_success("设置成功");
                }else{
                    return ajax_error('设置失败');
                }

            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺编辑
     **************************************
     */
    public function  general_update(Request $request,$id=null){
        if($request->isPost()){
            $array = $request->param();
            $data=[
                "store_name"=>$array['store_name'],
                "store_number"=>$array['store_number'],
                "store_qq"=>$array['store_qq'],
                "store_introduction"=>$array['store_introduction'],
            ];
            $store_img =$request->file("store_logo");
            if(!empty($store_img)){
                $info = $store_img->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["store_logo"] = str_replace("\\","/",$info->getSaveName());
            }
            $bool =Db::table("tb_store")->where("id",$id)->update($data);
            if($bool){
                $this->success("修改成功");
            }else{
                $this->error("请重新修改");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:店铺logo删除
     **************************************
     */
    public function general_logo_del(Request $request){
        if($request->isPost()){
            $id =$request->only(['id'])['id'];
            $img_logo =Db::table('tb_store')->where("id",$id)->value('store_logo');
            if(!empty($img_logo)){
                unlink(ROOT_PATH . 'public' . DS . 'uploads/'.$img_logo);
            }
            Db::table('tb_store')->where("id",$id)->update(['store_logo'=>null]);
            return ajax_success("删除成功");
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序设置
     **************************************
     * @return \think\response\View
     */
    public function small_routine_index(){
        $data =Db::table("applet")
            ->field("id,name,appID,appSecret,mchid,signkey")
            ->where("store_id",$this->store_ids)
            ->find();
        return view("small_routine_index",["data"=>$data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序设置添加编辑功能
     **************************************
     */
    public function  small_routine_edit(Request $request,$id=null){
            //编辑
            if($request->isPost()){
                $store_id =$this->store_ids;
                if($id){
                    $is_set_appid =Db::table("applet")
                        ->where("store_id","NEQ",$store_id)
                        ->where("appID",trim(input("appID")))
                        ->value("id");
                    if($is_set_appid){
                        $this->error("此小程序appID已存在，请更换其他小程序appid");
                    }
                    $appletid =$id;
                    $app = array(
                        "name" => trim(input("name")),
                        "appID" => trim(input("appID")),
                        "appSecret" => trim(input("appSecret")),
                        "mchid" => trim(input("mchid")),
                        "signkey" => trim(input("signkey"))
                    );
                    $app_is = Db::table("applet")
                        ->where("store_id",$store_id)
                        ->where("id",$appletid)
                        ->update($app);
                    if($app_is){
                        $this->success("编辑成功");
                    }else{
                        $this->error("未改动数据");
                    }
                }else {
                    $is_set =Db::table("applet")->where("store_id",$store_id)->value("id");
                    if($is_set){
                        $this->error("此店铺小程序已存在，无法再添加");
                    }
                   $is_set_appid =Db::table("applet")
                       ->where("store_id","NEQ",$store_id)
                       ->where("appID",trim(input("appID")))
                       ->value("id");
                    if($is_set_appid){
                        $this->error("此小程序appID已存在，请更换其他小程序appid");
                    }
                    $app = array(
                        "name" => trim(input("name")),
                        "appID" => trim(input("appID")),
                        "appSecret" => trim(input("appSecret")),
                        "mchid" => trim(input("mchid")),
                        "signkey" => trim(input("signkey")),
                        "store_id"=>$store_id,
                        "id"=>$store_id
                    );
                    $app_is = Db::table("applet")->insertGetId($app);
                    if($app_is){
                        $this->success("添加成功");
                    }else{
                        $this->error("未改动数据");
                    }
                }
            }else{
                $this->error("请求失败");
            }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序装修
     **************************************
     * @return \think\response\View
     */


    public function decoration_routine_index(Request $request){
        if($request->isPost()){
            $list = Db::table("applet")
                ->where("store_id",$this->store_ids)
                ->limit(1)
                ->select();
            $goods_names = Session::get('goods_names');  
             
            if(!empty($list)){
                foreach ($list as $k=>$v){
                    $list[$k]["tplid"] = Db::table("ims_sudu8_page_diypagetpl")
                            ->where("store_id",$this->store_ids)
                            ->value("id");
                     $list[$k]["tpliddddd"] = Db::table("ims_sudu8_page_diypagetpl")
                             ->field('id')
                            ->where("store_id",$this->store_ids)
                             ->select();
                    $list[$k]["goods_names"] =Db::table("tb_set_meal_order")
                        ->where("store_id",$this->store_ids)
                        ->where("audit_status",1)
                        ->order('id desc')
                        ->value("goods_name");
                        $list[$k]["goods_names_id"] =Db::table("tb_set_meal_order")
                        ->where("store_id",$this->store_ids)
                        ->where("audit_status",1)
                        ->order('id desc')
                        ->value("id");
                        $goods_list_name =Db::table("tb_set_meal_order")
                        ->where("store_id",$this->store_ids)
                        ->where("audit_status",1)
                        ->select();
                        foreach ($goods_list_name as $key => $value) {
                           if($goods_list_name[$key]['id']==$list[$k]["goods_names_id"]){
                              $goods_list_name[$key]['status_type']=1;
                              $update=Db::table("tb_set_meal_order")->where("id",$goods_list_name[$key]['id'])->update($goods_list_name[$key]);
                           }else{
                               $goods_list_name[$key]['status_type']=0;
                               $update=Db::table("tb_set_meal_order")->where("id",$goods_list_name[$key]['id'])->update($goods_list_name[$key]);
                           }
                        }

                       //鲁文兵添加
                      $list[$k]["goods_names_test"]=  Db::table("tb_set_meal_order")
                        ->field('goods_name')
                        ->where("store_id",$this->store_ids)
                        ->where("audit_status",1)
                        ->select();
                        /*鲁文兵版本切换*/
                       if(!empty($goods_names)){                          
                            if($list[$k]["goods_names"]=="茶进阶版"){
                            $list[$k]["goods_names_test"][2]['goods_name']="进阶版";
                            $list[$k]["goods_names_test"][2]['status_type']=1;
                            $list[$k]["goods_names_test"][1]['goods_name']="行业版";
                            $list[$k]["goods_names_test"][1]['status_type']=0;
                            $list[$k]["goods_names_test"][0]['status_type']="万用版";
                            $list[$k]["goods_names_test"][0]['status_type']=0;

                        }
                        if($list[$k]["goods_names"]=="茶行业版"){
                            $list[$k]["goods_names_test"][1]['goods_name']="行业版";
                            $list[$k]["goods_names_test"][1]['status_type']=1;
                            $list[$k]["goods_names_test"][0]['status_type']="万用版";
                            $list[$k]["goods_names_test"][0]['status_type']=0;

                        }
                        if($list[$k]["goods_names"]=="万用版"){
                            $list[$k]["goods_names_test"][0]['status_type']="万用版";
                            $list[$k]["goods_names_test"][0]['status_type']=1;

                        }
                        }else{
                            $length= count($list[$k]["goods_names_test"]);                        
                                if($list[$k]["goods_names"]=="茶进阶版"){
                                $list[$k]["goods_names_test"][2]['goods_name']="进阶版";
                                $list[$k]["goods_names_test"][2]['status_type']=1;
                                $list[$k]["goods_names_test"][1]['goods_name']="行业版";
                                $list[$k]["goods_names_test"][1]['status_type']=0;
                                $list[$k]["goods_names_test"][0]['status_type']="万用版";
                                $list[$k]["goods_names_test"][0]['status_type']=0;

                            }                                 
                            if($list[$k]["goods_names"]=="茶行业版"){
                                $list[$k]["goods_names_test"][1]['goods_name']="行业版";
                                $list[$k]["goods_names_test"][1]['status_type']=1;
                                $list[$k]["goods_names_test"][0]['status_type']="万用版";
                                $list[$k]["goods_names_test"][0]['status_type']=0;

                            }
                            if($list[$k]["goods_names"]=="万用版"){
                                $list[$k]["goods_names_test"][0]['status_type']="万用版";
                                $list[$k]["goods_names_test"][0]['status_type']=1;

                            }
                        }                         
                    }                
                
               
                return ajax_success("数据返回成功",["data"=>$list]);
            }else{
                return ajax_error("请先编辑小程序设置");

            }
        }
        return view("decoration_routine_index");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序装修
     **************************************
     * @return \think\response\View
     */
    public function xiaochengxu_edit(){
        $appletid = input("appletid");//每一个用户返回不一样的
        $res = Db::table('applet')->where("id",$appletid)->find();
        $a=Db::table('ims_sudu8_page_base')->where("uniacid",$appletid)->find();
        $bg_music=$a['diy_bg_music'];
        //*鲁文兵版本切换*/
         $goods_names = input("goods_names");
      
        if(!empty($goods_names)){
            if(empty(Session::get('goods_names'))){
                
                Session::set('goods_names',$goods_names);
            }else{
                 Session::delete('goods_names');
                 Session::set('goods_names',$goods_names);
                
                }
           
        }
        
      $this->assign('goods_names',$goods_names);

        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $op=input("op");
        $tplid=input("tplid"); //打印出来是1(因为是写死，现在需要使用到store-id)
        if($op){
            if($op=="setindex"){
                $val = input('v');
                $key_id = input('key_id');
                if(empty($key_id)){
                    return false;
                }
                if($val == 1){
                    Db::table('ims_sudu8_page_diypage')
                        ->where("uniacid",$appletid)
                        ->update(array("index"=>0));
                    $result = Db::table('ims_sudu8_page_diypage')
                        ->where("uniacid",$appletid)
                        ->where("id",$key_id)
                        ->update(array("index"=>1));
                }else{
                    $result = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("id",$key_id)->update(array("index"=>0));
                }
                if($result){
                    return  json_encode(['status' => 1,'result' => ['returndata' => 1]]);
                }else{
                    return json_encode(['status' => 0]);
                }
            }
            if($op == "query"){
                $type = input('type');
                $kw = input('kw');
                switch ($type){
                    case 'news':
                        $list = Db::table('ims_sudu8_page_products')->where("uniacid",$appletid)->where("type","showArt")->where("title","like","%".$kw."%")->field("id,title")->select();
                        $html = '';
                        if($list){
                            foreach ($list as $k => $v){
                                $html .= '<div class="line">

                                        <div class="icon icon-link1"></div>

                                        <nav data-href="/sudu8_page/showArt/showArt?id='.$v['id'].'" data-linktype="page" class="btn btn-default btn-sm" title="选择">选择</nav>

                                        <div class="text"><span class="label lable-default">普通</span>'.$v['title'].'</div>

                                        </div>';
                            }
                        }else{
                            $html = '<div class="line">

                                            无相关搜索结果

                                        </div>';
                        }

                        break;
                    case 'pic':

                        $list = Db::table('ims_sudu8_page_products')->where("uniacid",$appletid)->where("type","showPic")->where("title","like","%".$kw."%")->field("id,title")->select();
                        $html = '';
                        if($list){
                            foreach ($list as $k => $v){
                                $html .= '<div class="line">

                                                    <div class="icon icon-link1"></div>

                                                    <nav data-href="/sudu8_page/showPic/showPic?id='.$v['id'].'" data-linktype="page" class="btn btn-default btn-sm" title="选择">选择</nav>

                                                    <div class="text"><span class="label lable-default">普通</span>'.$v['title'].'</div>

                                                </div>';
                            }
                        }else{
                            $html = '<div class="line">

                                            无相关搜索结果

                                        </div>';
                        }

                        break;

                    case 'goods':
                        $list = Db::table('ims_sudu8_page_products')->where("uniacid",$appletid)->where("type","neq","showArt")->where("type","neq","showPic")->where("type","neq","wxapp")->where("title","like","%".$kw."%")->field("id,title,price,pro_kc,pro_flag")->select();
                        $html = '';
                        if($list){
                            foreach ($list as $k => $v){
                                if($v['pro_flag'] == 2){
                                    $url = "/sudu8_page/showProMore/showProMore?id=".$v['id'];
                                    $g = "多规格";
                                }else{
                                    $url = "/sudu8_page/showPro/showPro?id=".$v['id'];
                                    $g = "单规格";
                                }
                                $html .= '<div class="line">
                                        <div class="icon icon-link1"></div>
                                            <nav data-href="'.$url.'" data-linktype="page" class="btn btn-default btn-sm" title="选择">选择</nav>
                                       <div class="text"><span class="label lable-default">普通</span>'.$g.' - 商品名称：'.$v['title'].' &nbsp; 价格：'.$v['price'].' &nbsp; 库存：'.$v['pro_kc'].'</div>

                                        </div>';
                            }
                        }else{
                            $html = '<div class="line">

                                            无相关搜索结果

                                        </div>';
                        }

                        break;
                }

                echo $html;
                exit;
            }
            if ($op == 'delpage'){
                $tpl_id = input("tplid");
                $tpl_pages = Db::table('ims_sudu8_page_diypagetpl')
                    ->where("uniacid",$appletid)
                    ->where("id",$tpl_id)
                    ->find()['pageid'];

                $tpl_pages_arr = explode(",",$tpl_pages);
                $tpl_pages_count = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("id","in",$tpl_pages_arr)->count();
                if($tpl_pages_count == 1){
                    $this->error('删除失败，模板必须保留一个页面');
                     exit;
                }

                $id = input('id') ? intval(input('id')) : 0;
                if($id == 0){

                    $this->error('参数错误');

                    exit;

                }
                 $is_index = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("id",$id)->where("index",1)->find();
                if($is_index){
                    $this->error("当前页面为首页不可删除");
                    exit;
                }
                $result = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("id",$id)->delete();

                if($result){
                    $this->success("删除成功");

                }else{
                    $this->error('删除失败');

                }

            }
            if($op == "setsave"){
                // $pid = input('key_id');
                $is = Db::table('ims_sudu8_page_diypageset')->where("uniacid",$appletid)->find();
                // $is = Db::table('ims_sudu8_page_diypageset')->where("uniacid",$appletid)->where("pid",$pid)->find();
                $go_home = input('go_home');
                $kp = input('kp');
                $kp_is = input('kp_is');
                $kp_m = input('kp_m');
                $kp_url = input('kp_url');
                $kp_urltype = input('kp_urltype');
                $tc_is = input('tc_is');
                $tc = input('tc');
                $tc_url = input('tc_url');
                $tc_urltype = input('tc_urltype');
                $foot_is = input('foot_is');
                $bg_music = input('bg_music');
                $data = array(
                    // "pid"=>$pid,
                    "go_home"=>$go_home,
                    "kp"=>remote($appletid,$kp,2),
                    "kp_is"=>intval($kp_is),
                    "kp_m"=>intval($kp_m),
                    "kp_url"=>$kp_url,
                    "kp_urltype"=>$kp_urltype,
                    "tc_is"=>$tc_is,
                    "tc"=>remote($appletid,$tc,2),
                    "tc_url"=>$tc_url,
                    "tc_urltype"=>$tc_urltype,
                    "foot_is"=>$foot_is,
                );
                Db::table("ims_sudu8_page_base")->where("uniacid",$appletid)->update(array("diy_bg_music"=>$bg_music));
                if($is){
                    $res = Db::table('ims_sudu8_page_diypageset')->where("uniacid",$appletid)->update($data);
                }else{
                    $data['uniacid'] = $appletid;
                    $res = Db::table('ims_sudu8_page_diypageset')->insert($data);
                }
                if($res==1){
                    return 1;
                }else{
                    return 2;
                }
            }
            if ($op == 'add'){

                $data = $_POST;

                if(isset($data['data']['page']['url']) && $data['data']['page']['url'] != ""){
                    $data['data']['page']['url'] = remote($appletid,$data['data']['page']['url'],2);
                }

                if(isset($data['data']['page']['name']) && $data['data']['page']['name'] != ''){

                    $sd = [];

                    $sd['tpl_name'] = $data['data']['page']['name'];
                    if(isset($data['data']['page']['url']) && $data['data']['page']['url'] != ""){
                        $data['data']['page']['url'] = remote($appletid,$data['data']['page']['url'],2);
                    }

                    $sd['page'] = serialize($data['data']['page']);
                    if(strpos($sd['page'], "\\") !== false){
                        echo json_encode(['status' => -1,'message' => '保存失败，请去除特殊字符“\”再保存'],JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    if(isset($data['data']['items'])){
                        foreach($data['data']['items'] as $ki => $vi){
                            if($vi['id'] == "video" ){
                                if(!empty($vi['params']['videourl'])){
                                    if(strpos($vi['params']['videourl'],"</iframe>") !== false || strpos($vi['params']['videourl'],"</embed>") !== false){
                                        $data['data']['items'][$ki]['params']['videourl'] = "";
                                    }
                                }
                            }
                            if($vi['id'] == "yuyin" ){
                                if(!empty($vi['params']['linkurl'])){
                                    if(strpos($vi['params']['linkurl'],"</iframe>") !== false || strpos($vi['params']['linkurl'],"</embed>") !== false){
                                        $data['data']['items'][$ki]['params']['linkurl'] = "";
                                    }
                                }
                                if(!isset($vi['params']['backgroundimg'])){
                                    $data['data']['items'][$ki]['params']['backgroundimg'] = '';
                                }
                            }
                        }
                    }
                    if(isset($data['data']['items']) && $data['data']['items'] != ""){
                        foreach ($data['data']['items'] as $k => &$v) {
                            if($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'xnlf' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'yuyin' || $v['id'] == 'feedback' || $v['id'] == 'yuyin'){
                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }
                            }
                            if($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew'){

                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }

                                if($v['data']){
                                    foreach ($v['data'] as $ki => $vi) {
                                        if($vi['imgurl'] != ""){
                                            $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],2);
                                        }
                                    }
                                }
                            }
                            if($v['id'] == 'contact'){

                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);

                                }
                                if($v['params']['src'] != ""){
                                    $v['params']['src'] = remote($appletid,$v['params']['src'],2);
                                }
                                if($v['params']['ewm'] != ""){
                                    $v['params']['ewm'] = remote($appletid,$v['params']['ewm'],2);
                                }
                            }
                            if($v['id'] == 'video'){

                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }

                                if($v['params']['poster'] != ""){
                                    $v['params']['poster'] = remote($appletid,$v['params']['poster'],2);
                                }
                            }
                            if($v['id'] == 'logo' || $v['id'] == 'dp'){

                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);

                                }
                                if($v['params']['src'] != ""){
                                    $v['params']['src'] = remote($appletid,$v['params']['src'],2);
                                }
                            }
                            if($v['id'] == 'footmenu'){
                                if($v['data']){
                                    foreach ($v['data'] as $ki => $vi) {
                                        if($vi['imgurl'] != ""){
                                            $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],2);
                                        }
                                    }
                                }
                            }
                        }
                        $sd['items'] = serialize($data['data']['items']);
                        if(strpos($sd['items'], "\\") !== false){
                            echo json_encode(['status' => -1,'message' => '保存失败，请去除特殊字符“\”再保存'],JSON_UNESCAPED_UNICODE);
                            exit;
                        }
                    }else{
                        $sd['items'] = "";
                    }


                    $sd['uniacid'] = $appletid;



                    if(intval($data['id']) == 0){

                        // $tplid = input('tplid');


                        /*新创建*/

                        $idata = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("tpl_name",$sd['tpl_name'])->find();

                        if($idata){
                            echo json_encode(['status' => 0,'message' => '创建页面名称重复','id' => 0],JSON_UNESCAPED_UNICODE);exit;
                        }
                        $is = Db::table('ims_sudu8_page_diypage')->where('uniacid',$appletid)->find();
                        if(!$is){
                            $sd['index'] = 1;
                        }
                        $result = Db::table('ims_sudu8_page_diypage')->insert($sd);

                        $key = Db::table('ims_sudu8_page_diypage')->getLastInsID();

                        if($tplid>0){
                            $pageid =  Db::table('ims_sudu8_page_diypagetpl')->where("uniacid",$appletid)->where("id",$tplid)->field("pageid")->find()['pageid'];
                            Db::table('ims_sudu8_page_diypagetpl')->where("uniacid",$appletid)->where("id",$tplid)->update(array("pageid"=>$pageid.",".$key));
                        }


                    }else{

                        $result = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("id",$data['id'])->update($sd);

                        $key = $data['id'];

                    }
                    if($result){

                        echo json_encode(['status' => 0,'message' => '保存成功','id' => $key],JSON_UNESCAPED_UNICODE);
                        exit;
                    }else{

                        echo json_encode(['status' => -1,'message' => '保存成功，本次保存未做修改'],JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }
            }
            //另存为模板
            if ($op == 'settemplate') {
                $pageid = input('ids/a');
                $pageids = "";
                foreach ($pageid as $key => $value) {
                    $info = Db::table("ims_sudu8_page_diypage")->where("id",$value)->find();
                    $info['page'] = unserialize($info['page']);
                    if(isset($info['page']['url']) && $info['page']['url'] != ""){
                        $info['page']['url'] = remote($appletid,$info['page']['url'],2);
                    }

                    $items = unserialize($info['items']);
                    if($items){
                        foreach ($items as $k => $v) {
                            if($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'xnlf' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'yuyin' || $v['id'] == 'feedback' || $v['id'] == 'yuyin'){
                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }
                            }
                            if($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew'){
                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }
                                if($v['data']){
                                    foreach ($v['data'] as $ki => $vi) {
                                        if($vi['imgurl'] != ""){
                                            $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],2);
                                        }
                                    }
                                }
                            }
                            if($v['id'] == 'contact'){
                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }
                                if($v['params']['src'] != ""){
                                    $v['params']['src'] = remote($appletid,$v['params']['src'],2);
                                }
                                if($v['params']['ewm'] != ""){
                                    $v['params']['ewm'] = remote($appletid,$v['params']['ewm'],2);
                                }
                            }
                            if($v['id'] == 'video'){
                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }
                                if($v['params']['poster'] != ""){
                                    $v['params']['poster'] = remote($appletid,$v['params']['poster'],2);
                                }
                            }
                            if($v['id'] == 'logo' || $v['id'] == 'dp'){
                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }
                                if($v['params']['src'] != ""){
                                    $v['params']['src'] = remote($appletid,$v['params']['src'],2);
                                }
                            }
                            if($v['id'] == 'footmenu'){
                                if($v['data']){
                                    foreach ($v['data'] as $ki => $vi) {
                                        if($vi['imgurl'] != ""){
                                            $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],2);
                                        }
                                    }
                                }
                            }

                            //去除栏目信息
                            //notice(公告) msmk(秒杀模块) goods(产品组) feedback(表单) pt(拼团) listdesc(文章) cases(图文)

                            if ($v['id'] == 'notice' || $v['id'] == 'msmk' || $v['id'] == 'goods' || $v['id'] == 'feedback' || $v['id'] == 'pt' || $v['id'] == 'listdesc' || $v['id'] == 'cases') {
                                $items[$k]['params']['sourceid'] = '';
                            }
                        }
                    }
                    $insert_id = Db::table('ims_sudu8_page_diypage_sys')->insertGetId(array(
                        'index' => $info['index'],
                        'page' => serialize($info['page']),
                        'items' => serialize($items),
                        'tpl_name' => $info['tpl_name'],
                    ));
                    $pageids = $pageids .','. $insert_id;
                }
                $pageids = substr($pageids,1);
                $data = [
                    'pageid' => $pageids,
                    'template_name' => input('name'),
                    'thumb' => input('preview'),
                    'create_time' => time()
                ];
                $key_id = Db::table("ims_sudu8_page_diypagetpl_sys")->insertGetId($data);
                echo json_encode(['status' => 1,'id' => $key_id,'message' => '保存成功'],JSON_UNESCAPED_UNICODE);
                exit;

            }
            if ($op == 'settemp') {
                $template_id = input('templateid');

                if($template_id > 0){

                    $data = [

                        // 'pageid' => implode(',',input('ids/a')),

                        'template_name' => input('name'),

                        'thumb' => remote($appletid,input('preview'),2),

                        'uniacid' => $appletid,

                        // 'create_time' => time()

                    ];

                    $res = Db::table("ims_sudu8_page_diypagetpl")->where("id",$template_id)->update($data);

                    if($res){
                        echo json_encode(['status' => 1],JSON_UNESCAPED_UNICODE);
                        exit;
                    }else{
                        echo json_encode(['status' => 0],JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }
            }
        }else{
            //页面设置 //需要提前设置好
            $setsave = Db::table("ims_sudu8_page_diypageset")->where("uniacid",$appletid)->find();
            if(!$setsave){
                $foot_is = 1;
                $setsave = [];
            }else{
                if($setsave['kp']){
                    $setsave['kp'] = remote($appletid,$setsave['kp'],1);
                }
                if($setsave['tc']){
                    $setsave['tc'] = remote($appletid,$setsave['tc'],1);
                }
                $foot_is = 0;
            }
            //查出当前模板关联页面id(没有关联的则为空)
            $type = input('type');
            if($type){
                $temp = Db::table("ims_sudu8_page_diypagetpl_sys")->where("id",$tplid)->find();
                if($temp['thumb']){
                    $temp['thumb'] = remote($appletid,$temp['thumb'],1);
                }
                if($temp['pageid'] == ""){
                    $pageid = Db::table("ims_sudu8_page_diypage_sys")->insertGetId(array(
                        'uniacid' => $appletid,
                        'index' => 1,
                        'page' => 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:18:"后台页面名称";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                        'items' => '',
                        'tpl_name' => '后台页面名称',
                    ));
                    Db::table("ims_sudu8_page_diypagetpl_sys")->where("id",$tplid)->update(array("pageid"=>$pageid));
                    $temp = Db::table("ims_sudu8_page_diypagetpl_sys")->where("id",$tplid)->find();
                }

                $pageidArray = explode(',',$temp['pageid']);


                //查出当前模板所有的页面
                $list = Db::table("ims_sudu8_page_diypage_sys")->where("id","in",$pageidArray)->field("id,tpl_name,index")->select();

                //页面操作
                $diypage = Db::table("ims_sudu8_page_diypage_sys")->where("id","in",$pageidArray)->where("index",1)->find();
                if($diypage == null){
                    $diypageone = Db::table("ims_sudu8_page_diypage_sys")->where("id","in",$pageidArray)->find();
                    Db::table("ims_sudu8_page_diypage_sys")->where("id",$diypageone['id'])->where("index",0)->update(array("index" => 1));
                    $diypage['id'] = $diypageone['id'];
                }
                $key_id = input('key_id') ? input('key_id') : $diypage['id'];  //显示页面id
                if($key_id>0){
                    $data = Db::table("ims_sudu8_page_diypage_sys")->where("id",$key_id)->find();
                    $data['page'] = unserialize($data['page']);
                    if(isset($data['page']['url']) && $data['page']['url'] != ""){
                        $data['page']['url'] = remote($appletid,$data['page']['url'],1);
                    }
                    $data['items'] = unserialize($data['items']);
                    if($data['items'] != ""){
                        if(isset($data['items']) && $data['items'] != ""){
                            foreach ($data['items'] as $k => &$v) {
                                if($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'xnlf' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'yuyin' || $v['id'] == 'feedback' || $v['id'] == 'yuyin'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                }
                                if($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['data']){
                                        foreach ($v['data'] as $ki => $vi) {
                                            if($vi['imgurl'] != "" && strpos($vi['imgurl'],"diypage/resource") === false){
                                                $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],1);
                                            }
                                        }
                                    }
                                }
                                if($v['id'] == 'contact'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['params']['src'] != ""  && strpos($v['params']['src'],"diypage/resource") === false){
                                        $v['params']['src'] = remote($appletid,$v['params']['src'],1);
                                    }
                                    if($v['params']['ewm'] != ""  && strpos($v['params']['ewm'],"diypage/resource") === false){
                                        $v['params']['ewm'] = remote($appletid,$v['params']['ewm'],1);
                                    }
                                }
                                if($v['id'] == 'video'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['params']['poster'] != "" && strpos($v['params']['poster'],"diypage/resource") === false){
                                        $v['params']['poster'] = remote($appletid,$v['params']['poster'],1);
                                    }
                                }
                                if($v['id'] == 'logo' || $v['id'] == 'dp'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['params']['src'] != ""  && strpos($v['params']['src'],"diypage/resource") === false){
                                        $v['params']['src'] = remote($appletid,$v['params']['src'],1);
                                    }
                                }
                                if($v['id'] == 'footmenu'){
                                    if($v['data']){
                                        foreach ($v['data'] as $ki => $vi) {
                                            if($vi['imgurl'] != "" && strpos($vi['imgurl'],"diypage/resource") === false){
                                                $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],1);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $page = $data['page'];
                    if(isset($page['url']) && $page['url'] != ""){
                        $page['url'] = remote($appletid,$page['url'],1);
                    }
                    $diyform = Db::table("ims_sudu8_page_formlist")->where("uniacid",$appletid)->field("id,formname as title")->select();
                    $data['diyform'] = $diyform;
                    $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                    $data = preg_replace("/\'/", "\'", $data);
                    $data = preg_replace('/(\\\n)/', "<br>", $data);

                }
            }else{
                //综合商城模板
                $temp = Db::table("ims_sudu8_page_diypagetpl")
                    ->where("id",$tplid)
                    ->find();
                if($temp['thumb']){
                    $temp['thumb'] = remote($appletid,$temp['thumb'],1);
                }
                if($temp['pageid'] == ""){
                    $pageid = Db::table("ims_sudu8_page_diypage")->insertGetId(array(
                        'uniacid' => $appletid,
                        'index' => 1,
                        'page' => 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:18:"后台页面名称";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                        'items' => '',
                        'tpl_name' => '后台页面名称',
                    ));
                    Db::table("ims_sudu8_page_diypagetpl")->where("id",$tplid)->update(array("pageid"=>$pageid));
                    $temp = Db::table("ims_sudu8_page_diypagetpl")->where("id",$tplid)->find();
                }
                //改变原来的模板状态为不启用
                $tpls = Db::table("ims_sudu8_page_diypagetpl")->where('uniacid',$appletid)->select();
                if($tpls){
                    foreach ($tpls as $k => $v) {
                        Db::table("ims_sudu8_page_diypagetpl")->where('uniacid',$appletid)->update(array('status' => 2));
                    }
                }
                Db::table("ims_sudu8_page_diypagetpl")->where("id",$tplid)->update(array("status"=>1));
                $pageidArray = explode(',',$temp['pageid']); //这是ims_sudo8_page_diypag表的数据（针对是哪个商家）
                //查出当前模板所有的页面(新添加的则没有)
                $list = Db::table("ims_sudu8_page_diypage")->where("uniacid",$appletid)->where("id","in",$pageidArray)->field("id,tpl_name,index")->select();
                //页面操作
                $diypage = Db::table("ims_sudu8_page_diypage")->where("uniacid",$appletid)->where("id","in",$pageidArray)->where("index",1)->find();
                if($diypage == null){
                    $diypageone = Db::table("ims_sudu8_page_diypage")->where("uniacid",$appletid)->where("id","in",$pageidArray)->find();
                    Db::table("ims_sudu8_page_diypage")->where("uniacid",$appletid)->where("id",$diypageone['id'])->where("index",0)->update(array("index" => 1));
                    $diypage['id'] = $diypageone['id'];
                }
                $key_id = input('key_id') ? input('key_id') : $diypage['id'];  //显示页面id
                if($key_id>0){
                    $data = Db::table("ims_sudu8_page_diypage")->where("id",$key_id)->where("uniacid",$appletid)->find();
                    $data['page'] = unserialize($data['page']);
                    if(isset($data['page']['url']) && $data['page']['url'] != ""){
                        $data['page']['url'] = remote($appletid,$data['page']['url'],1);
                    }
                    $data['items'] = unserialize($data['items']);
                    if($data['items'] != ""){
                        if(isset($data['items']) && $data['items'] != ""){
                            foreach ($data['items'] as $k => &$v) {
                                if($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'xnlf' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'feedback'  || $v['id'] == 'yuyin'){

                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                }
                                if($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['data']){
                                        foreach ($v['data'] as $ki => $vi) {
                                            if($vi['imgurl'] != "" && strpos($vi['imgurl'],"diypage/resource") === false){
                                                $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],1);
                                            }
                                        }
                                    }
                                }
                                if($v['id'] == 'contact'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['params']['src'] != ""  && strpos($v['params']['src'],"diypage/resource") === false){
                                        $v['params']['src'] = remote($appletid,$v['params']['src'],1);
                                    }
                                    if($v['params']['ewm'] != ""  && strpos($v['params']['ewm'],"diypage/resource") === false){
                                        $v['params']['ewm'] = remote($appletid,$v['params']['ewm'],1);
                                    }
                                }
                                if($v['id'] == 'video'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['params']['poster'] != "" && strpos($v['params']['poster'],"diypage/resource") === false){
                                        $v['params']['poster'] = remote($appletid,$v['params']['poster'],1);
                                    }
                                }
                                if($v['id'] == 'logo' || $v['id'] == 'dp'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['params']['src'] != ""  && strpos($v['params']['src'],"diypage/resource") === false){
                                        $v['params']['src'] = remote($appletid,$v['params']['src'],1);
                                    }
                                }
                                if($v['id'] == 'footmenu'){
                                    if($v['data']){
                                        foreach ($v['data'] as $ki => $vi) {
                                            if($vi['imgurl'] != "" && strpos($vi['imgurl'],"diypage/resource") === false){
                                                $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],1);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $page = $data['page'];
                    if(isset($page['url']) && $page['url'] != ""){
                        $page['url'] = remote($appletid,$page['url'],1);
                    }
                    $diyform = Db::table("ims_sudu8_page_formlist")->where("uniacid",$appletid)->field("id,formname as title")->select();
                    $data['diyform'] = $diyform;
                    $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                    $data = preg_replace("/\'/", "\'", $data);
                    $data = preg_replace('/(\\\n)/', "<br>", $data);
                    $this->assign("page",$page);
                }
            }
            //到这一块进行模板赋值
            $this->assign("data",$data);
            $this->assign("template_id",$tplid);
            $this->assign("key_id",$key_id);
            $this->assign("list",$list);
            $this->assign("setsave",$setsave);
            $this->assign("foot_is",$foot_is);
            $this->assign("temp",$temp);
            $this->assign("bg_music",$bg_music);
        }
        return view("xiaochengxu_edit");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:系统推荐模板
     **************************************
     */
    public function  system_template(Request $request){
        if($request->isPost()){
            $store_id =$this->store_ids;
            //添加系统推荐模板
            $arrs=[
                "uniacid"=>$store_id,
                "index"=>0,
                "page"=>'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:23:"后台页面名称11111";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                "items"=>'a:9:{s:14:"M1556441265605";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#eeeeee";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:2:"20";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:0:"";}s:2:"id";s:3:"ssk";}s:14:"M1556442497229";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:9:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:3:{s:14:"C1556442497229";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/0a798157280c216842778b14703d2174.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1556442497230";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/4e24ab5a4e1eaf6c8a9e2cb44925715e.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}s:14:"M1556442727577";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/130a87d7c2de0d0271bca1477b81c5e8.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1556442901109";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"30";}s:4:"data";a:4:{s:14:"C1556442901109";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/21e8d6a0a0a9b02bddfe1f8c7dd3291d.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:15:"我的分享码";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901110";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/29c68a53ed8082397dce5c06f6bbefde.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"商品分类";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901111";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/6708933e84c6252df819a7bfe46be951.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:9:"购物车";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901112";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/f2a6a4efdf216a9530e009948310ba79.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"公司介绍";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1556447643377";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:2:"10";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:2:"10";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#d9d9d9";}s:4:"data";a:1:{s:14:"C1556447643377";a:5:{s:4:"text";s:6:"商品";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:6:"更多";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";}s:14:"M1556447629116";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447629116";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447629117";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447629118";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447629119";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1556447710765";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"2";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:9:"block one";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447710765";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447710766";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447710767";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447710768";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1556447741843";a:6:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:11:"block three";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447741843";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447741844";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447741845";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447741846";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";s:5:"index";s:3:"NaN";}s:14:"M1556447763411";a:5:{s:3:"max";s:1:"5";s:4:"icon";s:23:"iconfont2 icon-fuwenben";s:6:"params";a:1:{s:7:"content";s:164:"PHAgc3R5bGU9InRleHQtYWxpZ246IGNlbnRlcjsiPuaZuuaFp+iMtuS7k+aPkOS+m+aKgOacr+aUr+aMgTwvcD48cCBzdHlsZT0idGV4dC1hbGlnbjogY2VudGVyOyI+d3d3LnpoaWh1aWNoYWNhbmcuY29tPC9wPg==";}s:5:"style";a:3:{s:10:"background";s:7:"#ffffff";s:7:"padding";s:2:"10";s:9:"margintop";s:2:"10";}s:2:"id";s:8:"richtext";}s:14:"M1556447842556";a:7:{s:4:"icon";s:21:"iconfont2 icon-caidan";s:6:"isfoot";s:1:"1";s:3:"max";s:1:"1";s:6:"params";a:8:{s:8:"navstyle";s:1:"0";s:8:"textshow";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:20:{s:11:"pagebgcolor";s:7:"#f9f9f9";s:7:"bgcolor";s:7:"#ffffff";s:9:"bgcoloron";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:11:"iconcoloron";s:7:"#f1415b";s:9:"textcolor";s:7:"#666666";s:11:"textcoloron";s:7:"#666666";s:11:"bordercolor";s:7:"#cccccc";s:13:"bordercoloron";s:7:"#ffffff";s:14:"childtextcolor";s:7:"#666666";s:12:"childbgcolor";s:7:"#f4f4f4";s:16:"childbordercolor";s:7:"#eeeeee";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:11:"paddingleft";s:1:"0";s:10:"paddingtop";s:1:"0";s:8:"iconfont";s:2:"28";s:8:"textfont";s:2:"12";s:3:"bdr";s:1:"0";s:8:"bdrcolor";s:7:"#cccccc";}s:4:"data";a:4:{s:14:"C1556447842557";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-x-shouye2";s:4:"text";s:6:"首页";}s:14:"M1556448352088";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-x-caidan5";s:4:"text";s:6:"首页";}s:14:"C1556447842558";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-2.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:11:"icon-x-gwc2";s:4:"text";s:9:"购物车";}s:14:"C1556447842560";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-4.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:13:"icon-x-geren2";s:4:"text";s:12:"联系我们";}}s:2:"id";s:8:"footmenu";}}',
                "tpl_name"=>"系统推荐"
            ];
            $diy_id=Db::table("ims_sudu8_page_diypage")->insertGetId($arrs);
            $old_pageid =DB::table("ims_sudu8_page_diypagetpl")
                ->where("uniacid",$store_id)
                ->value("pageid");
            $new_pageid =$old_pageid.",".$diy_id; //"1,2";
            $bool =Db::table("ims_sudu8_page_diypagetpl")
                ->where("uniacid",$store_id)
                ->update(["pageid"=>$new_pageid]);
            if($bool){
                return ajax_success("生成系统推荐模块成功");
            }else{
                return ajax_error("生成系统推荐模块成功");
            }
        }
    }


    /**
     * [增值服务(增值商品显示)]
     * 郭杨
     */    
    public function added_service_index(){
        return view("added_service_index");      
    }


   /**
    * [增值服务(增值商品显示)]
    * 郭杨
    */    
   public function added_service_list(Request $request){
       if($request->isPost()){
            $list = db("analyse_goods")->where("label",1)->field("id,goods_name,goods_standard,goods_selling,product_type,goods_new_money,goods_bottom_money,goods_volume,goods_show_images,goods_show_image")->select();    
            $list_one = db("analyse_goods")->where("label",1)->where("status",1)->field("id,goods_name,goods_standard,goods_selling,product_type,goods_new_money,goods_bottom_money,goods_volume,goods_show_images,goods_show_image")->select();    
            if(!empty($list)){
                foreach($list as $k => $v){
                    $list[$k]["goods_show_images"] = explode(",",$list[$k]["goods_show_images"]);
                    if($list[$k]["goods_standard"] == 1){
                        $min[$k] = db("analyse_special")->where("goods_id", $list[$k]['id'])-> min("price");
                        $line[$k] = db("analyse_special")->where("goods_id", $list[$k]['id'])-> min("line");
                        $list[$k]["goods_new_money"] = $min[$k];
                        $list[$k]["goods_bottom_money"] = $line[$k];
                    }
                }
                $goods_list["goods_list"] = $list; 
                
                if(!empty($list_one)){
                    foreach($list_one as $ke => $vl){
                        $list_one[$ke]["goods_show_images"] = explode(",",$list_one[$ke]["goods_show_images"]);
                        if($list_one[$ke]["goods_standard"] == 1){
                            $min_one[$ke] = db("analyse_special")->where("goods_id", $list_one[$ke]['id'])-> min("price");
                            $line_one[$ke] = db("analyse_special")->where("goods_id", $list_one[$ke]['id'])-> min("line");
                            $list_one[$ke]["goods_new_money"] = $min_one[$ke];
                            $list_one[$ke]["goods_bottom_money"] = $line_one[$ke];
                        }
                    }
                    $count = count($list_one);
                    if($count > 4){
                        $arandom = array_rand($list_one,4);
                        foreach($list_one as $key => $value){
                            if(in_array($key,$arandom)){
                                $arr[] = $value;
                            }
                        }
                        $goods_list["arandom"] = $arr;
                    } else {
                        $goods_list["arandom"] = $list_one;
                    }
                }
                return ajax_success('传输成功', $goods_list);
            } else {
                return ajax_error("数据为空");
            }
        }
   }



    /**
     * [增值服务(增值商品详情)]
     * 郭杨
     */    
    public function added_service_show(Request $request){
        if($request->isPost()){
            $id = $request->only("id")["id"];
            $goods = db("analyse_goods")->where("id",$id)->field("id,goods_name,goods_selling,goods_type,goods_new_money,goods_sign,goods_describe,goods_bottom_money,comment,trade,goods_standard,goods_show_images,goods_show_image,goods_text,goods_delivery,goods_franking,templet_id")->find();    
            if(!empty($goods)){
                $goods["goods_show_images"] = explode(",",$goods["goods_show_images"]);
                if($goods["goods_standard"] == 1){
                    $standard = db("analyse_special")->where("goods_id", $goods['id'])->order('price asc')-> select();
                    $min = db("analyse_special")->where("goods_id", $goods['id'])-> min("price");
                    $line = db("analyse_special")->where("goods_id", $goods['id'])-> min("line");
                    $goods["goods_new_money"] = $min;
                    $goods["goods_bottom_money"] = $line;
                    $goods["standard"] = $standard;
                    $goods["goods_repertory"] = $standard[0]["stock"];

                }
                return ajax_success('传输成功', $goods);
            } else {
                return ajax_error("数据为空");
            }
        }
        return view("added_service_show");
    }

    /**
     * [(增值商品详情再看看)]
     * 郭杨
     */    
    public function added_service_look(Request $request){
        if($request->isPost()){
            $list = db("analyse_goods")->where("label",1)->field("id,goods_name,goods_standard,goods_selling,product_type,goods_new_money,goods_bottom_money,goods_volume,goods_show_images,goods_show_image")->select();    
            if(!empty($list)){
                foreach($list as $k => $v){
                    $list[$k]["goods_show_images"] = explode(",",$list[$k]["goods_show_images"]);
                    if($list[$k]["goods_standard"] == 1){
                        $min[$k] = db("analyse_special")->where("goods_id", $list[$k]['id'])-> min("price");
                        $line[$k] = db("analyse_special")->where("goods_id", $list[$k]['id'])-> min("line");
                        $list[$k]["goods_new_money"] = $min[$k];
                        $list[$k]["goods_bottom_money"] = $line[$k];
                    }
                }        
                $count = count($list);
                if($count > 4){
                    $arandom = array_rand($list,4);
                    foreach($list as $key => $value){
                        if(in_array($key,$arandom)){
                            $arr[] = $value;
                        }
                    }
                    $goods_list = $arr;
                } else {
                    $goods_list = $list;
                }
                return ajax_success('传输成功', $goods_list);
            } else {
                return ajax_error("数据为空");
            }
        }
    }


    /**
     * [(增值商品分类搜索)]
     * 郭杨
     */    
    public function added_service_search(Request $request){
        if($request->isPost()){
            $product_type = $request->only("product_type")["product_type"];
            if(!empty($product_type)){
                $list = db("analyse_goods")
                        ->where("label",1)
                        ->where("product_type",$product_type)
                        ->field("id,goods_name,goods_standard,goods_selling,product_type,goods_new_money,goods_bottom_money,goods_volume,goods_show_images,goods_show_image")
                        ->select();    
                if(!empty($list)){
                    foreach($list as $k => $v){
                        $list[$k]["goods_show_images"] = explode(",",$list[$k]["goods_show_images"]);
                        if($list[$k]["goods_standard"] == 1){
                            $min[$k] = db("analyse_special")->where("goods_id", $list[$k]['id'])-> min("price");
                            $line[$k] = db("analyse_special")->where("goods_id", $list[$k]['id'])-> min("line");
                            $list[$k]["goods_new_money"] = $min[$k];
                            $list[$k]["goods_bottom_money"] = $line[$k];
                        }
                    }        
                    return ajax_success('传输成功', $list);
                } else {
                    return ajax_error("数据为空");
                }
            } else {
                $list = db("analyse_goods")
                ->where("label",1)
                ->field("id,goods_name,goods_standard,goods_selling,product_type,goods_new_money,goods_bottom_money,goods_volume,goods_show_images,goods_show_image")
                ->select();
                if(!empty($list)){
                    foreach($list as $k => $v){
                        $list[$k]["goods_show_images"] = explode(",",$list[$k]["goods_show_images"]);
                        if($list[$k]["goods_standard"] == 1){
                            $min[$k] = db("analyse_special")->where("goods_id", $list[$k]['id'])-> min("price");
                            $line[$k] = db("analyse_special")->where("goods_id", $list[$k]['id'])-> min("line");
                            $list[$k]["goods_new_money"] = $min[$k];
                            $list[$k]["goods_bottom_money"] = $line[$k];
                        }
                    }        
                    return ajax_success('传输成功', $list);
                } else {
                    return ajax_error("数据为空");
                }
            }
        }
    }



    /**
     * [订单套餐]
     * 郭杨
     */    
    public function order_package_index(){
        
        $order_package = db("enter_meal")->where("status",1)->field("id,name,price,favourable_price,year")->select();
        foreach($order_package as $key => $value){
            $order_package[$key]['priceList'] = db("enter_all") -> where("enter_id",$order_package[$key]['id'])->select();
        }       
        return view("order_package_index");
    }


    /**
     * [订单套餐(显示)]
     * 郭杨
     */    
    public function order_package_show(){
        $order_package = db("enter_meal")->where("status",1)->field("id,name,price,favourable_price,year")->select();
        foreach($order_package as $key => $value){
            $order_package[$key]['priceList'] = db("enter_all") -> where("enter_id",$order_package[$key]['id'])->order('year asc')->select();
        }
        if(!empty($order_package)){
            return ajax_success('传输成功',$order_package);
        } else {
            return ajax_error('传输失败,请添加套餐');
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:套餐购买下单页面
     **************************************
     * @return \think\response\View
     */
    public function order_package_buy(Request $request){
        if($request->isPost()){
            $id = $request->only(["id"])["id"];
            $data =Db::table("tb_meal_orders")
                 ->alias('a')
                 ->join('tb_enter_all b','a.enter_all_id=b.id','left')
                ->field("a.id,a.order_number,a.create_time,a.goods_name,a.goods_quantity,
                    a.amount_money,a.store_id,a.images_url,a.store_name,a.unit,a.cost,a.enter_all_id,b.year")
                ->where("a.store_id",$this->store_ids)
                ->where("a.status",-1)
                ->where("a.id",$id)
                ->select();
    
             if($data){
                if($data[0]['year'] == 0){
                    $data[0]['year'] = 10;
                    $data[0]['unit'] = "天";
                }
                foreach ($data as $k=>$v){
                    $last_money = Db::name("meal_orders")
                        ->where("store_id",$v['store_id'])
                        ->where("audit_status",1)
                        ->field("amount_money,enter_all_id")
                        ->find();
                   //判断是否相同的套餐id
                    if($last_money){
                        if($last_money["enter_all_id"]==$v["enter_all_id"]){
                            $data[$k]["last_money"] =0;
                        }else{
                            $data[$k]["last_money"] =$last_money['amount_money'];
                        }
                    }else{
                        $data[$k]["last_money"] = 0;
                    }
                }
                return ajax_success("订单信息返回成功",$data);
            }else{             
                return ajax_error("没有订单信息",0);
            }
        }
        return view("order_package_buy");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:下套餐之前需要判断的条件
     **************************************
     * @param Request $request
     */
    public function order_package_condition(Request $request){
        if($request->isPost()){
            $store_id = $this->store_ids;  //店铺id
            $enter_all_id = $request->only(['id'])['id'];//套餐id
            $years =$request->only(["year"])["year"]; //年份            
            //先判断该单是否已经存在，没有则进行添加，不能重复下单,而且不能降级(到期的要进行续费购买或者更换其他套餐)
            $isset_id = Db::name("meal_orders")
                ->where("store_id",$store_id)
                ->where("audit_status","NEQ",1)
                ->value("id");
              
            if($isset_id){
                //不能购买降级购买套餐(同事不能购买低于这个id的，所谓降级)
                $isset_ids =Db::name("meal_orders")
                    ->where("store_id",$store_id)
                    ->where("enter_all_id",">",$enter_all_id)
                    ->value("id");
                
               $isset_idData =Db::name("meal_orders")
                    ->where("store_id",$store_id)
                    ->where("enter_all_id","EQ",$enter_all_id)
                    ->where("audit_status","EQ",1)
                    ->value("id");

                if($isset_ids){
                    //这里还需要判断相同年份进来的数据
                    exit(json_encode(array("status"=>3,"info"=>"不能购买降级购买套餐","data"=>["id"=>$isset_ids])));
                }else{
                   exit(json_encode(array("status"=>2,"info"=>"您有历史订单未支付，点击确定去支付或者点击取消支付新的商品","data"=>["id"=>$isset_id])));
                }
                if($isset_idData) {
                     exit(json_encode(array("status"=>3,"info"=>"不能重复购买相同套餐","data"=>["id"=>$isset_ids])));
                }
                

              }else{
                //不能购买降级购买套餐                
                $isset_ids =Db::name("meal_orders")
                    ->where("store_id",$store_id)
                    ->where("enter_all_id",">",$enter_all_id)
                    ->where("audit_status","EQ",1)
                    ->value("id");
                if($isset_ids){
                    exit(json_encode(array("status"=>3,"info"=>"不能购买降级购买套餐","data"=>["id"=>$isset_ids])));
                }
                 $isset_idData =Db::name("meal_orders")
                    ->where("store_id",$store_id)
                    ->where("enter_all_id","EQ",$enter_all_id)
                    ->where("audit_status","EQ",1)
                    ->value("id");
               if($isset_idData){
                    exit(json_encode(array("status"=>3,"info"=>"不能重复购买相同套餐","data"=>["id"=>$isset_ids])));
                }
           
                //不能升级为年份少于之前的年份
                //这是查找id方便查找年份
               $set_id =Db::name("meal_orders")
                   ->where('store_id',$store_id)
                   ->where("audit_status","EQ",1)
                   ->value("enter_all_id");
                if($set_id){
                    $year =Db::name("enter_all")->where("id",$set_id)->value("year"); //当前套餐的年份                 
                    if($year>$years){
                        
                        exit(json_encode(array("status"=>4,"info"=>"不能升级为年份少于之前的年份","data"=>["id"=>$set_id])));
                    }else{
                        
                        exit(json_encode(array("status"=>1,"info"=>"可以升级","data"=>["id"=>$enter_all_id])));
                    }
                }else{
                  
                    exit(json_encode(array("status"=>1,"info"=>"正常购买成功","data"=>["id"=>$enter_all_id])));
                }
            }

        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:套餐购买下单操作
     **************************************
     */
    public function order_package_do_by(Request $request){
        if($request->isPost()){
            $store_id = $this->store_ids; //店铺id
            $enter_all_id =$request->only(['id'])['id'];//套餐id
            if(empty($store_id)){
                return ajax_error("请登录店铺进行购买");
            }
            $account = Db::table("tb_admin")
                ->where("store_id",$store_id)
                ->where("status",0)
                ->value("account");
            $is_business =Db::table("tb_pc_user")
                ->where("phone_number",$account)
                ->where("status",1)
                ->value("id");
            if(empty($is_business)){
                return ajax_error("请使用本店铺商家账号进行购买");
            }
            $enter_data =Db::table("tb_enter_all")
                ->where("id",$enter_all_id)
                ->find();
            $meal_name =Db::table("tb_enter_meal")
                ->where("id",$enter_data['enter_id'])
                ->value("name");
            if($enter_data['enter_id'] ==5){
                $images_url ="/static/admin/common/img/wanyong.png";
            }else if($enter_data['enter_id'] ==7){
                $images_url ="/static/admin/common/img/hangye.png";
            }else{
                $images_url ="/static/admin/common/img/jingjie.png";
            }
            $store_name =Db::table("tb_store")
                ->where("id",$store_id)
                ->value("store_name");
            //先判断这单是否需要重新申请，需要把之前未支付的删除
            Db::name("meal_orders")
                ->where("store_id",$store_id)
                ->where("pay_type",null)
                ->delete();
            $time=date("Y-m-d",time());
            $v=explode('-',$time);
            $time_second=date("H:i:s",time());
            $vs= explode(':',$time_second);
            $order_number ="TC".$v[0].$v[1].$v[2].$vs[0].$vs[1].$vs[2].$is_business; //订单编号
            $data =[
                "order_number"=>$order_number, //订单号
                "create_time"=>time(), //创建订单的时间
                "goods_name"=>$meal_name,//套餐名称
                "goods_quantity"=>1, //数量
                "unit"=>"年", //单位
                "images_url"=>$images_url,//图标
                "store_name"=>$store_name, //店铺名字
                "amount_money"=>$enter_data["favourable_cost"],//金额
                "cost" =>$enter_data["cost"],//原价
                "store_id"=>$store_id,//店铺id
                "enter_all_id"=>$enter_all_id,//套餐id
                "status"=>-1,//订单状态（-1为未付款，1为已付款）
                "is_del"=>1,//订单状态（1为正常状态，-1为被删除）
            ];
            $set_meal_id = Db::table("tb_meal_orders")->insertGetId($data);
            $bool =Db::table("tb_set_meal_order")->insert($data);
            
            if($set_meal_id >0){
                return ajax_success("下单成功",["id"=>intval($set_meal_id)]);
            }else{
                return ajax_error("下单失败，请重新下单");
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:订单套餐支付汇款
     **************************************
     */
    public function  order_package_remittance(Request $request){
        if($request->isPost()){
            $meal_order_id =$request->only(["id"])["id"]; //套餐订购的ids
            $store_id =$this->store_ids; //店铺id
            $money =$request->only(["money"])["money"]; //钱
            $remittance_name =$request->only(["remittance_name"])["remittance_name"];//汇款户名
            $remittance_account =$request->only(["remittance_account"])["remittance_account"];//汇款账号
            $pay_time =$request->only(["pay_time"])["pay_time"];//汇款时间如果大于当前时间
            if(strtotime($pay_time)>time()){
                return ajax_error("汇款时间不能大于当前时间");
            }
            $data =[
                "store_id"=>$store_id,
                "money"=>$money,
                "remittance_name"=>$remittance_name,
                "remittance_account"=>$remittance_account,
                "create_time"=>time(),
                "meal_order_id"=>$meal_order_id,
                "pay_time"=>$pay_time,
                "status"=>1,
                "pay_type"=>2,//支付类型（1扫码支付，2汇款支付，3余额支付）
                "pay_status"=>1,//到账状态（1为已到账，-1未到账，2待审核）
                "audit_status"=>0,//订单审核状态（1审核通过，-1审核不通过,0待审核）
            ];
            $bool =Db::name("meal_pay_form")->insertGetId($data);
            if($bool){
                //对订单表进行审核操作
                return ajax_success("已提交，请等待审核");
            }else{
                return ajax_error("失败，请重新提交");
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:套餐订购微信二维码扫码支付
     **************************************
     * @param Request $request
     */
    public function  order_code_pay(Request $request){
        if($request->isPost()){
            $money =$request->only(["money"])["money"];//支付钱数
            $order_number =$request->only(["order_number"])["order_number"];//订单编号
            $goods_name =$request->only(["goods_name"])["goods_name"];//商品名称
            header("Content-type: text/html; charset=utf-8");
            ini_set('date.timezone', 'Asia/Shanghai');
            include('../extend/WxpayAll/lib/WxPay.Api.php');
            include('../extend/WxpayAll/example/WxPay.NativePay.php');
            include('../extend/WxpayAll/example/log.php');
            $notify = new \NativePay();
            $input = new \WxPayUnifiedOrder();//统一下单
            $paymoney = $money; //支付金额
            $out_trade_no = $order_number; //商户订单号
            $goods_name = $goods_name.'套餐'; //商品名称
            $goods_id =123456789; //商品Id
            $input->SetBody($goods_name);//设置商品或支付单简要描述
            $input->SetAttach($goods_name);//设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
            $input->SetOut_trade_no($out_trade_no);//设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
            $input->SetTotal_fee($paymoney * 100);//金额乘以100
            $input->SetTime_start(date("YmdHis")); //设置订单生成时间,格式为yyyyMMddHHmmss
            $input->SetTime_expire(date("YmdHis", time() + 600)); //设置订单失效时间
            $input->SetGoods_tag("test"); //设置商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
            $input->SetNotify_url(config("domain.url")."/set_meal_notify"); //回调地址
            $input->SetTrade_type("NATIVE"); //交易类型(扫码)
            $input->SetProduct_id($goods_id);//设置trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
            $result = $notify->GetPayUrl($input);
            $url2 = $result["code_url"];
            $bool = Db::name("set_meal_order")->where("order_number",'EQ',$order_number)->update(["pay_money"=>$money]);
            $boole = Db::name("meal_orders")->where("order_number",'EQ',$order_number)->update(["pay_money"=>$money]);
            if($url2){
                return ajax_success("微信二维码返回成功",["url"=>"/qrcode?url2=".$url2]);
            }else{
                return ajax_error("二维码生成失败");
            }

        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:套餐订购支付宝二维码支付
     **************************************
     * @param Request $request
     */
    public function  order_code_alipay(Request $request){
        if($request->isPost()){
            //支付宝二维码
            $money =$request->only(["money"])["money"];//支付钱数
            $order_number =$request->only(["order_number"])["order_number"];//订单编号
            $goods_name =$request->only(["goods_name"])["goods_name"];//商品名称
            header("Content-type:text/html;charset=utf-8");
            include EXTEND_PATH . "/lib/payment/alipay/alipay.class.php";
            $obj_alipay = new \alipay();
            $arr_data = array(
                "return_url" => trim(config("domain.url")."admin"),
                "notify_url" => trim(config("domain.url")."/set_meal_notify_alipay.html"),
                "service" => "create_direct_pay_by_user", //服务参数，这个是用来区别这个接口是用的什么接口，所以绝对不能修改
                "payment_type" => 1, //支付类型，没什么可说的直接写成1，无需改动。
                "seller_email" => '717797081@qq.com', //卖家
                "out_trade_no" => $order_number, //订单编号
                "subject" => $goods_name, //商品订单的名称
                "total_fee" => number_format($money, 2, '.', ''),
            );
            $bool = Db::name("set_meal_order")->where("order_number",'EQ',$order_number)->update(["pay_money"=>$money]);
            $boole = Db::name("meal_orders")->where("order_number",'EQ',$order_number)->update(["pay_money"=>$money]);
            $str_pay_html = $obj_alipay->make_form($arr_data, true);
            if($str_pay_html){
                return ajax_success("二维码成功",["url"=>$str_pay_html]);
            }else{
                return ajax_error("生成二维码失败");
            }
        }
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:轮询操作（判断该订单是否支付）
     **************************************
     * @param Request $request
     */
    public function  check_code_apy(Request $request){
        $order_number = $request->only(["order_number"])["order_number"];
        $result =Db::name("meal_orders")
            ->where("order_number",$order_number)
            ->where("status",1)
            ->find();
        if($result){
            return ajax_success("付款成功");
        }else{
            return ajax_error("未付款成功");
        }
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:订单套餐余额支付
     **************************************
     */
    public function  order_package_balance(Request $request){
        if($request->isPost()){
            $password =$request->only(["password"])["password"];
            $meal_order_id =$request->only(["id"])["id"];//订单的id
            $store_pass =Db::name("store")
                ->where("id",$this->store_ids)
                ->field("store_pay_pass,store_wallet")
                ->find();
            if(empty( $store_pass['store_pay_pass'])){
                exit(json_encode(array("status" => 2, "info" => "没有设置支付密码，请前往设置")));
            }
            if(md5($password) !==$store_pass["store_pay_pass"]){
                exit(json_encode(array("status" => 3, "info" => "支付密码错误")));
            }
            
            $order_data = Db::name("meal_orders")
                ->where("id",$meal_order_id)
                ->find();
            $year = Db::name("enter_all")->where("id",$order_data['enter_all_id'])->value("year");
            if($store_pass["store_wallet"]<$order_data['amount_money']){
                exit(json_encode(array("status" => 3, "info" => "账号余额不足")));
            }

            //先判断是第一次购买还是套餐升级  
            $is_set_order = Db::name("set_meal_order")
            ->where("store_id",$this->store_ids)
            ->where("audit_status",1)
            ->find();    
            if($is_set_order){
                    //这是套餐升级的情况
                    $data["pay_time"] = time();//支付时间
                    $data["pay_type"] =3; //支付类型（1扫码支付,2汇款支付，3余额支付）
                    $data["pay_status"] = 1;//到账状态（1为已到账，-1未到账，2待审核）
                    $data['goods_name'] = $order_data['goods_name']; //升级套餐名
                    $data["start_time"] = time();  //开始时间
                    $data['goods_quantity'] = $order_data['goods_quantity']; //数量
                    $data['enter_all_id'] = $order_data['enter_all_id']; //套餐id
                    if($year > 0){
                        $data["end_time"] = strtotime("+$year  year");//结束时间
                    } else {
                        $data["end_time"] = strtotime("+10  day");//结束时间

                    }
                    $data["explains"] ="账户余额支付";//审核说明
                    $data["status"] =1; //订单状态（-1为未付款，1已付款)
                    $data["apply"] = 1; //订单状态（-1为未付款，1已付款)
                    $data["audit_status"] =1; //订单审核状态（1审核通过，-1审核不通过,0待审核)
                    $res = Db::name("set_meal_order")
                        ->where("order_number",$is_set_order["order_number"])
                        ->update($data);

                    $rest = Db::name("meal_orders")
                    ->where("order_number",$order_data["order_number"])
                    ->update($data);
                    $delete_new_order = Db::name('set_meal_order')->where('order_number',$order_data["order_number"])->delete();
                    
                    if($res){                           
                      //审核通过则对店铺进行开放，修改店铺的权限（普通访客）为商家店铺
                        if($order_data['enter_all_id'] <= 6){
                            $role_id = 13;
                        }
                        if(  ($order_data['enter_all_id'] > 6) && ($order_data['enter_all_id'] <= 17)){
                            $role_id = 14;
                        }
                        if( $order_data['enter_all_id'] > 17){
                            $role_id = 15;
                        }
                        Db::table("tb_admin")
                            ->where("store_id",$order_data["store_id"])
                            ->where("is_own",1)
                            ->update(["role_id"=>$role_id]);
                //进行账号余额减然后插入消费表中
                $new_wallet = Db::name("store")
                ->where("id",$this->store_ids)
                ->setDec("store_wallet",$order_data['amount_money']);
                exit(json_encode(array("status" => 1, "info" => "支付成功")));
            }else{
                exit(json_encode(array("status" => 3, "info" => "支付失败")));
            }
        }else{
        //这是新加入套餐的情况
        $data["pay_time"] = time();//支付时间
        $data["pay_type"] = 3;//支付类型（1扫码支付，2汇款支付，3余额支付）
        $data["pay_status"] = 1;//到账状态（1为已到账，-1未到账，2待审核）
        $data["start_time"] = time();//开始时间
        $data["apply"] = 1;
        if($year > 0){
            $data["end_time"] = strtotime("+$year  year");//结束时间
        } else {
            $data["end_time"] = strtotime("+10  day");//结束时间

        }
        $data["explains"] ="余额支付直接通过";//审核说明
        $data["status"] =1; //订单状态（-1为未付款，1已付款）
        $data["audit_status"] =1; //订单审核状态（1审核通过，-1审核不通过,0待审核）
        $result =Db::name("set_meal_order")
            ->where("order_number",$order_data['order_number'])
            ->update($data);

        $resultet = Db::name("meal_orders")
        ->where("order_number",$order_data['order_number'])
        ->update($data);
        
        if($result){
            //审核通过则对店铺进行开放，修改店铺的权限（普通访客）为商家店铺
            if($order_data['enter_all_id'] <= 6){
                $role_id = 13;
            }
            if(  ($order_data['enter_all_id'] > 6) && ($order_data['enter_all_id'] <= 17)){
                $role_id = 14;
            }
            if( $order_data['enter_all_id'] > 17){
                $role_id = 15;
            }
            Db::table("tb_admin")
                ->where("store_id",$order_data["store_id"])
                ->where("is_own",1)
                ->update(["role_id"=>$role_id]);

            //审核通过的时候先判断是否有小程序模板，没有的话则进行添加，有的话则不需要
            $is_set = Db::table("ims_sudu8_page_diypageset")
                ->where("store_id",$order_data["store_id"])
                ->find();

                
               if(!$is_set){
                $is_uniacid =Db::table("ims_sudu8_page_base")
                    ->where("uniacid",$order_data["store_id"])
                    ->find();
                if(!$is_uniacid){
                    $insert_data =[
                        "uniacid"=>$order_data["store_id"],
                        "index_style"=>"header",
                        "copyimg"=>"",
                        "base_color_t"=>"",
                        "tabnum_new"=>5,
                        "homepage"=>2,
                    ];
                    Db::table("ims_sudu8_page_base")->insert($insert_data);
                }
                $array = [
                    "go_home"=>1,
                    "uniacid"=>$order_data["store_id"],
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
                    "store_id"=>$order_data["store_id"],
                ];
                Db::table("ims_sudu8_page_diypageset")->insert($array);
                //添加首页
                $arr=[
                    "uniacid"=>$order_data["store_id"],
                    "index"=>1,
                    "page"=>'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:23:"后台页面名称11111";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                    "items"=>"",
                    "tpl_name"=>"首页"
                ];
                $diy_id[0] = Db::table("ims_sudu8_page_diypage")->insertGetId($arr);
                //添加系统推荐模板
                $arrs=[
                    "uniacid"=>$order_data["store_id"],
                    "index"=>0,
                    "page"=>'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:23:"后台页面名称11111";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                    "items"=>'a:9:{s:14:"M1556441265605";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#eeeeee";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:2:"20";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:0:"";}s:2:"id";s:3:"ssk";}s:14:"M1556442497229";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:9:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:3:{s:14:"C1556442497229";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/0a798157280c216842778b14703d2174.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1556442497230";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/4e24ab5a4e1eaf6c8a9e2cb44925715e.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}s:14:"M1556442727577";a:4:{s:6:"imgurl";s:55:"/upimages/20190428/130a87d7c2de0d0271bca1477b81c5e8.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1556442901109";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"30";}s:4:"data";a:4:{s:14:"C1556442901109";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/21e8d6a0a0a9b02bddfe1f8c7dd3291d.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:15:"我的分享码";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901110";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/29c68a53ed8082397dce5c06f6bbefde.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"商品分类";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901111";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/6708933e84c6252df819a7bfe46be951.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:9:"购物车";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1556442901112";a:5:{s:6:"imgurl";s:55:"/upimages/20190428/f2a6a4efdf216a9530e009948310ba79.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"公司介绍";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1556447643377";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:2:"10";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:2:"10";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#d9d9d9";}s:4:"data";a:1:{s:14:"C1556447643377";a:5:{s:4:"text";s:6:"商品";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:6:"更多";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";}s:14:"M1556447629116";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447629116";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447629117";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447629118";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447629119";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1556447710765";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"2";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:9:"block one";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447710765";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447710766";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447710767";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447710768";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1556447741843";a:6:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:11:"block three";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556447741843";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556447741844";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447741845";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556447741846";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";s:5:"index";s:3:"NaN";}s:14:"M1556447763411";a:5:{s:3:"max";s:1:"5";s:4:"icon";s:23:"iconfont2 icon-fuwenben";s:6:"params";a:1:{s:7:"content";s:164:"PHAgc3R5bGU9InRleHQtYWxpZ246IGNlbnRlcjsiPuaZuuaFp+iMtuS7k+aPkOS+m+aKgOacr+aUr+aMgTwvcD48cCBzdHlsZT0idGV4dC1hbGlnbjogY2VudGVyOyI+d3d3LnpoaWh1aWNoYWNhbmcuY29tPC9wPg==";}s:5:"style";a:3:{s:10:"background";s:7:"#ffffff";s:7:"padding";s:2:"10";s:9:"margintop";s:2:"10";}s:2:"id";s:8:"richtext";}s:14:"M1556447842556";a:7:{s:4:"icon";s:21:"iconfont2 icon-caidan";s:6:"isfoot";s:1:"1";s:3:"max";s:1:"1";s:6:"params";a:8:{s:8:"navstyle";s:1:"0";s:8:"textshow";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:20:{s:11:"pagebgcolor";s:7:"#f9f9f9";s:7:"bgcolor";s:7:"#ffffff";s:9:"bgcoloron";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:11:"iconcoloron";s:7:"#f1415b";s:9:"textcolor";s:7:"#666666";s:11:"textcoloron";s:7:"#666666";s:11:"bordercolor";s:7:"#cccccc";s:13:"bordercoloron";s:7:"#ffffff";s:14:"childtextcolor";s:7:"#666666";s:12:"childbgcolor";s:7:"#f4f4f4";s:16:"childbordercolor";s:7:"#eeeeee";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:11:"paddingleft";s:1:"0";s:10:"paddingtop";s:1:"0";s:8:"iconfont";s:2:"28";s:8:"textfont";s:2:"12";s:3:"bdr";s:1:"0";s:8:"bdrcolor";s:7:"#cccccc";}s:4:"data";a:4:{s:14:"C1556447842557";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-x-shouye2";s:4:"text";s:6:"首页";}s:14:"M1556448352088";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-x-caidan5";s:4:"text";s:6:"首页";}s:14:"C1556447842558";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-2.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:11:"icon-x-gwc2";s:4:"text";s:9:"购物车";}s:14:"C1556447842560";a:4:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-4.png";s:7:"linkurl";s:0:"";s:9:"iconclass";s:13:"icon-x-geren2";s:4:"text";s:12:"联系我们";}}s:2:"id";s:8:"footmenu";}}',
                    "tpl_name"=>"系统推荐"
                ];
                $diy_id[1]=Db::table("ims_sudu8_page_diypage")->insertGetId($arrs);
                $new_array =[
                    "uniacid"=>$order_data["store_id"],
                    "pageid"=>implode(',',$diy_id),
                    "template_name"=>"综合商城模板",
                    "thumb"=>"/diypage/template_img/template_shop/cover.png",
                    "create_time"=>time(),
                    "status"=>1,
                    "store_id"=>$order_data["store_id"]
                ];
                $bool = Db::table("ims_sudu8_page_diypagetpl")->insertGetId($new_array);
             }
                //进行账号余额减然后插入消费表中
                $new_wallet = Db::name("store")
                ->where("id",$this->store_ids)
                ->setDec("store_wallet",$order_data['amount_money']);
                exit(json_encode(array("status" => 1, "info" => "支付成功")));
            }else{
                exit(json_encode(array("status" => 3, "info" => "支付失败")));
            }
        }
    }
}


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:套餐订单删除
     **************************************
     */
    public function order_package_del(Request $request){
        if($request->isPost()){
            $id =$request->only('order_number')['order_number'];
            if($id){
                $bool =Db::name('set_meal_order')
                    ->where("order_number",":order_number")
                    ->bind(["order_number"=>[$id,\PDO::PARAM_INT]])
                    ->delete();
                    $boole =Db::name('meal_orders')
                    ->where("order_number",":order_number")
                    ->bind(["order_number"=>[$id,\PDO::PARAM_INT]])
                    ->delete();
                if($bool || $boole){
                    return ajax_success('删除成功');
                }else{
                    return ajax_error('删除失败');
                }
            }else{
                return ajax_error('这条信息不正确');
            }
        }
    }

    
    /**
     * [套餐订购支付]
     * 郭杨
     */    
    public function order_package_purchase(){
        return view("order_package_purchase");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:资金管理资金明细
     **************************************
     */
    public function capital_management(){
        $store_wallet =$this->store_wallet($this->store_ids);
        return view("capital_management",["store_wallet"=>$store_wallet]);

    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:资金详情
     **************************************
     * @return \think\response\View
     */
    public function capital_management_details(){
        return view("capital_management_details");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:线下充值记录
     **************************************
     */
    public function unline_recharge_record(){
        $store_wallet =$this->store_wallet($this->store_ids);
        return view("unline_recharge_record",["store_wallet"=>$store_wallet]);
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:提现记录
     **************************************
     */
    public function unline_withdrawal_record(){
        $store_wallet = $this->store_wallet($this->store_ids);
        return view("unline_withdrawal_record",["store_wallet"=>$store_wallet]);
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:代理邀请
     **************************************
     */
    public function agency_invitation(){
        $store_wallet =$this->store_wallet($this->store_ids);
        return view("agency_invitation",["store_wallet"=>$store_wallet]);
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:立即分销邀请
     **************************************
     */
    public function now_agency_invitation(){
        return view("now_agency_invitation");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:钱包
     **************************************
     * @param $store_id
     * @return mixed
     */
    private  function store_wallet($store_id){
        $store_wallet = Db::name("store")->where("id",$store_id)->value("store_wallet");
        return $store_wallet;
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:安全设置
     **************************************
     */
    public function security_setting(){
        $store_wallet =$this->store_wallet($this->store_ids);
        return view("security_setting",["store_wallet"=>$store_wallet]);
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:套餐订单
     **************************************
     */
    public function store_set_meal_order(){
        $store_id =$this->store_ids; //店铺id
        if(!$store_id){
            $this->error("只给商家进行查看");
        }
        //检测店铺是否删除
            $data =Db::table('tb_meal_orders')
                ->field("tb_meal_orders.*,tb_store.phone_number,tb_store.contact_name,tb_store.is_business")
                ->join("tb_store","tb_meal_orders.store_id=tb_store.id",'left')
                ->where("is_del",1)
                ->where("store_id",$store_id)
                ->order("tb_meal_orders.create_time","desc")
                ->paginate(20 ,false, [
                    'query' => request()->param(),
                ]);
        return view("store_set_meal_order",["data"=>$data]);
    }


    /**
     **************GY*******************
     * @param Request $request
     * Notes:后台店铺申请开发票
     **************************************
     */
    public function store_write_receipt(Request $request){
        if($request -> isPost()){
            $store_id = $this->store_ids; //店铺id
            $id = $request->only(['id'])['id']; //套餐id
            $receipt = Db::name("store_receipt")
                ->where("store_id","EQ",$store_id)
                ->where("meal_order_id","EQ",$id)
                ->find();
            $location = db("pc_store_address")->where("store_id",'EQ',$store_id)->where("default","EQ",1)->find();
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
     * Notes:后台店铺立即开发票
     **************************************
     */
    public function store_receipt_now(Request $request){
        if($request -> isPost()){
            $store_id = $this->store_ids; //店铺id
            $id = $request->only(['id'])['id'];
            $order_number = Db::name("meal_orders")->where('id','EQ',$id)->value('order_number');
            $data = $request->param();
            $data['store_id'] = $store_id;
            $data['apply'] = 2;
            $data['meal_order_id'] = $id;
            if(empty($data['location'])){
                return ajax_error("请添加默认收获地址");
            }
            if(empty($data['title']) || empty($data['company_number'])){
                exit(json_encode(array("status" => -1, "info" => "参数错误")));
            }
            
            $receipt = Db::name("store_receipt")
                ->where("store_id","EQ",$store_id)
                ->where("meal_order_id","EQ",$id)
                ->find();
            if(!empty($receipt)){
                return ajax_error("您已经开具过发票");
            } else {
                unset($data['id']);
                $receipt_id = Db::name("store_receipt")->insert($data);
                if($receipt_id){
                    $bool = Db::name("meal_orders")->where("id",'EQ',$id)->update(["apply"=>2]);
                    return ajax_success("开票成功");
                      
                } else {
                    return ajax_error("开具发票失败");
                }
                
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:增值订单
     **************************************
     */
    public function store_order(){
        return view("store_order");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后维权
     **************************************
     * @return \think\response\View
     */
    public function  store_order_after(){
        return view("store_order_after");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后维权申请中
     **************************************
     * @return \think\response\View
     */
    public function  store_order_after_ing(){
        return view("store_order_after_ing");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后维权已拒绝
     **************************************
     * @return \think\response\View
     */
    public function  store_order_after_refuse(){
        return view("store_order_after_refuse");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后维权处理中
     **************************************
     * @return \think\response\View
     */
    public function  store_order_after_handle(){
        return view("store_order_after_handle");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后维权已关闭
     **************************************
     * @return \think\response\View
     */
    public function  store_order_after_close(){
        return view("store_order_after_close");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后维权完成换货
     **************************************
     * @return \think\response\View
     */
    public function  store_order_after_replace(){
        return view("store_order_after_replace");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后维权完成退款
     **************************************
     * @return \think\response\View
     */
    public function  store_order_after_complete(){
        return view("store_order_after_complete");
    }





    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后维权详情页面
     **************************************
     */
    public function  store_order_after_edit(){
        return view("store_order_after_edit");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我要支付
     **************************************
     */
    public function  go_to_pay(){
        return view("go_to_pay");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:售后维权
     **************************************
     * @return mixed
     */
    public function  store_after_sale(){
        return view('store_after_sale');
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:追加评论
     **************************************
     */
    public function additional_comments(){
        return view("additional_comments");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我要评论
     **************************************
     * @return \think\response\View
     */
    public function additional_comments_add(){
        return view("additional_comments_add");
    }



 }