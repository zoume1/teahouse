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
 * 陈绪
 */
Route::group("",[
    /*首页*/
    "/$"=>"index/index/index",
    /*TODO:PC端注册登录开始*/
    "PcsendMobileCode"=>"index/Register/PcsendMobileCode",//PC端注册验证码
    "doRegByPhone"=>"index/Register/doRegByPhone",//PC端注册操作
    "dolog"=>"index/Login/dolog",//登录操作
    "isLogin"=>"index/Login/isLogin",//判断是否登录
    "logout"=>"index/Login/logout",//退出登录操作
    "find_password_by_phone"=>"index/Findpwd/find_password_by_phone",//找回密码
    "sendMobileCodeByPhone"=>"index/Findpwd/sendMobileCodeByPhone",//找回密码验证码
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


    /*TODO：start*/
    /*登录授权*/
    "wechatlogin"=>"index/Login/wechatlogin",  //登录授权
    "my_show_grade"=>"index/My/show_grade",  //会员等级
    "my_qrcode"=>"index/My/qrcode",  //会员二维码
    "my_index"=>"index/My/my_index",  //我的页面
    "wx_index"=>"index/Pay/index",//小程序支付（活动）
    "wx_order_index"=>"index/Pay/order_index",//小程序订单支付
    "wx_recharge_pay"=>"index/Pay/recharge_pay",//小程序充值支付
     "notify"=>"index/order/notify",//小程序支付回调（活动）
     "order_notify"=>"index/order/order_notify",//小程序订单支付回调
     "recharge_notify"=>"index/order/recharge_notify",//小程序充值支付回调

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
    "order_place"=>"index/Order/order_place",//下订单
    "order_place_by_shopping"=>"index/Order/order_place_by_shopping",//购物车下订单
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
    "update_application"=>"index/AfterSale/update_application",//修改售后
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


    /*商品管理*/
    "commodity_index"=>"index/Commodity/commodity_index",        //商品分类
    "commodity_list"=>"index/Commodity/commodity_list",          //商品列表
    "commodity_detail"=>"index/Commodity/commodity_detail",      //商品详情
    "commodity_recommend"=>"index/Commodity/commodity_recommend",//商品首页推荐

    /*优惠券*/
    "coupon_untapped"=>"index/Coupon/coupon_untapped",        //未使用优惠券显示
    "coupon_user"=>"index/Coupon/coupon_user",                //已使用优惠券显示
    "coupon_time"=>"index/Coupon/coupon_time",                //过期优惠券显示
    "coupon_goods"=>"index/Coupon/coupon_goods",              //优惠券使用商品
    "coupon_appropriated"=>"index/Coupon/coupon_appropriated",//商品下单适用优惠券
    "coupon_minute"=>"index/Coupon/coupon_minute",            //优惠券显示
    "limitations_show"=>"index/Coupon/limitations_show",      //判断该商品是否限时限购
    "limitations"=>"index/Coupon/limitations",                //判断用户是否能购买商品
    
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
    /*TODO:设置支付密码结束*/

    /*TODO:充值提现开始*/
    "member_balance_return"=>"index/Wallet/member_balance_return",//账户余额和积分返回
    "recharge_setting_return"=>"index/Wallet/recharge_setting_return",//账户充值页面对应的储值规则数据返回
    "member_balance_recharge"=>"index/Wallet/member_balance_recharge",//账户余额充值
    "wallet_recharge_del"=>"index/wallet/recharge_del",     //钱包充值下单未付款自动关闭取消删除(ajax)
    "withdrawal_return"=>"index/wallet/withdrawal_return",     //钱包提现页面数据返回
    "withdrawal"=>"index/wallet/withdrawal",     //钱包银行卡提现
    "wechat_withdrawal"=>"index/wallet/wechat_withdrawal",     //钱包微信提现

    /*TODO:充值提现结束*/



    /*TODO:手机号头像昵称绑定开始*/
    "user_phone_return"=>"index/My/user_phone_return",//手机号绑定数据返回
    "user_phone_bingding"=>"index/My/user_phone_bingding",//手机号绑定
    "user_phone_bingding_update"=>"index/My/user_phone_bingding_update",//手机号绑定修改
    "user_name_return"=>"index/My/user_name_return",//用户昵称绑定数据返回
    "user_name_update"=>"index/My/user_name_update",//用户昵称绑定修改
    "user_img_return"=>"index/My/user_img_return",//用户头像绑定数据返回
    "user_img_update"=>"index/My/user_img_update",//用户头像修改
    /*TODO:手机号头像昵称绑定结束*/

    /*TODO:我的账单开始*/
    "consume_index"=>"index/Bill/consume_index",//账单我的消费
    "consume_search"=>"index/Bill/consume_search",//账单我的消费搜索
    /*TODO:我的账单结束*/

    /*TODO:短信验证开始*/
    "sendMobileCode"=>"index/MobileVerification/sendMobileCode",//这是新绑定手机验证码验证
    "sendMobileCodeBank"=>"index/MobileVerification/sendMobileCodeBank",//这是银行卡绑定时需手机验证码验证
    "sendMobileCodePay"=>"index/MobileVerification/sendMobileCodePay",//忘记支付密码（找回密码验证码）
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
    "crowd_index"=>"index/Crowd/crowd_index",       //众筹商品首页显示
    "crowd_now"=>"index/Crowd/crowd_now",           //正在众筹商品
    "crowd_support"=>"index/Crowd/crowd_support",   //众筹商品去支持



]);

