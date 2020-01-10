<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/6/28
 * Time: 15:21
 */

namespace app\index\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Validate;
use think\Image;
use app\api\model\UpdateLine;

const RESTEL_ZERO = 0;
const RESTEL_ONE = 1;
const RESTEL_TWO = 2;
const RESTEL_THREE = 3;
const RESTEL_FOUR = 4;
const RESTEL_FIVE = 5;
const RESTEL_SIX = 6;


class Storehouse extends Controller
{

    public static $restel = 0;
    public static $restel_one = 1;
    public static $restel_two = 2;
    /**
     * @param int $uniacid
     * @param int member_id
     * [店铺小程序前端存茶数据]
     * @return 成功时返回，其他抛异常
     */
    public function getStoreData(Request $request)
    {
        if ($request->isPost()) {
            $store_id = $request->only(['uniacid'])['uniacid'];
            $member_id = $request->only(['member_id'])['member_id'];
            $time = time();
            $depot = Db::name("store_house")->where("store_id", $store_id)->select();
            foreach ($depot as $kk => $va) {
                $depot_name[] = $va['number'];
            }
            if (isset($member_id) && isset($store_id)) {
                if (!empty($depot)) {
                    foreach ($depot as $key => $value) {
                        $house_order[$key] = Db::table("tb_house_order")
                            ->field("tb_house_order.id,store_unit,store_house_id,pay_time,goods_image,special_id,goods_id,end_time,goods_money,store_number,tb_goods.date,tb_store_house.number,cost,store_unit,tb_goods.goods_name,brand,goods_bottom_money,tb_wares.name,tb_store_house.unit,tb_store_house.name store_name")
                            ->join("tb_goods", "tb_house_order.goods_id = tb_goods.id", 'right')
                            ->join("tb_store_house", " tb_store_house.id = tb_house_order.store_house_id", 'left')
                            ->join("tb_wares", "tb_wares.id = tb_goods.pid", 'left')
                            ->where("tb_house_order.status", ">", 2)
                            ->where("tb_house_order.order_quantity", ">", 0)
                            ->where(["tb_house_order.store_id" => $store_id, "tb_house_order.store_house_id" => $depot[$key]['id'], "tb_house_order.member_id" => $member_id])
                            ->order("order_create_time asc")
                            ->select();
                    }

                    $count_number = count($house_order);
                    for ($i = 0; $i < $count_number; $i++) {
                        foreach ($house_order[$i] as $zt => $kl) {
                            $house_order[$i][$zt]["store_number"] = explode(',', $house_order[$i][$zt]["store_number"]);
                            $house_order[$i][$zt]["unit"] = explode(',', $house_order[$i][$zt]["unit"]);
                            $house_order[$i][$zt]["cost"] = explode(',', $house_order[$i][$zt]["cost"]);
                            $rest_key = array_search($house_order[$i][$zt]["store_unit"], $house_order[$i][$zt]["unit"]);
                            $house_order[$i][$zt]["unit_price"] = $house_order[$i][$zt]["cost"][$rest_key];

                            if ($time < $house_order[$i][$zt]["end_time"]) {
                                $house_order[$i][$zt]['limit_time'] = round(($house_order[$i][$zt]["end_time"] - $time) / 86400); //剩余天数
                                if ($house_order[$i][$zt]['limit_time'] > 30) {
                                    $house_order[$i][$zt]['limit_time'] = 0; //未到期
                                } else {
                                    $house_order[$i][$zt]['limit_time'] = 1; //即将到期
                                }
                            } else {
                                $house_order[$i][$zt]['limit_time'] = 2; //已到期
                            }
                            if (!empty($house_order[$i][$zt]['special_id'])) {
                                $house_order[$i][$zt]['goods_bottom_money'] = Db::name("special")->where("id", $house_order[$i][$zt]['special_id'])->value("line");
                            }
                        }
                    }

                    foreach ($depot_name as $ds => $nm) {
                        $depots_names[$ds]['name'] = $nm;
                        $depots_names[$ds]['getArr'] = $house_order[$ds];

                        if (empty($depots_names[$ds]['getArr'])) {
                            unset($depots_names[$ds]);
                        }
                    }
                    $depots_names = array_values($depots_names);
                    if (!empty($depots_names)) {
                        return ajax_success("传输成功", $depots_names);
                    } else {
                        $store_first = Db::name('store_house')->where("store_id", $store_id)->field('number,name')->find();
                        $rest[0] = $store_first;
                        return ajax_error("没有存茶订单", $rest);
                    }
                } else {
                    return ajax_error("该店铺没有存茶仓库");
                }
            } else {
                return ajax_error("参数有误");
            }
        }
    }

