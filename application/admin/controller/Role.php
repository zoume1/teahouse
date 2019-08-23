<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Session;

class Role extends Controller
{
    /**
     **************gy*******************
     * @param Request $request
     * Notes:角色列表
     **************************************
     * @param Request $request
     * @return \think\response\View
     */
    public function index(Request $request){
        //判断是admin登录还是其他登录
        $store_id =Session::get("store_id");
        if(!empty($store_id)){
            $role_lists = db("role")->where("store_id",$store_id)->select();
            foreach($role_lists as $key=>$value){
                if($value["pid"]){
                    $rs = db("role")
                        ->where("id",$value['pid'])
                        ->field("name")
                        ->find();
                    $role_lists[$key]["parent_depart_name"] = $rs["name"];
                }
            }
        }else{
            $role_lists = db("role")->select();
            foreach($role_lists as $key=>$value){
                if($value["pid"]){
                    $rs = db("role")->where("id",$value['pid'])->field("name")->find();
                    $role_lists[$key]["parent_depart_name"] = $rs["name"];
                }
            }
        }
        $url = 'admin/role/index';
        $pag_number = 20;
        $role_lists = paging_data($role_lists,$url,$pag_number);
        return view("index",["role_lists"=>$role_lists]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:角色搜索
     **************************************
     * @param Request $request
     */
    public function role_search(){
        $search_a =input("search_a") ? input("search_a"):null;
        $search_b  =input("search_b") ? input("search_b"):null;
        $store_id =Session::get("store_id");
        if(!empty($store_id)){
            if(!empty($search_a)){
                $condition =" `name` like '%{$search_a}%'";
                $role_lists = db("role")->where($condition)->where("store_id",$store_id)->select();
                foreach($role_lists as $key=>$value){
                    if($value["pid"]){
                        $rs = db("role")
                            ->where("id",$value['pid'])
                            ->field("name")
                            ->find();
                        $role_lists[$key]["parent_depart_name"] = $rs["name"];
                    }
                }
            }else{
                if(!empty($search_b)){
                    $condition =" `status` = '{$search_b}'";
                    $role_lists = db("role")->where($condition)->where("store_id",$store_id)->select();
                    foreach($role_lists as $key=>$value){
                        if($value["pid"]){
                            $rs = db("role")
                                ->where("id",$value['pid'])
                                ->field("name")
                                ->find();
                            $role_lists[$key]["parent_depart_name"] = $rs["name"];
                        }
                    }
                }else{
                    $this->redirect("admin/role/index");
                }
            }
        }else{
            if(!empty($search_a)){
                $condition =" `name` like '%{$search_a}%'";
                $role_lists = db("role")->where($condition)->select();
                foreach($role_lists as $key=>$value){
                    if($value["pid"]){
                        $rs = db("role")
                            ->where("id",$value['pid'])
                            ->field("name")
                            ->find();
                        $role_lists[$key]["parent_depart_name"] = $rs["name"];
                    }
                }
            }else{
                if(!empty($search_b)){
                    $condition =" `status` = '{$search_b}'";
                    $role_lists = db("role")->where($condition)->select();
                    foreach($role_lists as $key=>$value){
                        if($value["pid"]){
                            $rs = db("role")
                                ->where("id",$value['pid'])
                                ->field("name")
                                ->find();
                            $role_lists[$key]["parent_depart_name"] = $rs["name"];
                        }
                    }
                }else{
                    $this->redirect("admin/role/index");
                }
            }
        }
        return view("index",["role_lists"=>$role_lists]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:角色节点查询
     **************************************
     * @param Request $request
     * @return \think\response\View
     */
    public function add(Request $request){
        $store_id =Session::get("store_id");
        if(!empty($store_id)){
            $role_id =db("admin")->where("store_id",$store_id)->value("role_id");
            $roles = db("role")->where("id",$role_id)->field("id,name")->select();
            $menu_list = db("menu")
                ->where("status", "<>", 0)
                ->where("pid","NEQ",172)
                ->where("id","NEQ",172)
                ->where("id","NEQ",185)
                ->select();
            $menu_lists = _tree_hTree(_tree_sort($menu_list, "sort_number"));
        }else{
            $roles = db("role")->field("id,name")->select();
            $menu_list = db("menu")->where("status", "<>", 0)->select();
            $menu_lists = _tree_hTree(_tree_sort($menu_list, "sort_number"));
        }
        return view("save",["roles"=>$roles,"menu_lists"=>$menu_lists]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:角色添加入库
     **************************************
     * @param Request $request
     */
    public function save(Request $request){
        $store_id =Session::get("store_id");
        $data = $request->only(["name","pid","status","desc"]);
        $data["menu_role_id"] = empty($request->only(["menu_role_id"])["menu_role_id"]) ? '' : implode(',', $request->only(["menu_role_id"])["menu_role_id"]);
       if(!empty($store_id)){
           $data["store_id"] =$store_id;
       }
        $boolData = db("role")->insert($data);
        if($boolData){
            $this->success("角色添加成功",url("admin/role/index"));
        }else{
            $this->error("添加角色失败",url("admin/role/add"));
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:角色删除
     **************************************
     * @param $id
     */
    public function del($id){
        $bool = model("role")->where("id",$id)->delete();
        if($bool){
            $this->success("删除成功",url("admin/role/index"));
        }else{
            $this->error("删除失败",url("admin/role/index"));
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:角色编辑
     **************************************
     * @param $id
     * @return \think\response\View
     */
    public function edit($id){
        $store_id =Session::get("store_id");
        if(!empty($store_id)){
            $roles = db("role")->where("id",$id)->select();
            $role_name = db("role")
                ->where("id",$roles[0]["pid"])
                ->field("name,id")
                ->select();
            $menu_list = db("menu")
                ->where("status", "<>", 0)
                ->where("pid","NEQ",172)
                ->where("id","NEQ",185)
                ->where("id","NEQ",172)
                ->select();
            $menu_lists = _tree_hTree(_tree_sort($menu_list, "sort_number"));
            $menu_role =explode(",",$roles[0]["menu_role_id"]);
            $memu_check =db("menu")->where("status","<>",0)->where("id","in",$menu_role)->field("id")->select();
            foreach ($memu_check as $keys=>$vals){
                $menu_array[] =$vals["id"];
            }
        }else{
            $roles = db("role")->where("id",$id)->select();
            $role_name = db("role")->where("id",$roles[0]["pid"])->field("name,id")->select();
            $menu_list = db("menu")->where("status","<>",0)->select();
            $menu_lists = _tree_hTree(_tree_sort($menu_list,"sort_number"));
            $menu_role =explode(",",$roles[0]["menu_role_id"]);
            $memu_check =db("menu")
                ->where("status","<>",0)
                ->where("id","in",$menu_role)
                ->field("id")
                ->select();
            foreach ($memu_check as $keys=>$vals){
                $menu_array[] =$vals["id"];
            }
        }
        return view("edit",["roles"=>$roles,"menu_lists"=>$menu_lists,"role_name"=>$role_name,"memu_check"=>$menu_array]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:[角色修改]
     **************************************
     * @param Request $request
     * @param $id
     */
    public function updata(Request $request,$id){
        $data = $request->only(["name","pid","status","desc"]);
        $data["menu_role_id"] = empty($request->only(["menu_role_id"])["menu_role_id"]) ? '' : implode(',', $request->only(["menu_role_id"])["menu_role_id"]);
        $boolData = db("role")->where("id",$id)->update($data);
        if($boolData){
            $this->success("角色修改成功",url("admin/role/index"));
        }else{
            $this->error("添加修改失败",url("admin/role/add"));
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:角色状态修改
     **************************************
     * @param Request $request
     */
    public function status(Request $request){
        if($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("role")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/role/index"));
                } else {
                    $this->error("修改失败", url("admin/role/index"));
                }
            }
            if($status == 1){
                $id = $request->only(["id"])["id"];
                $bool = db("role")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/role/index"));
                } else {
                    $this->error("修改失败", url("admin/role/index"));
                }
            }
        }
    }



        /**
     **************gy*******************
     * @param Request $request
     * Notes:角色列表
     **************************************
     * @param Request $request
     * @return \think\response\View
     */
    public function search(Request $request){
        $search_a = input('search_a')?input('search_a'):null;
        if(!empty($search_a)){
            $condition =" `name` like '%{$search_a}%'";
            $role_lists = db("role")->where($condition)->select();
        } else {
            $role_lists = db("role")->select();
        }

        foreach($role_lists as $key=>$value){
            if($value["pid"]){
                $rs = db("role")->where("id",$value['pid'])->field("name")->find();
                $role_lists[$key]["parent_depart_name"] = $rs["name"];
            }
        }
        $url = 'admin/role/index';
        $pag_number = 20;
        $role_lists = paging_data($role_lists,$url,$pag_number);   
        return view("index",["role_lists"=>$role_lists]);
    }


}