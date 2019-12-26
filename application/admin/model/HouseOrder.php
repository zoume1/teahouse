<?php
namespace app\admin\model;

use think\Model;
use app\admin\model\Member;
use app\admin\model\Goods;
use app\admin\model\AaccompanyShare;
use app\admin\model\AccompanyCode;
use app\index\controller\Order;


class HouseOrder extends Model
{
    protected $name = "house_order";
 
    /**
     * 获取存茶订单详情
     * @param $parts_order_number
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getHouseOrder($order_id)
    {
        $data = self::get($order_id);
        return $data ? $data : false;

    }


    /**
     * 添加赠茶订单
     * @param $data 赠茶数据
     * @member_id   会员id
     * @return bool
     * @throws \think\exception\DbException
     */
    public  function geAddHouseOrder($data,$member_id,$code_id)
    {
        $time = date("Y-m-d", time());
        $v = explode('-', $time);
        $time_second = date("H:i:s", time());
        $vs = explode(':', $time_second);
        $parts_order_number = "ZS" . $v[0] . $v[1] . $v[2] . $vs[0] . $vs[1] . $vs[2] . ($member_id + 1001); //订单编号
        //查询商品详情
        $goods_data = (new Goods())->where('id','=',$data['goods_id'])->find();
        $member = Member::detail($member_id);

        $key = array_search($goods_data['monomer'], explode(',',$goods_data['unit']));
        //先判断有多少位数量等级
        $store_number = (new Order())->unit_calculate($goods_data['unit'], $goods_data['num'], $key, $data['single_number']);
        $this->startTrans();
        try {
            $add_ata = [
                'parts_order_number' => $parts_order_number,
                'parts_goods_name' => $goods_data['goods_name'],    //商品名
                'goods_image' => $goods_data['goods_show_image'],   //商品图片
                'goods_standard' => $goods_data['goods_standard'], //商品规格
                'goods_describe' => $goods_data['goods_describe'], //分享描述
                'order_quantity' => $data['single_number'], //送存数量
                'member_id' => $member_id,
                'user_account_name' => $member['member_name'],
                'user_phone_number' => $member['member_phone_num'],
                'order_create_time' => time(),
                'goods_id' => $data['goods_id'],
                'store_house_id' => $data['store_house_id'],
                'store_name' => $data['store_house_name'],
                'store_unit' => $goods_data['monomer'],
                'store_number' => $store_number,
                'end_time' => $data['end_time'],
                'status' => 4, //已入仓
                'coupon_type' => 1, //普通商品
                'store_id' => $data['store_id'],
                'pay_time' => time(),
                'accompany_code_id' => $code_id
            ];
            $rest = $this->save($add_ata);
            if($rest){
                $AaccompanyShare = [
                    'accompany_code_id' => $code_id,
                    'time' => time(),
                    'member_id' => $member_id,
                    'is_status' => 1,

                ];
                (new AaccompanyShare())->share_add($AaccompanyShare);
                switch($data['choose_status']){
                    case 1:
                        //全向营销则增加扫描次数
                        $AccompanyCode = new AccompanyCode();
                        $res = $AccompanyCode->where('id','=',$code_id)->setInc('scan_number', 1);
                        break;
                    default:
                        break;

                }
            } else {
                throw new Exception('添加失败');
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            halt($this->error);
            $this->rollback();
            return false;
        }

    }


 



 
}