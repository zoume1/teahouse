<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;


/**
 * [前端路由]
 *
 */
Route::group("",[

    /*首页*/
    "weixintest"=>"index/WxTest/index", //微信扫码支付宝扫码测试
    "qrcode"=>"index/WxTest/qrcode",
    "qrcode_create"=>"index/WxTest/qrcode_create",
    "/$"=>"index/index/index",
    /*TODO:PC端注册登录开始*/
    "PcsendMobileCode"=>"index/Register/PcsendMobileCode",//PC端注册验证码
    "doRegByPhone"=>"index/Register/doRegByPhone",//PC端注册操作
    "dolog"=>"index/Login/dolog",//登录操作
    "isLogin"=>"index/Login/isLogin",//判断是否登录
    "logout"=>"index/Login/logout",//退出登录操作
    "find_password_by_phone"=>"index/Findpwd/find_password_by_phone",//找回密码
    "sendMobileCodeByPhone"=>"index/Findpwd/sendMobileCodeByPhone",//找回密码验证码
    "sendIdentiFyingCode"=>"index/Findpwd/sendIdentiFyingCode",//发送手机验证码
    "sendMobileCodeByPh"=>"index/Findpwd/sendMobileCodeByPh",//修改密码验证码
    "update_password"=>"index/Findpwd/update_password",//修改密码操作
    "new_phone_update"=>"index/Findpwd/new_phone_update",//修改手机操作
    /*TODO:PC端注册登录结束*/
    /*TODO:PC端店铺开始*/
    "store_add"=>"index/Store/store_add",//创建店铺
    "store_return"=>"index/Store/store_return",//店铺信息返回
    "store_edit"=>"index/Store/store_edit",//店铺信息编辑
    "store_give_up"=>"index/Store/store_give_up",//店铺放弃再次申请
    "store_goto_admin"=>"index/Store/store_goto_admin",//店铺跳转后台
    "store_all_data"=>"index/Store/store_all_data",//所有店铺信息返回
    "store_logo_index"=>"index/Store/store_logo_index",//店铺loge
    /*TODO:PC端店铺结束*/


    "index_home"=>"index/index/home",
    "tea_factory"=>"index/index/tea_factory", //茶厂 
    "tea_merchant"=>"index/index/tea_merchant", //茶商
    "tea_moment"=>"index/index/tea_moment", //茶圈
    "consumer"=>"index/index/consumer", //用户
    "wisdom"=>"index/index/wisdom", //智慧茶仓
    "partner"=>"index/index/partner", //招募合伙人
    "about"=>"index/index/about", //关于我们
    "sign_up"=>"index/index/sign_up", //注册
    "sign_in"=>"index/index/sign_in", //登录
    "forget_pw"=>"index/index/forget_pw", //登录
    "my_shop"=>"index/index/my_shop", //我的店铺
    "city_apply"=>"index/index/city_apply", //申请城市合伙人
    "city_login"=>"index/index/city_login", //合伙人后台登陆
    "get_wenshidu"=>"index/index/get_wenshidu", //获取温湿度
    "get_bank_list"=>"index/My/get_bank_list", //获取用户的银行卡列表



    /*TODO：start*/
    /*登录授权*/
    "wechatlogin"=>"index/Login/wechatlogin",  //登录授权
    "my_show_grade"=>"index/My/show_grade",  //会员等级
    "my_qrcode"=>"index/My/qrcode",  //会员二维码
    "my_index"=>"index/My/my_index",  //我的页面
    "wx_index"=>"index/Pay/index",//小程序支付（活动）
    "wx_order_index"=>"index/Pay/order_index",//小程序普通商品订单支付
    "crowd_order"=>"index/Pay/crowd_order",//小程序众筹订单支付
    "wx_recharge_pay"=>"index/Pay/recharge_pay",//小程序充值支付
    "reward_pay"=>"index/Pay/reward_pay",   //众筹商品打赏支付
    "series_pay"=>"index/Pay/series_pay",   //仓库订单续费支付
    "setContinuAtion"=>"index/Pay/setContinuAtion",  //店铺小程序仓库订单出仓
     "notify"=>"index/order/notify",//小程序支付回调（活动）
     "order_notify"=>"index/order/order_notify",//小程序订单支付回调
     "recharge_notify"=>"index/order/recharge_notify",//小程序充值支付回调
     "member_notify"=>"index/order/member_notify",//小程序会员升级充值支付回调
     "reward_notify"=>"index/order/reward_notify",//打赏订单支付成功回来修改状态
     "set_meal_notify"=>"index/AdminWx/set_meal_notify",//后台套餐订购订单微信扫码支付回调
     "set_meal_notify_alipay"=>"index/AdminWx/set_meal_notify_alipay",//后台套餐订购订单支付宝扫码支付回调
     "set_meal_notify2"=>"index/AdminWx/set_meal_notify2",//后台资金管理微信充值扫码支付回调
     "set_meal_notify_alipay2"=>"index/AdminWx/set_meal_notify_alipay2",//后台资金管理微信充值支付宝扫码支付回调
     "series_notify"=>"index/order/series_notify",//茶仓订单续费支付回调
     "continuAtion_notify"=>"index/order/continuAtion_notify",//茶仓订单出仓支付回调
     "analyse_meal_notify"=>"index/AdminWx/analyse_meal_notify",//增值服务订购订单微信扫码支付回调
     "analyse_meal_notify_alipay"=>"index/AdminWx/analyse_meal_notify_alipay",//增值服务订单支付宝扫码支付回调
     "city_meal_notify"=>"index/AdminWx/city_meal_notify",//城市合伙人订单微信支付回调
     "city_meal_notify_alipay"=>"index/AdminWx/city_meal_notify_alipay",//城市合伙人订单支付宝支付回调


    /*TODO:end*/

    /*TODO:地址管理开始*/
    "member_address_information"=>"index/Address/member_address_information", //所有地址列表数据返回
    "member_address_adds"=>"index/Address/member_address_adds", //收货地址添加
    "member_address_del"=>"index/Address/member_address_del", //收货地址删除
    "member_address_edit_information"=>"index/Address/member_address_edit_information", //编辑地址数据返回
    "member_address_edit"=>"index/Address/member_address_edit", //收货地址编辑操作
    "member_address_status"=>"index/Address/member_address_status", //设置默认地址
    "member_default_address_return"=>"index/Address/member_default_address_return", //购买页面默认地址返回或者选择其他地址

    /*TODO:地址管理结束*/
    /*TODO:到店自提地址开始*/
    "delivery_address_return"=>"index/DeliveryAddress/delivery_address_return",//下单页面点击到店自提数据返回
    "delivery_address_all_return"=>"index/DeliveryAddress/delivery_address_all_return",//下单页面到店自提所有数据返回
    /*TODO:到店自提地址结束*/


    /*TODO:订单开始*/
    "order_return"=>"index/Order/order_return",//立即购买过去购物清单数据返回
    "order_places"=>"index/Order/order_places",//下订单----商品详情下单
    "order_place"=>"index/Order/order_place",//下订单
    "order_place_by_shopping"=>"index/Order/order_place_by_shopping",//购物车下订单
    "order_place_by_shoppings"=>"index/Order/order_place_by_shoppings",//购物车下订单----购物车
    "order_detail"=>"index/Order/order_detail",//订单详情（未需要）
    "order_detail_cancel"=>"index/Order/order_detail_cancel",//未付款判断时间是否过了订单设置的时间，过了则进行自动关闭（优惠券未实现）
    "ios_api_order_all"=>"index/Order/ios_api_order_all",//我的所有订单
    "ios_api_order_wait_pay"=>"index/Order/ios_api_order_wait_pay",//我的待支付订单
    "ios_api_order_wait_send"=>"index/Order/ios_api_order_wait_send",//我的待发货订单
    "ios_api_order_wait_deliver"=>"index/Order/ios_api_order_wait_deliver",//我的待收货订单
    "ios_api_order_wait_evaluate"=>"index/Order/ios_api_order_wait_evaluate",//我待评价订单
    "ios_api_order_collect_goods"=>"index/Order/ios_api_order_collect_goods",//买家确认收货
    "order_details"=>"index/Order/order_details",//订单详情
    "ios_api_order_del"=>"index/Order/ios_api_order_del",//买家删除订单接口(ajax)
    "ios_api_order_no_pay_cancel"=>"index/Order/ios_api_order_no_pay_cancel",//订单状态修改（未付款买家取消订单）
    "tacitly_approve"=>"index/Order/tacitly_approve",//存茶默认收货地址
    "tacitly_list"=>"index/Order/tacitly_list",//存茶默认收货地址列表
    "transportation"=>"index/Order/transportation",//对应模板
    "del_order"=>"index/Order/del_order",          //立即支付--取消支付
    "get_member_banlance"=>"index/Order/get_member_banlance",          //立即支付--取消支付
    /*TODO:订单结束*/

    /*TODO:快递100物流信息开始*/
    "express_hundred"=>"index/Api/express_hundred",//快递100实时物流
    /*TODO:快递100物流信息结束*/


    /*TODO:售后处理开始*/
    "after_sale_all"=>"index/AfterSale/after_sale_all",//售后全部订单
    "after_sale_applying"=>"index/AfterSale/after_sale_applying",//售后订单（申请中）
    "after_sale_rescinded"=>"index/AfterSale/after_sale_rescinded",//售后订单（已撤销）
    "after_sale_completed"=>"index/AfterSale/after_sale_completed",//售后订单（已完成）

    "after_sale_upload"=>"index/AfterSale/after_sale_upload",//上传的图片，注意：小程序只能一张张上传
    "after_sale_images_del"=>"index/AfterSale/after_sale_images_del",//售后图片删除（取消申请，和修改申请进行删除）
    "apply_after_sale"=>"index/AfterSale/apply_after_sale",//用户申请售后
    "update_time_automatic"=>"index/AfterSale/update_time_automatic",//时间倒计时自动确认
    "after_sale_is_set"=>"index/AfterSale/after_sale_is_set",//判断用户是否申请过该订单售后
    "add_express_information"=>"index/AfterSale/add_express_information",//售后添加物流信息
    "after_sale_order_return"=>"index/AfterSale/after_sale_order_return",//售后订单信息返回（未用到）
    "after_sale_information_return"=>"index/AfterSale/after_sale_information_return",//退货信息返回
    "buyer_replay"=>"index/AfterSale/buyer_replay",//售后买家回复
    "update_application"=>"index/AfterSale/update_application",//售后修改申请
    "cancellation_of_application"=>"index/AfterSale/cancellation_of_application",//售后撤销售后申请
    "business_address"=>"index/AfterSale/business_address",//售后商家寄还地址返回
    "order_refund"=>"index/Api/order_refund",//微信退款
    "sendMoney"=>"index/Api/sendMoney",//TODO：提现测试
    /*TODO:售后处理结束*/

    /*TODO:订单用户提醒发货开始*/
    "option_add"=>"index/Notification/option_add",//用户提醒
    /*TODO:订单用户提醒发货结束*/
    /*TODO:订单评价开始*/
    "order_evaluate_index"=>"index/Evaluate/order_evaluate_index",//评价数据返回
    "order_evaluate_images_add"=>"index/Evaluate/order_evaluate_images_add",//评价图片添加
    "order_evaluate_images_del"=>"index/Evaluate/order_evaluate_images_del",//初始订单评价图片删除(就是点击返回键)
    "order_evaluate_add"=>"index/Evaluate/order_evaluate_add",//评价添加
    /*TODO:订单评价结束*/

    /*TODO:购物车开始*/
    "shopping_index"=>"index/Shopping/shopping_index",//购物车列表信息返回
     "get_goods_id_to_shopping"=>"index/Shopping/get_goods_id_to_shopping",//获取商品id 存入购物车
     "shopping_information_add"=>"index/Shopping/shopping_information_add",//购物车添加商品数量
     "shopping_information_del"=>"index/Shopping/shopping_information_del",//购物车减少商品数量
     "shopping_del"=>"index/Shopping/shopping_del",//购物车删除
     "shopping_numbers"=>"index/Shopping/shopping_numbers",//购物车数量返回

    /*TODO:购物车结束*/


    /*茶圈*/
    "teacenter_data"=>"index/TeaCenter/teacenter_data",          //茶圈父级显示
    "teacenter_display"=>"index/TeaCenter/teacenter_display",    //茶圈分类显示
    "teacenter_activity"=>"index/TeaCenter/teacenter_activity",  //茶圈活动页面显示
    "teacenter_detailed"=>"index/TeaCenter/teacenter_detailed",  //茶圈活动详细显示
    "teacenter_alls"=>"index/TeaCenter/teacenter_alls",          //茶圈所有活动
    "activity_status"=>"index/TeaCenter/activity_status",        //茶圈活动是否报名
    "teacenter_recommend"=>"index/TeaCenter/recommend",          //茶圈首页推荐活动
    "activity_order"=>"index/TeaCenter/activity_order",          //茶圈订单
    "activity_order_delete"=>"index/TeaCenter/activity_order_delete", //茶圈取消订单
    "teacenter_comment"=>"index/TeaCenter/teacenter_comment",         //茶圈活动评论存储
    "teacenter_comment_show"=>"index/TeaCenter/teacenter_comment_show", //茶圈活动评论显示
    "teacenter_comment_updata"=>"index/TeaCenter/teacenter_comment_updata", //茶圈活动评论点赞
    "tacitly_adress"=>"index/TeaCenter/tacitly_adress", //收货地址详情
    "day_test"=>"index/TeaCenter/day_test", //时间测试
    


    /*商品管理*/
    "commodity_index"=>"index/Commodity/commodity_index",        //商品分类
    "commodity_list"=>"index/Commodity/commodity_list",          //商品列表
    "commodity_detail"=>"index/Commodity/commodity_detail",      //商品详情
    "commodity_recommend"=>"index/Commodity/commodity_recommend",//商品首页推荐
    "approve_list"=>"index/Commodity/approve_list",              //默认自提地址列表
    "approve_detailed"=>"index/Commodity/approve_detailed",      //选择自提地址详情
    "approve_address"=>"index/Commodity/approve_address",        //默认上门自提地址
    "getSearchGood"=>"index/Commodity/getSearchGood",            //小程序前端搜索框（商品）



    "get_coinquotation"=>"index/Commodity/get_coinquotation",        //默认上门自提地址

    /*优惠券*/
    "coupon_untapped"=>"index/Coupon/coupon_untapped",        //未使用优惠券显示
    "coupon_user"=>"index/Coupon/coupon_user",                //已使用优惠券显示
    "coupon_time"=>"index/Coupon/coupon_time",                //过期优惠券显示
    "coupon_goods"=>"index/Coupon/coupon_goods",              //优惠券使用商品
    "coupon_appropriated"=>"index/Coupon/coupon_appropriated",//商品下单适用优惠券
    "coupon_minute"=>"index/Coupon/coupon_minute",            //优惠券显示
    "limitations_show"=>"index/Coupon/limitations_show",      //判断该商品是否限时限购
    "limitations"=>"index/Coupon/limitations",                //判断用户是否能购买商品
    "coupon_search2"=>"admin/Limitations/coupon_search2",     //判断用户是否能购买商品
    "limit_search"=>"admin/Limitations/limit_search",         //判断用户是否能购买商品
    
    /*积分商城*/
    "bonus_index"=>"index/Coupon/bonus_index",           //积分商城显示
    "bonus_detailed"=>"index/Coupon/bonus_detailed",     //积分商城详细显示
    "integrals"=>"index/Coupon/integrals",               //积分流水显示
    "order_integaral"=>"index/Coupon/order_integaral",   //积分商城下单 
    "integrals_detail"=>"index/Coupon/integrals_detail", //积分商城订单详情
    "integaral_list"=>"index/Coupon/integaral_list",     //积分商城所有订单
    "integaral_search"=>"index/Coupon/integaral_search", //积分记录搜索
    "integaral_delivered"=>"index/Coupon/integaral_delivered",     //积分商城待发货定单
    "integaral_collections"=>"index/Coupon/integaral_collections", //积分商城待收货订单
    "take_delivery"=>"index/Coupon/take_delivery",                 //积分订单确认收货
    "attention_to"=>"index/Coupon/attention_to",                   //积分订单提醒发货



    /*TODO:身份证绑定开始*/
    "id_card_return"=>"index/Owner/id_card_return",//身份证数据返回
    "id_card_add"=>"index/Owner/id_card_add",//身份证绑定
    "id_card_edit"=>"index/Owner/id_card_edit",//身份证修改
    /*TODO:身份证绑定结束*/

    /*TODO:银行卡管理开始*/
    "bank_bingding"=>"index/Owner/bank_bingding",//银行卡数据返回
    "bank_bingding_add"=>"index/Owner/bank_bingding_add",//银行卡银行卡添加
    "bank_bingding_update_return"=>"index/Owner/bank_bingding_update_return",//银行卡银行卡修改数据返回
    "bank_bingding_update"=>"index/Owner/bank_bingding_update",//银行卡银行卡编辑
    "bank_binding_status"=>"index/Owner/bank_binding_status",///银行卡银行卡设置为默认
    "bank_binding_del"=>"index/Owner/bank_binding_del",///银行卡银行卡删除
    /*TODO:银行卡管理结束*/

    /*TODO:设置支付密码开始*/
    "pay_password_add" =>"index/PassWord/pay_password_add",//支付密码添加编辑
    "pay_password_return" =>"index/PassWord/pay_password_return",//支付密码返回（判断是否存在支付密码）
    "balance_payment"=>"index/Balance/balance_payment",//商品余额支付
    "check_password"=>"index/Balance/check_password",//校验支付密码
    "remainder_pay"=>"index/Balance/remainder_pay",//众筹商品余额打赏支付
    "crowd_payment"=>"index/Balance/crowd_payment",//众筹商品订单余额支付
    /*TODO:设置支付密码结束*/

    /*TODO:充值提现开始*/
    "member_balance_return"=>"index/Wallet/member_balance_return",//账户余额和积分返回
    "recharge_setting_return"=>"index/Wallet/recharge_setting_return",//账户充值页面对应的储值规则数据返回
    "member_balance_recharge"=>"index/Wallet/member_balance_recharge",//账户余额充值
    "wallet_recharge_del"=>"index/wallet/recharge_del",     //钱包充值下单未付款自动关闭取消删除(ajax)
    "withdrawal_return"=>"index/wallet/withdrawal_return",  //钱包提现页面数据返回
    "withdrawal"=>"index/wallet/withdrawal",                //钱包银行卡提现
    "wechat_withdrawal"=>"index/wallet/wechat_withdrawal",  //钱包微信提现

    /*TODO:充值提现结束*/



    /*TODO:手机号头像昵称绑定开始*/
    "user_phone_return"=>"index/My/user_phone_return",//手机号绑定数据返回
    "user_phone_bingding"=>"index/My/user_phone_bingding",//修改支付密码
    "user_phone_bangding"=>"index/My/user_phone_bangding",//手机号绑定
    "user_phone_bingding_update"=>"index/My/user_phone_bingding_update",//手机号绑定修改
    "user_name_return"=>"index/My/user_name_return", //用户昵称绑定数据返回
    "user_name_update"=>"index/My/user_name_update", //用户昵称绑定修改
    "user_img_return"=>"index/My/user_img_return",  //用户头像绑定数据返回
    "user_img_update"=>"index/My/user_img_update",  //用户头像修改
    "consumerCode"=>"index/My/consumerCode", //用户会员码

    /*TODO:手机号头像昵称绑定结束*/

    /*TODO:我的账单开始*/
    "consume_index"=>"index/Bill/consume_index",//账单我的消费
    "ceshi12"=>"index/Bill/ceshi12",//账单我的消费
    "consume_search"=>"index/Bill/consume_search",//账单我的消费搜索
    /*TODO:我的账单结束*/

    /*TODO:短信验证开始*/
    "sendMobileCode"=>"index/MobileVerification/sendMobileCode",        //这是新绑定手机验证码验证
    "sendMobileCodeBank"=>"index/MobileVerification/sendMobileCodeBank",//这是银行卡绑定时需手机验证码验证
    "sendMobileCodePay"=>"index/MobileVerification/sendMobileCodePay",//忘记支付密码（找回密码验证码）
    "StoreMobile"=>"index/Register/StoreMobile",//店铺支付密码（找回密码验证码）

    /*TODO:短信验证结束*/


    /*测试接口*/
    "index_text"=>"index/index/text",

    /*常见问题*/
    "problem_data"=>"index/Manage/problem_data", //问题列表描述
    "problem_list"=>"index/Manage/problem_list", //问题列表
    "problem_show"=>"index/Manage/problem_show", //问题解答详情

    /*协议合同*/
    "agreement_contract"=>"index/Manage/agreement_contract",//协议合同列表
    "agreement_show"=>"index/Manage/agreement_show",        //协议合同详细
    
    /*消息提醒*/
    "message_reminder"=>"index/Manage/message_reminder",//消息提醒列表
    "message_show"=>"index/Manage/message_show",        //消息提醒详细
    

    /*关于我们*/
    "about_us"=>"index/Manage/about_us",//关于我们



    /*我的收藏*/
    "collect"=>"index/Manage/collect",               //添加茶圈收藏
    "collect_updata"=>"index/Manage/collect_updata", //取消茶圈收藏
    "collect_judge"=>"index/Manage/collect_judge",   //判断茶圈活动是否被收藏
    "enshrine_data"=>"index/Manage/enshrine_data",   //茶圈收藏列表
    "demand_collect"=>"index/Manage/demand_collect", //添加供求收藏
    "demand_data"=>"index/Manage/demand_data",       //供求收藏列表


    /*快递费用*/
    "express_charge"=>"index/Manage/express_charge",//快递费用结算


    /*众筹商品*/
    "crowd_index"=>"index/Crowd/crowd_index",               //众筹商品首页显示
    "crowd_now"=>"index/Crowd/crowd_now",                   //正在众筹商品
    "crowd_support"=>"index/Crowd/crowd_support",           //众筹商品去支持
    "crowd_period"=>"index/Crowd/crowd_period",             //往期众筹商品
    "crowd_reward"=>"index/Crowd/crowd_reward",             //众筹商品打赏订单
    "getaAnsporTation"=>"index/Crowd/getaAnsporTation",     //众筹商品下单运费
    "crowd_goods_timeout"=>"index/Crowd/crowd_goods_timeout",     //众筹商品--到期操作

    /*发票*/
    "bill"=>"index/Receipt/bill",                      //添加企业新户名
    "receipt_status"=>"index/Receipt/receipt_status",  //所有发票状态
    "people"=>"index/Receipt/people",                  //添加个人新户名
    "corporation"=>"index/Receipt/corporation",        //企业户名列表
    "individual"=>"index/Receipt/individual",          //个人户名列表
    "approve_corporation"=>"index/Receipt/approve_corporation",        //默认企业户名
    "approve_individual"=>"index/Receipt/approve_individual",          //默认户名
    "set_default"=>"index/Receipt/set_default",        //默认户名
    "bill_delete"=>"index/Receipt/bill_delete",        //删除默认户名
    "proportion"=>"index/Receipt/proportion",          //查询发票费率

    /*众筹订单*/
    "crowd_order_return"=>"index/Crowdfinancing/crowd_order_return",                         //立即购买过去清单数据返回
    "crowd_order_place"=>"index/Crowdfinancing/crowd_order_place",                           //提交订单
    "crowd_order_place_by_shoppings"=>"index/Crowdfinancing/crowd_order_place_by_shoppings", //购物车提交订单
    "crowd_detail_cancel"=>"index/Crowdfinancing/crowd_detail_cancel",                       //未付款判断时间是否过了订单设置的时间，过了则进行自动关闭（优惠券未实现）
    "crowd_order_all"=>"index/Crowdfinancing/crowd_order_all",                               //我的所有订单
    "crowd_wait_pay"=>"index/Crowdfinancing/crowd_wait_pay",                                 //我的待支付订单
    "crowd_wait_send"=>"index/Crowdfinancing/crowd_wait_send",                               //我的待发货订单
    "crowd_wait_deliver"=>"index/Crowdfinancing/crowd_wait_deliver",                         //我的待收货订单
    "crowd_wait_evaluate"=>"index/Crowdfinancing/crowd_wait_evaluate",                       //我待评价订单
    "crowd_collect_goods"=>"index/Crowdfinancing/crowd_collect_goods",                       //买家确认收货
    "crowd_order_details"=>"index/Crowdfinancing/crowd_order_details",                       //订单详情
    "crowd_order_del"=>"index/Crowdfinancing/crowd_order_del",                               //买家删除订单接口(ajax)
    "crowd_no_pay_cancel"=>"index/Crowdfinancing/crowd_no_pay_cancel",                       //订单状态修改（未付款买家取消订单）
    "crowd_order_notify"=>"index/Crowdfinancing/crowd_order_notify",                         //小程序众筹订单支付成功回来修改状态
    "ceshi"=>"index/Crowdfinancing/ceshi",                         
    


    /*TODO:众筹商品购物车开始*/
    "crowd_shopping_index"=>"index/CrowdShopping/crowd_shopping_index",                     //购物车列表信息返回
    "get_crowd_goods_id_to_shopping"=>"index/CrowdShopping/get_crowd_goods_id_to_shopping", //获取商品id 存入购物车
    "crowd_shopping_information_add"=>"index/CrowdShopping/crowd_shopping_information_add", //购物车添加商品数量
    "crowd_shopping_information_del"=>"index/CrowdShopping/crowd_shopping_information_del", //购物车减少商品数量
    "crowd_shopping_del"=>"index/CrowdShopping/crowd_shopping_del",                         //购物车删除
    "crowd_shopping_numbers"=>"index/CrowdShopping/crowd_shopping_numbers",                 //购物车数量返回

    /*TODO:购物车结束*/

    /*TODO:茶仓出入仓*/
    "getStoreData"=>"index/Storehouse/getStoreData",        //店铺小程序前端存茶数据
    "theStoreValue"=>"index/Storehouse/theStoreValue",      //店铺小程序前端存茶总价值
    "getStoreHouse"=>"index/Storehouse/getStoreHouse",      //店铺小程序前端所有仓库
    "doHouseOrder"=>"index/Storehouse/doHouseOrder",        //店铺小程序前端选择仓库
    "takeOrderData"=>"index/Storehouse/takeOrderData",      //店铺小程序前端入仓详情
    "logContinuAtion"=>"index/Storehouse/logContinuAtion",  //店铺小程序前端仓库订单续费
    "outPositionOrder"=>"index/Storehouse/outPositionOrder",//店铺小程序前端仓库订单出仓
    "getHousePrice"=>"index/Storehouse/getHousePrice",      //店铺小程序前端仓库订单出仓运费
    "getLinePrice"=>"index/Storehouse/getLinePrice",        //存茶详情年划线价
    
    /* 微信公众平台路由  */
    "receive_ticket"=>"index/WxTest/receive_ticket",        //微信小程序接受ticket
    "callback"=>"index/WxTest/callback",                    //微信小程序--授权成功后，获取回调信息
    /**  分享 */
    "qr_back_points"=>"index/My/qr_back_points",        //微信小程序--分享返积分

]);

