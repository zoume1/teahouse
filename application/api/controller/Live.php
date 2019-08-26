<?php
/**
 * Created by PhpStorm.
 * User: FYK
 * Date: 2019/8/23 0028
 * Time: 16:57
 */
namespace app\api\controller;
use think\Controller;
use think\Db;
use think\Request;
use app\api\model\DirectSeeding; 
use app\api\model\VideoFrequency;

class Live extends Controller{

    //查询直播分类
    public function classification($store_id){

        $classif = new DirectSeeding();
        $data = $classif->detail($store_id);
        
        foreach($data as $k=>$v){
            $list = new VideoFrequency();
            $list1 = $list->live_broadcast($store_id,$v['id']);
            $data[$k]['list'] = $list1;
           // print_r($list1);die;
        }
       
        $res = $data ?['code'=>1,'msg'=>'获取成功','data'=>$data] : ['code'=>0,'msg'=>'获取失败'];

        return json($res);exit;

    }


    //直播列表
    public function video_list($store_id,$class_id){

        $list = new VideoFrequency();
        $data = $list->live_broadcast($store_id,$class_id);

        $res = $data ?['code'=>1,'msg'=>'获取成功','data'=>$data] : ['code'=>0,'msg'=>'获取失败'];

        return json($res);exit;
    }

    //详情
    public function details($store_id,$vid){
        $list = new VideoFrequency();
       
        $browsing = $list->live_browsing($store_id,$vid);

        $data = $list->live_details($store_id,$vid);
        $res = $data ?['code'=>1,'msg'=>'获取成功','data'=>$data] : ['code'=>0,'msg'=>'获取失败'];

        return json($res);exit;
    }
    
    
}