    /**
     * @param int $uniacid
     * @param int member_id
     * [店铺小程序前端存茶总价值]
     * @return 成功时返回，其他抛异常
     */
    public function theStoreValue(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            if (isset($data['uniacid']) && isset($data['member_id'])) {
                $depot  = Db::name("house_order")
                    ->where(["store_id" => $data['uniacid'], "member_id" => $data['member_id']])
                    ->where("status", '>', 1)
                    ->sum("order_quantity * goods_money");

                $depot_value = round($depot, 2);
                return json_encode(array("status" => 1, "info" => "获取成功", "data" => ['order_real_pay' => $depot_value]));
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }
    }


    /**
     * @param int $uniacid
     * @param int member_id
     * [店铺小程序前端所有仓库]
     * @return 成功时返回，其他抛异常
     */
    public function getStoreHouse(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            if (isset($data['uniacid']) && isset($data['member_id'])) {
                $depot  = Db::table("tb_house_order")
                    ->field("tb_house_order.store_house_id,tb_store_house.number,name")
                    ->join("tb_store_house", "tb_store_house.id = tb_house_order.store_house_id", 'left')
                    ->where(["tb_house_order.store_id" => $data['uniacid'], "tb_house_order.member_id" => $data['member_id']])
                    ->group("tb_house_order.store_house_id")
                    ->select();

                if (!empty($depot)) {
                    return ajax_success("传输成功", $depot);
                } else {
                    return ajax_error("该用户未进行存茶操作");
                }
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }
    }

    /**
     * @param int $uniacid
     * @param int member_id
     * @param int store_house_id
     * [店铺小程序前端选择仓库]
     * @return 成功时返回，其他抛异常
     */
    public function doHouseOrder(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            $time = time();
            $rest_number = self::$restel;
            if (isset($data['uniacid']) && isset($data['member_id']) && isset($data['store_house_id'])) {
                $house_name = Db::name("store_house")->where("id", $data['store_house_id'])->value("number");
                if (empty($house_name)) {
                    return ajax_error("获取仓库失败");
                }
                $house_order = Db::table("tb_house_order")
                    ->field("tb_house_order.id,store_name,pay_time,goods_image,special_id,goods_id,end_time,goods_money,store_number,store_unit,tb_goods.date,tb_store_house.number,tb_goods.goods_name,brand,goods_bottom_money,tb_wares.name")
                    ->join("tb_goods", "tb_house_order.goods_id = tb_goods.id", 'right')
                    ->join("tb_store_house", " tb_store_house.id = tb_house_order.store_house_id", 'left')
                    ->join("tb_wares", "tb_wares.id = tb_goods.pid", 'left')
                    ->where(["tb_house_order.store_id" => $data['uniacid'], "tb_house_order.member_id" => $data['member_id'], "tb_house_order.store_house_id" => $data['store_house_id']])
                    ->order("end_time desc")
                    ->select();


                if (!empty($house_order)) {
                    foreach ($house_order as $k => $l) {
                        $house_order[$k]["store_number"] = explode(',', $house_order[$k]["store_number"]);
                        if ($time < $house_order[$k]["end_time"]) {
                            $house_order[$k]['limit_time'] = round(($house_order[$k]["end_time"] - $time) / 86400); //剩余天数
                            if ($house_order[$k]['limit_time'] > 30) {
                                $house_order[$k]['limit_time'] = 0; //未到期
                            } else {
                                $house_order[$k]['limit_time'] = 1; //即将到期
                            }
                        } else {
                            $house_order[$k]['limit_time'] = 2; //已到期
                        }
                        if (!empty($house_order[$k]['special_id'])) {
                            $house_order[$k]['goods_bottom_money'] = Db::name("special")->where("id", $house_order[$k]['special_id'])->value("line");
                        }
                    }
                    $rest_house['name'] = $house_name;
                    $rest_house['getArr'] = $house_order;
                    $restul[$rest_number] = $rest_house;

                    return ajax_success("发送成功", $restul);
                } else {
                    $rest_house['name'] = $house_name;
                    $rest_house['getArr'] = [];
                    $restul[$rest_number] = $rest_house;
                    return ajax_error("该店铺没有存茶订单", $restul);
                }
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }
    }


