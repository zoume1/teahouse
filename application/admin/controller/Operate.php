<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/12 0012
 * Time: 15:09
 */

namespace app\admin\controller;


use think\Controller;
use think\console\Input;
use think\Db;
use think\Request;

class Operate extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:运营模块页
     **************************************
     * @return \think\response\View
     */
    public function operate_index(){
        return view("operate_index");
    }



    /**
     * [常见问题显示]
     * GY
    */
    public function operate_problem(){
        return view("operate_problem");
    }



    /**
     * [常见问题添加]
     * GY
    */
    public function operate_problem_add(){
        $problem = db("problem") -> select();
        return view("operate_problem_add",["problem"=>$problem]);
    }




    /**
     * [协议合同显示]
     * GY
    */
    public function operate_contract(){
        $operate = db("protocol") -> paginate(20);
        return view("operate_contract",["operate" => $operate]);
    }


    /**
     * [协议合同添加]
     * GY
    */
    public function operate_contract_add(){
        return view("operate_contract_add");
    }


    /**
     * [协议合同编辑]
     * GY
    */
    public function operate_contract_edit($id){
        $operate_data = db("protocol")->where("id",$id)->select();
        return view("operate_contract_edit",["operate_data"=>$operate_data]);
    }


    /**
     * [协议合同编辑]
     * GY
    */
    public function operate_contract_save(Request $request){
        if($request->isPost()){
            $data = $request->param();
            $bool = db("protocol")->insert($data);
            if($bool){
                $this->success('添加成功', 'admin/operate/operate_contract');
            } else {
                $this ->error("添加失败","admin/operate/operate_contract");
            }
        }
        
    }


    /**
     * [协议合同编辑]
     * GY
    */
    public function operate_contract_update(Request $request){
        if($request->isPost()){
            $updata = $request->param();
            $bool = db("protocol")->where('id', $request->only(["id"])["id"])->update($updata);
            if($bool){
                $this->success('更新成功', 'admin/operate/operate_contract');
            } else {
                $this ->error("更新失败","admin/operate/operate_contract");
            }
        }
    }


    /**
     * [协议合同删除]
     * GY
    */
    public function operate_contract_delete($id){

        $bool = db("protocol")->where('id', $id)->delete();
        if($bool){
            $this->success('删除成功', 'admin/operate/operate_contract');
        } else {
            $this ->error("删除失败","admin/operate/operate_contract");
        }
        
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:消息提醒
     **************************************
     * @return \think\response\View
     */
    public function operate_message(){
        return view("operate_message");
    }
     /**
     **************李火生*******************
     * @param Request $request
     * Notes:消息提醒添加编辑
     **************************************
     * @return \think\response\View
     */
    public function operate_message_add(){
        return view("operate_message_add");
    }



    /**
     * [积分规则]
     * GY
     */
    public function operate_integral_rule(){
        $recommend_data = db('recommend_integral')->select();
        return view("operate_integral_rule",['recommend_data'=>$recommend_data]);
    }

    /**
     * 积分设置更新
     ***** GY *****
     *
     */
    public function operate_integral_update(Request $request)
    {
        if($request->isPost())
        {
            $data = $request -> param();
            $bool = db("recommend_integral") -> where('id',1) -> update($data);

            if ($bool) {
                $this->success("编辑成功", url("admin/operate/operate_integral_rule"));
            } else {
                $this->error("编辑失败", url("admin/operate/operate_integral_rule"));
            }
        }

    }
}