<?php
namespace app\api\model;

use think\Model;

class VideoFrequency extends Model
{
    protected $table = "tb_video_frequency";
    protected $resultSetType = 'collection';

    //查询分类直播列表
    public static function live_broadcast($store_id,$lid)
    {
        return self::all(['store_id'=>$store_id,'classify_id'=>$lid,'status'=>1])
         -> toArray();
    }

    //直播详情
    public static function live_details($store_id,$vid)
    {
        return self::get(['store_id'=>$store_id,'id'=>$vid]) ->  toArray();
    }

    //直播浏览量+1
    public static function live_browsing($store_id,$vid)
    {
        $user = model('VideoFrequency');
        $data = $user::where(['store_id'=>$store_id,'id'=>$vid])-> setInc('numbers');
        return $data; 
    }
}