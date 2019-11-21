<?php

namespace app\admin\controller;

use app\index\model\Sanction;
use think\Controller;
use think\Session;
use think\Validate;
use think\Request;
use think\Db;
use app\rec\model\With;


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
        $search = input();
        $data = With::management_index($search);
        return view("bankroll_index",['data'=>$data]);
    }

    /**
     * [总控资金管理-资金奖惩]
     * 郭杨
     */    
    public function rewardsIndex(){
        $search = input();
        $data = Sanction::index($search);
        return view("rewards_index",['data'=>$data]);
    }


        /**
     * [总控资金管理-资金奖惩添加]
     * 郭杨
     */    
    public function rewards_index_add(Request $request){
        if($request->isPost()){
            $data = Request::instance()->param();
            $data['create_time'] = time();
            $restul = Sanction::sanction_add($data);
           
            switch($restul){
                case 0:
                    $this->error("添加奖惩成功", url("admin/Bankroll/rewardsIndex"));
                    break;
                case 1:
                    $this->success("添加奖惩成功", url("admin/Bankroll/rewardsIndex"));
                    break;
                case 2:
                    $this->error("该商户账号不存在，请仔细核对后添加", url("admin/Bankroll/rewards_index_add"));
                    break;
                case 3:
                    $this->error("该城市合伙人账号不存在，请仔细核对后添加", url("admin/Bankroll/rewards_index_add"));
                    break;
                default:
                    $this->error("添加失败", url("admin/Bankroll/rewardsIndex"));
                    break;
            }
        }
        return view("rewards_index_add");
    }


        /**
     * [总控资金管理-资金奖惩编辑]
     * 郭杨
     */    
    public function rewards_index_edit(){
        return view("rewards_index_edit");
    }
        
    /**
     * [总控资金管理-审核操作]
     * 郭杨
     */    
    public function adminBankRollExamine(Request $request){
        if($request->isPost()){
            $data =  Request::instance()->param();
            $restul = With::management_update($data);
            if($restul){
                return jsonSuccess('操作成功');
            } else {
                return jsonError('操作重复，该笔订单已支付');
            }
        
        }

    }

}