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
        $data = self::all(['store_id'=>$store_id,'classify_id'=>$lid,'status'=>1])
         -> toArray();

        foreach ($data as $k =>$v){
            //调取评论数
            $comment = new VideoComment();
            $comment_count = $comment->comment_count($store_id,$v['id']);
            $data[$k]['comments'] = $comment_count;
            //调取总点赞数
            $give = new Give();
            $gice_count = $give->give_count($store_id,$v['id']);
            $data[$k]['clickings'] = $gice_count;
        }
        return $data;

    }

    //直播详情
    public static function live_details($store_id,$vid)
    {
        $data =  self::get(['store_id'=>$store_id,'id'=>$vid]) ->  toArray();
        //调取评论数
        $comment = new VideoComment();
        $comment_count = $comment->comment_count($store_id,$vid);
        $data['comments'] = $comment_count;
        //调取总点赞数
        $give = new Give();
        $gice_count = $give->give_count($store_id,$vid);
        $data['clickings'] = $gice_count;

        return $data;
    }

    //直播浏览量+1
    public static function live_browsing($store_id,$vid)
    {
        $user = model('VideoFrequency');
        $data = $user::where(['store_id'=>$store_id,'id'=>$vid])-> setInc('numbers');
        return $data; 
    }

    //直播token
    public function token($store_id,$id)
    {
        $token = self::where(['member_id'=>$store_id,'id'=>$id])->value('accesstoken,expiretime');
        return $token;
    }
}