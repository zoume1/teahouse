<?php


namespace app\admin\behavior;
use think\Controller;
use think\Session;
use think\Config;

class checkLogin extends Controller {
    use \traits\controller\Jump;
    public function run(){
        $arr = request()->routeInfo();
        if(!preg_match("/admin\/Login/",$arr["route"])){
            $data = Session::get("user_id");
            if(empty($data)){
                $this->error("请登录",url("/admin/index","",false));
                exit();
            }
            $user_info = Session::get("user_info");
            $store_id = Session::get('store_id');
            $store = [77,296];
            if(in_array($store_id,$store)){
                $menu_list = db("menu")->where('status','<>',0)->select();
            } else {
                $menu_list = db("menu")->where('status','=',1)->select();
            }
            $role = db("role")->where("id",$user_info[0]['role_id'])->field("menu_role_id")->select();
            $role = explode(",",$role[0]["menu_role_id"]);
            //在控制台获取当前的url地址
 

            // if(!in_array($store_id,$store)){
            //     foreach($role as $k => $v){
            //         if (in_array($role[$k], $menu_id)){
            //             unset($role[$k]);
            //         }
            //     }
            // }
            $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['REQUEST_URI'];
            //var_dump($url);

            $explode = explode("/",$url);
            if(count($explode) > 3){
                $url = "/".$explode[1]."/".$explode[2];
            }

            $if_url = 0;
            
            foreach ($menu_list as $key => $values) {
                if (!in_array($values['id'], $role)){
                    unset($menu_list[$key]);
                } else {
                    if ($values['url'] == $url) {
                        $if_url = 1;
                    }
                }
            }
            $menu_list = _tree_hTree(_tree_sort($menu_list,"sort_number"));
            config("menu_list",$menu_list);//节点信息
            //halt(Config::get("menu_list"));
        }
    }
}