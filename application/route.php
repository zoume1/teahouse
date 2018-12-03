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


    /*登录页面*/
    "login_index"=>"index/Login/index",

    /*TODO：start*/
    /*登录授权*/
    "wechatlogin"=>"index/Login/wechatlogin",  //登录授权


    "my_show_grade"=>"index/My/show_grade",  //会员等级
    "my_index"=>"index/My/my_index",  //我的页面
    "wxpay"=>"index/WechatPay/wxpay",//微信支付测试
    "Wx_Pay"=>"index/Test/Wx_Pay", //测试
    /*TODO:end*/



    /*退出登录*/
    "logout"=>"index/Login/logout",
    /*验证码*/
    "login_captcha"=>"index/Login/captchas",



    /*注册页面*/
    "register"=>"index/Register/index",
    "register_code"=>"index/Register/code",
    "register_index"=>"index/Register/register",



    /*茶圈*/
    "teacenter_data"=>"index/TeaCenter/teacenter_data",          //茶圈父级显示
    "teacenter_display"=>"index/TeaCenter/teacenter_display",    //茶圈分类显示
    "teacenter_activity"=>"index/TeaCenter/teacenter_activity",  //茶圈活动页面显示
    "teacenter_detailed"=>"index/TeaCenter/teacenter_detailed",  //茶圈活动详细显示
    "teacenter_alls"=>"index/TeaCenter/teacenter_alls",          //茶圈所有活动
    "teacenter_recommend"=>"index/TeaCenter/recommend",          //茶圈首页推荐活动
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


    /*充值和提现*/
    "recharge_list"=>"admin/Recharge/index", //充值和提现首页
    "recharge_edit"=>"admin/Recharge/edit",   //充值和提现编辑

    /*TODO：资金管理开始*/
    /*资金管理*/
    "capital_index"=>"admin/Capital/index",  //资金管理界面
	"capital_edit"=>"admin/Capital/edit", //资金管理界面edit
	"capital_add"=>"admin/Capital/add", //资金管理界面add
	"capital_del"=>"admin/Capital/del", //资金管理删除del
	"capital_status"=>"admin/Capital/status", //资金管理状态修改
    /*TODO:资金管理结束*/


    /*积分中心*/
    "integral_center"=>"admin/Integral/index", //积分中心
    "integral_detail"=>"admin/Integral/detail", //积分详情





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



    /*客户中心*/
    "client_index"=>"admin/Client/index",


	
    /* 图片库*/
	"photo_index"=>"admin/Photo/index",
    "images_online_push"=>"admin/Photo/images_online_push", //上传图片库
    "photo_del"=>"admin/Photo/delete", //删除单张图片

	
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
	 "comments_edit"=>"admin/Comments/edit",         //评论管理编辑
     "comments_save"=>"admin/Comments/save",         //评论管理保存
     "comments_status"=>"admin/Comments/status",     //评论管理状态修改
     "comments_delete"=>"admin/Comments/delete",     //评论管理组删除
     "comments_deletes"=>"admin/Comments/deletes",   //评论管理组批量删除
     "comments_search"=>"admin/Comments/search",     //评论管理组模糊搜索

	 
     "active_order_index"=>"admin/ActiveOrder/index",   //活动订单显示
     "active_order_search"=>"admin/ActiveOrder/search", //评论管理组模糊搜索
	 
	 
]);

Route::miss("public/miss");