/**
 * [后台路由]
 *
 */
Route::group("admin",[
    /*首页*/
    "/$"=>"admin/index/index",
    "get_id_return_info"=>"admin/index/get_id_return_info",   //获取点击二级菜单下三级菜单的权限菜单
    "shop_store_date"=>"admin/index/shop_store_date",         //进入店铺后台显示但前使用版本及日期
    "get_info_store"=>"admin/index/get_info_store",           //店铺后台获取消息
    "get_info_zong"=>"admin/index/get_info_zong",             //总控后台获取消息
    "informationhint"=>"admin/index/informationhint",         //总控后台判断消息提醒

    /* 后台首页 */
    "home_index"=>"admin/Home/index",

    /*登录页面*/
    "index"=>"admin/Login/index",
    "login"=>"admin/Login/login",
    "logout"=>"admin/Login/logout",

    /*验证码*/
    "login_captcha"=>"admin/Login/captchas",

    /*管理员列表*/
    "admin_index"=>"admin/admin/index",
    "admin_add"=>"admin/admin/add",
    "admin_save"=>"admin/admin/save",
    "admin_del"=>"admin/admin/del",
    "admin_edit"=>"admin/admin/edit",
    "admin_updata"=>"admin/admin/updata",
    "admin_status"=>"admin/admin/status",
    "admin_passwd"=>"admin/admin/passwd",
    "admin_search"=>"admin/admin/search",


    /*菜单列表*/
    "menu_index"=>"admin/menu/index",
    "menu_add"=>"admin/menu/add",
    "menu_save"=>"admin/menu/save",
    "menu_del"=>"admin/menu/del",
    "menu_edit"=>"admin/menu/edit",
    "menu_updata"=>"admin/menu/updata",
    "menu_status"=>"admin/menu/status",


    /*角色列表*/
    "role_index"=>"admin/role/index",//列表
    "role_search"=>"admin/role/role_search",//列表查询
    "role_add"=>"admin/role/add",//角色添加
    "role_save"=>"admin/role/save",//角色保存
    "role_del"=>"admin/role/del",//角色删除
    "role_edit"=>"admin/role/edit",//角色编辑
    "role_updata"=>"admin/role/updata",//角色数据更新
    "role_status"=>"admin/role/status",//角色状态修改
    "role_search"=>"admin/role/role_search",//角色状态修改





    /*TODO：会员管理开始*/
    "user_index"=>"admin/User/index",        //会员概况
    "user_search"=>"admin/User/user_search", //会员概况搜索
    "user_status"=>"admin/User/status", //会员状态编辑
    "user_edit"=>"admin/User/edit",     //会员编辑
    "user_update"=>"admin/User/update",     //会员信息更新
    "user_del"=>"admin/User/del",     //会员删除

    "user_grade"=>"admin/User/grade",  //会员等级
	"user_grade_edit"=>"admin/User/grade_edit",  //会员等级编辑
     "user_grade_add"=>"admin/User/grade_add",  //会员等级添加（写在编辑里面了）
     "user_grade_del"=>"admin/User/grade_del",  //会员等级删除
     "user_grade_start_image_del"=>"admin/User/grade_start_image_del",  //会员等级图片删除
    "user_grade_status"=>"admin/User/grade_status",  //会员等级状态修改
    "piv_handle"=>"admin/User/piv_handle",  //会员等级--图片预处理
    /*TODO:会员管理结束*/

    /*TODO：会员储值开始*/
    /*资金管理*/
    "capital_index"=>"admin/Capital/index",  //资金管理界面
	"capital_edit"=>"admin/Capital/edit", //资金管理界面edit
	"capital_add"=>"admin/Capital/add", //资金管理界面add
	"capital_del"=>"admin/Capital/del", //资金管理删除del
	"capital_status"=>"admin/Capital/status", //资金管理状态修改
	"capital_adddo"=>"admin/Capital/capital_adddo", //资金管理添加处理
    /*TODO:会员储值结束*/
    /*TODO：会员余额开始*/
    "member_balance"=>"admin/Money/balance",//
    /*TODO：会员余额结束*/

    /* TODO:图片库开始*/
    "photo_index"=>"admin/Photo/index", //图片库进入页面
    "phone_information"=>"admin/Photo/phone_information", //图片库页面
    "imgupload"=>"admin/Photo/imgupload", //图片上传
    "makegroup"=>"admin/Photo/makegroup", //创建相册
    "phone_del"=>"admin/Photo/phone_del", //删除相册
    "pic_del"=>"admin/Photo/pic_del", //删除图片
    "picGrouping"=>"admin/Photo/picGrouping", //图片分组
    /* TODO:图片库结束*/

    /*TODO:订单开始*/
    "order_index"=>"admin/Order/order_index",//初始订单页面
    "order_search"=>"admin/Order/order_search",//初始订单搜索
    "order_way_pay"=>"admin/Order/order_way_pay",//初始订单待付款
    "order_wait_send"=>"admin/Order/order_wait_send",//初始订单待发货
    "order_shipped"=>"admin/Order/order_shipped",//初始订单已发货
    "order_completed"=>"admin/Order/order_completed",//初始订单已完成
    "order_closed"=>"admin/Order/order_closed",//初始订单已关闭
    "order_confirm_shipment"=>"admin/Order/order_confirm_shipment",//初始订单卖家确认发货
    "order_information_return"=>"admin/Order/order_information_return",//初始订单基本信息
   
    "order_integral"=>"admin/Order/order_integral",//积分订单
    "order_integral_search"=>"admin/Order/order_integral_search",//积分订单搜索
    "transaction_setting"=>"admin/Order/transaction_setting",//交易设置
    "order_setting_update"=>"admin/Order/order_setting_update",//更新
    "refund_protection_index"=>"admin/Order/refund_protection_index",//退款维权
    "refund_protection_applying"=>"admin/Order/refund_protection_applying",//退款维权申请中
    "refund_protection_processing"=>"admin/Order/refund_protection_processing",//退款维权处理中
    "refund_protection_receipting"=>"admin/Order/refund_protection_receipting",//退款维权收货中
    "refund_protection_completed"=>"admin/Order/refund_protection_completed",//退款维权换货完成
    "refund_protection_refuse"=>"admin/Order/refund_protection_refuse",//退款维权拒绝
    "refund_protection_search"=>"admin/Order/refund_protection_search",//退款维权搜索
    "changeOderPrice"=>"admin/Order/changeOderPrice",//订单改价
    "get_receipt_detail"=>"admin/Order/get_receipt_detail",//获取发票详情
    "receipt_do"=>"admin/Order/receipt_do",                //申请发票处理
    /*TODO:订单结束*/

    /*TODO:地址订单开始*/
    "reward_index"=>"admin/Order/reward_index",//众筹打赏页面
    "reward_search"=>"admin/Order/reward_search",//众筹打赏搜索页面

    /*TODO:地址订单结束*/

    /*TODO:定制订单开始*/
    "make_oder_index"=>"admin/Order/make_oder_index",//定制订单页面

    /*TODO:定制订单结束*/
    /*TODO:售后开始*/
    "business_replay"=>"admin/AfterSale/business_replay",//售后官方回复
    "business_after_sale_information"=>"admin/AfterSale/business_after_sale_information",//售后页面数据返回
    "after_sale_status"=>"admin/AfterSale/after_sale_status",//售后状态修改
    "after_sale_express_add"=>"admin/AfterSale/after_sale_express_add",//售后状态修改带快递信息
    "adder_business_after_sale_information2"=>"admin/AfterSale/adder_business_after_sale_information2",//售后页面数据返回--增值
    "adder_after_sale_status2"=>"admin/AfterSale/adder_after_sale_status2",//售后状态修改--增值
    "adder_after_sale_express_add2"=>"admin/AfterSale/adder_after_sale_express_add2",//售后状态修改带快递信息--增值
    "adder_adder_business_replay2"=>"admin/AfterSale/adder_business_replay2",//售后状态修改带快递信息----增值
    "after_sale_money_add"=>"admin/AfterSale/after_sale_money_add",//售后状态修改带退钱操作
    "after_sale_refound"=>"admin/AfterSale/after_sale_refound", //售后状态-退钱，原路返回
    "adder_send_goods"=>"admin/AfterSale/adder_send_goods",     //售后状态-店铺退还商品

    /*TODO:售后结束*/
    /*TODO:退款*/
//    "order_refund"=>"admin/Api/order_refund",//微信退款

    /*TODO:订单备注开始*/
    "notice_index"=>"admin/Notification/notice_index",//卖家备注数据返回
    "option_add_notice"=>"admin/Notification/option_add_notice",//卖家备注

    /*TODO:订单备注结束*/


    /*TODO:评价开始*/
    "evaluate_index"=>"admin/Evaluate/evaluate_index",//评价管理页面
    "evaluate_edit"=>"admin/Evaluate/evaluate_edit",//评价编辑
    "evaluate_del"=>"admin/Evaluate/evaluate_del",//评价删除
    "evaluate_dels"=>"admin/Evaluate/evaluate_dels",//评价批量删除(检查一下)
    "evaluate_search"=>"admin/Evaluate/evaluate_search",//评价搜索
    "evaluate_status"=>"admin/Evaluate/evaluate_status",//评价功能开启关闭
    "evaluate_repay"=>"admin/Evaluate/evaluate_repay",//评价商家回复
    "evaluate_setting"=>"admin/Evaluate/evaluate_setting",//评价积分设置
    /*TODO:评价结束*/

	 /*茶圈*/
    "category_index"=>"admin/Category/index",   //活动分类显示
    "category_add"=>"admin/Category/add",       //活动分类添加
    "category_save"=>"admin/Category/save",     //活动分类分组入库
    "category_edit"=>"admin/Category/edit",     //活动分类分组修改
    "category_del"=>"admin/Category/del",       //活动分类分组删除
    "category_updata"=>"admin/Category/updata", //活动分类分组更新
    "category_ajax"=>"admin/Category/ajax_add", //活动分类分组ajax显示
    "category_dels"=>"admin/Category/dels",     //活动分类批量删除
    "category_images"=>"admin/Category/images", //活动分类图片删除
    "category_status"=>"admin/Category/status", //活动分类分组状态修改
    "category_search"=>"admin/Category/search", //活动分类分组状态修改

    "accessories_business_advertising"=>"admin/Advertisement/index",                 //活动管理分组显示
    "accessories_advertising_search"=>"admin/Advertisement/accessories_advertising_search",              //活动管理分组搜索显示
    "accessories_business_add"=>"admin/Advertisement/accessories_business_add",      //活动管理分组添加
    "accessories_business_edit"=>"admin/Advertisement/accessories_business_edit",    //活动管理分组编辑
    "accessories_business_save"=>"admin/Advertisement/accessories_business_save",    //活动管理分组保存
    "accessories_business_updata"=>"admin/Advertisement/accessories_business_updata",//活动管理分组保存
    "accessories_business_del"=>"admin/Advertisement/accessories_business_del",      //活动管理分组删除
    "accessories_business_images"=>"admin/Advertisement/accessories_business_images",//活动管理分组图片删除
    "accessories_business_dels"=>"admin/Advertisement/accessories_business_dels",    //活动管理分组批量删除(前端没写)
    "accessories_business_label"=>"admin/Advertisement/accessories_business_label",  //活动管理分组标签修改
    "accessories_business_search"=>"admin/Advertisement/accessories_business_search",//活动管理分组模糊搜索

	 "comments_index"=>"admin/Comments/index",       //评论管理显示
	 "comments_add"=>"admin/Comments/add",           //评论积分设置
	 "comments_preserve"=>"admin/Comments/preserve", //评论积分设置保存
     "comments_save"=>"admin/Comments/updata",       //评论管理保存
     "comments_status"=>"admin/Comments/status",     //评论管理状态修改
     "comments_delete"=>"admin/Comments/delete",     //评论管理组删除
     "comments_deletes"=>"admin/Comments/deletes",   //评论管理组批量删除
     "comments_search"=>"admin/Comments/search",     //评论管理组模糊搜索


     "active_order_index"=>"admin/ActiveOrder/index",   //活动订单显示
     "active_order_search"=>"admin/ActiveOrder/search", //评论管理组模糊搜索

    /*商品列表*/
    "goods_index"=>"admin/Goods/index",          //普通商品列表显示
    "goods_add"=>"admin/Goods/add",              //普通商品列表组添加
    "goods_save"=>"admin/Goods/save",            //普通商品列表组保存入库
    "goods_edit"=>"admin/Goods/edit",            //普通商品列表组编辑
    "goods_updata"=>"admin/Goods/updata",        //普通商品列表组更新
    "goods_status"=>"admin/Goods/status",        //普通商品列表组首页推荐
    "goods_ground"=>"admin/Goods/ground",        //普通商品列表组是否上架
    "goods_del"=>"admin/Goods/del",              //普通商品列表组删除
    "goods_dels"=>"admin/Goods/dels",            //普通商品列表组批量删除
    "goods_search"=>"admin/Goods/search",        //普通商品列表组模糊搜索
    "goods_images"=>"admin/Goods/images",        //普通商品列表组图片删除
    "goods_photos"=>"admin/Goods/photos",        //普通商品列表规格图片删除
    "goods_value"=>"admin/Goods/value",          //普通商品列表规格值修改
    "goods_switches"=>"admin/Goods/switches",    //普通商品列上架开关
    "goods_saves"=>"admin/Goods/saves",          //普通商品列存茶开关
    "goods_addphoto"=>"admin/Goods/addphoto",    //普通商品列表规格图片添加 
    "goods_offer"=>"admin/Goods/offer",          //普通商品多规格列表单位编辑 
    "goods_standard"=>"admin/Goods/standard",    //普通商品多规格列表单位id查找 
    "goods_templet"=>"admin/Goods/goods_templet",//普通商品运费模板编辑 
    "crowd_templet"=>"admin/Goods/crowd_templet",//众筹商品运费模板编辑 
    "distribution_status"=>"admin/Goods/distribution_status",//普通商品分销开关

    "crowd_index"=>"admin/Goods/crowd_index",        //众筹商品列表显示
    "crowd_add"=>"admin/Goods/crowd_add",            //众筹商品列表添加
    "crowd_edit"=>"admin/Goods/crowd_edit",          //众筹商品编辑
    "crowd_offer"=>"admin/Goods/crowd_offer",        //众筹商品单位编辑
    "crowd_update"=>"admin/Goods/crowd_update",      //众筹商品单位更新
    "crowd_delete"=>"admin/Goods/crowd_delete",      //众筹商品删除
    "crowd_standard"=>"admin/Goods/crowd_standard",  //众筹商品多规格列表单位id查找
    "crowd_status"=>"admin/Goods/crowd_status",      //众筹商品首页轮播推荐
    "crowd_ground"=>"admin/Goods/crowd_ground",      //众筹商品是否上架
    "crowd_dels"=>"admin/Goods/crowd_dels",          //众筹商品批量删除
    "crowd_photos"=>"admin/Goods/crowd_photos",      //众筹商品规格图片删除
    "crowd_images"=>"admin/Goods/crowd_images",      //众筹商品项目图片删除
    "crowd_value"=>"admin/Goods/crowd_value",        //众筹商品规格值修改
    "crowd_switches"=>"admin/Goods/crowd_switches",  //众筹商品规格开关
    "crowd_addphoto"=>"admin/Goods/crowd_addphoto",  //众筹商品规格图片添加
    "crowd_search"=>"admin/Goods/crowd_search",      //众筹商品商品列表搜索

    "exclusive_index"=>"admin/Goods/exclusive_index",   //专属定制商品显示
    "exclusive_add"=>"admin/Goods/exclusive_add",       //专属定制商品添加
    "exclusive_edit"=>"admin/Goods/exclusive_edit",     //专属定制商品编辑


    



    /*商品分类*/
    "goods_type_index"=>"admin/GoodsType/index",      //商品分类列表显示
    "goods_type_add"=>"admin/GoodsType/add",          //商品分类列表增加
    "goods_type_edit"=>"admin/GoodsType/edit",        //商品分类列表编辑
    "goods_type_save"=>"admin/GoodsType/save",        //商品分类列表组入库
    "goods_type_updata"=>"admin/GoodsType/updata",    //商品分类列表组更新
    "goods_type_del"=>"admin/GoodsType/del",          //商品分类列表组删除 
    "goods_type_ajax_add"=>"admin/GoodsType/ajax_add",//商品分类列表组ajax显示
    "goods_type_dels"=>"admin/GoodsType/dels",        //商品分类列表组批量删除
    "goods_type_search"=>"admin/GoodsType/search",    //商品分类列表组模糊搜索 

    /*TODO：分销开始*/
    "distribution_setting_index"=>"admin/Distribution/setting_index",  //分销设置页面
    "distribution_setting_edit"=>"admin/Distribution/setting_edit",    //分销设置页面编辑
    "distribution_setting_updata"=>"admin/Distribution/setting_updata",//分销设置页面保存
    "distribution_goods_index"=>"admin/Distribution/goods_index",      //分销商品页面
    "distribution_goods_add"=>"admin/Distribution/goods_add",          //分销商品添加
    "distribution_goods_addtwo"=>"admin/Distribution/goods_addtwo",    //商品列表分销设置添加
    "distribution_goods_edit"=>"admin/Distribution/goods_edit",        //分销商品编辑
    "distribution_goods_save"=>"admin/Distribution/goods_save",        //分销商品添加入库
    "distribution_goods_savetwo"=>"admin/Distribution/goods_savetwo",  //商品列表分销设置添加入库
    "distribution_goods_update"=>"admin/Distribution/goods_update",    //分销商品编辑更新
    "distribution_goods_delete"=>"admin/Distribution/goods_delete",    //分销商品组删除
    "distribution_goods_search"=>"admin/Distribution/goods_search",    //分销商品组搜素
    "distribution_record_index"=>"admin/Distribution/record_index",    //分销记录页面
    "distribution_record_search"=>"admin/Distribution/record_search",   //分销记录页面搜索
    "distribution_member_index"=>"admin/Member/member_index",          //分销成员页面
    "distribution_member_add"=>"admin/Member/member_add",              //分销成员添加
    "distribution_member_edit"=>"admin/Member/member_edit",            //分销成员页面编辑
    "distribution_member_update"=>"admin/Member/member_update",        //分销成员页面更新
    "distribution_member_save"=>"admin/Member/member_save",            //分销成员保存入库
    "distribution_member_status"=>"admin/Member/member_status",        //分销成员状态更改
    /*TODO：分销结束*/




    /*积分商城*/
    "bonus_index"=>"admin/Bonus/bonus_index",   //积分商城显示商品
    "bonus_add"=>"admin/Bonus/bonus_add",       //积分商城添加商品
    "bonus_save"=>"admin/Bonus/bonus_save",     //积分商城保存商品
    "bonus_edit"=>"admin/Bonus/bonus_edit",     //积分商城编辑商品
    "bonus_update"=>"admin/Bonus/bonus_update", //积分商城更新商品
    "bonus_delete"=>"admin/Bonus/bonus_delete", //积分商城删除商品
    "bonus_images"=>"admin/Bonus/bonus_images", //积分商城商品图片删除
    "bonus_search"=>"admin/Bonus/bonus_search", //积分商城搜索商品
    "bonus_dels"=>"admin/Bonus/bonus_dels",     //积分商城批量删除商品


    /*限时限购*/
    "limitations_index"=>"admin/Limitations/limitations_index",  //限时限购列表显示  
    "limitations_search"=>"admin/Limitations/limitations_search",//限时限购列表搜索  
    "limitations_edit"=>"admin/Limitations/limitations_edit",    //限时限购编辑 
    "limitations_add"=>"admin/Limitations/limitations_add",      //限时限购添加商品
    "limitations_save"=>"admin/Limitations/limitations_save",    //限时限购添加
    "limitations_save_do"=>"admin/Limitations/limitations_save_do",    //限时限购编辑处理
    "limitations_weave"=>"admin/Limitations/limitations_weave",  //限时限购编辑商品
    "limitations_update"=>"admin/Limitations/limitations_update",//限时限购更新
    "limitations_delete"=>"admin/Limitations/limitations_delete",//限时限购删除
    // "limitations_search"=>"admin/Limitations/limitations_search",//限时限购删除
       

    /*优惠券*/
    "coupon_index"=>"admin/Bonus/coupon_index",    //优惠券列表显示
    "coupon_add"=>"admin/Bonus/coupon_add",        //优惠券添加
    "coupon_save"=>"admin/Bonus/coupon_save",      //优惠券保存入库
    "coupon_edit"=>"admin/Bonus/coupon_edit",      //优惠券编辑
    "coupon_weave"=>"admin/Bonus/coupon_weave",    //优惠券添加商品编辑
    "coupon_update"=>"admin/Bonus/coupon_update",  //优惠券编辑
    "coupon_del"=>"admin/Bonus/coupon_del",        //优惠券删除
    "coupon_search"=>"admin/Bonus/coupon_search",  //优惠券商品搜索
    "coupon_seek"=>"admin/Bonus/coupon_seek",      //优惠券搜索


    /*运营模块*/
    "operate_index"=>"admin/operate/operate_index",                      //*******运营模块页
    "operate_problem"=>"admin/operate/operate_problem",                  //常见问题显示
    "operate_problem_add"=>"admin/operate/operate_problem_add",          //常见问题添加
    "operate_problem_save"=>"admin/operate/operate_problem_save",        //常见问题保存
    "operate_problem_edit"=>"admin/operate/operate_problem_edit",        //常见问题编辑
    "operate_problem_update"=>"admin/operate/operate_problem_update",    //常见问题更新
    "operate_problem_delete"=>"admin/operate/operate_problem_delete",    //常见问题删除
    "operate_problem_status"=>"admin/operate/operate_problem_status",    //常见问题状态值修改
    "operate_problem_search"=>"admin/operate/operate_problem_search",    //常见问题搜索


    "operate_contract"=>"admin/operate/operate_contract",                //协议合同显示
    "operate_contract_add"=>"admin/operate/operate_contract_add",        //协议合同添加
    "operate_contract_edit"=>"admin/operate/operate_contract_edit",      //协议合同编辑
    "operate_contract_update"=>"admin/operate/operate_contract_update",  //协议合同更新
    "operate_contract_delete"=>"admin/operate/operate_contract_delete",  //协议合同删除
    "operate_contract_save"=>"admin/operate/operate_contract_save",      //协议合同保存


    
    "operate_message"=>"admin/operate/operate_message",               //*********消息提醒
    "operate_message_add"=>"admin/operate/operate_message_add",       //消息提醒添加
    "operate_message_save"=>"admin/operate/operate_message_save",     //消息提醒保存
    "operate_message_edit"=>"admin/operate/operate_message_edit",     //消息提醒编辑
    "operate_message_update"=>"admin/operate/operate_message_update", //消息提醒更新
    "operate_message_delete"=>"admin/operate/operate_message_delete", //消息提醒删除

    "operate_integral_rule"=>"admin/operate/operate_integral_rule",    //积分规则
    "operate_integral_update"=>"admin/operate/operate_integral_update",//积分规则更新

       
    "operate_about_index"=>"admin/operate/operate_about_index",         //关于我们显示
    "operate_about_update"=>"admin/operate/operate_about_update",       //关于我们更新

    "operate_broadcast"=>"admin/operate/operate_broadcast",                //广播消息显示
    "operate_broadcast_edit"=>"admin/operate/operate_broadcast_edit",      //广播消息编辑
    "operate_broadcast_update"=>"admin/operate/operate_broadcast_update",  //广播消息更新
    "operate_broadcast_delete"=>"admin/operate/operate_broadcast_delete",  //广播消息删除
    "operate_broadcast_save"=>"admin/operate/operate_broadcast_save",      //广播消息保存
    "operate_broadcast_status"=>"admin/operate/operate_broadcast_status",  //广播消息显示状态编辑
   
    "operate_receipt_index"=>"admin/operate/operate_receipt_index",  //发票显示状态
    "operate_receipt_update"=>"admin/operate/operate_receipt_update",  //发票显示状态编辑



    


    /*配送设置*/
    "delivery_index"=>"admin/Delivery/delivery_index",//*******配送设置
    "delivery_status"=>"admin/Delivery/delivery_status",//买家上门自提功能开启关闭
    "delivery_add"=>"admin/Delivery/delivery_add",//上门自提添加
    "delivery_edit"=>"admin/Delivery/delivery_edit",//上门自提编辑
    "delivery_del"=>"admin/Delivery/del",//上门自提删除
    "delivery_dels"=>"admin/Delivery/dels",//上门自提批量删除
    "delivery_label"=>"admin/Delivery/delivery_label",//上门字体状态编辑


    "delivery_goods"=>"admin/Delivery/delivery_goods",                       //快递发货显示
    "delivery_goods_add_number"=>"admin/Delivery/delivery_goods_add",        //快递发货添加
    "delivery_goods_add_numbers"=>"admin/Delivery/delivery_goods_adds",      //快递发货添加
    "delivery_goods_edit"=>"admin/Delivery/delivery_goods_edit",             //快递发货编辑
    "delivery_goods_update"=>"admin/Delivery/delivery_goods_update",         //快递发货更新
    "delivery_goods_delete"=>"admin/Delivery/delivery_goods_delete",         //快递发货删除
    "delivery_are"=>"admin/Delivery/delivery_are",                           //快递地区编辑
    "delivery_templet"=>"admin/Delivery/delivery_templet",                   //快递模板
    "delivery_goods_addd"=>"admin/Delivery/delivery_goods_addd",
   


    /*TODO:*/
    /*专属定制*/
    "custom_made"=>"admin/Made/custom_made",  //专属定制

    /*仓储*/
    "store_house" =>"admin/StoreHouse/store_house",                     //仓库管理
    "store_house_add" =>"admin/StoreHouse/store_house_add",             //仓库管理添加
    "store_house_delete" =>"admin/StoreHouse/store_house_delete",       //仓库管理刪除
    "store_house_edit" =>"admin/StoreHouse/store_house_edit",           //仓库管理编辑
    "store_house_update" =>"admin/StoreHouse/store_house_update",       //仓库管理更新
    "store_house_unit" =>"admin/StoreHouse/store_house_unit",           //仓库管理更新所有单位
    "store_house_cost" =>"admin/StoreHouse/store_house_cost",           //仓库编辑价格单位
    "store_house_status" =>"admin/StoreHouse/store_house_status",       //仓库编辑默认入仓
    "stores_divergence" =>"admin/StoreHouse/stores_divergence",         //入仓
    "stores_divergence_search" =>"admin/StoreHouse/stores_divergence_search",     //入仓订单搜索
    "stores_divergence_out" =>"admin/StoreHouse/stores_divergence_out", //出仓
    "stores_divergence_out_search" =>"admin/StoreHouse/stores_divergence_out_search", //出仓搜索
    "stores_series_index" =>"admin/StoreHouse/stores_series_index",     //仓储续费
    "stores_series_search" =>"admin/StoreHouse/stores_series_search",   //仓储续费订单搜索
    "stores_order_confirm_shipment" =>"admin/StoreHouse/stores_order_confirm_shipment",//订单出仓订单发货

    /*资产*/
    "property_day" =>"admin/Property/property_day",     //对账单日汇报
    "property_month" =>"admin/Property/property_month", //对账单月汇报

    /*会员*/
    "recharge_application" =>"admin/User/recharge_application",     //微信提现
    "recharge_application_search" =>"admin/User/recharge_application_search",     //微信提现搜索
    "withdrawal_application" =>"admin/User/withdrawal_application", //银行卡提现
    "withdrawal_application_search" =>"admin/User/withdrawal_application_search", //银行卡提现搜索
    "withdrawal_setting" =>"admin/User/withdrawal_setting",         //提现设置
    "withdrawal_save" =>"admin/User/withdrawal_save",         //提现设置更新保存
    "property_day_index" =>"admin/Property/property_day_index",     //日账单详

    /*物联*/
    "anti_fake" =>"admin/Material/anti_fake",                                                       //防伪溯源
    "chip_details" =>"admin/Material/chip_details",                                                       //防伪溯源
    "video_token" =>"admin/Material/video_token",       //token值
    "edit_video_token" =>"admin/Material/edit_video_token",//更新token值
    "direct_seeding" =>"admin/Material/direct_seeding",                                             //视频直播
    "direct_seeding_add" =>"admin/Material/direct_seeding_add",                                     //视频直播添加设备
    "direct_seeding_save" =>"admin/Material/direct_seeding_save",                                   //视频直播保存设备
    "direct_seeding_edit" =>"admin/Material/direct_seeding_edit",                                   //视频直播编辑设备
    "direct_seeding_update" =>"admin/Material/direct_seeding_update",                               //视频直播更新设备
    "direct_seeding_delete" =>"admin/Material/direct_seeding_delete",                               //视频直播删除设备
    "direct_seeding_status" =>"admin/Material/direct_seeding_status",                               //视频直播状态开启
    "direct_seeding_search" =>"admin/Material/direct_seeding_search",                               //视频直播搜索
    "direct_seeding_classification" =>"admin/Material/direct_seeding_classification",               //视频直播分类
    "direct_seeding_classification_save" =>"admin/Material/direct_seeding_classification_save",     //视频直播分类保存
    "direct_seeding_classification_add" =>"admin/Material/direct_seeding_classification_add",       //直播分类添加
    "direct_seeding_classification_edit" =>"admin/Material/direct_seeding_classification_edit",     //直播分类添加
    "direct_seeding_classification_delete" =>"admin/Material/direct_seeding_classification_delete", //直播分类删除
    "direct_seeding_classification_update" =>"admin/Material/direct_seeding_classification_update", //直播分类更新
    "direct_seeding_classification_delete_image" =>"admin/Material/direct_seeding_classification_delete_image", //直播分类图片删除
    "direct_seeding_classification_status" =>"admin/Material/direct_seeding_classification_status", //直播分类状态开关
    "direct_seeding_delete_image" =>"admin/Material/direct_seeding_delete_image",                   //视频直播图片删除
    "interaction_index" =>"admin/Material/interaction_index",                                       //温湿感应
    "interaction_add" =>"admin/Material/interaction_add",                                           //温湿感应
    "interaction_add_do" =>"admin/Material/interaction_add_do",                                     //温湿感应仪器添加处理
    "wenshidu" =>"admin/Material/wenshidu",                                                         //温湿感应仪器登录操作
    "video_comment" =>"admin/Material/video_comment",                                               //直播评论

    /*数据*/
    "data_index" =>"admin/Information/data_index",                //数据概况
    "analytical_index" =>"admin/Information/analytical_index",    //溯源分析
    "store_analyse" =>"admin/Information/store_analyse",            //订单数据
    "store_money_analyse" =>"admin/Information/store_money_analyse",    //销售额数据统计

    /*店铺*/
    "general_index"=>"admin/General/general_index",             //店铺信息
    "general_address"=>"admin/General/general_address",             //店铺信息收货地址
    "general_address_add"=>"admin/General/general_address_add",             //店铺信息地址添加编辑
    "general_address_del"=>"admin/General/general_address_del",             //店铺地址删除
    "general_address_edit_info"=>"admin/General/general_address_edit_info",             //店铺地址编辑数据返回
    "general_address_return_info"=>"admin/General/general_address_return_info",             //店铺地址编辑所有数据返回
    "general_address_status"=>"admin/General/general_address_status",             //店铺地址设置默认
    "general_update"=>"admin/General/general_update",             //店铺信息编辑
    "general_logo_del"=>"admin/General/general_logo_del",             //店铺信息logo图删除
    "small_routine_index"=>"admin/General/small_routine_index",           //小程序设置
    "small_routine_edit"=>"admin/General/small_routine_edit",           //小程序设置添加编辑功能
    "decoration_routine_index"=>"admin/General/decoration_routine_index", //小程序装修
    "xiaochengxu_edit"=>"admin/General/xiaochengxu_edit", //小程序装修
    "system_template"=>"admin/General/system_template", //小程序系统推荐模板生成
    "test_selecticon"=>"admin/Test/selecticon",//图标库
    "test_selecticon2"=>"admin/Test/selecticon2",//图标库--图片
    "test_selecticon3"=>"admin/Test/selecticon3",//图标库3--图片
    "test_select_url"=>"admin/Test/select_url",//轮播图功能库（数据来源）
    "test_select_source"=>"admin/Test/select_source",//公告来源栏目（数据来源）

    "added_service_index"=>"admin/General/added_service_index",         //增值服务(增值商品显示)
    "added_service_list"=>"admin/General/added_service_list",           //增值服务(增值商品列表)
    "added_service_show"=>"admin/General/added_service_show",           //增值服务(增值商品详情)
    "added_service_look"=>"admin/General/added_service_look",           //增值服务(增值商品再看看)
    "added_service_search"=>"admin/General/added_service_search",       //增值服务(增值商品分类搜索)
    "order_package_index"=>"admin/General/order_package_index",         //订单套餐
    "order_package_show"=>"admin/General/order_package_show",           //订单套餐(显示)
    "order_package_buy"=>"admin/General/order_package_buy",             //订单套餐购买页面（ajax订单信息返回）
    "order_package_condition"=>"admin/General/order_package_condition", //下套餐之前需要判断的条件
    "order_package_do_by"=>"admin/General/order_package_do_by",         //订单套餐购买操作
    "order_code_pay"=>"admin/General/order_code_pay",                   //套餐订购微信二维码扫码支付
    "order_code_pay2"=>"admin/General/order_code_pay2",                 //资金管理在线充值-微信
    "check_code_apy"=>"admin/General/check_code_apy",                   //轮询充值订购微信二维码扫码支付是否成功
    "check_code_two"=>"admin/General/check_code_two",                   //轮询增值订购微信二维码扫码支付是否成功
    "check_code_one"=>"admin/General/check_code_one",                   //轮询套餐订单订购微信二维码扫码支付是否成功
    "order_code_alipay"=>"admin/General/order_code_alipay",             //套餐订购支付宝二维码扫码支付
    "order_code_alipay2"=>"admin/General/order_code_alipay2",           //后台充值支付宝二维码扫码支付
    "order_package_remittance"=>"admin/General/order_package_remittance",      //订单套餐支付汇款
    "order_package_balance"=>"admin/General/order_package_balance",  //订单套餐余额支付
    "order_package_del"=>"admin/General/order_package_del",          //套餐订单删除
    "is_exist_app"=>"admin/General/is_exist_app",                    //判断小程序是否存在
    "getShareCode"=>"admin/General/getShareCode",                    //判断分享码是否正确
    "change_edition"=>"admin/General/change_edition",                //切换店铺版本号

    "order_package_purchase"=>"admin/General/order_package_purchase",//套餐订购页面(未写)
    "capital_management"=>"admin/General/capital_management",//资金管理资金明细
    "capital_management_details"=>"admin/General/capital_management_details",//资金管理资金详情
    "unline_recharge_record"=>"admin/General/unline_recharge_record",//资金管理线下充值记录
    "unline_recharge_serach"=>"admin/General/unline_recharge_serach",//资金管理线下充记录搜索
    "unline_withdrawal_record"=>"admin/General/unline_withdrawal_record",//资金管理提现记录
    "agency_invitation"=>"admin/General/agency_invitation",//代理分销邀请
    "now_agency_invitation"=>"admin/General/now_agency_invitation",//立即分销邀请
    "security_setting"=>"admin/General/security_setting",//立即分销邀请
    "store_update_password"=>"admin/Store/store_update_password",//店铺的支付密码修改
    "store_wallet_add"=>"admin/Store/store_wallet_add",//店铺的钱包充值页面
    "store_wallet_reduce"=>"admin/Store/store_wallet_reduce",//店铺的钱包提现页面
    "store_add_bankcard"=>"admin/Store/store_add_bankcard",//店铺的银行卡页面
    "store_icard_save"=>"admin/Store/store_icard_save",//银行开添加入库
    "store_icard_delete"=>"admin/Store/store_icard_delete",//银行卡删除
    "OfflineRecharge"=>"admin/Store/OfflineRecharge",//店铺钱包进行充值
    "OfflineRecharge2"=>"admin/Store/OfflineRecharge2",//店铺钱包进行充值
    "withdrawCash"=>"admin/Store/withdrawCash",//店铺钱包进行提现

    "store_wallet_return"=>"admin/Store/store_wallet_return",//店铺的钱包返回
    "store_isset_password"=>"admin/Store/store_isset_password",//店铺检测是否进行了支付密码设置，没有设置则前往设置
    "unline_recharge_reasch"=>"admin/General/unline_recharge_reasch",//线下充值记录搜索
    "unline_withdrawl_reasch"=>"admin/General/unline_withdrawl_reasch",//线下提现记录搜索




    "store_set_meal_order"=>"admin/General/store_set_meal_order",//套餐订单
    "store_write_receipt"=>"admin/General/store_write_receipt",//后台店铺申请开发票
    "store_receipt_now"=>"admin/General/store_receipt_now",//后台店铺立即开发票
    "store_order"=>"admin/General/store_order",//增值订单
    "store_order_search"=>"admin/General/store_order_search",//增值订单搜索
    "store_order_after"=>"admin/General/store_order_after",//售后维权
    "store_order_after_ing"=>"admin/General/store_order_after_ing",//售后维权申请中
    "store_order_after_refuse"=>"admin/General/store_order_after_refuse",//售后维权已拒绝
    "store_order_after_handle"=>"admin/General/store_order_after_handle",//售后维权处理中
    "store_order_after_close"=>"admin/General/store_order_after_close",//售后维权已关闭
    "store_order_after_replace"=>"admin/General/store_order_after_replace",//售后维权完成换货
    "store_order_after_complete"=>"admin/General/store_order_after_complete",//售后维权完成退款
    // "store_notice_index"=>"admin/General/store_notice_index",//这是处理回复
    "store_confirm_status"=>"admin/Analyse/store_confirm_status",//更改订单状态
    "adder_order_information_return"=>"admin/Analyse/adder_order_information_return",//增值订单信息返回
    "adder_order_confirm_shipment"=>"admin/Analyse/adder_order_confirm_shipment", //订单确认发货
    "store_order_after_edit"=>"admin/General/store_order_after_edit",//售后维权详情
    "go_to_pay"=>"admin/General/go_to_pay",//我要支付
    "additional_comments"=>"admin/General/additional_comments",//追加评论
    "additional_comments_add"=>"admin/General/additional_comments_add",//我要评论
    "adder_order_comment"=>"admin/General/adder_order_comment",     //增加订单评论
    "get_adder_comment"=>"admin/General/get_adder_comment",     //获取订单评论
    "adder_after_sale" =>"admin/General/adder_after_sale",                       //增值订单申请售后
    "adder_apply_after_sale" =>"admin/General/adder_apply_after_sale",           //增值订单申请售后处理

   
    /**一键上传 */
    "auth_pre"=>"admin/Upload/auth_pre",                             //一键生成--第一页预备
    "auth_index"=>"admin/Upload/auth_index",                             //一键生成--开始授权
    "auth_detail"=>"admin/Upload/auth_detail",                             //一键生成--授权获取的详情
    "set_tiyan"=>"admin/Upload/set_tiyan",                             //一键生成--授权获取的详情
    "send_message"=>"admin/Upload/send_message",                             //一键生成--短信提醒
    "get_qrcode"=>"admin/Upload/get_qrcode",                             //一键生成--获取体验码
    "publish"=>"admin/Upload/publish",                               //一键生成--提交上传的版本
    "release"=>"admin/Upload/release",                               //一键生成--发布正式版
    "relieve"=>"admin/Upload/relieve",                               //一键生成--接触授权绑定
    "check_detail"=>"admin/Upload/check_detail",                     //一键生成--查看审核详情
    "check_publish"=>"admin/Upload/check_publish",                     //一键生成--查看发布详情
    "get_tiyanlist"=>"admin/Upload/get_tiyanlist",                     //一键生成--获取体验者列表
    "unDoCodeAudit"=>"admin/Upload/unDoCodeAudit",                     //一键生成--版本撤销
    "cate_list"=>"Admin/Upload/cate_list",                             //一键生成--获取分类
    "is_templete"=>"Admin/Upload/is_templete",                             //一键生成--获取分类

    
    /*总控*/
    "control_index"=>"admin/Control/control_index",                //总控店铺
    "control_meal_index"=>"admin/Control/control_meal_index",      //入驻套餐
    "control_meal_add"=>"admin/Control/control_meal_add",          //添加入驻套餐
    "control_meal_status"=>"admin/Control/control_meal_status",    //入驻套餐首页显示
    "control_meal_edit"=>"admin/Control/control_meal_edit",        //入驻套餐编辑
    "control_meal_update"=>"admin/Control/control_meal_update",    //入驻套餐编辑保存
    "control_order_index"=>"admin/Control/control_order_index",    //入驻订单页面
    "control_store_return"=>"admin/Control/control_store_return",  //入驻资料审核页面
    "control_store_search"=>"admin/Control/control_store_search",  //入驻资料搜索
    "control_order_add"=>"admin/Control/control_order_add",        //入驻订单店铺编辑审核
    "control_order_delete"=>"admin/Control/control_order_delete",  //入驻订单店铺删除
    "control_order_update"=>"admin/Control/control_order_update",  //入驻订单店铺审核更新操作
    "control_order_status"=>"admin/Control/control_order_status",  //入驻订单编辑审核
    "control_order_status_update"=>"admin/Control/control_order_status_update",       //入驻订单编辑审核操作
    "control_order_search"=>"admin/Control/control_order_search",  //入驻订单搜索
    "control_store_index"=>"admin/Control/control_store_index",    //店铺分析
    "control_store_templet"=>"admin/Control/control_store_templet",//增值商品运费模板
    "control_templet_add"=>"admin/Control/control_store_add",      //增值商品运费添加
    "control_templet_delete"=>"admin/Control/control_templet_delete",//增值商品运费模板删除
    "control_templet_edit"=>"admin/Control/control_templet_edit",   //增值商品运费模板编辑
    "control_templet_update"=>"admin/Control/control_templet_update",//增值商品运费模板更新
    "control_online_charging"=>"admin/Control/control_online_charging",//线下充值申请
    "control_charging_edit"=>"admin/Control/control_charging_edit",//线下充值申请编辑
    "control_withdraw_deposit"=>"admin/Control/control_withdraw_deposit",//提现申请
    "control_withdraw_edit"=>"admin/Control/control_withdraw_edit",//提现申请编辑
    "control_notice_index"=>"admin/Control/control_notice_index",//公告通知
    "control_notice_add"=>"admin/Control/control_notice_add",//公告通知新增
    "control_notice_edit"=>"admin/Control/control_notice_edit",//公告通知编辑
    "control_notice_update"=>"admin/Control/control_notice_update",//公告通知更新
    "control_notice_status"=>"admin/Control/control_notice_status",//公告通知状态
    "control_notice_shop"=>"admin/Control/control_notice_shop",//公告店铺通知
    "control_notice_del"=>"admin/Control/control_notice_del",//公告通知删除
    "store_examine_receipt"=>"admin/Control/store_examine_receipt",//admin后台审核订单发票
    "admin_auditing_receipt"=>"admin/Control/admin_auditing_receipt",//后台审核发票
    "control_order_index_search"=>"admin/Control/control_order_index_search",    //入驻订单搜索
    "control_online_charging_search"=>"admin/Control/control_online_charging_search",//线下充值申请搜索
    "control_withdraw_deposit_search"=>"admin/Control/control_withdraw_deposit_search",//提现申请搜索
    "control_store_list"=>"admin/Control/control_store_list",           //总控店铺list
    "control_store_edit"=>"admin/Control/control_store_edit",           //总控店铺list编辑
    "control_store_edit_do"=>"admin/Control/control_store_edit_do",           //总控店铺list编辑
    "version_control"=>"admin/Control/version_control",                 //总控版本控制
    "version_control_do"=>"admin/Control/version_control_do",           //总控版本控制处理
    "control_store_analyse"=>"admin/Control/control_store_analyse",     //总控--增值订单分析



    "analyse_index"=>"admin/Analyse/analyse_index",          //总控增值商品
    "analyse_dels"=>"admin/Analyse/analyse_dels",            //总控增值商品批量删除
    "analyse_add"=>"admin/Analyse/analyse_add",              //增值商品实物添加
    "analyse_invented"=>"admin/Analyse/analyse_invented",    //增值虚拟商品添加
    "analyse_edit"=>"admin/Analyse/analyse_edit",            //增值商品编辑
    "analyse_update"=>"admin/Analyse/analyse_update",        //增值商品更新
    "analyse_search"=>"admin/Analyse/analyse_search",        //增值商品搜索
    "analyse_delete"=>"admin/Analyse/analyse_delete",        //增值商品删除
    "analyse_images"=>"admin/Analyse/analyse_images",        //增值商品图片删除
    "analyse_dels"=>"admin/Analyse/analyse_dels",            //增值商品批量删除
    "analyse_photos"=>"admin/Analyse/analyse_photos",        //增值商品规格图片删除
    "analyse_addphoto"=>"admin/Analyse/analyse_addphoto",    //增值商品规格图片添加
    "analyse_value"=>"admin/Analyse/analyse_value",          //增值商品规格值修改
    "analyse_ground"=>"admin/Analyse/analyse_ground",        //增值商品上架开关
    "analyse_status"=>"admin/Analyse/analyse_status",        //增值商品系统推荐
    "control_charging_update"=>"admin/Analyse/control_charging_update",//线下充值申请审核
    "control_withdraw_update"=>"admin/Analyse/control_withdraw_update",//线下提现申请审核
    "store_notice_index"=>"admin/Analyse/store_notice_index",//增值商品处理回复
    "adder_order_change"=>"admin/Analyse/adder_order_change",//增值订单修改快递编号


    "analyse_order"=>"admin/Analyse/analyse_order",          //总控增值订单
    "analyse_waiting"=>"admin/Analyse/analyse_waiting",      //总控增值订单待发货
    "analyse_delivered"=>"admin/Analyse/analyse_delivered",  //总控增值已发货
    "analyse_received"=>"admin/Analyse/analyse_received",    //总控增值待收货
    "analyse_served"=>"admin/Analyse/analyse_served",          //总控增值待服务
    "analyse_ok"=>"admin/Analyse/analyse_ok",               //总控增值已完成
    "analyse_after_sale"=>"admin/Analyse/analyse_after_sale",//总控增值退款维权
    "analyse_order_search"=>"admin/Analyse/analyse_order_search",          //总控增值订单搜索
    "adder_after_sale_information"=>"admin/Analyse/adder_after_sale_information",          //总控增值订单获取售后信息
    "adder_after_sale_status"=>"admin/Analyse/adder_after_sale_status",            //总控增值订单修改售后的状态
    "adder_after_sale_express_add"=>"admin/Analyse/adder_after_sale_express_add",          //总控增值订单--快递信息
    "adder_after_sale_refound"=>"admin/Analyse/adder_after_sale_refound",          //总控增值订单--退款
    "adder_business_replay"=>"admin/Analyse/adder_business_replay",                 //总控增值订单--回复



    "analyse_optimize_index"=>"admin/Analyse/analyse_optimize_index",   //SEO优化
    "analyse_optimize_update"=>"admin/Analyse/analyse_optimize_update", //SEO优化编辑

    /*众筹订单*/
    "crowd_order_index" => "admin/CrowdOder/crowd_order_index",                         //众筹订单显示
    "crowd_order_search"=>"admin/CrowdOder/crowd_order_search",                        //众筹订单搜索
    "crowd_order_way_pay"=>"admin/CrowdOder/crowd_order_way_pay",                     //众筹订单待付款
    "crowd_order_wait_send"=>"admin/CrowdOder/crowd_order_wait_send",                //众筹订单待发货
    "crowd_order_shipped"=>"admin/CrowdOder/crowd_order_shipped",                   //众筹订单已发货
    "crowd_order_completed"=>"admin/CrowdOder/crowd_order_completed",              //众筹订单已完成
    "crowd_order_closed"=>"admin/CrowdOder/crowd_order_closed",                   //众筹订单已关闭
    "crowd_order_confirm_shipment"=>"admin/CrowdOder/crowd_order_confirm_shipment", //众筹订单卖家确认发货
    "crowd_order_information_return"=>"admin/CrowdOder/crowd_order_information_return", //众筹订单基本信息
    "changeCrowdOderPrice"=>"admin/CrowdOder/changeCrowdOderPrice",//订单改价

    /*TODO:*/

    /* 增值服务下单 */
    "adder_place"=>"admin/AddeOrder/adder_place",                  //增值订单数据返回
    "analyse_code_pay"=>"admin/AddeOrder/analyse_code_pay",        //增值商品订购微信二维码扫码支付
    "analyse_code_alipay"=>"admin/AddeOrder/analyse_code_alipay",  //增值商品订购支付宝二维码扫码支付
    "analyse_small_pay"=>"admin/AddeOrder/analyse_small_pay",      //增值商品店铺余额支付
    
    /*高级分销设置 */
    "setting_index"=>"admin/Setting/setting_index",                  //高级分销设置显示
    "setting_update"=>"admin/Setting/setting_update",                //高级分销设置编辑

   /*总控分销代理 */
   "detail_index"=>"admin/City/detail_index",                    //分销详细
   "agent_index"=>"admin/City/agent_index",                      //代理详细
   "city_setting"=>"admin/City/city_setting",                    //分销代理设置
   "city_rank_meal"=>"admin/City/city_rank_meal",                //城市等级套餐
   "city_rank_meal_add"=>"admin/City/city_rank_meal_add",        //城市等级套餐添加
   "city_rank_meal_edit"=>"admin/City/city_rank_meal_edit",      //城市等级套餐编辑
   "city_rank_meal_update"=>"admin/City/city_rank_meal_update",  //城市等级套餐更新
   "city_rank_setting"=>"admin/City/city_rank_setting",          //城市等级设置
   "city_rank_setting_edit"=>"admin/City/city_rank_setting_edit",//城市等级设置编辑
   "city_datum_verify"=>"admin/City/city_datum_verify",          //城市入驻资料审核
   "city_datum_verify_edit"=>"admin/City/city_datum_verify_edit",//城市入驻资料审核编辑
   "city_datum_verify_update"=>"admin/City/city_datum_verify_update",//城市入驻资料审核更新
   "city_price_examine"=>"admin/City/city_price_examine",        //城市入驻费用审核
   "city_price_examine_update"=>"admin/City/city_price_examine_update",      //城市入驻费用审核编辑
   " city_price_examine_replace"=>"admin/City/city_price_examine_replace",      //城市入驻费用点击审核
   "order_preparation"=>"admin/City/order_preparation",      //城市入驻费用订单筛选
   "city_rank_add"=>"admin/City/city_rank_add",                //城市等级添加
   "city_rank_delete"=>"admin/City/city_rank_delete",          //城市等级删除
   "city_rank_update"=>"admin/City/city_rank_update",          //城市等级移动


]);

