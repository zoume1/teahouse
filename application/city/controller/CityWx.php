<?php

/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/27
 * Time: 16:17
 */

namespace app\city\controller;

use app\rec\model\Wechat as WechatAll;
use think\Request;
use think\Validate;
use think\Controller;
use think\Config;
use think\Db;
use app\admin\model\Store;
use app\city\model\CityComment;
use app\city\model\CityCopartner;
use app\rec\model\CityDetail;
use app\city\model\CityDetail as CityDetaile;


//微信授权登录 获取个人信息
class CityWx extends Controller
{
    //https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxf120ba19ce55a392&redirect_uri=xxx&response_type=code&scope=xxx&state=STATE#wechat_redirect
    //微信公众平台信息（appid/secret）
    protected $sj_appid = 'wxf120ba19ce55a392';
    protected $sj_secret = '06c0107cff1e3f5fe6c2eb039ac2d0b7';

    //手机端跳转首页
    protected $app_index = 'app/wechat/user/hhr-index.html';
    //手机端跳转绑定账号页面
    protected $app_wx = 'app/wechat/user/hhr-login.html';

    //手机端跳转支付页面
    protected $app_wxpay = 'app/wechat/user/hhr-wxpay.html';

    /**
     * @function 手机端网页微信登录授权（微信公众平台微信登录授权）
     */
    public function city_accredit()
    {

        $redirect_uri = Config::get('web_url') . 'city/city_wx_code';
        $redirect_uri = urlencode($redirect_uri);
        //微信公众平台appid
        $appid = $this->sj_appid;

        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';

        header('Location:' . $url);
    }
    /**
     * 用户登录直接跳转
     * @function 获取openid
     */
    public function city_wx_code()
    {
        $request = Request::instance();
        $param = $request->param();
        if (empty($param['code'])) {
            return returnJson(0, 'code参数为空');
            exit;
        }
        //微信信息调用model方法
        $code = new \app\rec\model\Wechat();
        $res = $code->WxOpenid($param['code']);
        $openid_name = db('city_copartner')->where(array('openid' => $res['openid']))->field('user_id,phone_number,judge_status')->find();
        //open_id
        if ($openid_name) {
            //更新用户信息
            db('city_copartner')->where(array('openid' => $res['openid']))
                ->update([
                    'weixin_head' => $res['headimgurl'],
                    'update_time' => time()
                ]);
            if ($openid_name['judge_status'] === 0) {
                // //跳转支付页面
                // $url = Config::get('web_url').$this->app_wxpay.'?openid='.$res['openid'];
                // header('Location:'.$url);
                //跳转绑定账号页面
                $url = Config::get('web_url') . $this->app_wx . '?openid=' . $res['openid'];
                header('Location:' . $url);
            } else {
                //跳转首页
                $url = Config::get('web_url') . $this->app_index . '?user_id=' . $openid_name['user_id'];
                header('Location:' . $url);
            }
        } else {
            //跳转绑定账号页面
            $url = Config::get('web_url') . $this->app_wx . '?openid=' . $res['openid'];
            header('Location:' . $url);
        }
    }

    /**
     *   公众号店铺评论城市合伙人
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function WeiChatStoreComment(Request $request)
    {

        if ($request->isPost()) {
            $store = Request::instance()->param();
            $validate     = new Validate([
                ['user_id', 'require', '账号id不能为空'],
                ['city_user_id', 'require', '合伙人id不能为空'],
                ['grade', 'require', '评论级别不能为空']

            ]);
            //验证部分数据合法性
            if (!$validate->check($store)) {
                return jsonError($validate->getError());
            }
            $bool_status = CityComment::proving_comment($store);
            if (!$bool_status) {
                return jsonError('您这个月已经对合伙人进行过评价，请下个月再次评价');
            }
            $data = [
                'user_id' => $store['user_id'],
                'create_time' => time(),
                'end_time' => strtotime(date('Y-m-d H:i:s', strtotime('+1month'))),
                'city_user_id' => $store['city_user_id'],
                'grade' => $store['grade']
            ];
            $bool = CityComment::store_comment_add($data);
            if ($bool) {
                return jsonSuccess('评价成功');
            } else {
                return jsonError('评价失败');
            }
        }
    }


    /**
     *   公众号店铺所有城市合伙人
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function allCityCobber(Request $request)
    {

        if ($request->isPost()) {
            $store = Request::instance()->param();
            $validate     = new Validate([
                ['user_id', 'require', 'user_id不能为空'],

            ]);
            //验证部分数据合法性
            if (!$validate->check($store)) {
                return jsonError($validate->getError());
            }
            $rest = Db::table('tb_pc_user')
                ->field("tb_city_copartner.user_name,tb_city_copartner.user_id as city_user_id,tb_city_copartner.phone_number,tb_city_copartner.city_address")
                ->join("tb_store", "tb_pc_user.id= tb_store.user_id", 'left')
                ->join("tb_city_detail", "tb_store.id= tb_city_detail.store_id", 'left')
                ->join("tb_city_copartner", "tb_city_detail.city_user_id= tb_city_copartner.user_id", 'left')
                ->where("tb_city_detail.city_user_id", '>', 0)
                ->where("tb_pc_user.id", '=', $store['user_id'])
                ->select();

            if (!$rest) {
                return jsonError('您的店铺暂时没有城市合伙人入驻');
            }

            return jsonSuccess("发送成功", $rest);
        }
    }






}