    /**
     * @param int $id
     * @param int member_id
     * @param int uniacid
     * [店铺小程序前端入仓详情]
     * @return 成功时返回，其他抛异常
     */
    public function takeOrderData(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            if (isset($data['uniacid']) && isset($data['member_id']) && isset($data['id'])) {
                $member_grade_id = Db::name("member")->where("member_id", $data['member_id'])->find();
                $rank = Db::name("member_grade")
                    ->where("member_grade_id", $member_grade_id['member_grade_id'])
                    ->where('store_id', '=', $data['uniacid'])
                    ->value("member_consumption_discount");
                $house_order = Db::table("tb_house_order")
                    ->field("tb_house_order.id,pay_time,accompany_code_id,goods_image,special_id,goods_id,parts_order_number,end_time,order_quantity,goods_money,order_amount,store_number,store_unit,tb_store_house.number,tb_store_house.adress,tb_goods.goods_name,date,goods_new_money,goods_member,goods_bottom_money,brand,num,tb_goods.unit,tb_wares.name,tb_store_house.name store_name")
                    ->join("tb_goods", "tb_house_order.goods_id = tb_goods.id", 'left')
                    ->join("tb_store_house", " tb_store_house.id = tb_house_order.store_house_id", 'left')
                    ->join("tb_wares", "tb_wares.id = tb_goods.pid", 'left')
                    ->where(["tb_house_order.store_id" => $data['uniacid'], "tb_house_order.member_id" => $data['member_id'], "tb_house_order.id" => $data['id']])
                    ->find();

                if (!empty($house_order)) {
                    $house_order['unit'] = explode(",", $house_order['unit']);
                    $house_order['num'] = explode(",", $house_order['num']);
                    $house_order["store_number"] = explode(',', $house_order["store_number"]);
                    if ($house_order["goods_money"] > 0) {
                        $house_order["scale"] = sprintf("%.2f", (($house_order["goods_new_money"] - $house_order["goods_money"])) * 100 / ($house_order["goods_money"]));
                    } else {
                        $house_order["scale"] = 0;
                    }
                    $give_sataus = 0;
                    if ($house_order['accompany_code_id'] != 0) {
                        $give_sataus = 1;
                    }
                    $house_order['give_sataus'] = $give_sataus;
                    if ($house_order['goods_member'] == 1) {
                        $scope = Db::name("goods")->where("id", $house_order['goods_id'])->value('scope');
                        if (!empty($scope)) {
                            $scope = explode(',', $scope);
                            if (!in_array($member_grade_id['member_grade_name'], $scope)) {
                                $rank = 1;
                            }
                        } else {
                            $rank = 1;
                        }
                    } else {
                        $rank = 1;
                    }
                    if (!empty($house_order['special_id'])) {
                        $goods = Db::name("special")->where("id", $house_order['special_id'])->find();
                        $house_order['goods_bottom_money'] = $goods['line'];
                        $house_order['goods_new_money'] = $house_order['goods_money'];
                        $house_order['discount_price'] = $goods['price'] * $rank;
                    } else {
                        $goods = Db::name("goods")->where("id", $house_order['goods_id'])->find();
                        $house_order['goods_new_money'] = $house_order['goods_money'];
                        $house_order['discount_price'] = $goods['goods_new_money'] * $rank;
                    }

                    return ajax_success("获取成功", $house_order);
                } else {
                    return ajax_error("该店铺没有存茶订单");
                }
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }
    }



