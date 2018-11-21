<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21 0021
 * Time: 14:35
 */
namespace  app\index\controller;
use think\Controller;
use think\Request;
use think\Db;

class My extends Controller
{
    public function my_index(Request $request){
        if($request->isPost()){
            $post_open_id = $request->only(['open_id'])['open_id'];
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:返回给小程序的会员等级数据
     **************************************
     * @param Request $request
     */
    public function show_grade(Request $request)
    {
        if ($request->isPost()) {
            $post_open_id = $request->only(['open_id'])['open_id'];
//            $post_open_id =session('member_openid');
            if (!empty($post_open_id)) {
                $member_information = Db::name('member')->where('member_openid', $post_open_id)->find();
                $data = [];
                $data['member_id'] = $member_information['member_id']; //会员码
                $data['member_grade_create_time'] = date('Y-m-d H:i:s', $member_information['member_grade_create_time']); // 创建等级的时间
                $domain_name = 'http://teahouse.siring.com.cn';//域名
                $member_id = $member_information['member_id'];   //所登录的id
                $reg = 'reg';  //注册地址
                $share_url = $domain_name . "/" . $reg . "/" . $member_id;
                $data['share_url'] = $share_url; //生成的二维码
                $data['member_grade_name'] =$member_information['member_grade_name'];
                $data['member_grade_id'] =$member_information['member_grade_id'];
                $member_data = Db::name('member_grade')->where('introduction_display', 1)->select();
                foreach ($member_data as $k => $v) {
                    $grade['order_number'] =$k;                 //排序号
                    $grade['member_grade_id'] = $v['member_grade_id'];           //会员等级ID
                    $grade['member_grade_name'] = $v['member_grade_name'];       //等级名称
                    $grade['member_grade_img'] = $v['member_grade_img'];     //等级图标
                    $grade['member_finite_period'] = $v['member_finite_period'];//有效期（年）
                    $grade['first_year_pay_full'] = $v['first_year_pay_full'];  //首年消费满（万元
                    $grade['recharge_member_send'] = $v['recharge_member_send']; //充值送会员（万元）
                    $grade['recharge_integral_send'] = $v['recharge_integral_send']; //充值送积分
                    $grade['member_background_color'] =$v['member_background_color']; //颜色
                }
                $user['member_grade'] = $member_data;//会员等级信息
                $user['information'] = $data;        //用户的所有信息
                if (!empty($user)) {
                    return ajax_success('成功返回数据', $user);
                } else {
                    return ajax_error('没有数据', ['status' => 0]);
                }
            }
        }

    }
}