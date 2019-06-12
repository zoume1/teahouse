<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21 0021
 * Time: 11:27
 */
namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;

class Test extends  Controller{
    /*图标库*/
    public function selecticon(){
        return $this->fetch('icon');
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:页面选择
     **************************************
     * @return mixed
     */
    public function select_url(){
        $uniacid = input('appletid');
        $tplid = input('tplid_only'); //模板id
        if(!empty(Session::get('goods_names'))){
           $da_change['goods_name']=Session::get('goods_names');
        }else{
            $da_change =Db::table("tb_set_meal_order")
                 ->alias('a')
                ->field("a.id,a.order_number,a.create_time,a.goods_name,a.goods_quantity,
                    a.amount_money,a.store_id,a.images_url,a.store_name,a.unit,a.cost,a.enter_all_id")
                ->where("store_id", $uniacid)
                ->where("audit_status",1)
                ->order('id desc')
               ->find();
        }
       
       if(!$tplid){
            $tplid = Db::table('ims_sudu8_page_diypagetpl')->where("uniacid",$uniacid)->where("status",1)->find()['id'];
        }
        $pageid = explode(",",Db::table('ims_sudu8_page_diypagetpl')->where("uniacid",$uniacid)->where("id",$tplid)->field("pageid")->find()['pageid']); //当前模板拥有的页面id
        //页面模板（自定义）
        $diypage = Db::table('ims_sudu8_page_diypage')->where("uniacid",$uniacid)->where("id","in",$pageid)->field("id,tpl_name")->select();
       //文章
        $article = Db::table('ims_sudu8_page_products')->where("uniacid",$uniacid)->where("type","showArt")->field("id,title")->select();
        //商品
//        $pro = Db::table('ims_sudu8_page_products')->where("uniacid",$uniacid)->where("type","neq","showArt")->where("type","neq","showPic")->where("type","neq","wxapp")->field("id,title,type,is_more")->select();
//        $pro = Db::table('ims_sudu8_page_products')->where("uniacid",$uniacid)->where("type","neq","showArt")->where("type","neq","showPic")->where("type","neq","wxapp")->field("id,title,type,is_more")->select();
//        if($pro){
//            foreach ($pro as $k => $v) {
//                if($v['is_more'] == 1){
//                    $pro[$k]['type'] = "showPro_lv";
//                }
//            }
//        }
        $pro =Db::table("tb_goods")->field("id,goods_name")->select();

        //栏目
//       $pic = Db::table('ims_sudu8_page_products')->where("uniacid",$uniacid)->where("type","showPic")->field("id,title")->select();
       //二级栏目
//        $cates = Db::table('ims_sudu8_page_cate')
//            ->where("uniacid",$uniacid)
//            ->where("cid",0)
//            ->field("id,name,type")
//            ->select();
//        if($cates){
//            foreach ($cates as $k => $v) {
//                if($v['type'] == "showPro"){
//                    $cates[$k]['type'] = "listPro";
//                }
//                if($v['type'] == "showPic" || $v['type'] == "showArt"){
//                    $cates[$k]['type'] = "listPic";
//                }
//                $subcate = Db::table('ims_sudu8_page_cate')->where("uniacid",$uniacid)->where("cid",$v['id'])->field("id,name,type")->select();
//                foreach ($subcate as $ki=> $vi) {
//                    if($vi['type'] == "showPro"){
//                        $subcate[$ki]['type'] = "listPro";
//                    }
//                    if($vi['type'] == "showPic" || $vi['type'] == "showArt"){
//                        $subcate[$ki]['type'] = "listPic";
//                    }
//                }
//                $cates[$k]['subcate'] = $subcate;
//            }
//
//        }
        //商品栏目
        $pic =Db::table("tb_wares")->where("pid",0)->field("id,name")->select();
        $cates =Db::table("tb_wares")->where("pid",0)->field("id,name")->select(); //一级
        foreach ($cates as $key=>$value){
            $catess =Db::table("tb_goods")->where("pid",$value['id'])->field("id,goods_name name")->select();
            $cates[$key]['subcate'] =$catess;
        }
        //活动栏目
//        $pic =Db::table("tb_goods_type")->where("pid",0)->field("id,name")->select();
//        $cates =Db::table("tb_goods_type")->where("pid",0)->field("id,name")->select(); //一级
//        foreach ($cates as $key=>&$value){
//            $catess =Db::table('tb_goods_type')->where("pid",$value["id"])->field("id,name")->select();
//            $value['subcate'] =$catess;
//        }

       $this->assign('da_change',$da_change);
        $this->assign("diypage",$diypage);
        $this->assign("article",$article);
        $this->assign("pro",$pro);
        $this->assign("pic",$pic);
        $this->assign("cates",$cates);
        $this->assign("uniacid",$uniacid);
        return $this->fetch('selecturl');
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:公告来源
     **************************************
     * @return mixed
     */
    public function select_source(){
        $uniacid = input("appletid");
        $type = input('type');
        switch ($type){
            //公告栏目
            case 'noticcate':
                $list = Db::table("ims_sudu8_page_cate")->where("uniacid",$uniacid)->where("type","showArt")->where("cid",0)->field("id,name")->select();
                foreach ($list as $key => &$value) {
                    $subcate = Db::table("ims_sudu8_page_cate")->where("uniacid",$uniacid)->where("type","showArt")->where("cid",$value['id'])->field("id,name")->select();
                    $value['subcate'] = $subcate;
                }
//                $list=Db::table("tb_broadcast")->field("id,title")->select();
//                foreach ($list as $key =>$value){
//                    $list[$key]['name'] =$value["title"];
//                    $subcate=Db::table("tb_broadcast")->field("id,title name")->select();
//                    $value['subcate'] =$subcate;
//                }
                break;
                //这是商品栏目的来源
            case 'goodscate':
//                $list = Db::table("ims_sudu8_page_cate")->where("uniacid",$uniacid)->where("type","showPro")->where("cid",0)->field("id,name")->select();
//
//                foreach ($list as $key => &$value) {
//                    $subcate = Db::table("ims_sudu8_page_cate")->where("uniacid",$uniacid)->where("type","showPro")->where("cid",$value['id'])->field("id,name")->select();
//                    $value['subcate'] = $subcate;
//                }

                //商品栏目
//                $pic =Db::table("tb_wares")->where("pid",0)->field("id,name")->select();
//                $cates =Db::table("tb_wares")->where("pid",0)->field("id,name")->select(); //一级
//                foreach ($cates as $key=>$value){
//                    $catess =Db::table("tb_goods")->where("pid",$value['id'])->field("id,goods_name name")->select();
//                    $cates[$key]['subcate'] =$catess;
//                }
                //商品分类
                $list =Db::table("tb_wares")->where(["pid"=>0,'store_id'=>$uniacid])->field("id,name")->select();
                foreach ($list as $key=>&$value){
                    $list[$key]['subcate'] =null;
                }
                break;
            case 'piccate':
                $list = Db::table("ims_sudu8_page_cate")->where("uniacid",$uniacid)->where("type","showPic")->where("cid",0)->field("id,name")->select();
                foreach ($list as $key => &$value) {
                    $subcate = Db::table("ims_sudu8_page_cate")->where("uniacid",$uniacid)->where("type","showPic")->where("cid",$value['id'])->field("id,name")->select();
                    $value['subcate'] = $subcate;
                }
                break;

            case 'picartcate':

                $list = Db::query("SELECT id,name,type FROM ims_sudu8_page_cate WHERE `uniacid` = {$uniacid} AND `cid` = 0 AND (`type` = 'showPic' or `type` = 'showArt')");
                foreach ($list as $key => &$value) {
                    $subcate = Db::query("SELECT id,name,type FROM ims_sudu8_page_cate WHERE `uniacid` = {$uniacid} AND (`type` = 'showPic' or `type` = 'showArt') AND cid = {$value['id']}");
                    $value['subcate'] = $subcate;
                }
                break;

            case 'articlecate':
                $list = Db::table("ims_sudu8_page_cate")->where("uniacid",$uniacid)->where("type","showArt")->where("cid",0)->field("id,name")->select();
                foreach ($list as $key => &$value) {
                    $subcate = Db::table("ims_sudu8_page_cate")->where("uniacid",$uniacid)->where("type","showArt")->where("cid",$value['id'])->field("id,name")->select();
                    $value['subcate'] = $subcate;
                }
                break;
            case 'ptcate':

                $list = Db::table("ims_sudu8_page_pt_cate")->where("uniacid",$uniacid)->field("id,title as name")->select();
                foreach ($list as $key => &$value) {
                    $value['subcate'] = "";
                }
                break;
            case 'formcate':

                $list = Db::table("ims_sudu8_page_formlist")->where("uniacid",$uniacid)->field("id,formname as name")->select();
                foreach ($list as $key => &$value) {
                    $value['subcate'] = "";
                }
                break;
        }
        $this->assign("type",$type);
        $this->assign("list",$list);
        $this->assign("uniacid",$uniacid);
        return $this->fetch('selectsource');

    }

}
