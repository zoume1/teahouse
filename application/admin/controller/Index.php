<?php
/**
 * Created by PhpStorm.
 * User: CHEN
 * Date: 2018/7/10
 * Time: 18:20
 */
namespace app\admin\controller;

use think\Controller;
use think\Config;
use think\captcha\Captcha;
use think\Request;
use think\Session;
use \traits\controller\Jump;
use think\Db;
class Index extends Controller
{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:后台首页
     **************************************
     * @param Request $request
     * @return \think\response\View
     */
    public function index()
    {
        $menu_list = Config::get("menu_list");
        $user_info = Session::get("user_info");
        $account = $user_info[0]["account"];
        $role_id = $user_info[0]["role_id"];
        $store_id = Session::get("store_id");
        $store_data = Db::name("store")->where('id','EQ',$store_id)->find();
        $store_logo = isset($store_data['store_logo'])?$store_data['store_logo']:null;
        $store_name = isset($store_data['store_name'])?$store_data['store_name']:null;
        if($store_id){
            $phone_id =Db::table("applet")
                ->where("store_id",$store_id)
                ->value("id");
        }else{
            $store_id=0;
            $phone_id =0;
        }
        
        return view("index", ["menu_list" => $menu_list,"account"=>$account,"phone_id"=>$phone_id,"store_name"=>$store_name,"store_logo"=>$store_logo,"store_id"=>$store_id,"role_id"=>$role_id]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:通过菜单栏id获取二级下面的三级权限内的信息
     **************************************
     */
    public function get_id_return_info(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(['id'])['id'];//当前id
            //查找当前账号权限
            if (!empty($id)) {
                $arr = request()->routeInfo();
                if (!preg_match("/admin\/Login/", $arr["route"])) {
                    $data = Session::get("user_id");
                    if (empty($data)) {
                        $this->error("请登录", url("/admin/index", "", false));
                        exit();
                    }
                    $user_info = Session::get("user_info");
                    $menu_list = db("menu")->where("pid",$id)->where('status', '<>', 0)->order("sort_number asc")->select();
                    $role = db("role")->where("id", $user_info[0]['role_id'])->field("menu_role_id")->select();
                    $role = explode(",", $role[0]["menu_role_id"]);
                    //在控制台获取当前的url地址
                    $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['REQUEST_URI'];
                    $explode = explode("/", $url);

                    if (count($explode) > 3) {
                        $url = "/" . $explode[1] . "/" . $explode[2];
                    }
                    $if_url = 0;
                    if ($user_info[0]['id'] != 2) {
                        foreach ($menu_list as $key => $values) {
                            if (!in_array($values['id'], $role)) {
                                unset($menu_list[$key]);
                            } else {
                                if ($values['url'] == $url) {
                                    $if_url = 1;
                                }
                            }
                        }
                    }
//                  $menu_list = _tree_hTree(_tree_sort($menu_list, "sort_number"));
                    if(!empty($menu_list)){
                        foreach ($menu_list as $val_data){
                            $menu_lists[] =$val_data;
                        }
                    }
                    return ajax_success("成功获取", $menu_lists);
                } else {
                    return ajax_error("没有获取到id");
                }

            }
        }

    }


    /**
     **************郭杨*******************
     * @param Request $request
     * Notes:进入店铺后台显示但前使用版本及日期
     **************************************
     */
    public function shop_store_date(Request $request)
    {
        if ($request->isPost()) {
            $store_id = Session::get('store_id');
            $store_data = db("set_meal_order")
                        ->where("store_id",$store_id)
                        ->where('pay_status',1)
                        ->field('start_time,goods_name,end_time')
                        ->find();
            
            if(empty($store_data)){
                return ajax_error("店铺版本不存在");
            } else {
                $time = time();
                if($time < $store_data["end_time"]){
                    $data_number = round(($store_data["end_time"]-$time)/86400);
                } else {
                    $data_number = 0;
                }
                if($data_number <= 10){
                    $store_information = [
                        'data_number'=>$data_number,
                        'goods_name'=> $store_data['goods_name']
                    ];
                    return ajax_success("获取成功",$store_information);
                } else {
                    exit(json_encode(array("status"=>2,"info"=>"该店铺未到结算显示时间")));
                }           
            }
        }
    }
    /**
     * lilu
     * 后台店铺获取消息提醒
     * store_id
     */
    public function get_info_store(){
        //获取参数 store_id
        $input=input();
        //获取普通订单待发货
        $where['status']=array('between',array(2,3));
        $where['store_id']=$input['store_id'];
        $p_order_number_dai=db('order')->where($where)->count();
        //获取众筹订单待发货
        $where2['status']=array('between',array(2,3));
        $where2['store_id']=$input['store_id'];
        $z_order_number_dai=db('crowd_order')->where($where2)->count();
        //获取积分订单待发货
        // $j_order_number_dai=db('crowd_order')->where(['store_id'=>$input['store_id'],'status'=>3])->count();
        //售后
        $s_order_number_dai=db('after_sale')->where(['store_id'=>$input['store_id'],'status'=>1])->count();
         $data['0']['number']=$p_order_number_dai;
         $data['1']['number']=$z_order_number_dai;
         $data['2']['number']=$s_order_number_dai;
         $data['0']['type']=0;
         $data['1']['type']=1;
         $data['2']['type']=2;
         $data['0']['id']=188;
         $data['1']['id']=234;
         $data['2']['id']=130;
         if($data){
             return ajax_success('获取成功',$data);
        }else{
             return ajax_error('获取失败');
         }

    }
    /**
     * lilu
     * 总控获取消息提醒---增值订单
     * store_id 
     */
    public function get_info_zong(){
        //增值订单        adder_order
        $where['status']=array('between',array(2,3));
        $z_order_number=db('adder_order')->where($where)->count();
        $data[0]['number']=$z_order_number;
        $data[0]['type']='0';
        if($data){
            return ajax_success('获取成功',$data);
        }else{
            return ajax_error('获取失败');
        }

    }
    /**
     * lilu
     * 判断订单待发货，发起售后（提示音）
     */
    public function informationhint(){
        //获取店铺消息
        $store_id =Session::get('store_id');
        if(empty($store_id)){
            //总控---增值订单
            $where['status']=array('between',array(2,3));
            $z_order_number=db('adder_order')->where($where)->count();
            if(empty(Session::get('zongk'))){
                Session::set('zongk',$z_order_number);
                $pp=0;
            }else{
                if($z_order_number > Session::get('zongk')){
                    Session::set('zongk',$z_order_number);
                    $pp=1;
                }else{
                    $pp=0;
                }
            }
            //总控---售后订单（先不做）


        }else{
            //判断session是否存在（待发货）
            $where['status']=array('between',array(2,3));
            $where['store_id']=$store_id;
            $number=db('order')->where($where)->count();
            if(empty(Session::get('daifu'.$store_id))){
                 Session::set('daifu'.$store_id,$number);
                 $pp=0;
            }else{
                if($number > Session::get('daifu'.$store_id)){
                    Session::set('daifu'.$store_id,$number);
                    $pp=1;
                    return ajax_success('获取成功1',$pp);
                    die;
                }else{
                    $pp=0;
                }
            }
            //售后申请
            //判断session是否存在（待发货）
            $where['status']=1;
            $where['store_id']=$store_id;
            $number2=db('after_sale')->where($where)->count();
            if(empty(Session::get('shouhou'.$store_id))){
                 Session::set('shouhou'.$store_id,$number2);
                 $pp2=0;
            }else{
                if($number2 > Session::get('shouhou'.$store_id)){
                    Session::set('shouhou'.$store_id,$number2);
                    $pp2=1;
                    return ajax_success('获取成功2',$pp2);
                    die;
                }else{
                    $pp2=0;
                }
            }
        }
        return ajax_success('获取失败3',0);

    }
    
}