/**
 * 小程序装修部分
 */
Route::group("api",[
    "doPagehomepage"=>"api/Wxapps/doPagehomepage",//Diy方法开始
    "doPageAppbase"=>"api/Wxapps/doPageAppbase",  //Diy方法开始
    "doPagegetNewSessionkey"=>"api/Wxapps/doPagegetNewSessionkey",//手机号自动获取时的sessionkey
    "doPageDiypage"=>"api/Wxapps/doPageDiypage", //http://teahouse.com/api/doPageDiypage?uniacid=1&pageid=1
    "doPageGetFoot"=>"api/Wxapps/doPageGetFoot", //http://teahouse.com/api/doPageGetFoot?uniacid=1&foot=1
    "doPagebindfxs"=>"api/Wxapps/doPagebindfxs", //http://teahouse.com/api/doPageGetFoot?uniacid=1&foot=1
    "dopageglobaluserinfo"=>"api/Wxapps/dopageglobaluserinfo", //http://teahouse.com/api/dopageglobaluserinfo?openid=o9NMH0ber2GnkHvkYEhrJamfNNPg&uniacid=1
      /**测试 */
    "wxapp2"=>"api/Wxapp2/index",
    "doPageBase"=>"api/Wxapp2/doPageBase",
    "limit_goods_more"=>"api/Wxapps/limit_goods_more",      //秒杀商品列表--更多


    /**
     * 小程序接口
     */
    //个人中心
    "order_count"=>"api/Wxapps/order_count",      //各订单类型统计
    
    

    /**
     * 小程序直播接口
    */
    "classification" => "api/Live/classification",//视频分类接口
    "video_list" => "api/Live/video_list",//视频列表接口
    "details" =>"api/Live/details",//视频详情接口
    "video_give" =>"api/Live/video_give",//视频点赞接口
    "video_comment" =>"api/Live/video_comment",//视频直播评论接口
    "video_reply" => "api/Live/video_reply",//视频直播回复接口
    "video_index" =>"api/Live/video_index",//评论显示接口
    /**
     * 根据appid获取uniacid
     */
    "get_uniacid_by_appid" =>"api/Wxapps/get_uniacid_by_appid",//进入首页获取uniacids


]);

