<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/18 0018
 * Time: 14:04
 * 账单
 */

namespace  app\index\controller;


use think\Controller;
use think\Request;
use think\Db;
use app\admin\model\Goods;
use\app\admin\model\MemberGrade;
use app\admin\model\Order as GoodsOrder;
use app\common\model\dealer\Order as OrderModel;
use app\common\model\dealer\Setting;
use app\city\model\User;
use app\city\controller\Picture;
use app\admin\model\Store;
use app\city\model\CityDetail;

class Bill extends Controller{


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的消费
     **************************************
     * @return \think\response\View
     */
    public function ceshi12(Request $request){
        if($request->isPost()){

            $array_city = [
            ['name'=>'成都市','rank_status'=>3],
            ['name'=>'杭州市'],
            ['name'=>'武汉市'],
            ['name'=>'重庆市'],
            ['name'=>'南京市'],
            ['name'=>'天津市'],
            ['name'=>'苏州市'],
            ['name'=>'西安市'],
            ['name'=>'长沙市'],
            ['name'=>'沈阳市'],
            ['name'=>'青岛市'],
            ['name'=>'郑州市'],
            ['name'=>'大连市'],
            ['name'=>'东莞市'],
            ['name'=>'宁波市'],
            ['name'=>'厦门市'],
            ['name'=>'福州市'],
            ['name'=>'无锡市'],
            ['name'=>'合肥市'],
            ['name'=>'昆明市'],
            ['name'=>'哈尔滨市'],
            ['name'=>'济南市'],
            ['name'=>'佛山市'],
            ['name'=>'长春市'],
            ['name'=>'温州市'],
            ['name'=>'石家庄市'],
            ['name'=>'南宁市'],
            ['name'=>'常州市'],
            ['name'=>'泉州市'],
            ['name'=>'南昌市'],
            ['name'=>'贵阳市'],
            ['name'=>'太原市'],
            ['name'=>'烟台市'],
            ['name'=>'嘉兴市'],
            ['name'=>'南通市'],
            ['name'=>'金华市'],
            ['name'=>'珠海市'],
            ['name'=>'惠州市'],
            ['name'=>'徐州市'],
            ['name'=>'海口市'],
            ['name'=>'乌鲁木齐市'],
            ['name'=>'绍兴市'],
            ['name'=>'中山市'],
            ['name'=>'台州市'],
            ['name'=>'兰州市']];
            //生成分销代理订单
            // $one = new CityDetail;
            // $bool = $one->city_store_update('云南省',31);
            // halt($bool);
  
            // $order_number='TC2019060616044231';
            // $enter_all_data = Db::name("set_meal_order")
            //         ->where("order_number",$order_number)
            //         ->find();
            
            // $store_data_rest = Db::name('store')->where('id',$enter_all_data['store_id'])->find();
            // // halt($store_data_rest);
            // CityDetail::store_order_commission($enter_all_data,$store_data_rest);
            // halt(222);
            //     $rest = db('store')->field('address_data,id')->select();
            //     // halt($rest);
            //     $city = "北京市";
                
            //     foreach($rest as $key =>  $value){
            //         if(in_array($city,explode(",",$value["address_data"]))){
            //             $one[$key]['id'] = $value['id'];
            //             $one[$key]['city_user_id'] = 1;
            //             // $one = new Store;
            //             // $reste[] = $one->where('id', $rest[$key]["id"])->saveAll(['city_user_id'=>1]); 
            //     }
            // }
            //  $onee = new Store;
            //  $reste = $onee->saveAll($one); 
            //     halt($one);
            //     foreach($one as $k => $l){
            //         unset($l['address_data']);
            //         $one[$k]['ll'] = 1;

            //     }

            //     $rest->cheshi2();
            // return  jsonError("失败",array(),ERROR_100);
            }
        }
    

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的消费
     **************************************
     * @return \think\response\View
     */
    public function consume_index(Request $request){
        if($request->isPost()){
            $user_id =$request->only(["member_id"])["member_id"];//用户id
            $now_time_one =date("Y");
            $condition = " `operation_time` like '%{$now_time_one}%' ";
            $data = Db::name("wallet")
                ->where("user_id",$user_id)
                ->where($condition)
                ->order("operation_time","desc")
                ->select();
            if(!empty($data)){
                return ajax_success("消费细节返回成功",$data);
            }else{
                return ajax_error("暂无消费记录",["status"=>0]);
            }
        }
        return view("my_consume");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:我的消费搜索
     **************************************
     */
    public function consume_search(Request $request){
        if($request->isPost()){
            $user_id =$request->only(["member_id"])["member_id"];//用户id
            $title =$request->only(["title"])["title"];//搜索关键词
            $now_time_one =date("Y");
            $condition = " `operation_time` like '%{$now_time_one}%' ";
            $conditions = " `title` like '%{$title}%' ";
            $data = Db::name("wallet")
                ->where("user_id",$user_id)
                ->where($condition)
                ->where($conditions)
                ->order("operation_time","desc")
                ->select();
            if(!empty($data)){
                return ajax_success("消费细节返回成功",$data);
            }else{
                return ajax_error("暂无消费记录",["status"=>0]);
            }
        }
        return view("my_consume");
    }
}