<?php


namespace app\admin\behavior;
use think\Controller;
use think\Session;
use think\Config;

class checkNfc extends Controller {
    use \traits\controller\Jump;
    public function run(){
        $arr = request()->routeInfo();
        $url2=$_SERVER['REQUEST_URI'];
        if(preg_match("/nfc\/check/",$url2)){
             //走的是扫码的流程
        }else{

        }
    }
}