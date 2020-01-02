<?php

namespace app\api\controller;

use app\admin\model\HouseOrder;
use app\admin\model\Store;
use think\Controller;
use think\Db;
use think\Request;
use \think\Exception;
use app\admin\model\Goods;
use app\admin\model\ShareOrder;

/**
 * 茶仓分享存茶
 * Class Message
 * @package app\api\controller
 */
class StoreHouseShareNumber extends Controller
{
    /**
     * 茶仓分享存茶页面
     * @param \think\Model $houseorder
     * @string 存茶订单id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function SharePictureData(Request $request)
    {
        if ($request->isPost()) {
            $order_id = $request->only(['id'])['id'];
            $order_data = HouseOrder::getHouseOrder($order_id);
            if (!$order_data)  return jsonError('该订单不存在');
            //检查分享存茶数量是否超过订单数量
            $share_number = ShareOrder::countOrderNumber($order_id);
            if($share_number >= $order_data['order_quantity']) return jsonError('您的赠送数量已达上限');
            $this->startTrans();
            try {
                $share_data = array(
                    'order_id' => $order_data['id'], //订单id
                    'goods_describe' => $order_data['goods_describe'], //商品买点
                    'parts_goods_name' => $order_data['parts_goods_name'], //商品名称
                    'order_quantity' => $order_data['order_quantity'], //订单数量
                    'member_id' => $order_data['order_quantity'], //会员id
                    'store_name' => (new Store())->getStoreName($order_data['store_id']),
                    'end_time' => strtotime("+3 days"),
                    'store_id' => $order_data['store_id']
                );

                $share_id = ShareOrder::share_add($share_data);
                if (!$share_id){
                    throw new Exception('添加失败');
                }                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      
                $return_url = (new Goods())->share_qrcode($share_id, $order_data['store_id']);
                $share_data['share_code'] = $return_url;
                $this->commit();
                return jsonSuccess('发送成功', $share_data);            
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                $this->rollback();
                return jsonError('发送失败');
            }
        }
    }
}
