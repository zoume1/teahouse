<?php

namespace app\admin\controller;
use think\Controller;
use think\Session;
use think\Validate;
use think\Request;
use think\Db;


/**
 * admin 总控资金管理
 * Class Bankroll
 * @package app\admin\controller
 */
class Bankroll extends Controller
{
    /**
     * [总控资金管理-提现审核]
     * 郭杨
     */    
    public function adminBankRollIndex(){
        $seach = input();
        return view("bankroll_index");
    }

    /**
     * [总控资金管理-资金奖惩]
     * 郭杨
     */    
    public function rewardsIndex(){
        return view("rewards_index");
    }


        /**
     * [总控资金管理-资金奖惩添加]
     * 郭杨
     */    
    public function rewards_index_add(){
        return view("rewards_index_add");
    }


        /**
     * [总控资金管理-资金奖惩编辑]
     * 郭杨
     */    
    public function rewards_index_edit(){
        return view("rewards_index_edit");
    }
        

}