/**
 * [后台路由]
 * 陈绪
 */
Route::group("admin",[
    /*首页*/
    "/$"=>"admin/index/index",

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


    /*菜单列表*/
    "menu_index"=>"admin/menu/index",
    "menu_add"=>"admin/menu/add",
    "menu_save"=>"admin/menu/save",
    "menu_del"=>"admin/menu/del",
    "menu_edit"=>"admin/menu/edit",
    "menu_updata"=>"admin/menu/updata",
    "menu_status"=>"admin/menu/status",


    /*角色列表*/
    "role_index"=>"admin/role/index",
    "role_add"=>"admin/role/add",
    "role_save"=>"admin/role/save",
    "role_del"=>"admin/role/del",
    "role_edit"=>"admin/role/edit",
    "role_updata"=>"admin/role/updata",
    "role_status"=>"admin/role/status",





    /*TODO：会员管理开始*/
    "user_index"=>"admin/User/index", //会员概况
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
    /*TODO:会员管理结束*/

    /*TODO：会员储值开始*/
    /*资金管理*/
    "capital_index"=>"admin/Capital/index",  //资金管理界面
	"capital_edit"=>"admin/Capital/edit", //资金管理界面edit
	"capital_add"=>"admin/Capital/add", //资金管理界面add
	"capital_del"=>"admin/Capital/del", //资金管理删除del
	"capital_status"=>"admin/Capital/status", //资金管理状态修改
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
    "transaction_setting"=>"admin/Order/transaction_setting",//交易设置
    "order_setting_update"=>"admin/Order/order_setting_update",//更新
    "refund_protection_index"=>"admin/Order/refund_protection_index",//退款维权
    "refund_protection_processing"=>"admin/Order/refund_protection_processing",//退款维权处理中
    "refund_protection_receipting"=>"admin/Order/refund_protection_receipting",//退款维权收货中
    "refund_protection_completed"=>"admin/Order/refund_protection_completed",//退款维权换货完成
    "refund_protection_refuse"=>"admin/Order/refund_protection_refuse",//退款维权拒绝
    "refund_protection_search"=>"admin/Order/refund_protection_search",//退款维权搜索
    /*TODO:订单结束*/
    /*TODO:售后开始*/
    "business_replay"=>"admin/AfterSale/business_replay",//售后官方回复
    "business_after_sale_information"=>"admin/AfterSale/business_after_sale_information",//售后页面数据返回
    "after_sale_status"=>"admin/AfterSale/after_sale_status",//售后状态修改
    "after_sale_express_add"=>"admin/AfterSale/after_sale_express_add",//售后状态修改带快递信息
    "after_sale_money_add"=>"admin/AfterSale/after_sale_money_add",//售后状态修改带退钱操作
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
    "goods_switches"=>"admin/Goods/switches",    //普通商品列表规格开关
    "goods_addphoto"=>"admin/Goods/addphoto",    //普通商品列表规格图片添加 
    "goods_offer"=>"admin/Goods/offer",          //普通商品多规格列表单位编辑 
    "goods_standard"=>"admin/Goods/standard",    //普通商品多规格列表单位id查找 
    "goods_templet"=>"admin/Goods/goods_templet",//普通商品运费模板编辑 

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
    "distribution_member_index"=>"admin/Member/member_index",          //分销成员页面
    "distribution_member_add"=>"admin/Member/member_add",              //分销成员添加
    "distribution_member_edit"=>"admin/Member/member_edit",            //分销成员页面编辑
    "distribution_member_save"=>"admin/Member/member_save",            //分销成员保存入库
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


    /*限时限购*/
    "limitations_index"=>"admin/Limitations/limitations_index",  //限时限购列表显示  
    "limitations_edit"=>"admin/Limitations/limitations_edit",    //限时限购编辑 
    "limitations_add"=>"admin/Limitations/limitations_add",      //限时限购添加商品
    "limitations_save"=>"admin/Limitations/limitations_save",    //限时限购添加
    "limitations_weave"=>"admin/Limitations/limitations_weave",  //限时限购编辑商品
    "limitations_update"=>"admin/Limitations/limitations_update",//限时限购更新
    "limitations_delete"=>"admin/Limitations/limitations_delete",//限时限购删除
    "limitations_search"=>"admin/Limitations/limitations_search",//限时限购删除
       

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
    


    /*配送设置*/
    "delivery_index"=>"admin/Delivery/delivery_index",//*******配送设置
    "delivery_status"=>"admin/Delivery/delivery_status",//买家上门自提功能开启关闭
    "delivery_add"=>"admin/Delivery/delivery_add",//上门自提添加
    "delivery_edit"=>"admin/Delivery/delivery_edit",//上门自提编辑
    "delivery_del"=>"admin/Delivery/del",//上门自提删除
    "delivery_dels"=>"admin/Delivery/dels",//上门自提批量删除


    "delivery_goods"=>"admin/Delivery/delivery_goods",                       //快递发货显示
    "delivery_goods_add_number"=>"admin/Delivery/delivery_goods_add",        //快递发货添加
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
    "stores_divergence_out" =>"admin/StoreHouse/stores_divergence_out", //出仓

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
    "anti_fake" =>"admin/Material/anti_fake",                 //防伪溯源
    "direct_seeding" =>"admin/Material/direct_seeding",       //视频直播
    "interaction_index" =>"admin/Material/interaction_index", //温湿感应

    /*数据*/
    "data_index" =>"admin/Information/data_index",                //数据概况
    "analytical_index" =>"admin/Information/analytical_index",    //溯源分析

    /*店铺*/
    "general_index"=>"admin/General/general_index",             //店铺信息
    "general_address"=>"admin/General/general_address",             //店铺信息收货地址
    "general_update"=>"admin/General/general_update",             //店铺信息编辑
    "general_logo_del"=>"admin/General/general_logo_del",             //店铺信息logo图删除
    "small_routine_index"=>"admin/General/small_routine_index",           //小程序设置
    "small_routine_edit"=>"admin/General/small_routine_edit",           //小程序设置添加编辑功能
    "decoration_routine_index"=>"admin/General/decoration_routine_index", //小程序装修
    "xiaochengxu_edit"=>"admin/General/xiaochengxu_edit", //小程序装修
    "test_selecticon"=>"admin/Test/selecticon",//图标库
    "test_select_url"=>"admin/Test/select_url",//轮播图功能库
    "test_select_source"=>"admin/Test/select_source",//公告来源栏目

    "added_service_index"=>"admin/General/added_service_index",      //增值服务(增值商品显示)
    "added_service_list"=>"admin/General/added_service_list",        //增值服务(增值商品列表)
    "added_service_show"=>"admin/General/added_service_show",        //增值服务(增值商品详情)
    "added_service_look"=>"admin/General/added_service_look",        //增值服务(增值商品再看看)
    "added_service_search"=>"admin/General/added_service_search",    //增值服务(增值商品分类搜索)
    "order_package_index"=>"admin/General/order_package_index",      //订单套餐
    "order_package_show"=>"admin/General/order_package_show",        //订单套餐(显示)
    "order_package_buy"=>"admin/General/order_package_buy",          //订单套餐购买
    "order_package_purchase"=>"admin/General/order_package_purchase",//套餐订购页面(未写)
 
    /*总控*/
    "control_index"=>"admin/Control/control_index",                //总控店铺
    "control_meal_index"=>"admin/Control/control_meal_index",      //入驻套餐
    "control_meal_add"=>"admin/Control/control_meal_add",          //添加入驻套餐
    "control_meal_status"=>"admin/Control/control_meal_status",    //入驻套餐首页显示
    "control_meal_edit"=>"admin/Control/control_meal_edit",        //入驻套餐编辑
    "control_meal_update"=>"admin/Control/control_meal_update",    //入驻套餐编辑保存
    "control_order_index"=>"admin/Control/control_order_index",    //入驻订单
    "control_order_add"=>"admin/Control/control_order_add",        //入驻订单编辑审核
    "control_order_update"=>"admin/Control/control_order_update",  //入驻订单审核更新
    "control_order_search"=>"admin/Control/control_order_search",  //入驻订单搜索
    "control_store_index"=>"admin/Control/control_store_index",    //店铺分析
    "control_store_templet"=>"admin/Control/control_store_templet",//增值商品运费模板
    "control_templet_add"=>"admin/Control/control_store_add",      //增值商品运费添加
    "control_templet_delete"=>"admin/Control/control_templet_delete",//增值商品运费模板删除
    "control_templet_edit"=>"admin/Control/control_templet_edit",   //增值商品运费模板编辑
    "control_templet_update"=>"admin/Control/control_templet_update",//增值商品运费模板更新

    "analyse_index"=>"admin/Analyse/analyse_index",          //增值商品
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
    



    "analyse_order_index"=>"admin/Analyse/analyse_order_index",     //增值订单
    "analyse_refund_index"=>"admin/Analyse/analyse_refund_index",   //退款维权

    "analyse_optimize_index"=>"admin/Analyse/analyse_optimize_index",   //SEO优化
    "analyse_optimize_update"=>"admin/Analyse/analyse_optimize_update", //SEO优化编辑
    





    /*TODO:*/


]);

Route::group("api",[
    "doPagehomepage"=>"api/Wxapps/doPagehomepage",//Diy方法开始
    "doPageAppbase"=>"api/Wxapps/doPageAppbase",//Diy方法开始
    "doPagegetNewSessionkey"=>"api/Wxapps/doPagegetNewSessionkey",//手机号自动获取时的sessionkey
    "doPageDiypage"=>"api/Wxapps/doPageDiypage", //http://teahouse.com/api/doPageDiypage?uniacid=1&pageid=1
    "doPageGetFoot"=>"api/Wxapps/doPageGetFoot", //http://teahouse.com/api/doPageGetFoot?uniacid=1&foot=1
    "doPagebindfxs"=>"api/Wxapps/doPagebindfxs", //http://teahouse.com/api/doPageGetFoot?uniacid=1&foot=1
    "dopageglobaluserinfo"=>"api/Wxapps/dopageglobaluserinfo", //http://teahouse.com/api/dopageglobaluserinfo?openid=o9NMH0ber2GnkHvkYEhrJamfNNPg&uniacid=1
]);

Route::miss("public/miss");


