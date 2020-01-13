<?php

namespace app\api\controller;

use app\admin\model\HouseOrder;
use app\admin\model\Store;
use think\Controller;
use think\Db;
use think\Request;
use app\common\exception\BaseException;
use app\admin\model\Goods;
use think\Validate;
use app\admin\model\ShareOrder;
use app\index\controller\Storehouse;

const RESTEL_ZERO = 0;
const RESTEL_ONE = 1;
const RESTEL_TWO = 2;
const RESTEL_THREE = 3;
const RESTEL_FOUR = 4;
const RESTEL_FIVE = 5;
const RESTEL_SIX = 6;
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
            $data = input();
            $validate  = new Validate([
                ['id', 'require', '仓库订单id不能为空'],
                ['give_number', 'require', '赠送数量不能为空'],
                ['string_number', 'require', '赠送显示单位数量不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($data)) {
                $error = $validate->getError();
                return jsonError($error);
            }
            $order_data = HouseOrder::getHouseOrder($data['id']);
            if (!$order_data)  return jsonError('该订单不存在');

            Db::startTrans();
            try {
                $share_data = array(
                    'order_id' => $order_data['id'], //订单id
                    'member_id' => $order_data['member_id'], //会员id
                    'end_time' => strtotime("+3 days"),
                    'store_id' => $order_data['store_id'],
                    'give_number' => $data['give_number'],
                    'string_number' => implode(",", $data['string_number']),
                );
                $share_id = (new ShareOrder())->share_add($share_data);
                if (!$share_id) {
                    throw new Exception('添加失败');
                }
                $return_url = (new Goods())->share_qrcode($share_id, $order_data['store_id']);
                $rest_data = [
                    'goods_describe' => $order_data['goods_describe'], //商品买点
                    'parts_goods_name' => $order_data['parts_goods_name'], //商品名称
                    'store_name' => (new Store())->getStoreName($order_data['store_id']),
                    'end_time' => strtotime("+3 days"),
                    'goods_image' => $order_data['goods_image'], //商品图片
                    'user_account_name' => $order_data['user_account_name'], //用户名
                    'share_code' => $return_url
                ];
                Db::commit();
                return jsonSuccess('发送成功', $rest_data);
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                Db::rollback();
                return jsonError('发送失败');
            }
        }
    }

    /**
     *用户扫描二维码领取存茶
     * @param \think\Model $houseorder
     * @string 存茶订单id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function getShareHouseData(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            $validate  = new Validate([
                ['share_order_id', 'require', 'code_id不能为空'],
                ['member_id', 'require', '会员id不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($data)) {
                $error = $validate->getError();
                return jsonError($error);
            }
            $time = time();
            $code_data = ShareOrder::getShareOrder($data['share_order_id']);
            if ($code_data['member_id'] == $data['member_id']) return jsonError('您不能领取自己的赠茶');
            //1.赠茶活动已取消（订单全部出仓）
            $house_order = HouseOrder::getHouseOrder($code_data['order_id']);
            if (!$house_order) return jsonError('赠茶活动已取消');
            $goods_data = Db::name('goods')->where('id', '=', $house_order['goods_id'])->find();
            //2.赠茶商品已下架（商品已删除）
            if (empty($goods_data)) return jsonError('赠茶商品已下架');
            //3.您已领取该赠茶（自己已领取）
            if ($code_data['status'] == 1 && $code_data['accept_id'] == $data['member_id']) return jsonError('您已领取该赠茶');
            //4.该赠茶已被领取（已被别人领过）
            if ($code_data['status'] == 1) return jsonError('该赠茶已被领取');
            //5.赠茶活动已结束（到期）
            if ($time > $code_data['end_time']) return jsonError('赠茶活动已结束');
            //6.该赠茶礼品已被领取完（赠送数量大于仓库数量）

            if (!empty($house_order)) {
                if (!empty($house_order['special_id'])) {
                    $goods = Db::name("special")->where("id", $house_order['special_id'])->find();
                    $house_order['goods_bottom_money'] = $goods['line'];
                    $house_order['unit'] = explode(",", $goods['unit']);
                    $house_order['num'] = explode(",", $goods['num']);
                } else {
                    $house_order['unit'] = explode(",", $house_order['unit']);
                    $house_order['num'] = explode(",", $house_order['num']);
                }

                $house_order["store_number"] = explode(',', $house_order["store_number"]);
                //前端要限制最低单位出仓数量
                $count = count($house_order['unit']);
                //下单单位对应数量
                switch ($count) {
                    case RESTEL_ONE:
                        $lowest = $house_order["store_number"][RESTEL_ZERO];
                        $lowest_unit = $house_order['unit'][RESTEL_ZERO];
                        break;
                    case RESTEL_TWO:
                        $lowest = intval($house_order["store_number"][RESTEL_ZERO]) * intval($house_order['num'][RESTEL_ONE]) + intval($house_order["store_number"][RESTEL_TWO]);
                        $lowest_unit = $house_order['unit'][RESTEL_ONE];
                        break;
                    case RESTEL_THREE:
                        $Replacement = intval(intval($house_order['num'][RESTEL_TWO]) / intval($house_order['num'][RESTEL_ONE]));
                        $lowest = intval($house_order["store_number"][RESTEL_ZERO]) * intval($house_order['num'][RESTEL_TWO]) + intval($house_order["store_number"][RESTEL_TWO]) * $Replacement + intval($house_order["store_number"][RESTEL_FOUR]);
                        $lowest_unit = $house_order['unit'][RESTEL_TWO];
                        break;
                    default:
                        $lowest = 0;
                        break;
                }
                $code_data['lowest_unit'] = $lowest_unit;
            }
            if ($code_data['give_number'] > $lowest) return jsonError('该赠茶礼品已被领取完');

            $add_bool = (new HouseOrder())->memberShareAddOrder($house_order, $data['member_id'], $code_data);
            //7更新share_order表，生成house_order新定单，更新送存订单数据
            if ($add_bool) {
                return jsonSuccess('领取赠茶成功');
            } else {
                return jsonError('领取赠茶失败');
            }
        }
    }

    /**
     * 茶仓赠茶点击赠送页面
     * @param \think\Model $houseorder
     * @string 存茶订单id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function ShowOrderNumber(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            $validate  = new Validate([
                ['id', 'require', '订单id不能为空'],
                ['lowest', 'require', '最大出仓数量不能为空'],
                ['out_number', 'require', '赠送数量不能为空'],
                ['lowest_unit', 'require', '出仓单位不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($data)) {
                $error = $validate->getError();
                return jsonError($error);
            }
            $surplus = intval($data['lowest'] - $data['out_number']);
            $house_order = HouseOrder::getHouseOrder($data['id']);
            if (!$house_order)  return jsonError('该订单不存在');
            if (!empty($house_order['special_id'])) {
                $goods = Db::name("special")->where("id", $house_order['special_id'])->find();
            } else {
                $goods = Db::name("goods")->where("id", $house_order['goods_id'])->find();
                if (empty($goods)) return jsonError('商品已下架');
            }

            $unit = explode(",", $goods['unit']);
            $num = explode(",", $goods['num']);
            //1.计算出仓数量等价单位
            $string_number = (new Storehouse())->getComputeUnit($data['out_number'], $num, $unit);
            //剩余单位数量
            $surplus_number = (new Storehouse())->getComputeUnit($surplus, $num, $unit);
            $rest_data = [
                'string_number' => explode(',', $string_number), //显示的赠送数量单位
                'surplus_number' => explode(',', $surplus_number), //剩余数量单位
                'lowest_unit' => $data['lowest_unit'], //赠送数量
            ];

            return jsonSuccess('发送成功', $rest_data);
        }
    }

    /**
     * 茶仓赠茶点击赠送
     * @param \think\Model $houseorder
     * @string 存茶订单id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function cLickGive(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            $validate  = new Validate([
                ['store_number', 'require', '订单id不能为空'],
                ['id', 'require', '订单id不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($data)) {
                $error = $validate->getError();
                return jsonError($error);
            }
            $house_order = HouseOrder::getHouseOrder($data['id']);
            if (!$house_order)  return jsonError('该订单不存在');
            if (!empty($house_order['special_id'])) {
                $goods = Db::name("special")->where("id", $house_order['special_id'])->find();
            } else {
                $goods = Db::name("goods")->where("id", $house_order['goods_id'])->find();
                if (empty($goods)) return jsonError('商品已下架');
            }
            $store_number = $data['store_number'];
            $unit = explode(",", $goods['unit']);
            $num = explode(",", $goods['num']);
            //前端要限制最低单位出仓数量
            $count = count($unit);
            switch ($count) {
                case RESTEL_ONE:
                    $lowest = $store_number[RESTEL_ZERO];
                    $lowest_unit = $unit[RESTEL_ZERO];
                    break;
                case RESTEL_TWO:
                    $lowest_unit = $unit[RESTEL_ONE];
                    $lowest = intval($store_number[RESTEL_ZERO]) * intval($num[RESTEL_ONE]) + intval($store_number[RESTEL_TWO]);
                    break;
                case RESTEL_THREE:
                    $lowest_unit = $unit[RESTEL_TWO];
                    $Replacement = intval(intval($num[RESTEL_TWO]) / intval($num[RESTEL_ONE]));
                    $lowest = intval($store_number[RESTEL_ZERO]) * intval($num[RESTEL_TWO]) + intval($store_number[RESTEL_TWO]) * $Replacement + intval($store_number[RESTEL_FOUR]);;
                    break;
                default:
                    $lowest = 0;
                    break;
            }
            //剩余单位数量
            $rest_data = [
                'lowest' => $lowest, //最大数量
                'lowest_unit' => $lowest_unit, //赠送单位
            ];
            return jsonSuccess('发送成功', $rest_data);
        }
    }
}
