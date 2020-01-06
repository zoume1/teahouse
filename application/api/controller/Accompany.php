<?php

namespace app\api\controller;
use think\Session;
use think\Model;
use think\Db;
use think\Validate;
use think\Controller;
use think\Request;
use app\admin\model\Goods;
use app\admin\model\Accompany as AccompanyGoods;
use app\admin\model\AccompanySetting;
use app\admin\model\AccompanyCode;
use app\admin\model\Member;
use app\admin\model\AaccompanyShare;
use app\admin\model\HouseOrder;
use app\admin\model\MakeZip;
use app\common\exception\BaseException;


/**
 * 赠茶商品
 * Class Accompany
 * @package app\api\controller
 */
class Accompany extends Controller{


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
            if($is_scanf) return jsonSuccess('您已经领取过该赠茶商品', array(), ERROR_202);

            $is_get = AaccompanyShare::detail(['accompany_code_id' => $data['code_id']]);
            if($is_get && $is_del['choose_status'] == 2) return jsonSuccess('赠茶商品已被领取',array(), ERROR_207);
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
            $is_add = (new HouseOrder())->geAddHouseOrder($is_del,$data['member_id'],$data['code_id']);
            if($is_add){
                return jsonSuccess('领取成功');
            }
            return jsonSuccess('领取失败',array(),ERROR_206);  
            
        }   
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