/**
 * 城市合伙人
 * gy
 */
Route::group("city",[
    "apply_login" => "city/Passport/login", //城市合伙人PC端登录
    "apply_register" => "city/Passport/register", //城市合伙人PC端注册
    "forget_password" => "city/Passport/forget_password", //城市合伙人PC端修改密码
    "remittance_login" => "city/Passport/remittance_login", //城市合伙人凭证
    "chooseCity" => "city/Passport/chooseCity", //城市合伙人选择省份直辖市
    "chooseRank" => "city/Passport/chooseRank", //城市合伙人选择等级
    "logCityTenantDetail"=>"city/Citydenglu/logCityTenantDetail",    //登陆后-城市累计商户明细
    "myInviteStore"=>"city/Citydenglu/myInviteStore",                //我邀请的商户明细
    "copartner_order_index"=>"city/Citydenglu/copartner_order_index",//我邀请的商户明细
    "city_server_index"=>"city/Citydenglu/city_server_index",//合伙人系统固定页面
    "order_index"=>"city/CityOrder/order_index",//城市合伙人订单显示
    "cityWhatChatPay"=>"city/CityOrder/cityWhatChatPay",//城市合伙人订单微信支付
    "cityAlipayCode"=>"city/CityOrder/cityAlipayCode",//城市合伙人订单支付宝支付
    "payment_image"=>"city/CityOrder/payment_image",//上传汇款支付凭证
    "mailing_address"=>"city/CityOrder/mailing_address",//开发票邮寄地址
    "cityOrderReceipt"=>"city/CityOrder/cityOrderReceipt",//开发票

]);

