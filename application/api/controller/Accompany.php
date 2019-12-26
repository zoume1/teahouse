<?php

namespace app\api\controller;
use think\Session;
use think\Model;
use think\Db;
use think\Validate;
use app\city\controller;
use app\admin\model\Goods;
use app\admin\model\Accompany as AccompanyGoods;
use app\admin\model\AccompanySetting;
use app\admin\model\AccompanyCode;
use app\admin\model\Member;
use app\admin\model\AaccompanyShare;
use app\admin\model\MakeZip;
use app\common\exception\BaseException;


/**
 * 赠茶商品
 * Class Accompany
 * @package app\api\controller
 */
class Accompany extends controller
{


    /**gy
     * 用户获取赠茶商品 
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAccompanyStatus(Request $request)
    {
        if($request->isPost()){
            $data = input();
            $time = time();
            $validate  = new Validate([
                ['code_id', 'require', 'code_id不能为空'],
                ['member_id', 'require', '会员id不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($data)) {
                $error = $validate->getError();
                return jsonError($error);
            } 
            
            //1.赠茶商品是否下架(已删除) 201
            $is_del = AccompanyCode::goods_detail($data['code_id']);
            if(!$is_del) return jsonSuccess('赠茶商品已下架', array(), ERROR_201);
            //商品是否下架(已删除)
            $goods_data = (new Goods())->where('id','=',$is_del['goods_id'])->find();
            if(!$goods_data) return jsonSuccess('商品已下架', array(), ERROR_200);
            //2.判断该用户是否已经领取过 202
            $is_scanf = AaccompanyShare::detail(['accompany_code_id' => $data['code_id'] , 'member_id' => $data['member_id']]);
            if($is_scanf) return jsonSuccess('您已经领取过该商品', array(), ERROR_202);
            //3.扫描的是什么码
            //4.是否过期 203
            if($time > $is_del['end_time']) return jsonSuccess('领取活动已过期', array(), ERROR_203);
            //5.是否满足会员范围 204
            $is_scope = Member::is_scope($data['member_id'],$is_del['scope']);
            if(!$is_scope) return jsonSuccess('您不在赠送的会员范围内', array(), ERROR_204);
            //6.如果是全向码扫描数是否已满 205
            $is_full = $this->is_full($data['code_id']);
            if(!$is_full) return jsonSuccess('商品已赠送完', array(), ERROR_205);
            //7.生成赠茶数据、用户扫码记录、修改code表数据
            
            
        }   
    }

    /**gy
     *  赠茶商品添加
     * @param $data //赠茶商品详情
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public  function accompany_add_order($data)
    {
        $store_id =  Session :: get('store_id');
        $this->startTrans();
        try {
            $goods_data = Goods::accompany_goods($data['goods_number'],1);
            if(isset($data['scope']) && !empty($data['scope'])) 
            {
                $scope = json_encode($data['scope'],true);
            } else {
                $scope = null;
            }
            $rest_data = [
                'choose_status' => $data['choose_status'],
                'goods_number' => $data['goods_number'],
                'goods_id' => $goods_data['id'],
                'goods_show_image' => $goods_data['goods_show_image'],
                'goods_name' => $goods_data['goods_name'],
                'scope' => $scope,
                'accompany_number' => $data['accompany_number'],
                'single_number' => $data['single_number'],
                'start_time' => strtotime($data['start_time']),
                'end_time' =>  strtotime($data['end_time']),
                'store_house_name' => $data['store_house_name'],
                'label' => $data['label'],
                'blessing' => $data['blessing'],
                'create_time' => time(),
                'store_id' => $store_id,
            ];
            $rest = $this->save($rest_data);
            if($rest){
                $data['accompany_id'] = $this->id;
                $setting = AccompanySetting::setting_add($data);
            }
            $restul = [
                'accompany_id' => $this->id,
                'code_status' => $data['choose_status'],
                'start_time' => strtotime($data['start_time']),
                'end_time' =>  strtotime($data['end_time']),
                'accompany_number' => $data['accompany_number'],
                'single_number' => $data['single_number'],
            ];
            switch($data['choose_status'])
            {
                case 1 :
                    $code_id = (new AccompanyCode())->code_add($restul);
                    $res = (new Goods())->unique_qrcode($code_id,$this->id);
                    break;
                case 2:
                    $method = ROOT_PATH . 'public' . DS . 'directional'. DS . $this->id;
                    $mkdir = mkdir($method, 0777, true);
                    for($i = 0 ; $i < $data['accompany_number'] ; $i++){
                        $code_id = (new AccompanyCode())->code_add($restul);
                        $res = (new Goods())->directional_qrcode($code_id);
                    }
                    break;
                default :
                    break;

            }
            $this->commit();
            //压缩文件
            $dir_path = ROOT_PATH . 'public' . DS . 'directional'. DS . $this->id . DS ; //想要压缩的目录
            $zipName = ROOT_PATH . 'public' . DS . 'directional'. DS . $this->id . DS.'test.zip';

            $makeZip = new MakeZip();
            //重复压缩，则会自动覆盖
            $res = $makeZip->zip($dir_path,$zipName,$this->id);
            if(!$res){
                throw new Exception('压缩失败');
            } 
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    
    }

        /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {

        if (isset($query['name']) && !empty($query['name'])) {
            $this->where('goods_number|goods_name', 'like', '%' . trim($query['name']) . '%');
        }
        if (isset($query['start_time']) && !empty($query['start_time'])) {
            $start_time = strtotime($query['start_time']);
            $time_condition  = "create_time > {$start_time} ";
            $this->where($time_condition);
        }
        if (isset($query['end_time']) && !empty($query['end_time'])) {
            $end_time = strtotime($query['end_time']);
            $time_condition  = "create_time < {$end_time} ";
            $this->where($time_condition);
        }
        if(isset($query['end_time']) && !empty($query['end_time']) && isset($query['start_time']) && !empty($query['start_time'])){
            $start_time = strtotime($query['start_time']);
            $end_time = strtotime($query['end_time']);
            $time_condition  = "create_time > {$start_time} and create_time< {$end_time} ";
            $this->where($time_condition);
        }
    }

    /**gy
     *获取赠茶商品
     * @param $meal_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        return self::get($id)->toArray();
    }


    /**获取所有市场反馈
     * gy
     * @param $useid
     * @return false|static[]
     * @throws \think\exception\DbException
     */
    public static function getList()
    {
        return self::all();
    }

    /**gy
     *  市场反馈更新
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function meal_update($data)
    {

        $model = new static;
        $rest = $model -> allowField(true)->save($data,['id'=>$data['id']]);
        return $rest ? $rest : false;
        
    }
    /**gy
     *  判断全向码扫描数是否已满
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function is_full($code_id)
    {
        $code = AccompanyCode::detail($code_id);
        if(($code['scan_number'] >= $code['accompany_number']) && $code['code_status'] == 1){
            return false;
        }
        return true;
        
    }

}