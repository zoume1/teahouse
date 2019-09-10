<?php
namespace app\admin\model;

use think\Model;
use think\db\Query;

class Store extends Model
{
    protected $table = "tb_store";
    protected $resultSetType = 'collection';

    public function getStoreName($id){
        $data  = $this->where(['id'=>$id])->value('store_name');    
        if($data){
            return $data;
        }
    }

}