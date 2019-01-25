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
    "index_home"=>"index/index/home",
    "tea_factory"=>"index/index/tea_factory", //茶厂 
    "tea_merchant"=>"index/index/tea_merchant", //茶商
    "tea_moment"=>"index/index/tea_moment", //茶圈
    "consumer"=>"index/index/consumer", //用户
    "wisdom"=>"index/index/wisdom", //智慧茶仓
    "partner"=>"index/index/partner", //招募合伙人
    "about"=>"index/index/about", //关于我们


    /*TODO：start*/
    /*登录授权*/
    "wechatlogin"=>"index/Login/wechatlogin",  //登录授权
    "my_show_grade"=>"index/My/show_grade",  //会员等级
    "my_qrcode"=>"index/My/qrcode",  //会员二维码
    "my_index"=>"index/My/my_index",  //我的页面
    "wx_index"=>"index/Pay/index",//小程序支付（活动）
    "wx_order_index"=>"index/Pay/order_index",//小程序订单支付
     "notify"=>"index/order/notify",//小程序支付回调（活动）
     "order_notify"=>"index/order/order_notify",//小程序订单支付回调

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
    "ios_api_order_del"=>"index/Order/ios_api_order_del",//买家删除订单接口(ajax)
    "ios_api_order_no_pay_cancel"=>"index/Order/ios_api_order_no_pay_cancel",//订单状态修改（未付款买家取消订单）

    /*TODO:订单结束*/

    /*TODO:订单用户提醒发货开始*/
    "option_add"=>"index/Notification/option_add",//用户提醒
    /*TODO:订单用户提醒发货结束*/




    /*TODO:购物车开始*/
    "shopping_index"=>"index/Shopping/shopping_index",//购物车列表信息返回
     "get_goods_id_to_shopping"=>"index/Shopping/get_goods_id_to_shopping",//获取商品id 存入购物车
     "shopping_information_add"=>"index/Shopping/shopping_information_add",//购物车添加商品数量
     "shopping_information_del"=>"index/Shopping/shopping_information_del",//购物车减少商品数量
     "shopping_del"=>"index/Shopping/shopping_del",//购物车删除

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
    "teacenter_comment"=>"index/TeaCenter/teacenter_comment",          //茶圈活动评论存储
    "teacenter_comment_show"=>"index/TeaCenter/teacenter_comment_show",  //茶圈活动评论显示
    "teacenter_comment_updata"=>"index/TeaCenter/teacenter_comment_updata", //茶圈活动评论点赞


    /*商品管理*/
    "commodity_index"=>"index/Commodity/commodity_index",        //商品分类
    "commodity_list"=>"index/Commodity/commodity_list",          //商品列表
    "commodity_detail"=>"index/Commodity/commodity_detail",      //商品详情
    "commodity_recommend"=>"index/Commodity/commodity_recommend",//商品首页推荐

    /*优惠券*/
    "coupon_untapped"=>"index/Coupon/coupon_untapped",   //未使用优惠券显示
    "coupon_user"=>"index/Coupon/coupon_user",           //已使用优惠券显示
    "coupon_time"=>"index/Coupon/coupon_time",           //过期优惠券显示
    "coupon_goods"=>"index/Coupon/coupon_goods",         //优惠券使用商品
    "coupon_appropriated"=>"index/Coupon/coupon_appropriated",//商品下单适用优惠券

    /*积分商城*/
    "bonus_index"=>"index/Coupon/bonus_index",       //积分商城显示
    "bonus_detailed"=>"index/Coupon/bonus_detailed", //积分商城显示

    /*测试接口*/
    "index_text"=>"index/index/text"
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


    /* TODO:图片库开始*/
	"photo_index"=>"admin/Photo/index",
    "images_online_push"=>"admin/Photo/images_online_push", //上传图片库
    "photo_del"=>"admin/Photo/delete", //删除单张图片
    /* TODO:图片库结束*/

    /*TODO:订单开始*/
    "order_index"=>"admin/Order/order_index",//初始订单页面
    "order_integral"=>"admin/Order/order_integral",//积分订单
    "transaction_setting"=>"admin/Order/transaction_setting",//交易设置
    "order_setting_update"=>"admin/Order/order_setting_update",//更新
    "refund_protection_index"=>"admin/Order/refund_protection_index",//退款维权
    /*TODO:订单结束*/
    /*TODO:订单备注开始*/
    "option_add_notice"=>"admin/Notification/option_add_notice",//卖家备注
    /*TODO:订单备注结束*/


    /*TODO:评价开始*/
    "evaluate_index"=>"admin/Evaluate/evaluate_index",//评价管理页面
    "evaluate_edit"=>"admin/Evaluate/evaluate_edit",//评价编辑
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
    "goods_index"=>"admin/Goods/index",      //商品列表显示
    "goods_add"=>"admin/Goods/add",          //商品列表组添加
    "goods_save"=>"admin/Goods/save",        //商品列表组保存入库
    "goods_edit"=>"admin/Goods/edit",        //商品列表组编辑
    "goods_updata"=>"admin/Goods/updata",    //商品列表组更新
    "goods_status"=>"admin/Goods/status",    //商品列表组首页推荐
    "goods_ground"=>"admin/Goods/ground",    //商品列表组是否上架
    "goods_del"=>"admin/Goods/del",          //商品列表组删除
    "goods_dels"=>"admin/Goods/dels",        //商品列表组批量删除
    "goods_search"=>"admin/Goods/search",    //商品列表组模糊搜索
    "goods_images"=>"admin/Goods/images",    //商品列表组图片删除
    "goods_photos"=>"admin/Goods/photos",    //商品列表规格图片删除
    "goods_value"=>"admin/Goods/value",      //商品列表规格值修改
    "goods_switches"=>"admin/Goods/switches",//商品列表规格开关
    "goods_addphoto"=>"admin/Goods/addphoto",//商品列表规格图片添加 
    



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
    "limitations_save"=>"admin/Limitations/limitations_save",    //限时限购添加limitations_weave
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



    


]);

Route::miss("public/miss");


