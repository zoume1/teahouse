<?php

namespace app\api\controller;

use think\Controller;
use app\api\model\WxappPrepayId as WxappPrepayIdModel;
use app\api\controller\WxTplMsg;
use app\admin\model\Member;

/**
 * 消息通知服务
 * Class Message
 * @package app\api\controller
 */
class Message extends Controller
{
    /**
     * 订单支付成功后通知
     * @param \think\Model $order
     * @param int $orderType 订单类型 (10商城订单 20拼团订单)
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function payment($order, $orderType)
    {
        // 1. 微信模板消息
        // $template = SettingModel::getItem('tplMsg', $order['wxapp_id'])['payment'];
        $template['template_id'] = 'mPROnmLqYAKicU8JgRYU33JrSiKG7am9lsaQGosvcCg'; //测试模板id
        // 获取 prepay_id
        $prepayId = $this->getPrepayId($order['id'], $orderType);
        $member = Member::detail(['member_id' => $order['member_id']]);

        // 页面链接
        $urls = 'pages/diy/index/index';

        // 发送模板消息
        $status = $this->sendTemplateMessage($order['store_id'], [
            'touser' => $member['member_openid'],
            'template_id' => $template['template_id'],
            'page' => $urls,
            'form_id' => $prepayId['prepay_id'],
            'data' => [
                // 支付时间
                'keyword1' => date('Y-m-d H:i:s', $order['pay_time']),
                // 订单编号
                'keyword2' => $order['parts_order_number'],
                // 订单金额
                'keyword3' => $order['order_real_pay'],
                // 商品名称
                'keyword4' => $this->formatGoodsName($order['parts_goods_name']),
            ]
        ]);
        // 标记已使用次数
        $status === true && $prepayId->updateUsedTimes();
        return $status;
        
        // // 2. 商家短信通知
        // $smsConfig = SettingModel::getItem('sms', $order['wxapp_id']);
        // $SmsDriver = new SmsDriver($smsConfig);
        // return $SmsDriver->sendSms('order_pay', ['order_no' => $order['order_no']]);
    }

    /**
     * 后台发货通知
     * @param \think\Model $order
     * @param int $orderType 订单类型 (10商城订单 20拼团订单)
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delivery($order, $orderType = OrderTypeEnum::MASTER)
    {
        // 获取 prepay_id
        $prepayId = $this->getPrepayId($order['order_id'], $orderType);
        // 微信模板消息
        $template = SettingModel::getItem('tplMsg', $order['wxapp_id'])['delivery'];
        if (!$template['is_enable'] || empty($template['template_id'])) {
            return false;
        }
        // 页面链接
        $urls = [
            OrderTypeEnum::MASTER => 'pages/order/detail',
            OrderTypeEnum::SHARING => 'pages/sharing/order/detail/detail',
        ];
        // 发送模板消息
        $status = $this->sendTemplateMessage($order['wxapp_id'], [
            'touser' => $order['user']['open_id'],
            'template_id' => $template['template_id'],
            'page' => $urls[$orderType] . '?order_id=' . $order['order_id'],
            'form_id' => $prepayId['prepay_id'],
            'data' => [
                // 订单编号
                'keyword1' => $order['order_no'],
                // 商品信息
                'keyword2' => $this->formatGoodsName($order['goods']),
                // 收货人
                'keyword3' => $order['address']['name'],
                // 收货地址
                'keyword4' => implode('', $order['address']['region']) . $order['address']['detail'],
                // 物流公司
                'keyword5' => $order['express']['express_name'],
                // 物流单号
                'keyword6' => $order['express_no'],
            ]
        ]);
        // 标记已使用次数
        $status === true && $prepayId->updateUsedTimes();
        return $status;
    }

    /**
     * 后台售后单状态通知
     * @param \think\Model $refund
     * @param $order_no
     * @param int $orderType 订单类型 (10商城订单 20拼团订单)
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function refund($refund, $order_no, $orderType = OrderTypeEnum::MASTER)
    {
        // 获取formid
        if (!$formId = FormidModel::getAvailable($refund['user_id'])) {
            return false;
        }
        // 微信模板消息
        $template = SettingModel::getItem('tplMsg', $refund['wxapp_id'])['refund'];
        if (!$template['is_enable'] || empty($template['template_id'])) {
            return false;
        }
        // 页面链接
        $urls = [
            OrderTypeEnum::MASTER => 'pages/order/refund/index',
            OrderTypeEnum::SHARING => 'pages/sharing/order/refund/index',
        ];
        // 发送模板消息
        $status = $this->sendTemplateMessage($refund['wxapp_id'], [
            'touser' => $refund['user']['open_id'],
            'template_id' => $template['template_id'],
            'page' => $urls[$orderType],
            'form_id' => $formId['form_id'],
            'data' => [
                // 售后类型
                'keyword1' => $refund['type']['text'],
                // 状态
                'keyword2' => $refund['status']['text'],
                // 订单号
                'keyword3' => $order_no,
                // 商品名称
                'keyword4' => $refund['order_goods']['goods_name'],
                // 申请时间
                'keyword5' => $refund['create_time'],
                // 申请原因
                'keyword6' => $refund['apply_desc'],
            ]
        ]);
        // 标记formid已使用
        $formId->setIsUsed();
        return $status;
    }

    /**
     * 拼团拼单状态通知
     * @param \app\common\model\sharing\Active $active
     * @param string $status_text
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function sharingActive($active, $status_text)
    {
        // 微信模板消息
        $config = SharingSettingModel::getItem('basic', $active['wxapp_id']);
        if (empty($config['tpl_msg_id'])) {
            return false;
        }
        foreach ($active['users'] as $item) {
            // 获取formid
            if (!$formId = FormidModel::getAvailable($item['user']['user_id'])) {
                continue;
            }
            // 发送模板消息
            $this->sendTemplateMessage($active['wxapp_id'], [
                'touser' => $item['user']['open_id'],
                'template_id' => $config['tpl_msg_id'],
                'page' => 'pages/sharing/active/index?active_id=' . $active['active_id'],
                'form_id' => $formId['form_id'],
                'data' => [
                    // 订单编号
                    'keyword1' => $item['sharing_order']['order_no'],
                    // 商品名称
                    'keyword2' => $active['goods']['goods_name'],
                    // 拼团价格
                    'keyword3' => $item['sharing_order']['pay_price'],
                    // 拼团人数
                    'keyword4' => $active['people'],
                    // 拼团时间
                    'keyword5' => $item['create_time'],
                    // 拼团结果
                    'keyword6' => $status_text,
                ]
            ]);
            // 标记formid已使用
            $formId->setIsUsed();
        }
        return true;
    }

    /**
     * 分销商提现审核通知
     * @param \app\common\model\dealer\Withdraw $withdraw
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function withdraw($withdraw)
    {
        // 模板消息id
        $template = DealerSettingModel::getItem('template_msg', $withdraw['wxapp_id']);
        if (empty($template['withdraw_tpl'])) {
            return false;
        }
        // 获取formid
        if (!$formId = FormidModel::getAvailable($withdraw['user_id'])) {
            return false;
        }
        // 获取用户信息
        $user = UserModel::detail($withdraw['user_id']);
        // 发送模板消息
        $remark = '无';
        if ($withdraw['apply_status'] == 30) {
            $remark = $withdraw['reject_reason'];
        }
        $status = $this->sendTemplateMessage($withdraw['wxapp_id'], [
            'touser' => $user['open_id'],
            'template_id' => $template['withdraw_tpl'],
            'page' => 'pages/dealer/withdraw/list/list',
            'form_id' => $formId['form_id'],
            'data' => [
                // 提现时间
                'keyword1' => $withdraw['create_time'],
                // 提现方式
                'keyword2' => $withdraw['pay_type']['text'],
                // 提现金额
                'keyword3' => $withdraw['money'],
                // 提现状态
                'keyword4' => $withdraw->applyStatus[$withdraw['apply_status']],
                // 备注
                'keyword5' => $remark,
            ]
        ]);
        // 标记formid已使用
        $formId->setIsUsed();
        return $status;
    }

    /**
     * 分销商入驻审核通知
     * @param \app\common\model\dealer\Apply $dealer
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function dealer($dealer)
    {
        // 模板消息id
        $template = DealerSettingModel::getItem('template_msg', $dealer['wxapp_id']);
        if (empty($template['apply_tpl'])) {
            return false;
        }
        // 获取formid
        if (!$formId = FormidModel::getAvailable($dealer['user_id'])) {
            return false;
        }
        // 获取用户信息
        $user = UserModel::detail($dealer['user_id']);
        // 发送模板消息
        $remark = '分销商入驻审核通知';
        if ($dealer['apply_status'] == 30) {
            $remark .= "\n\n驳回原因：" . $dealer['reject_reason'];
        }
        $status = $this->sendTemplateMessage($dealer['wxapp_id'], [
            'touser' => $user['open_id'],
            'template_id' => $template['apply_tpl'],
            'page' => 'pages/dealer/index/index',
            'form_id' => $formId['form_id'],
            'data' => [
                // 申请时间
                'keyword1' => $dealer['apply_time'],
                // 审核状态
                'keyword2' => $dealer->applyStatus[$dealer['apply_status']],
                // 审核时间
                'keyword3' => $dealer['audit_time'],
                // 备注信息
                'keyword4' => $remark,
            ]
        ]);
        // 标记formid已使用
        $formId->setIsUsed();
        return $status;
    }

    /**
     * 发送模板消息
     * @param $wxapp_id
     * @param $params
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    private function sendTemplateMessage($store_id, $params)
    {
        // 微信模板消息
        $wxConfig = Db::table('applet')->where('id','=',$store_id)->find();
        $WxTplMsg = new WxTplMsg($wxConfig['appID'], $wxConfig['appSecret']);
        return $WxTplMsg->sendTemplateMessage($params);
    }

    /**
     * 获取小程序prepay_id记录
     * @param $order_id
     * @param int $orderType 订单类型 (10普通订单 20众筹订单)
     * @return WxappPrepayIdModel|array|false|string|\think\Model
     */
    private function getPrepayId($order_id, $orderType )
    {
        return WxappPrepayIdModel::detail($order_id, $orderType);
    }

    /**
     * 格式化商品名称
     * @param $goodsData
     * @return string
     */
    private function formatGoodsName($goodsData)
    {
        $str = '';
        foreach ($goodsData as $goods) $str .= $goods['goods_name'] . ' ';
        return $str;
    }

}