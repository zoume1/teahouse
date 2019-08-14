<?php
namespace app\admin\model;

use think\Model;
use app\admin\model\Distribution as Distribution;
use app\admin\model\Leaguer as Leaguer;

class Commodity extends Model
{
    protected $table = "tb_commodity";
 
    /**
     * 获取分销商品设置
     * @param $goods_id
     * @param $store_id
     * @param $member_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getCommissionScale($goods_id,$store_id,$member_id)
    {
         $member = new Leaguer;
         $data = $member->getMemberScale($member_id);
         if (empty($data['grade'])){
            $scale_data = $this->where('goods_id',"=",$goods_id)->find();
            if(!empty($scale_data['grade'])){
                $grade = explode(",",$scale_data['grade']);
                $award = explode(",",$scale_data['award']);
                $scale = explode(",",$scale_data['scale']);
                $integral = explode(",",$scale_data['integral']);

                $data = [
                    'grade' => $grade,
                    'award' => $award,
                    'scale' => $scale,
                    'integral' => $integral
                ];
            } else {
                $rest = new Distribution;
                $data = $rest->getStoreScale($store_id);
            }
         }

         return $data;
        
    }



 
}