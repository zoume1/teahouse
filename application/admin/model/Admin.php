<?php
namespace app\admin\model;

use think\Model;

class Admin extends Model
{

    protected $table = "tb_admin";
    protected $resultSetType = 'collection';

    /**
     * [管理员查找]
     * @author 陈绪
     * @return \think\Paginator
     */
    public function sSelect(){
        return $this->paginate(10);
    }

    /**
     * [管理员入库]
     * @author 陈绪
     * @param $arr
     * @return false|int
     */
    public function sSave($arr){
        if(is_array($arr)){
            return $this->save($arr);
        }
    }

    /**gy
     * 获取信息
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($data)
    {
        $rest = self::get($data);
        return $rest ? $rest->toArray() : false;
    }



    /**gy
     *  更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function user_update($data)
    {

        $model = new static;
        $rest = $model -> allowField(true)->save($data,['id'=>$data['id']]);
        return $rest ? $rest : false;
        
    }
}