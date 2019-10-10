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
use app\city\model\CityRank;

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
                ['name'=>'克拉玛依市'],
                ['name'=>'长治市'],
                ['name'=>'永州市'],
                ['name'=>'绥化市'],
                ['name'=>'巴音郭楞蒙古自治州'],
                ['name'=>'拉萨市'],
                ['name'=>'云浮市'],
                ['name'=>'益阳市'],
                ['name'=>'百色市'],
                ['name'=>'资阳市'],
                ['name'=>'荆门市'],
                ['name'=>'松原市'],
                ['name'=>'凉山彝族自治州'],
                ['name'=>'达州市'],
                ['name'=>'伊犁哈萨克自治州'],
                ['name'=>'广安市'],
                ['name'=>'自贡市'],
                ['name'=>'汉中市'],
                ['name'=>'朝阳市'],
                ['name'=>'漯河市'],
                ['name'=>'钦州市'],
                ['name'=>'贵港市'],
                ['name'=>'安顺市'],
                ['name'=>'鄂州市'],
                ['name'=>'广元市'],
                ['name'=>'河池市'],
                ['name'=>'鹰潭市'],
                ['name'=>'乌兰察布市'],
                ['name'=>'铜陵市'],
                ['name'=>'昌吉回族自治州'],
                ['name'=>'衡水市'],
                ['name'=>'黔西南布依族苗族自治州'],
                ['name'=>'濮阳市'],
                ['name'=>'锡林郭勒盟'],
                ['name'=>'巴彦淖尔市'],
                ['name'=>'鸡西市'],
                ['name'=>'贺州市'],
                ['name'=>'防城港市'],
                ['name'=>'兴安盟'],
                ['name'=>'白山市'],
                ['name'=>'三门峡市'],
                ['name'=>'忻州市'],
                ['name'=>'双鸭山市'],
                ['name'=>'楚雄彝族自治州'],
                ['name'=>'新余市'],
                ['name'=>'来宾市'],
                ['name'=>'淮北市'],
                ['name'=>'亳州市'],
                ['name'=>'湘西土家族苗族自治州'],
                ['name'=>'吕梁市'],
                ['name'=>'攀枝花市'],
                ['name'=>'晋城市'],
                ['name'=>'延安市'],
                ['name'=>'毕节市'],
                ['name'=>'张家界市'],
                ['name'=>'酒泉市'],
                ['name'=>'崇左市'],
                ['name'=>'萍乡市'],
                ['name'=>'乌海市'],
                ['name'=>'伊春市'],
                ['name'=>'六盘水市'],
                ['name'=>'随州市'],
                ['name'=>'德宏傣族景颇族自治州'],
                ['name'=>'池州市'],
                ['name'=>'黑河市'],
                ['name'=>'哈密市'],
                ['name'=>'文山壮族苗族自治州'],
                ['name'=>'阿坝藏族羌族自治州'],
                ['name'=>'天水市'],
                ['name'=>'辽源市'],
                ['name'=>'张掖市'],
                ['name'=>'铜仁市'],
                ['name'=>'鹤壁市'],
                ['name'=>'儋州市'],
                ['name'=>'保山市'],
                ['name'=>'安康市'],
                ['name'=>'白城市'],
                ['name'=>'巴中市'],
                ['name'=>'普洱市'],
                ['name'=>'鹤岗市'],
                ['name'=>'莱芜市'],
                ['name'=>'阳泉市'],
                ['name'=>'甘孜藏族自治州'],
                ['name'=>'嘉峪关市'],
                ['name'=>'白银市'],
                ['name'=>'临沧市'],
                ['name'=>'商洛市'],
                ['name'=>'阿克苏地区'],
                ['name'=>'海西蒙古族藏族自治州'],
                ['name'=>'大兴安岭地区'],
                ['name'=>'七台河市'],
                ['name'=>'朔州市'],
                ['name'=>'铜川市'],
                ['name'=>'定西市'],
                ['name'=>'迪庆藏族自治州'],
                ['name'=>'日喀则市'],
                ['name'=>'庆阳市'],
                ['name'=>'昭通市'],
                ['name'=>'喀什地区'],
                ['name'=>'怒江傈僳族自治州'],
                ['name'=>'海东市'],
                ['name'=>'阿勒泰地区'],
                ['name'=>'平凉市'],
                ['name'=>'石嘴山市'],
                ['name'=>'武威市'],
                ['name'=>'阿拉善盟'],
                ['name'=>'塔城地区'],
                ['name'=>'林芝市'],
                ['name'=>'金昌市'],
                ['name'=>'吴忠市'],
                ['name'=>'中卫市'],
                ['name'=>'陇南市'],
                ['name'=>'山南市'],
                ['name'=>'吐鲁番市'],
                ['name'=>'博尔塔拉蒙古自治州'],
                ['name'=>'临夏回族自治州'],
                ['name'=>'固原市'],
                ['name'=>'甘南藏族自治州'],
                ['name'=>'昌都市'],
                ['name'=>'阿里地区'],
                ['name'=>'海南藏族自治州'],
                ['name'=>'和田地区'],
                ['name'=>'克孜勒苏柯尔克孜自治州'],
                ['name'=>'海北藏族自治州'],
                ['name'=>'那曲地区'],
                ['name'=>'玉树藏族自治州'],
                ['name'=>'黄南藏族自治州'],
                ['name'=>'果洛藏族自治州']];
            
            $onee = new CityRank;
            $reste = $onee->saveAll($array_city); 
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