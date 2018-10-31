<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/22
 * Time: 19:53
 */
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Db;

class Photo extends Controller{


    /**
     * 图片库
     * 邹梅
     */
    public function index(){

        $this->getImagesInformation();
        return view("photo_index");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * 上传图片库
     **************************************
     */
    public function images_online_push(Request $request){
        if($request->isPost()){
            $images_data =[];
            $file =$request->file("goods_show");
            foreach ($file as $k=>$v){
                $info = $v->move(ROOT_PATH . 'public' . DS . 'upload');
                $images_url = str_replace("\\","/",$info->getSaveName());
                $images_data[] = ["images"=>$images_url,"content"=>'图片库图片'];
            }
            if(!empty($images_data)){
                $res_boll = model('images_online')->saveAll($images_data);
                if(!empty($res_boll)){
                    $this->success('上传成功');
                }
            }

        }
    }

    /**
     **************李火生*******************
     * 显示图片库图片信息
     **************************************
     */
    public function getImagesInformation(){
        $datas =Db::name('images_online')->select();
        $this->assign('images_url',$datas);
    }





}