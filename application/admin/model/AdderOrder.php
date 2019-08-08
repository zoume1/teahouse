<?php
namespace app\admin\model;

use think\Model;
use think\Model\Store as Store;
use think\db\Query;

class AdderOrder extends Model
{
    protected $table = "tb_adder_order";
    protected $resultSetType = 'collection';

    public function getOrderIdInformation($id){
        $data  = $this->where(['id'=>$id])->select();    
        if($data){
            $data = $data->toArray();
            return $data;
        } else {
            return null;
        }

    }

}