/**
 * 智慧茶仓公众号
 */
Route::group("rec",[
    //登录注册
    "code" => "rec/User/code", //获取短信验证码
    "register" =>"rec/User/register",//注册
    "login" =>"rec/User/login",//登录
    "vs_code" =>"rec/User/vs_code",//验证码
    "forget" =>"rec/User/forget",//忘记密码
    "edit_phone" =>"rec/User/edit_phone",//修改手机号

    //我的
    "user_store"=>"rec/User/user_store",

    //发票
    "send_invoice" =>"rec/Invoice/requestBilling",
    "query_invoice" =>"rec/Invoice/CheckEInvoice",
    "getMerchantToken" =>"rec/Invoice/getMerchantToken",

    "filename" =>"rec/Invoice/index",
    "refer_invoice" =>"rec/Invoice/refer_invoice",
    "ele_invoice" =>"rec/Invoice/ele_invoice",

    //微信登录
    "wx_openid" =>"rec/Wechat/wx_accredit",
    "wx_code" =>"rec/Wechat/wx_code",
    "wx_code1" =>"rec/Wechat/wx_code1",//测试

    "app_notice" =>"rec/WechatPay/app_notice",
    "get_pay" =>'rec/WechatPay/get_pay',

    //订单
    "classify" =>"rec/Meal/class_index",
    "in_address"=>"rec/Order/in_address",
    "meal_order" =>"rec/Order/meal_order",

    //上传
    "upload"=>"rec/File/upload",
    
    //分享
    "qr_code"=>"rec/Share/qr_code",

    //提现申请
    "cash_with"=>"rec/With/cash_with",
    "record_with"=>"rec/With/record",

    //合伙人考核
    "assessment"=>"rec/Meal/assessment",


]);

Route::miss("public/miss");