    /**
     * @param int $id
     * @param int member_id
     * @param int uniacid
     * [店铺小程序前端订单出仓]
     * @return 成功时返回，其他抛异常
     */
    public function outPositionOrder(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            if (isset($data['uniacid']) && isset($data['member_id']) && isset($data['id'])) {
                $member_grade_id = Db::name("member")->where("member_id", $data['member_id'])->value("member_grade_id");
                $rank = Db::name("member_grade")->where("member_grade_id", $member_grade_id)->value("member_consumption_discount");
                $house_order = Db::table("tb_house_order")
                    ->field("tb_house_order.id,pay_time,goods_image,special_id,goods_id,parts_order_number,end_time,order_quantity,goods_money,order_amount,store_number,store_unit,tb_store_house.number,tb_store_house.adress,tb_goods.goods_name,templet_id,date,goods_new_money,goods_member,goods_bottom_money,brand,num,tb_goods.unit,tb_wares.name,tb_store_house.name store_name")
                    ->join("tb_goods", "tb_house_order.goods_id = tb_goods.id", 'left')
                    ->join("tb_store_house", " tb_store_house.id = tb_house_order.store_house_id", 'left')
                    ->join("tb_wares", "tb_wares.id = tb_goods.pid", 'left')
                    ->where(["tb_house_order.store_id" => $data['uniacid'], "tb_house_order.member_id" => $data['member_id'], "tb_house_order.id" => $data['id']])
                    ->find();

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
                    $value_key = array_search($house_order['store_unit'], $house_order['unit']);
                    switch ($count) {
                        case RESTEL_ONE:
                            $lowest = $house_order["store_number"][RESTEL_ZERO];
                            $lowest_unit = $house_order['unit'][RESTEL_ZERO];
                            break;
                        case RESTEL_TWO:
                            $lowest_unit = $house_order['unit'][RESTEL_ONE];
                            switch ($value_key) {
                                case RESTEL_ZERO:
                                    $lowest = intval($house_order["store_number"][RESTEL_ZERO]) * intval($house_order['num'][RESTEL_ONE]) + intval($house_order["store_number"][RESTEL_THREE]);
                                    break;
                                case RESTEL_ONE:
                                    $lowest =  $house_order["store_number"][RESTEL_TWO];
                                    break;
                                default:
                                    $lowest = 0;
                                    break;
                            }
                            break;
                        case RESTEL_THREE:
                            $lowest_unit = $house_order['unit'][RESTEL_TWO];
                            $Replacement = intval(intval($house_order['num'][RESTEL_TWO]) / intval($house_order['num'][RESTEL_ONE]));
                            switch ($value_key) {
                                case RESTEL_ZERO:
                                    //换算数量
                                    $lowest = intval($house_order["store_number"][RESTEL_ZERO]) * intval($house_order['num'][RESTEL_TWO]) + intval($house_order["store_number"][RESTEL_TWO]) * $Replacement + intval($house_order["store_number"][RESTEL_FOUR]);
                                    break;
                                case RESTEL_ONE:
                                    $lowest =  intval($house_order["store_number"][RESTEL_ZERO]) * intval($house_order['num'][RESTEL_TWO]) + intval($house_order["store_number"][RESTEL_TWO]) * $Replacement + intval($house_order["store_number"][RESTEL_FOUR]);
                                    break;
                                case RESTEL_TWO:
                                    $lowest =  $house_order["store_number"][RESTEL_FOUR];
                                    break;
                                default:
                                    $lowest = 0;
                                    break;
                            }
                            break;
                        default:
                            $lowest = 0;
                            break;
                    }
                    $house_order['lowest'] = $lowest;
                    $house_order['lowest_unit'] = $lowest_unit;
                    if ($house_order['goods_member'] != RESTEL_ONE) {
                        $rank = RESTEL_ONE;
                    }
                    return ajax_success("获取成功", $house_order);
                } else {
                    return ajax_error("该店铺没有存茶订单");
                }
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }
    }


    /**
     * @param int $goods_id
     * @param string  $are
     * @param int member_id
     * [店铺小程序前端订单出仓运费]
     * @return 成功时返回，其他抛异常
     */
    public function getHousePrice(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            if (isset($data['goods_id']) && isset($data['member_id']) && isset($data['are'])) {
                $goods_data = Db::name('goods')->where('id', $data['goods_id'])->find();
                if (empty($goods_data)) {
                    return ajax_error("商品参数id不正确");
                }

                $goods_franking = $goods_data['goods_franking'];
                if ($goods_franking == 0 && !empty($goods_data["templet_name"])  && !empty($goods_data["templet_id"])) {
                    $templet_id = explode(",", $goods_data['templet_id']);
                    foreach ($templet_id as $kk => $yy) {
                        $rest[$kk] =  db("express")->where("id", $templet_id[$kk])->find();
                        $rest[$kk]["are"] = explode(",", $rest[$kk]["are"]);
                        if (in_array($data['are'], $rest[$kk]["are"])) {
                            $datas[$kk]["collect"] = $rest[$kk]["price"]; //首费
                            $datas[$kk]["markup"] = $rest[$kk]["markup"]; //续费
                        } else {
                            $datas[$kk]["collect"] = $rest[$kk]["price_two"]; //首费
                            $datas[$kk]["markup"] = $rest[$kk]["markup_two"]; //续费
                        }
                    }
                    return json_encode(array("status" => 1, "info" => "发送成功", "franking_type" => 1, "data" => $datas));
                } else {
                    $datas['collect'] = $goods_franking;
                    return json_encode(array("status" => 1, "info" => "发送成功", "franking_type" => 2, "data" => $datas));
                }
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }
    }


    /**
     * @param int $goods_id
     * @param string  $uniacid
     * @param int $special_id
     * [存茶详情年划线价]
     * @return 成功时返回，其他抛异常
     */
    public function getLinePrice(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            $rest = new UpdateLine;
            if (isset($data['goods_id']) && isset($data['uniacid']) && isset($data['special_id'])) {
                if ($data['special_id'] > 0) {
                    $query_data = $rest->getList($data['special_id']);
                    if (empty($query_data)) {
                        $data[] = db('special')->where('id', '=', $data['special_id'])->value('line');
                        $categories[] = date('Y');
                        $query_data = [
                            'data' => $data,
                            'categories' => $categories,
                        ];
                        return ajax_success('发送成功', [$query_data]);
                    }
                } else {
                    $query_data = $rest->getList($data['goods_id']);
                    if (empty($query_data)) {
                        $datas[] = db('goods')->where('id', '=', $data['goods_id'])->value('goods_bottom_money');
                        $categories[] = date('Y');
                        $query_data = [
                            'data' => $datas,
                            'categories' => $categories,
                        ];
                        return ajax_success('发送成功', [$query_data]);
                    }
                }

                foreach ($query_data as $value) {
                    $datas[] = $value['line'];
                    $categories[] = $value['year_number'];
                }
                $rest = [
                    'data' => $datas,
                    'categories' => $categories,
                ];
                return ajax_success('发送成功', $rest);
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }
    }



    /**
     * @param int $lowest
     * @param int $member_id
     * @param int $out_number
     * @param int $goods_id
     * @param string $are
     * @param array  $unit
     * @param array $num
     * [最小单位换算出仓]
     * @return 成功时返回，其他抛异常
     */
    public function geTexchange(Request $request)
    {
        if ($request->isPost()) {
            $data = input();
            $validate  = new Validate([
                ['out_number', 'require', '出仓数量不能为空'],
                ['num', 'require', 'num不能为空'],
                ['unit', 'require', 'unit不能为空'],
                ['goods_id', 'require', '出仓商品id不能为空'],
                ['lowest', 'require', '仓储总数量不能为空'],
                ['lowest_unit', 'require', '仓储最小不能为空'],
                ['member_id', 'require', '账号id不能为空'],
                ['are', 'require', '邮寄地址不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($data)) {
                $error = $validate->getError();
                return jsonError($error);
            }
            //出仓换算为单位
            $string_number = $this->getComputeUnit($data['out_number'], $data['num'], $data['unit']);
            //剩余数量
            $surplus = intval($data['lowest'] - $data['out_number']);
            $surplus_number = $this->getComputeUnit($surplus, $data['num'], $data['unit']);
            if (!empty($string_number) && !empty($surplus_number)) {
                $out_price = $this->getOutMomeny($data, $string_number);
                $transmit = [
                    'surplus' => $surplus, //剩余数量
                    'surplus_number' => $surplus_number, //剩余仓储
                    'string_number' => explode(",", $string_number), //出仓显示单位
                    'out_price' => $out_price, //出仓费用
                ];
                return jsonSuccess('发送成功', $transmit);
            } else {
                return jsonError('单位计算失败');
            }
        }
    }


    /**
     * @param int $lowest
     * @param int $member_id
     * @param int $out_number
     * @param int $goods_id
     * @param string $are
     * @param array  $unit
     * @param array $num
     * [最小单位换算出仓]
     * @return $string_number 成功时返回，其他抛异常
     */
    public function getComputeUnit($out_number, $num, $unit)
    {
        $count = count($unit);
        switch ($count) {
            case RESTEL_ONE:
                $string_number = $out_number . ',' . $unit[RESTEL_ZERO];
                break;
            case RESTEL_TWO:
                $restul =  intval($out_number / $num[RESTEL_ONE]);
                if ($restul > RESTEL_ONE) {
                    $one = intval(fmod($out_number, $num[RESTEL_ONE]));
                    $string_number = $restul . ',' . $unit[RESTEL_ZERO] . ',' . $one . ',' . $unit[RESTEL_ONE];
                } elseif ($restul < RESTEL_ONE) {
                    $string_number = RESTEL_ZERO . ',' . $unit[RESTEL_ZERO] . ',' . $out_number . ',' . $unit[RESTEL_ONE];
                } else {
                    $string_number = RESTEL_ONE . ',' . $unit[RESTEL_ZERO] . ',' . RESTEL_ZERO . ',' . $unit[RESTEL_ONE];
                }
                break;
            case RESTEL_THREE:
                $order_quantity = $out_number;
                $number_two = $unit[RESTEL_TWO];              //当前单位
                $num_two = intval($num[RESTEL_TWO]);          //当前数量
                $number_one = $unit[RESTEL_ONE];              //上一级等级单位
                $num_one = intval($num[RESTEL_ONE]);          //上一级等级数量
                $number_zero = $unit[RESTEL_ZERO];            //上上一级等级单位
                $num_zero = intval($num[RESTEL_ZERO]);        //上上一级等级数量
                $num_among = intval($num_two / $num_one);     //与上一级装换满足的最低数量

                if ($order_quantity > $num_two) {
                    $rest_one = $order_quantity / $num_two; //最高单位除数
                    $rest_two = fmod($order_quantity, $num_two); //最低单位余数
                    if ($rest_two > $num_among) {
                        $rest_three = $rest_two / $num_among;   //第二单位除数
                        $rest_four = fmod($rest_two, $num_among);   //第二单位余数
                        $string_number = intval($rest_one) . ',' . $number_zero . ',' . intval($rest_three) . ',' . $number_one . ',' . $rest_four . ',' . $number_two;
                    } elseif ($rest_two < $num_among) {
                        $rest_three = RESTEL_ZERO;   //第二单位除数
                        $string_number = intval($rest_one) . ',' . $number_zero . ',' . $rest_three . ',' . $number_one . ',' . $rest_two . ',' . $number_two;
                    } else {
                        $rest_three = RESTEL_ONE;
                        $rest_two = RESTEL_ZERO;
                        $string_number = intval($rest_one) . ',' . $number_zero . ',' . $rest_three . ',' . $number_one . ',' . $rest_two . ',' . $number_two;
                    }
                } elseif ($order_quantity < $num_two) {
                    $rest_one = RESTEL_ZERO;
                    if ($order_quantity > $num_among) {
                        $rest_three = $order_quantity / $num_among;   //第二单位除数
                        $rest_four = fmod($order_quantity, $num_among);   //第二单位余数
                        $string_number = $rest_one . ',' . $number_zero . ',' . intval($rest_three) . ',' . $number_one . ',' . intval($rest_four) . ',' . $number_two;
                    } elseif ($order_quantity < $num_among) {
                        $rest_three = RESTEL_ZERO;   //第二单位除数
                        $rest_four = $order_quantity;   //第二单位余数
                        $string_number = $rest_one . ',' . $number_zero . ',' . intval($rest_three) . ',' . $number_one . ',' . $rest_four . ',' . $number_two;
                    } else {
                        $rest_three = RESTEL_ONE;
                        $rest_four = RESTEL_ZERO;
                        $string_number = $rest_one . ',' . $number_zero . ',' . $rest_three . ',' . $number_one . ',' . $rest_four . ',' . $number_two;
                    }
                } else {
                    $rest_one = RESTEL_ONE;
                    $rest_three = RESTEL_ZERO;
                    $rest_four = RESTEL_ZERO;
                    $string_number = $rest_one . ',' . $number_zero . ',' . $rest_three . ',' . $number_one . ',' . $rest_four . ',' . $number_two;
                }
                break;
            default:
                $string_number = null;
                break;
        }
        return $string_number;
    }


    /**
     * @param int $lowest
     * @param int $member_id
     * @param int $out_number
     * @param int $goods_id
     * @param string $are
     * @param array  $unit
     * @param array $num
     * [计算出仓费用]
     * @return 成功时返回，其他抛异常
     */
    public function getOutMomeny($data, $string_number)
    {
        $goods_id = $data["goods_id"]; //商品id
        $are = $data['are']; //地区
        $lowest_unit = $data['lowest_unit']; //商品单位
        $goods = db("goods")->where("id", $goods_id)->find();
        if ($goods['goods_franking'] != RESTEL_ZERO) {
            $datas["collect"] = $goods["goods_franking"]; //统一邮费
            $datas["markup"] = RESTEL_ZERO; //统一邮费
            $out_price = sprintf("%.2f",$data['out_number'] * $datas["collect"]);
        } elseif ($goods["goods_franking"] == RESTEL_ZERO && !empty($goods["templet_name"])  && !empty($goods["templet_id"])) {
            $templet_name = explode(",", $goods["templet_name"]);
            $templet_id = explode(",", $goods["templet_id"]);
            $string_number = explode(",", $string_number);
            $size = count($string_number);
            switch ($size) {
                case RESTEL_TWO:
                    $out_price = $this->templet($templet_name, $templet_id,$are,RESTEL_ONE,$string_number);
                    break;
                case RESTEL_FOUR:
                    $one_price = $this->templet($templet_name, $templet_id,$are,RESTEL_ONE,$string_number);
                    $two_price = $this->templet($templet_name, $templet_id,$are,RESTEL_THREE,$string_number);
                    $out_price = $one_price + $two_price;
                    break;
                case RESTEL_SIX:
                    $one_price = $this->templet($templet_name, $templet_id,$are,RESTEL_ONE,$string_number);
                    $two_price = $this->templet($templet_name, $templet_id,$are,RESTEL_THREE,$string_number);
                    $three_price = $this->templet($templet_name, $templet_id,$are,RESTEL_FIVE,$string_number);
                    $out_price = $one_price + $two_price + $three_price;
                    break;
                default:
                    $out_price = RESTEL_ZERO;

            }
        } else {
            $out_price = RESTEL_ZERO;
        }

        return $out_price;
    }


    /**
     * @param string $monomer
     * @param array $templet_name
     * @param array $templet_id
     * [单位模板对应计算费用]
     * @return 成功时返回，其他抛异常
     */
    public function templet($templet_name, $templet_id,$are,$number,$string_number)
    {
        $monomer = $string_number[$number];
        $tempid = array_search($monomer, $templet_name);
        $express_id = $templet_id[$tempid];
        $rest_number = $number - RESTEL_ONE;
        $rest = db("express")->where("id", $express_id)->find();
        if (!empty($rest)) {
            $are_block = explode(",", $rest["are"]);
            if (in_array($are, $are_block)) {
                $datas["collect"] = $rest["price"]; //首费
                $datas["markup"] = $rest["markup"]; //续费
                if(intval($string_number[$rest_number]) > RESTEL_ZERO){
                    $out_price = sprintf("%.2f",$datas["collect"] + (intval($string_number[$rest_number]) - RESTEL_ONE) * $datas["markup"]);
                } else {
                    $out_price = RESTEL_ZERO;
                }
            } else {
                $datas["collect"] = $rest["price_two"]; //首费
                $datas["markup"] = $rest["markup_two"]; //续费
                if(intval($string_number[$rest_number]) > RESTEL_ZERO){
                    $out_price = sprintf("%.2f",$datas["collect"] + (intval($string_number[$rest_number]) - RESTEL_ONE) * $datas["markup"]);
                } else {
                    $out_price = RESTEL_ZERO;
                }
            }
        } else {
            $out_price = RESTEL_ZERO;
        }
        return $out_price;

    }
}
