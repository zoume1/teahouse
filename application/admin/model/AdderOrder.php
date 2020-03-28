<?php
namespace app\admin\model;

use think\Model;
use think\Model\Store as Store;
use think\db\Query;

class AdderOrder extends Model
{
    protected $table = "tb_adder_order";
    protected $resultSetType = 'collection';

    public function getOrderIdInformation($order_number){
        $data  = $this->where(['parts_order_number'=>$order_number])->select();    
        if($data){
            $data = $data->toArray();
            return $data;
        } else {
            return null;
        }

    }

}