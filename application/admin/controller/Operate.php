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
use think\Session;
use think\Validate;
use app\admin\model\TempletMessage;

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
        $store_id = Session::get("store_id");
        $store = config("store_id");
        $problem = db("common_ailment")->where("store_id","EQ",$store_id)->select();
        if(empty($problem)){
            $problem = db("common_ailment")->where("store_id","EQ",$store)->select();
            foreach($problem as $k => $value){
                unset($problem[$k]['id']);
                $problem[$k]['store_id'] = $store_id;
            }

            foreach($problem as $ke => $val){
                $bool = db("common_ailment")->insert($val);
            }
        }
        $problem = db("common_ailment")->where("store_id","EQ",$store_id)->paginate(20 ,false, [
            'query' => request()->param(),
        ]);
        return view("operate_problem",["problem"=>$problem]);
    }



    /**
     * [常见问题添加]
     * GY
    */
    public function operate_problem_add(){
        
        return view("operate_problem_add");
    }

    /**
     * [常见问题保存]
     * GY
    */
    public function operate_problem_save(Request $request){
        if($request->isPost()){
            $data = $request->param();
            $store_id = Session::get("store_id");
            $problem_name = db("problem") -> where("pid",$data["pid"]) ->value("name");
            $data["name"] = $problem_name;
            $data["store_id"] = $store_id;
            $bool = db("common_ailment")->insert($data);
            if($bool){
                $this->success('添加成功', 'admin/operate/operate_problem');
            } else {
                $this ->error("添加失败","admin/operate/operate_problem");
            }
        }
        
        return view("operate_problem_add");
    }


    /**
     * [常见问题编辑]
     * GY
    */
    public function operate_problem_edit($id){
        $problem = db("common_ailment")->where("id",$id)->select();     
        return view("operate_problem_edit",["problem"=>$problem]);
    }
    
    
    /**
     * [常见问题更新]
     * GY
    */
    public function operate_problem_update(Request $request){
        if($request->isPost()){
            $common_ailment = $request->param();
            $name = db("problem") -> where("pid",$common_ailment["pid"]) ->value("name");
            $common_ailment["name"] = $name;

            $bool = db("common_ailment")->where('id', $request->only(["id"])["id"])->update($common_ailment);
            if($bool){
                $this->success('更新成功', 'admin/operate/operate_problem');
            } else {
                $this ->error("更新失败","admin/operate/operate_problem");
            }
        }
    }


    /**
     * [常见问题删除]
     * GY
    */
    public function operate_problem_delete($id){
        $bools = db("common_ailment")->where("id",$id)->delete();     
        if($bools){
            $this->success('删除成功', 'admin/operate/operate_problem');
        } else {
            $this ->error("删除失败","admin/operate/operate_problem");
        }
    }

    /**
     * [常见问题状态值修改]
     * GY
    */
    public function operate_problem_status(Request $request){
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("common_ailment")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/operate/operate_problem"));
                } else {
                    $this->error("修改失败", url("admin/operate/operate_problem"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("common_ailment")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/operate/operate_problem"));
                } else {
                    $this->error("修改失败", url("admin/operate/operate_problem"));
                }
            }
        }
    }


    /**
     * [协议合同显示]
     * GY
    */
    public function operate_contract(){
        $store = config("store_id");
        $store_id = Session::get("store_id");
        $operate = db("protocol")->where("store_id","EQ",$store_id)->select();
        if(empty($operate)){
            $operate = db("protocol")->where("store_id","EQ",$store)->select();
            foreach($operate as $k => $value){
                unset($operate[$k]['id']);
                $operate[$k]['store_id'] = $store_id;
            }

            foreach($operate as $ke => $val){
                $bool = db("protocol")->insert($val);
            }
        }
        $operate = db("protocol")->where("store_id","EQ",$store_id)->paginate(20 ,false, [
            'query' => request()->param(),
        ]);
        
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
     * [协议合同保存]
     * GY
    */
    public function operate_contract_save(Request $request){
        if($request->isPost()){
            $store_id = Session::get("store_id");
            $data = $request->param();
            $data["store_id"] = $store_id;
            $bool = db("protocol")->insert($data);
            if($bool){
                $this->success('添加成功', 'admin/operate/operate_contract');
            } else {
                $this ->error("添加失败","admin/operate/operate_contract");
            }
        }
        
    }


    /**
     * [协议合同更新]
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
     * [消息提醒显示]
     * GY
    */
    public function operate_message(){
        $store_id = Session::get("store_id");
        $message = db("remind")->where("store_id","EQ",$store_id)->paginate(20 ,false, [
            'query' => request()->param(),
        ]);
        return view("operate_message",["message"=>$message]);
    }


    /**
     * [消息提醒添加]
     * GY
    */
    public function operate_message_add(){
        return view("operate_message_add");
    }

    /**
     * [消息提醒保存]
     * GY
    */
    public function operate_message_save(Request $request)
    {
        if($request->isPost()){
            $store_id = Session::get("store_id");
            $remind = $request -> param();
            $time = time();
            $remind["time"] = $time;
            $remind["store_id"] = $store_id;
            $bool = db("remind") -> insert($remind);
            if($bool){
                $this->success('添加成功', 'admin/operate/operate_message');
            } else {
                $this -> error("添加失败","admin/operate/operate_message");
            }
        }
    }


    /**
     * [消息提醒编辑]
     * GY
    */
    public function operate_message_edit($id)
    {
        $message_id = db("remind")->where("id",$id)->select();
        return view("operate_message_edit",["message_id"=>$message_id]);
    }


    /**
     * [消息提醒更新]
     * GY
    */
    public function operate_message_update(Request $request)
    {
        if($request->isPost()){
            $data = $request -> param();
            $time = time();
            $data["time"] = $time;
            $bools = db("remind")->where("id",$request->only(["id"])["id"])->update($data);
            if($bools){
                $this->success("更新成功","admin/operate/operate_message");
            } else {
                $this->error("更新成功","admin/operate/operate_message");             
            }
        }
    }


    /**
     * [消息提醒删除]
     * GY
    */
    public function operate_message_delete($id){
        $boole = db("remind")->where("id",$id)->delete();

        if($boole){
            $this->success("删除成功","admin/operate/operate_message");
        } else {
            $this->success("删除失败","admin/operate/operate_message");
        }
        
    }

    /**
     * [积分规则]
     * GY
     */
    public function operate_integral_rule(){
        $store_id = Session::get("store_id");
        $store = config("store_id");
        $recommend_data = db('recommend_integral')->where("store_id","EQ",$store_id)->select();
        if(empty($recommend_data)){
            $data = db('recommend_integral')->where("store_id","EQ",$store)->find();
            $data['store_id'] = $store_id;
            unset($data['id']);
            $bool = db('recommend_integral')->insert($data);
            $recommend_data = db('recommend_integral')->where("store_id","EQ",$store_id)->select();
        }
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
            $store_id = Session::get("store_id");
            $data = $request -> param();
            $bool = db("recommend_integral") -> where('store_id',$store_id) -> update($data);
            if ($bool) {
                $this->success("编辑成功", url("admin/operate/operate_integral_rule"));
            } else {
                $this->error("编辑失败", url("admin/operate/operate_integral_rule"));
            }
        }

    }


    /**
     * [关于我们显示]
     * GY
    */
    public function operate_about_index(){
        $store_id = Session::get("store_id");
        $store = config("store_id");
        $about = db("about_us")->where("store_id","EQ",$store_id)->select();
        if(empty($about)){
            $abouts = db("about_us")->where("store_id","EQ",$store)->find();
            $abouts['store_id'] = $store_id;
            unset($abouts['id']);
            $bool = db('about_us')->insert($abouts);
            $about = db("about_us")->where("store_id","EQ",$store_id)->select();
        }
        return view("operate_about",["about"=>$about]);
    }


    /**
     * [关于我们更新]
     * GY
    */
    public function operate_about_update(Request $request){
        if($request->isPost()){
            $store_id = Session::get("store_id");
            $about_us = $request->param();
            $bool = db("about_us")->where('store_id', $store_id)->update($about_us);
            if($bool){
                $this->success('更新成功', 'admin/operate/operate_about_index');
            } else {
                $this ->error("更新失败","admin/operate/operate_about_index");
            }
        }
    }

    /**
     * [广播消息显示]
     * GY
    */
    public function operate_broadcast(){
        $store_id = Session::get("store_id");
        $broadcast = db("broadcast")->where("store_id","EQ",$store_id)->paginate(20 ,false, [
            'query' => request()->param(),
        ]);
        return view("operate_broadcast",["broadcast"=>$broadcast]);
    }


    /**
     * [广播消息保存]
     * GY
    */
    public function operate_broadcast_save(Request $request){
        if($request->isPost()){
            $store_id = Session::get("store_id");
            $data = $request->param();
            $data["status"] = 1;
            $data["store_id"] = $store_id;
            $bool = db("broadcast")->insert($data);
            if($bool){
                $this->success('添加成功', 'admin/operate/operate_broadcast');
            } else {
                $this ->error("添加失败","admin/operate/operate_broadcast");
            }
        }
        
        return view("operate_broadcast_add");
    }


    /**
     * [常见问题编辑]
     * GY
    */
    public function operate_broadcast_edit($id){
        $broadcast_new = db("broadcast")->where("id",$id)->select();   
        return view("operate_broadcast_edit",["broadcast_new"=>$broadcast_new]);
    }
    
    
    /**
     * [常见问题更新]
     * GY
    */
    public function operate_broadcast_update(Request $request){
        if($request->isPost()){
            $broadcast = $request->param();

            $bool = db("broadcast")->where('id', $request->only(["id"])["id"])->update($broadcast);
            if($bool){
                $this->success('更新成功', 'admin/operate/operate_broadcast');
            } else {
                $this ->error("更新失败","admin/operate/operate_broadcast");
            }
        }
    }


    /**
     * [常见问题删除]
     * GY
    */
    public function operate_broadcast_delete($id){
        $bools = db("broadcast")->where("id",$id)->delete();     
        if($bools){
            $this->success('删除成功', 'admin/operate/operate_broadcast');
        } else {
            $this ->error("删除失败","admin/operate/operate_broadcast");
        }
    }


    /**
     * [广播消息状态修改]
     * GY
    */
    public function operate_broadcast_status(Request $request){
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("broadcast")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/operate/operate_broadcast"));
                } else {
                    $this->error("修改失败", url("admin/operate/operate_broadcast"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("broadcast")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/operate/operate_broadcast"));
                } else {
                    $this->error("修改失败", url("admin/operate/operate_broadcast"));
                }
            }
        }
    }


    /**
     * 发票显示
     * GY
    */
    public function operate_receipt_index(){
        $store_id = Session::get("store_id");
        $store = config("store_id");
        $data = db("receipt")->where("store_id","EQ",$store_id)->select();
        if(empty($data)){
            $datas = db("receipt")->where("store_id","EQ",$store)->find();
            $datas['store_id'] = $store_id;
            unset($datas['id']);
            $bool = db("receipt")->insert($datas);
            $data = db("receipt")->where("store_id","EQ",$store_id)->select();
        }
        return view('operate_receipt_index',['data'=>$data]);
    }

    /**
     * 发票更新
     * GY
    */
    public function operate_receipt_update(Request $request){
        if($request -> isPost()){
            $store_id = Session::get("store_id");
            $status = isset($request->only(["status"])["status"])?$request->only(["status"])["status"]:null;
            $common = $request->only(["common"])["common"];
            $senior = $request->only(["senior"])["senior"];
            if(($common > 30 ) ||  ($senior > 30)  || ($senior < 0) || ($common < 0)){
                $this ->error("更新失败,请参照输入规则","admin/operate/operate_receipt_index");
            } 
            if(empty($status)){
                $data=[
                    'common'=>$common,
                    'senior'=>$senior
                ];
            } else {
                if(count($status) == 1){
                    if(isset($status[0])){
                        $data=[
                            'status'=>$status[0],
                            'common'=>$common,
                            'senior'=>$senior
                        ];
                    } else {
                        $data=[
                            'status'=>$status[1],
                            'common'=>$common,
                            'senior'=>$senior
                        ]; 
                    }
                } else {
                    $data=[
                        'status'=> 3,
                        'common'=>$common,
                        'senior'=>$senior
                    ];
                }
            }
     
            $bool = db("receipt") -> where("store_id",$store_id) -> update($data);
            if($bool){
                $this->success('更新成功', 'admin/operate/operate_receipt_index');
            } else {
                $this->error('更新失败，请稍后重试', 'admin/operate/operate_receipt_index');
                
            }           
        }     
    }

       /**
     * [常见问题显示]
     * GY
    */
    public function operate_problem_search(){
        $store_id = Session::get("store_id");
        $titles = input('titles')?input('titles'):null;
        $pid = input('pid')?input('pid'):null;

        if(!empty($titles) && !empty($pid)){
            $problem = db("common_ailment")
            ->where("store_id","EQ",$store_id)
            ->where("title", "like", "%" . $titles . "%")
            ->where("pid","EQ",$pid)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        } elseif(empty($titles) && !empty($pid)){
            $problem = db("common_ailment")
            ->where("store_id","EQ",$store_id)
            ->where("pid","EQ",$pid)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        } elseif(!empty($titles) && empty($pid)){
            $problem = db("common_ailment")
            ->where("store_id","EQ",$store_id)
            ->where("title", "like", "%" . $titles . "%")
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        } else {
            $problem = db("common_ailment")->where("store_id","EQ",$store_id)->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        }

        return view("operate_problem",["problem"=>$problem]);
    }


    /**
     * [模板消息设置]
     * GY
    */
    public function templet_message_index(){
        $store_id = Session::get("store_id");
        $data = TempletMessage::getTemplet($store_id);
        // if(empty($data)){
        //     $categoryed = Db::table('tp_templet_message')->where("store_id","EQ",6)->select();
        //     foreach($categoryed as $kk => $val){
        //         unset($categoryed[$kk]['id']);
        //         $categoryed[$kk]['store_id'] = $store_id;
                
        //     }
        
        //     foreach($categoryed as $kv => $ve){
        //         $boole = Db::table('tp_templet_message')->insert($ve);
        //     }
        //     $data = TempletMessage::getTemplet($store_id);

        // }
        return view("templet_message_index",['data' => $data]);
    }

    /**
     * [模板消息设置开关]
     * GY
     * @param $id
     * @param $name
    */
    public function templet_switch(Request $request){
        if($request->isPost()){
            $param = Request::instance()->param();
            $store_id = Session::get("store_id");
            $id = $request->only('id');
            $validate = new Validate([
                ['id', 'require', 'id不能为空'],  
                ['name', 'require', '字段名不能为空'],  
                ['status', 'require', '字段值不能为空'],  
            ]);
            //验证部分数据合法性
            if (!$validate->check($param)) {
                return jsonError($validate->getError());
            }
            $param['store_id'] = $store_id;
            $rest_data = array(
                'store_id' => $store_id,
            );
            if($param['name'] == 'sms_status'){
                $rest_data['sms_status'] = $param['status'];
            } elseif($param['name'] =='message_status'){
                $rest_data['message_status'] = $param['status'];
            } elseif($param['name'] =='notify_status'){
                $rest_data['notify_status'] = $param['status'];
            }
            if(isset($rest_data['message_status']) && $rest_data['message_status'] == 1){
                $template = TempletMessage::detail(['id'=>$param['id']]);
                if(empty($template['template_id'])){
                    return jsonError('请添加模板id');
                }
            }
            $rest = (new TempletMessage)-> allowField(true)->save($rest_data,$id);
            return jsonSuccess('设置成功');
        } 
    }

    /**
     * [模板消息编辑模板ID]
     * GY
     * @param $id
     * @param $name
    */
    public function templetid_edit(Request $request){
        if($request->isPost()){
            $id = $request->only('id');
            $param = Request::instance()->param();
            $store_id = Session::get("store_id");
            $validate = new Validate([
                ['id', 'require', 'id不能为空'],
                ['template_id', 'require', 'template_id不能为空'],

            ]);
            //验证部分数据合法性
            if (!$validate->check($param)) {
                return jsonError($validate->getError());
            }
            $param['store_id'] = $store_id;
            $rest = (new TempletMessage)-> allowField(true)->save($param,$id);
            if(!$rest){
                return jsonError('编辑失败');
            }
            return jsonSuccess('编辑成功');
        } 
    }


    /**
     * [模板id页面显示]
     * GY
     * @param $id
     * @param $name
    */
    public function templetid_index(Request $request){
        if($request->isPost()){
            $param = Request::instance()->param();
            $validate = new Validate([
                ['id', 'require', 'id不能为空'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($param)) {
                return jsonError($validate->getError());
            }
            $rest = (new TempletMessage)->get($param);
            return jsonSuccess("发送成功",['template_id' => $rest['template_id']]);
        } 
    }

}