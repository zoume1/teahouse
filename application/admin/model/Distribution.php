<?php
namespace app\admin\model;

use think\Model;

class Distribution extends Model
{
    protected $table = "tb_distribution";
 
    /**
     * 获取全局分销设置
     * @param $store_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getStoreScale($store_id)
    {
        $scale_data = $this->where('store_id',"=",$store_id)->select();
        if($scale_data){
            foreach($scale_data as $value){
                $grade[] = $value['grade'];
                $award[] = $value['award'];
                $scale[] = $value['scale'];
                $integral[] = $value['integral'];
            }
            $data = [
                'grade' => $grade,
                'award' => $award,
                'scale' => $scale,
                'integral' => $integral
            ];
            return $data;
        } 
        
            return $scale_data;
        
    }



 
}

