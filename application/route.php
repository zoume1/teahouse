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


    /*商品列表*/
    "goods_index"=>"index/Goods/index",
    "goods_detail"=>"index/Goods/detail",
    "goods_id"=>"index/Goods/ajax_id",
    "particulars_id"=>"index/Goods/goods_id",
    "goods_big_images"=>"index/Goods/big_images",



    /*登录页面*/
    "login_index"=>"index/Login/index",
    /*退出登录*/
    "logout"=>"index/Login/logout",
    /*验证码*/
    "login_captcha"=>"index/Login/captchas",



    /*注册页面*/
    "register"=>"index/Register/index",
    "register_code"=>"index/Register/code",
    "register_index"=>"index/Register/register",



    /*安全中心*/
    "security_index"=>"index/Security/index",



    /*模板商城*/
    "template_index"=>"index/Template/index",
    "template_goods_show"=>"index/Template/goods_show",
    "template_goods_buy"=>"index/Template/goods_buy",


    /*定制开发*/
    "exploit_index"=>"index/Exploit/index",



    /*安全中心*/
    "center_index"=>"index/Center/index",



]);

/**
 * [后台路由]
 * 陈绪
 */
Route::group("admin",[
    /*首页*/
    "/$"=>"admin/index/index",


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


    /*配件商品管理*/
    "goods_index"=>"admin/Goods/index",
    "goods_add"=>"admin/Goods/add",
    "goods_save"=>"admin/Goods/save",
    "goods_edit"=>"admin/Goods/edit",
    "goods_updata"=>"admin/Goods/updata",
    "goods_del"=>"admin/Goods/del",
    "images_del"=>"admin/Goods/images",
    "images_dels"=>"admin/Goods/image",
    "goods_status"=>"admin/Goods/status",
    "goods_batches"=>"admin/Goods/batches",
    "goods_putaway"=>"admin/Goods/putaway",                                    //商品上架
    "goods_pay"=>"admin/Goods/pay",                                            //商品付费详情
    "affirm_pay"=>"admin/Goods/affirm",


    /*服务商品管理*/
    "serve_index"=>"admin/Serve/index",
    "serve_add"=>"admin/Serve/add",



    /*商品分类*/
    "category_index"=>"admin/Category/index",
    "category_add"=>"admin/Category/add",
    "category_save"=>"admin/Category/save",
    "category_edit"=>"admin/Category/edit",
    "category_del"=>"admin/Category/del",
    "category_updata"=>"admin/Category/updata",
    "category_ajax"=>"admin/Category/ajax_add",
    "category_images"=>"admin/Category/images",
    "category_status"=>"admin/Category/status",



    /*会员管理 ：TODO*/
    "user_index"=>"admin/User/index", //会员概况
    "user_edit"=>"admin/User/edit",     //会员编辑
    "user_grade"=>"admin/User/grade",  //会员等级
	"user_grade_edit"=>"admin/User/grade_edit",  //会员等级编辑
     "user_grade_add"=>"admin/User/grade_add",  //会员等级添加
	
    /*充值和提现*/
    "recharge_list"=>"admin/Recharge/index", //充值和提现首页
    "recharge_edit"=>"admin/Recharge/edit",   //充值和提现编辑
    /*资金管理*/
    "capital_index"=>"admin/Capital/index",  //资金管理界面
	"capital_edit"=>"admin/Capital/edit", //资金管理界面edit
	"capital_add"=>"admin/Capital/add", //资金管理界面add
    /*积分中心*/
    "integral_center"=>"admin/Integral/index", //积分中心
    "integral_detail"=>"admin/Integral/detail", //积分详情

    /*配件商广告，服务商广告，平台广告开始*/
    "Accessories_business_advertising"=>"admin/Advertisement/accessories_business_advertising",
    "Accessories_business_add"=>"admin/Advertisement/accessories_business_add",
    "Accessories_business_edit"=>"admin/Advertisement/accessories_business_edit",

    "Service_business_advertising"=>"admin/service_advertisement/Service_business_advertising",
    "Service_business_add"=>"admin/service_advertisement/Service_business_add",
    "Service_business_edit"=>"admin/service_advertisement/Service_business_edit",

    "platform_business_index"=>"admin/platform_advertisement/platform_business_index",
    "platform_business_add"=>"admin/platform_advertisement/platform_business_add",
    "platform_business_edit"=>"admin/platform_advertisement/platform_business_edit",
    /*配件商广告，服务商广告，平台广告结束*/



    /*优惠券*/
    "discount_index"=>"admin/Discount/index",
    "discount_add"=>"admin/Discount/add",
    "discount_save"=>"admin/Discount/save",
    "discount_edit"=>"admin/Discount/edit",
    "discount_updata"=>"admin/Discount/updata",
    "discount_del"=>"admin/Discount/del",
    "discount_batches"=>"admin/Discount/batches",


    /*代理*/
    "agency_index"=>"admin/Agency/index",
    "agency_add"=>"admin/Agency/add",
    "agency_save"=>"admin/Agency/save",
    "agency_edit"=>"admin/Agency/edit",
    "agency_updata"=>"admin/Agency/updata",
    "agency_del"=>"admin/Agency/del",



    /*订单管理：TODO:配件商订单开始*/
    "order_index"=>"admin/Order/index", //订单列表
    "order_edit"=>"admin/Order/edit", //*********订单设置

    "order_evaluate"=>"admin/Order/evaluate",   //订单评价
    "order_evaluate_details"=>"admin/Order/evaluate_details", //******订单评价详情

    "order_after_sale"=>"admin/Order/after_sale", //订单维修售后
    "order_after_sale_wait_handle"=>"admin/Order/after_sale_wait_handle", //****订单维修售后待处理
    "order_after_sale_wait_deliver"=>"admin/Order/after_sale_wait_deliver", //****订单维修售后待发货

    "order_invoice"=>"admin/Order/invoice", //发票列表
    "order_invoice_edit"=>"admin/Order/invoice_edit", //****发票信息
    /*订单管理：TODO:配件商订单结束*/

    /*订单管理：TODO:平台商订单开始*/
    "platform_order_service_index"=>"admin/Order/platform_order_service_index", //平台商服务商订单列表
    "platform_order_parts_index"=>"admin/Order/platform_order_parts_index", //平台商配件商订单列表
    "platform_after_sale"=>"admin/Order/platform_after_sale", //平台商售后服务
    "platform_invoice_index"=>"admin/Order/platform_invoice_index", //平台商发票列表
    "platform_invoice_details"=>"admin/Order/platform_invoice_details", //平台商发票详情
    "platform_order_evaluate"=>"admin/Order/platform_order_evaluate", //平台商订单评价
    "platform_order_evaluate_edit"=>"admin/Order/platform_order_evaluate_edit", //平台商订单评价编辑
    "platform_order_set_up"=>"admin/Order/platform_order_set_up", //平台商订单设置
    /*订单管理：TODO:平台订单结束*/

    
    /*订单管理：TODO:服务商商订单开始*/
    'service_order_index'=>"admin/Order/service_order_index", //服务商界面服务商订单列表
    "service_order_evaluate"=>"admin/Order/service_order_evaluate", //服务商界面订单评价
    "service_order_evaluate_edit"=>"admin/Order/service_order_evaluate_edit", //服务商界面订单评价
    /*订单管理：TODO:服务商订单结束*/







    /*聊天管理*/
    "chat_index"=>"admin/Chat/index",
    /*后台获取用户发送过来的聊天信息*/
    "all_information"=>"admin/Chat/all_information",
    /*后台获取用户发送过来的聊天信息(已读)*/
    "read_all_information"=>"admin/Chat/read_all_information",
    /*后台获取用户发送过来的聊天信息（未读）*/
    "unread_all_information"=>"admin/Chat/unread_all_information",
    /*后台聊天信息的删除*/
    "chat_information_del"=>"admin/Chat/chat_information_del",
    /*批量删除*/
    "chat_information_deletes"=>"admin/Chat/chat_deletes",
    /*未读中按下回复按钮进入回复页面把状态值改变为已读*/
    "reading_information"=>"admin/Chat/reading_information",
    /*客服回复信息*/
    "admin_chat_push"=>"admin/Chat/admin_chat_push",


    /*内容管理*/
    "content_index"=>"admin/Content/index",
    "content_add"=>"admin/Content/add",
    "content_save"=>"admin/Content/save",
    "content_edit"=>"admin/Content/edit",
    "content_del"=>"admin/Content/del",
    "content_updata"=>"admin/Content/updata",


    /*常见问题*/
    "issue_index"=>"admin/Issue/index",
    "issue_add"=>"admin/Issue/add",
    "issue_save"=>"admin/Issue/save",
    "issue_edit"=>"admin/Issue/edit",
    "issue_del"=>"admin/Issue/del",
    "issue_updata"=>"admin/Issue/updata",
    "issue_status"=>"admin/Issue/status",
    "issue_putaway"=>"admin/Issue/putaway",


    /*客户中心*/
    "client_index"=>"admin/Client/index",



    /*广告管理*/
    "advertising_index"=>"admin/Advertising/index",
    "advertising_add"=>"admin/Advertising/add",
    "advertising_save"=>"admin/Advertising/save",
    "advertising_del"=>"admin/Advertising/del",
    "advertising_edit"=>"admin/Advertising/edit",
    "advertising_updata"=>"admin/Advertising/updata",
    "advertising_images"=>"admin/Advertising/images",


    /*设置*/
    "install_index"=>"admin/Install/index",
    "recommend_index"=>"admin/Install/recommend",
    "integral_index"=>"admin/Install/integral",
    "putaway_index"=>"admin/Install/putaway",
    "recharge_index"=>"admin/Install/recharge",
    "service_index"=>"admin/Install/service_index",
    "service_add"=>"admin/Install/service_add",
    "service_save"=>"admin/Install/service_save",
    "service_edit"=>"admin/Install/service_edit",
    "service_updata"=>"admin/Install/service_updata",
    "service_del"=>"admin/Install/service_del",
    "message_index"=>"admin/Install/message_index",
    "message_del"=>"admin/Install/message_del",
    "message_save"=>"admin/Install/message_save",




    /*品牌*/
    "brand_index"=>"admin/Brand/index",
    "brand_add"=>"admin/Brand/add",
    "brand_save"=>"admin/Brand/save",
    "brand_edit"=>"admin/Brand/edit",
    "brand_updata"=>"admin/Brand/updata",
    "brand_del"=>"admin/Brand/del",


    /*店铺管理*/
    "shop_index"=>"admin/Shop/index",
    "shop_add"=>"admin/Shop/add",
	
	
    /* 图片库*/
	"photo_index"=>"admin/Photo/index",
    "images_online_push"=>"admin/Photo/images_online_push", //上传图片库
    "photo_del"=>"admin/Photo/delete", //删除单张图片

	
	 /*茶圈*/
	 "teacircle_index"=>"admin/Teacircle/index",
	 "teacircle_add"=>"admin/Teacircle/add",
	 "teacircle_edit"=>"admin/Teacircle/edit",
	 
]);

Route::miss("public/miss");


