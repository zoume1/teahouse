<?php
namespace app\admin\model;

use think\Model;

class TempletMessage extends Model
{
    protected $table = "tp_templet_message";
    protected $resultSetType = 'collection';

 
    /**
     * 模板消息设置
     * @param $store_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getTemplet($store_id)
    {
        $rest = self::where('store_id','=',$store_id)->select();
        return $rest ? $rest->toArray() :false;
        
    }

    /**gy
     * 获取模板信息
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($meal_id)
    {
        return self::get($meal_id)->toArray();
    }


    /**
     * 模板消息返回tid
     * @param $store_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getTempletStatus($store_id,$status)
    {   
        $rest = self::getTemplet($store_id);
        if($rest){
            $template_id = $rest[$status]['template_id'];
            return $template_id ? $template_id :false;
        }
        return $rest;
    }



 
}