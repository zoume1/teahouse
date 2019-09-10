<?php
namespace app\admin\model;

use think\Model;

class Apply extends Model
{
    protected $name = "dealer_apply";
 
    /**
     * 获取分销商品设置
     * @param $goods_id
     * @param $store_id
     * @param $member_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getdataScale($id)
    {

  

        $data = $this->where('apply_id',"=",$id)->find()->toArray();
        unset($data['apply_id']);

        $bool = $this->save($data);
 
   

         return $data;
        
    }



 
}