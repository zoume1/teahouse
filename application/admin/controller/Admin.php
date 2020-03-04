<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\paginator\driver\Bootstrap;
class Admin extends Controller
{
    /**
     **************gy*******************
     * @param Request $request
     * Notes: [管理员列表]
     **************************************
     * @param Request $request
     * @return \think\response\View
     */
    public function index(Request $request){
        $store_id = Session::get("store_id");
        //admin进来
        if(empty($store_id)){
            $account_list = db("admin")->order("id")->select();
            foreach ($account_list as $key=>$value){
                $account_list[$key]["role_name"] = db("role")->where("id",$value["role_id"])->value("name");
            }
            $roleList = getSelectList("role");
        } else {
            $account_list = db("admin")->where("store_id",$store_id)->order("id")->select();
            foreach ($account_list as $key=>$value){
                $account_list[$key]["role_name"] = db("role")->where("id",$value["role_id"])->value("name");
            }
            $roleList = getSelectListes("role");
        }
        $url = 'admin/admin/index';
        $pag_number = 20;
        $account_list = paging_data($account_list,$url,$pag_number);
        return view("index",["account_list"=>$account_list,"roleList"=>$roleList]);
    }

    /**
     **************gy*******************
     * @param Request $request
     * Notes:管理员查询
     **************************************
     * @return \think\response\View
     */
    public function add(){
        $store_id = Session::get("store_id");
        if(!empty($store_id)){
            $roles = db("role")
                ->where("store_id",$store_id)
                ->where("status","1")
                ->field("id,name")
                ->select();
            $roleList = db("role")->where("store_id",$store_id)->field("id,name")->select();
        }else{
            $roles = db("role")->where("status","1")->field("id,name")->select();
            $roleList = getSelectList("role");
        }
        return view("save",["role"=>$roles,"roleList"=>$roleList]);
    }

    /**
     **************gy*******************
     * @param Request $request
     * Notes:管理员添加入库
     **************************************
     * @param Request $request
     */
    public function save(Request $request){
        $data = $request->param();
        $store_id =Session::get("store_id");
        if(!empty($store_id)){
            $data["store_id"] =$store_id;
        }
        $data["passwd"] = password_hash($data["passwd"],PASSWORD_DEFAULT);
        $data["stime"] = date("Y-m-d H:i:s");
        $data["admin_status"] = 1;
        $data['role_id'] = 8;
        $boolData = model("Admin")->sSave($data);
        if($boolData){
            $this->redirect("admin/admin/index");
        }else{
            $this->redirect("admin/admin/add");
        }
    }

    /**
     **************gy*******************
     * @param Request $request
     * Notes:管理员删除
     **************************************
     * @param $id
     */
    public function del($id){
        $bool = model("Admin")->where("id",$id)->delete();
        if($bool){
            $this->redirect("admin/admin/index");
        }else{
            $this->error("admin/admin/index");
        }
    }

    /**
     **************gy*******************
     * @param Request $request
     * Notes:管理员编辑
     **************************************
     * @param $id
     * @return \think\response\View
     */
    public function edit($id){
        $store_id =Session::get("store_id");
        if(!empty($store_id)){
            $admin = db("Admin")->where("id","$id")->where("store_id",$store_id)->select();
            $roleList = db("role")->where("store_id",$store_id)->field("id,name")->select();
        }else{
            $admin = db("Admin")->where("id","$id")->select();
            $roleList = getSelectList("role");
        }
        return view("edit",["admin"=>$admin,"roleList"=>$roleList]);
    }

    /**
     **************gy*******************
     * @param Request $request
     * Notes:管理员修改
     **************************************
     * @param Request $request
     */
    public function updata(Request $request){
        $data = $request->param();
        $data["passwd"] = password_hash($data["passwd"],PASSWORD_DEFAULT);
        $data["stime"] = date("Y-m-d H:i:s");
        $id = $request->only(['id'])['id'];
        $bool = db("Admin")->where('id', $id)->update($data);
        if ($bool){
            $this->success("编辑成功","admin/admin/index");
        }else{
            $this->error("编辑失败","admin/admin/edit");
        }
    }


    /**
     **************gy*******************
     * @param Request $request
     * Notes:管理员状态修改
     **************************************
     * @param Request $request
     */
    public function status(Request $request){
        if($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("Admin")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect("admin/admin/index");
                } else {
                    $this->error("修改失败", "admin/admin/index");
                }
            }
            if($status == 1){
                $id = $request->only(["id"])["id"];
                $bool = db("Admin")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect("admin/admin/index");
                } else {
                    $this->error("修改失败","admin/admin/index");
                }
            }
        }
    }


    /**
     **************gy*******************
     * @param Request $request
     * Notes:密码修改
     **************************************
     * @param Request $request
     */
    public function passwd(Request $request){
        $id = $request->only(['id'])['id'];
        $passwd = md5($request->only(["passwd"])["passwd"]);
        $bool = db("Admin")->where("id",$id)->update(["passwd"=>$passwd]);
        if($bool){
            $this->success("修改成功，请重新登录", "admin/Login/index");
        }
    }

        /**
     **************gy*******************
     * @param Request $request
     * Notes: [管理员列表搜索]
     **************************************
     * @param Request $request
     * @return \think\response\View
     */
    public function search(){
        $search_a = input('goods_number')?input('goods_number'):null;
        if(!empty($goods_number)){
            $condition =" `account` like '%{$search_a}%' or `name` like '%{$search_a}%'";
            $account_list = db("admin")
            ->order("id")
            ->where($condition)
            ->select();
        } else {
            $account_list = db("admin")
            ->order("id")
            ->select();
        }
        foreach ($account_list as $key=>$value){
            $account_list[$key]["role_name"] = db("role")->where("id",$value["role_id"])->value("name");
        }
        $roleList = getSelectList("role");
        $url = 'admin/admin/index';
        $pag_number = 20;
        $account_list = paging_data($account_list,$url,$pag_number);
        return view("index",["account_list"=>$account_list,"roleList"=>$roleList]);
    }



}