<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/28 0028
 * Time: 16:57
 */
namespace app\api\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\paginator\driver\Bootstrap;
use think\Session;
class  Wxapps extends  Controller{
    /*Diy方法开始*/
    public function doPagehomepage()
    {
        $uniacid = input("uniacid");

        $da_change =Db::table("tb_set_meal_order")
                 ->alias('a')
                ->field("a.id,a.order_number,a.create_time,a.goods_name,a.goods_quantity,
                    a.amount_money,a.store_id,a.images_url,a.store_name,a.unit,a.cost,a.enter_all_id")
                ->where("store_id", $uniacid)
                ->where("audit_status",1)
                ->order('id desc')
               ->find();
      
        if(!empty($da_change)){
            if($da_change['enter_all_id'] <= 6){
                $da_change['enter_all_id'] = 1;
            }
            if(  ($da_change['enter_all_id'] > 6) && ($da_change['enter_all_id'] <= 17)){
                $da_change['enter_all_id'] = 2;
            }
            if( $da_change['enter_all_id'] > 17){
                $da_change['enter_all_id'] = 3;
            }
        }
        $res = Db::table('ims_sudu8_page_base')->where("uniacid", $uniacid)->field("homepage")->find();
        if (!$res) {
            $res['homepage'] = 1;
        }
//       Db::table('ims_sudu8_page')->where('uniacid',$uniacid)->update(array("visitnum" => $fxsid));
        Db::execute("UPDATE ims_sudu8_page_base set visitnum = visitnum + 1 where uniacid = ".$uniacid);

        //找到使用的模板(综合商场模板)
        $tplinfo = Db::table('ims_sudu8_page_diypagetpl')->where("uniacid", $uniacid)->where("status", 1)->find();
        $pageids = explode(",", $tplinfo['pageid']);
        if ($tplinfo) {
            $pageid = Db::table('ims_sudu8_page_diypage')
                ->where("uniacid", $uniacid)
                ->where("id", "in", $pageids)
                ->where("index", 1)
                ->field("id")
                ->find();
        } else {
            $pageid = Db::table('ims_sudu8_page_diypage')
                ->where("uniacid", $uniacid)
                ->where("index", 1)
                ->field("id")
                ->find();
        }
        $foot = Db::table('ims_sudu8_page_diypageset')->where("uniacid", $uniacid)->field("foot_is")->find();
        if ($pageid) {
            $res['pageid'] = $pageid['id'];
        } else {
            $res['pageid'] = 0;
        }
        $res['foot_is'] = $foot['foot_is'] ? $foot['foot_is'] : 1;
        $result['data'] = $res;
        $result['data']['test_name'] = $da_change;

        return json_encode($result);
    }

    //手机号自动获取时的sessionkey
    public function doPagegetNewSessionkey()
    {
        $uniacid = input('uniacid');
        $app = Db::table('applet')->where("id", $uniacid)->find();
        $appid = $app['appID'];
        $appsecret = $app['appSecret'];
        $code = input('code');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $appid . "&secret=" . $appsecret . "&js_code=" . $code . "&grant_type=authorization_code";
        $weixin = file_get_contents($url);
        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
        $array = get_object_vars($jsondecode);//转换成数组
        $sessionKey = $array['session_key'];
        $result['data'] = $sessionKey;
        return json_encode($result);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:返回小程序数据给前端小程序
     **************************************
     * @return string
     */
    public function doPageDiypage()
    {

        $uniacid = input("uniacid");
        $pageid = input("pageid");
        
        $da_change =Db::table("tb_set_meal_order")
                 ->alias('a')
                ->field("a.id,a.order_number,a.create_time,a.goods_name,a.goods_quantity,
                    a.amount_money,a.store_id,a.images_url,a.store_name,a.unit,a.cost,a.enter_all_id")
                ->where("store_id", $uniacid)
                ->where("audit_status",1)
                ->order('id desc')
               ->find();
               
        
        $foot = Db::table('ims_sudu8_page_diypageset')->where("uniacid", $uniacid)->field("foot_is")->find();
        $tplinfo = Db::table('ims_sudu8_page_diypagetpl')->where("uniacid", $uniacid)->where("status", 1)->find();
        $pageids = explode(",", $tplinfo['pageid']);
        if (!in_array($pageid, $pageids)) {
            $err = array();
            $err['data'] = 3;
            return json_encode($err);
            exit;
        }
        $data = Db::table('ims_sudu8_page_diypage')
            ->where("id", $pageid)
            ->where("uniacid", $uniacid)
            ->find();
        $data['foot'] = $foot['foot_is'] ? $foot['foot_is'] : 1;
        if ($data['page'] != '') {
            //将已序列化的字符串还原回 PHP 的值
            $data['page'] = unserialize($data['page']);
            if (isset($data['page']['url']) && $data['page']['url'] != "") {
                $data['page']['url'] = remote($uniacid, $data['page']['url'], 1);
            }
        }

        if ($data['items'] != '') {
            $data['items'] = array_values(unserialize($data['items']));
            include 'VideoInfo.php';
            $videoInfo = new videoInfo();
            // dump($data['items']);die;
            foreach ($data['items'] as $k => &$v) {
                if (is_array($v)) {
                    if (isset($v['id'])) {
                        if ($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'yuyin' || $v['id'] == 'feedback') {
                            if ($v['params']['backgroundimg'] != "") {
                                $v['params']['backgroundimg'] = remote($uniacid, $v['params']['backgroundimg'], 1);
                            }
                        } else if ($v['id'] == 'xnlf') {
                            $num = Db::table('ims_sudu8_page_base')->where("uniacid", $uniacid)->find();
                            $v['params']['fwl'] = $v['params']['fwl'] * 1 + $num['visitnum'] * 1;
                            if ($v['params']['backgroundimg'] != "") {
                                $v['params']['backgroundimg'] = remote($uniacid, $v['params']['backgroundimg'], 1);
                            }
                        }else if($v['id']=='ddlb'){
                            $a=Db::table('ims_sudu8_page_order')->alias('o')->join('ims_sudu8_page_user u','o.openid=u.openid')->where("o.uniacid",$uniacid)->order('o.creattime','desc')->limit(5)->field('u.nickname,u.avatar,o.creattime')->select();
                            $b=Db::table('ims_sudu8_page_pt_order')->alias('o')->join('ims_sudu8_page_user u','o.openid=u.openid')->where("o.uniacid",$uniacid)->order('o.creattime','desc')->limit(5)->field('u.nickname,u.avatar,o.creattime')->select();
                            $c=Db::table('ims_sudu8_page_duo_products_order')->alias('o')->join('ims_sudu8_page_user u','o.openid=u.openid')->where("o.uniacid",$uniacid)->order('o.creattime','desc')->limit(5)->field('u.nickname,u.avatar,o.creattime')->select();
                            $array1=array_merge($a,$b);
                            $array2=array_merge($array1,$c);
                            $date = array_column($array2, 'creattime');
                            array_multisort($date,SORT_ASC,$array2);
                            $v['count']=array();
                            for($i=count($array2)-1;$i>0;$i--){
                                $name=rawurldecode($array2[$i]['nickname']);
                                $array2[$i]['nickname']=mb_substr($name,0,1,'utf-8')."**";
//                                /upimages/05423746ceb82b4273993384f42b4ea2.jpg
                                if(!$array2[$i]['avatar']){
                                    $array2[$i]['avatar']=remote($uniacid,"/image/avatar1.jpg",1);
                                }
                                array_push($v['count'],$array2[$i]);
                            }

                        }else if ($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew') {
                            if ($v['params']['backgroundimg'] != "") {
                                $v['params']['backgroundimg'] = remote($uniacid, $v['params']['backgroundimg'], 1);
                            }
                            if ($v['data']) {
                                foreach ($v['data'] as $ki => $vi) {
                                    if ($vi['imgurl'] != "") {
                                        if (strpos($vi['imgurl'], "diypage/resource") !== false) {
                                            $v['data'][$ki]['imgurl'] = ROOT_HOST . $vi['imgurl'];
                                        } else {
                                            $v['data'][$ki]['imgurl'] = remote($uniacid, $vi['imgurl'], 1);
                                        }
                                    }
                                }
                            }

                        }else if ($v['id'] == 'contact') {
                            if ($v['params']['backgroundimg'] != "") {
                                $v['params']['backgroundimg'] = remote($uniacid, $v['params']['backgroundimg'], 1);
                            }
                            if ($v['params']['src'] != "") {
                                if (strpos($v['params']['src'], "diypage/resource") !== false) {
                                    $v['params']['src'] = ROOT_HOST . $v['params']['src'];
                                } else {
                                    $v['params']['src'] = remote($uniacid, $v['params']['src'], 1);
                                }
                            }
                            if ($v['params']['ewm'] != "") {
                                if (strpos($v['params']['ewm'], "diypage/resource") !== false) {
                                    $v['params']['ewm'] = ROOT_HOST . $v['params']['ewm'];
                                } else {
                                    $v['params']['ewm'] = remote($uniacid, $v['params']['ewm'], 1);
                                }
                            }
                        }else if ($v['id'] == 'video') {
                            if (isset($v['params']['backgroundimg']) && $v['params']['backgroundimg'] != "") {
                                $v['params']['backgroundimg'] = remote($uniacid, $v['params']['backgroundimg'], 1);
                            }
                            if ($v['params']['poster'] != "") {
                                if (strpos($v['params']['poster'], "diypage/resource") !== false) {
                                    $v['params']['poster'] = ROOT_HOST . $v['params']['poster'];
                                } else {
                                    $v['params']['poster'] = remote($uniacid, $v['params']['poster'], 1);
                                }
                            }
                        }else if ($v['id'] == 'logo' || $v['id'] == 'dp') {
                            if ($v['params']['backgroundimg'] != "") {
                                $v['params']['backgroundimg'] = remote($uniacid, $v['params']['backgroundimg'], 1);
                            }
                            if ($v['params']['src'] != "") {
                                if (strpos($v['params']['src'], "diypage/resource") !== false) {
                                    $v['params']['src'] = ROOT_HOST . $v['params']['src'];
                                } else {
                                    $v['params']['src'] = remote($uniacid, $v['params']['src'], 1);
                                }
                            }
                        }else if ($v['id'] == 'footmenu') {
                               //自定义菜单部分
                            if ($v['data']) {
                                foreach ($v['data'] as $ki => $vi) {
                                    if ($vi['imgurl'] != "") {
                                        if (strpos($vi['imgurl'], "diypage/resource") !== false) {
                                            $v['data'][$ki]['imgurl'] = ROOT_HOST . $vi['imgurl'];
                                        } else {
                                            $v['data'][$ki]['imgurl'] = remote($uniacid, $vi['imgurl'], 1);
                                        }
                                    }
                                }
                            }
                        }
                        //轮播图
                        if ($v['id'] == "banner") {
                            $v['data'] = array_values($v['data']);
                            if ($v['data']) {
                                $imginfo = explode(" ", getimagesize($v['data'][0]['imgurl'])[3]);
                                $v['params']['imgw'] = explode('"', $imginfo[0])[1];
                                $v['params']['imgh'] = explode('"', $imginfo[1])[1];
                            }
                            //富文本
                        }else if ($v['id'] == "richtext") {

                            $v['richtext'] = base64_decode($v['params']['content']);
                        }else if ($v['id'] == "feedback") {

                            if (isset($v['params']['sourceid']) && $v['params']['sourceid'] != "") {
                                $sourceid = explode(':', $v['params']['sourceid'])[1];
                                $data['forminfo'] = Db::table('ims_sudu8_page_formlist')->where("uniacid", $uniacid)->where("id", $sourceid)->find();
                                if ($data['forminfo']) {
                                    $data['forminfo']['tp_text'] = unserialize($data['forminfo']['tp_text']);
                                    foreach ($data['forminfo']['tp_text'] as $key => &$res) {
                                        if ($res["type"] != 2 && $res["type"] != 5) {
                                            $vals = explode(",", $res['tp_text']);
                                            $kk = array();
                                            foreach ($vals as $key => &$rec) {
                                                $kk['yval'] = $rec;
                                                $kk['checked'] = "false";
                                                $rec = $kk;
                                            }
                                            $res['tp_text'] = $vals;
                                        }
                                        if ($res["type"] == 2) {
                                            $vals = explode(",", $res['tp_text']);
                                            $res['tp_text'] = $vals;
                                        }
                                        $res['val'] = '';
                                    }
                                }
                            }
                        }else if ($v['id'] == "msmk") {
                            //商品分类秒杀
                             if (isset($v['params']['sourceid']) && $v['params']['sourceid'] != "") {
                                $sourceid = explode(':', $v['params']['sourceid'])[1];
                                $count = $v['params']['goodsnum']; //数量
                                $con_type = $v['params']['con_type'];
                                $con_key = $v['params']['con_key'];
                                $where = "";
                                if ($con_type == 1 && $con_key == 1) {
                                    $where = 'ORDER BY id DESC';
                                }
                                if ($con_type == 2 && $con_key == 1) {
                                    $where = 'AND type_x=1 ORDER BY id DESC';
                                }
                                if ($con_type == 3 && $con_key == 1) {
                                    $where = 'AND type_y=1 ORDER BY id DESC';
                                }
                                if ($con_type == 4 && $con_key == 1) {
                                    $where = 'AND type_i=1 ORDER BY id DESC';
                                }
                                if ($con_type == 1 && $con_key == 2) {
                                    $where = 'ORDER BY hits DESC';
                                }
                                if ($con_type == 2 && $con_key == 2) {
                                    $where = 'AND type_x=1 ORDER BY hits DESC';
                                }
                                if ($con_type == 3 && $con_key == 2) {
                                    $where = 'AND type_y=1 ORDER BY hits DESC';
                                }
                                if ($con_type == 4 && $con_key == 2) {
                                    $where = 'AND type_i=1 ORDER BY hits DESC';
                                }
                                if ($con_type == 1 && $con_key == 3) {
                                    $where = 'ORDER BY num DESC';
                                }
                                if ($con_type == 2 && $con_key == 3) {
                                    $where = 'AND type_x=1 ORDER BY num DESC';
                                }
                                if ($con_type == 3 && $con_key == 3) {
                                    $where = 'AND type_y=1 ORDER BY num DESC';
                                }
                                if ($con_type == 4 && $con_key == 3) {
                                    $where = 'AND type_i=1 ORDER BY num DESC';
                                }
                                //秒杀模块屏蔽
                                // $list = Db::query("SELECT title,thumb,id,`desc`,price,market_price,sale_num,sale_tnum,sale_time,sale_end_time,pro_kc FROM ims_sudu8_page_products WHERE `uniacid` = {$uniacid} AND `type` = 'showPro' AND `is_more` = 0 AND `flag` = 1 AND `is_sale`=0 AND  (`cid` = {$sourceid} or `pcid` = {$sourceid} ) " . $where . " LIMIT 0,{$count}");
                                // halt($list);
                                //获取秒杀的商品
                                
                                $list=db('limited')->where('store_id',$uniacid)->limit($count)->select();
                                if ($list) {
                                    foreach ($list as $kk => $vv) {
                                        // $count = Db::table("ims_sudu8_page_order")->where("uniacid", $uniacid)->where("pid", $vv['id'])->where("flag", "neq", 1)->field("id")->count();
                                        $list2[$kk]['title']=$vv['goods_name'];     //title
                                        $list2[$kk]['linkurl'] = "/pages/goods_detail/goods_detail?title=" . $vv['goods_id'];
                                        $list2[$kk]['linktype'] = "page";
                                        $jianjie=json_decode($vv['limit_condition'],true);
                                        $list2[$kk]['goods_selling']=$jianjie['label']['label'];
                                        // $list2[$kk]['endtime']=$vv['end_time']-time();
                                        if($vv['end_time']==0)
                                        {
                                            $list2[$kk]['emd_time']='0';
                                        }else{
                                            $list2[$kk]['end_time']=$vv['end_time']-time();
                                            if($list2[$kk]['end_time']<=0)     //秒杀商品时间已结束
                                            {
                                                //删除秒杀商品
                                                $rr=db('limited')->where('id',$vv['id'])->delete();
                                                //修改商品秒杀特性
                                                $mm['limit_goods']='0';
                                                $res=db('goods')->where('id',$vv['goods_id'])->update($mm);
                                                //去除该秒杀商品
                                                 unset($list2[$kk]);
                                                 continue;
                                            }

                                        }
                                        $list2[$kk]['sale_time']=$vv['create_time'];
                                        $list2[$kk]['sale_end_time']=$vv['end_time'];
                                        $list2[$kk]['pro_kc']=$vv['goods_repertory'];      //商品库存
                                        // if($list2[$kk]['end_time']<0)    //已结束
                                        // {
                                        //   $list2[$kk]['t_flag']=2;
                                        // }elseif($vv['create_time']>time()){   //活动未开始
                                        //    $list2[$kk]['t_flag']=1;
                                        // }else{
                                        //     $list2[$kk]['t_flag']=0;
                                        // }
                                        $goods_images='//uploads/'.$vv['goods_show_images'];
                                        // $list[$kk]['sale_num'] = $vv['sale_num'] + $vv['sale_tnum'];
                                        if (strpos($goods_images, 'http') === false && $goods_images != "") {
                                            $list2[$kk]['thumb'] = remote($uniacid, $goods_images, 1);
                                        }
                                        $info=db('goods')->where(['id'=>$vv['goods_id'],'store_id'=>$uniacid])->find();
                                        $list2[$kk]['price']=$info['limit_price'];    //商品价格
                                        $list2[$kk]['market_price']=$info['goods_bottom_money'];    //划线价
                                        $list2[$kk]['video_link']=$info['video_link'];    //商品视频
                                        //获取已出售的数量
                                        $pp2['goods_id']=$vv['goods_id'];
                                        $pp2['status']=array('between',array(2,8));
                                        $num=db('order')->where($pp2)->count();
                                        $list2[$kk]['sale_num']=$num;    //商品已出售数量
                                        //统计商品的销售数量
                                        // $orders = Db::table('ims_sudu8_page_order') ->where('pid', $vv['id']) ->where('uniacid', $uniacid) ->select();
                                        // $sale_num_temp = 0;
                                        // if($orders){
                                        //     foreach ($orders as $rec) {
                                        //         $sale_num_temp+= $rec['num'];
                                        //     }
                                        // }
                                        // $vv['sale_num'] = $vv['sale_num'] + $sale_num_temp;
                                    }
                                    // $data['msmk'] = $list;
                                    // foreach($list as $k=>$v){
                                    //     //获取商品的信息
                                    //     $info=db('goods')->where(['id'=>$v['goods_id'],'store_id'=>$uniacid])->find();
                                    //     $list[$k]['goods_price']=$info['goods_new_money'];    //商品价格
                                    //     $list[$k]['goods_bottom_money']=$info['goods_bottom_money'];    //划线价
                                    //     //获取已出售的数量
                                    //     $pp2['goods_id']=$v['goods_id'];
                                    //     $pp2['status']=array('between',array(2,8));
                                    //     $num=db('order')->where($pp2)->count();
                                    //     $list[$k]['sell_number']=$num;    //商品已出售数量
                                    // }
                                    $data['msmk']=$list2;
                                } else {
                                    $data['msmk'] = [];
                                }
                            }
                            /*if (isset($v['params']['sourceid']) && $v['params']['sourceid'] != "") {
                                $sourceid = explode(':', $v['params']['sourceid'])[1]; //这是商品栏目的分类id
                                $count = intval($v['params']['goodsnum']) +1; //goodsnum数据分组
                                $con_type = $v['params']['con_type']; //
                                $con_key = $v['params']['con_key'];
                                if(!empty($v['params']['goodsiconsrc'])){
                                    $v['params']['goodsiconsrc'] =config("domain.url").$v["params"]["goodsiconsrc"];  //自定义图标
                                }

                                //在这里返回数据
                                $member_grade_name = input("member_grade_name");; //会员等级
                                $member_id =  input("open_id");  //open-ID


                                $list = db('limited')
                                    ->alias('a')
                                    ->join('goods g','a.goods_id=g.id')
                                    ->where('g.status',1)//条件:状态为1
                                    ->field("g.goods_name title,g.id,g.goods_selling,g.goods_show_image,g.goods_new_money,
                                    g.scope,goods_volume,g.goods_standard,g.goods_bottom_money")
                                    ->select();

                                $member_grade_id = db("member")
                                    ->where("member_openid", $member_id)
                                    ->value("member_grade_id");
                                $discount = db("member_grade")
                                    ->where("member_grade_id", $member_grade_id)
                                    ->value("member_consumption_discount");
                                $member_grade_img = db("member_grade")
                                    ->where("member_grade_id", $member_grade_id)
                                    ->value("member_grade_img");
                                foreach ($list as $kks => $vvs) {
                                    if (!empty($list[$kks]["scope"])) {
                                        $list[$kks]["scope"] = explode(",", $list[$kks]["scope"]);
                                    }
                                    $list[$kks]['linkurl'] = "/pages/goods_detail/goods_detail?title=" . $vvs["id"]; //跳转详情链接
                                    $list[$kks]['sale_num'] = $vvs['goods_volume']; //销量
                                    if ($list[$kks]["goods_standard"] == 1) {
                                        $standard[$kks] = db("special")->where("goods_id", $list[$kks]['id'])->select();
                                        $min[$kks] = db("special")->where("goods_id", $list[$kks]['id'])->min("price") * $discount;//最低价格
                                        $list[$kks]["goods_standard"] = $standard[$kks];
                                        $list[$kks]["thumb"] = config("domain.url")."/uploads/".$list[$kks]["goods_show_image"]; //图片
                                        $list[$kks]["member_grade_img"] =config("domain.url")."/uploads/".$member_grade_img;
                                        $list[$kks]['sale_num'] = $vvs['goods_volume']; //销量
                                        $list[$kks]["price"] = $min[$kks]; //价钱
                                        if (!empty($list[$kks]["scope"])) {
                                            if (!in_array($member_grade_name, $list[$kks]["scope"])) {
                                                unset($list[$kks]);
                                            }
                                        }
                                    } else {
                                        $list[$kks]["price"] = $list[$kks]["goods_new_money"] * $discount;
                                        $list[$kks]["thumb"] = config("domain.url")."/uploads/".$list[$kks]["goods_show_image"]; //图片
                                        $list[$kks]["member_grade_img"] =config("domain.url")."/uploads/".$member_grade_img;
                                        if (!empty($list[$kks]["scope"])) {
                                            if (!in_array($member_grade_name, $list[$kks]["scope"])) {
                                                unset($list[$kks]);
                                            }
                                        }
                                    }
                                }
                                $list = array_values($list);
                                $data['items'][$k]['data'] = $list;
                            }else {
                                $data['items'][$k]['data'] = [];
                            }*/
                        }else if ($v['id'] == "pt") {

                            if (isset($v['params']['sourceid']) && $v['params']['sourceid'] != "") {
                                $sourceid = explode(':', $v['params']['sourceid'])[1];
                                $count = $v['params']['goodsnum'];
                                $con_type = $v['params']['con_type'];
                                $con_key = $v['params']['con_key'];
                                $where = "";
                                if ($con_type == 1 && $con_key == 1) {
                                    $where = 'ORDER BY id DESC';
                                }
                                if ($con_type == 2 && $con_key == 1) {
                                    $where = 'AND type_x=1 ORDER BY id DESC';
                                }
                                if ($con_type == 3 && $con_key == 1) {
                                    $where = 'AND type_y=1 ORDER BY id DESC';
                                }
                                if ($con_type == 4 && $con_key == 1) {
                                    $where = 'AND type_i=1 ORDER BY id DESC';
                                }
                                if ($con_type == 1 && $con_key == 2) {
                                    $where = 'ORDER BY hits DESC';
                                }
                                if ($con_type == 2 && $con_key == 2) {
                                    $where = 'AND type_x=1 ORDER BY hits DESC';
                                }
                                if ($con_type == 3 && $con_key == 2) {
                                    $where = 'AND type_y=1 ORDER BY hits DESC';
                                }
                                if ($con_type == 4 && $con_key == 2) {
                                    $where = 'AND type_i=1 ORDER BY hits DESC';
                                }
                                if ($con_type == 1 && $con_key == 3) {
                                    $where = 'ORDER BY num DESC';
                                }
                                if ($con_type == 2 && $con_key == 3) {
                                    $where = 'AND type_x=1 ORDER BY num DESC';
                                }
                                if ($con_type == 3 && $con_key == 3) {
                                    $where = 'AND type_y=1 ORDER BY num DESC';
                                }
                                if ($con_type == 4 && $con_key == 3) {
                                    $where = 'AND type_i=1 ORDER BY num DESC';
                                }
                                $list = Db::query("SELECT * FROM ims_sudu8_page_pt_pro WHERE `uniacid` = {$uniacid} AND `show_pro`=0 AND `cid` = {$sourceid} " . $where . " LIMIT 0,{$count}");
                                if ($list) {
                                    foreach ($list as $kk => $vv) {
                                        $list[$kk]['linkurl'] = "/sudu8_page_plugin_pt/products/products?id=" . $vv['id'];
                                        $list[$kk]['linktype'] = "page";
                                        if (strpos($vv['thumb'], 'http') === false && $vv['thumb'] != "") {
                                            $list[$kk]['thumb'] = remote($uniacid, $vv['thumb'], 1);
                                        }
                                    }
                                    $data['items'][$k]['data'] = $list;
                                } else {
                                    $data['items'][$k]['data'] = [];
                                }
                            }
                        }else if ($v['id'] == "cases") {

                            if (isset($v['params']['sourceid']) && $v['params']['sourceid'] != "") {
                                $sourceid = explode(':', $v['params']['sourceid'])[1];
                                $count = $v['params']['casenum'];
                                $con_type = $v['params']['con_type'];
                                $con_key = $v['params']['con_key'];
                                $where = "";
                                if ($con_type == 1 && $con_key == 1) {
                                    $where = 'ORDER BY id DESC';
                                }
                                if ($con_type == 2 && $con_key == 1) {
                                    $where = 'AND type_x=1 ORDER BY id DESC';
                                }
                                if ($con_type == 3 && $con_key == 1) {
                                    $where = 'AND type_y=1 ORDER BY id DESC';
                                }
                                if ($con_type == 4 && $con_key == 1) {
                                    $where = 'AND type_i=1 ORDER BY id DESC';
                                }
                                if ($con_type == 1 && $con_key == 2) {
                                    $where = 'ORDER BY hits DESC';
                                }
                                if ($con_type == 2 && $con_key == 2) {
                                    $where = 'AND type_x=1 ORDER BY hits DESC';
                                }
                                if ($con_type == 3 && $con_key == 2) {
                                    $where = 'AND type_y=1 ORDER BY hits DESC';
                                }
                                if ($con_type == 4 && $con_key == 2) {
                                    $where = 'AND type_i=1 ORDER BY hits DESC';
                                }
                                if ($con_type == 1 && $con_key == 3) {
                                    $where = 'ORDER BY num DESC';
                                }
                                if ($con_type == 2 && $con_key == 3) {
                                    $where = 'AND type_x=1 ORDER BY num DESC';
                                }
                                if ($con_type == 3 && $con_key == 3) {
                                    $where = 'AND type_y=1 ORDER BY num DESC';
                                }
                                if ($con_type == 4 && $con_key == 3) {
                                    $where = 'AND type_i=1 ORDER BY num DESC';
                                }
                                $list = Db::query("SELECT id,title,thumb,type FROM ims_sudu8_page_products WHERE (`type` = 'showPic' or `type` = 'showArt') AND `uniacid` = {$uniacid} AND `flag` = 1 AND `is_sale`=0 AND (`cid` = {$sourceid} or `pcid` = {$sourceid} ) " . $where . " LIMIT 0,{$count}");
                                if ($list) {
                                    foreach ($list as $kk => $vv) {
                                        $list[$kk]['linkurl'] = "/sudu8_page/" . $vv['type'] . "/" . $vv['type'] . "?id=" . $vv['id'];
                                        if (strpos($vv['thumb'], 'http') === false && $vv['thumb'] != "") {
                                            $list[$kk]['thumb'] = remote($uniacid, $vv['thumb'], 1);
                                        }
                                    }
                                    $data['items'][$k]['data'] = $list;
                                } else {
                                    $data['items'][$k]['data'] = [];
                                }
                            }
                        }else if ($v['id'] == "listdesc") {

                            if (isset($v['params']['sourceid']) && $v['params']['sourceid'] != "") {
                                $sourceid = explode(':', $v['params']['sourceid'])[1];
                                $count = $v['params']['newsnum'];
                                $con_type = $v['params']['con_type'];
                                $con_key = $v['params']['con_key'];
                                $where = "";
                                if ($con_type == 1 && $con_key == 1) {
                                    $where = 'ORDER BY id DESC';
                                }
                                if ($con_type == 2 && $con_key == 1) {
                                    $where = 'AND type_x=1 ORDER BY id DESC';
                                }
                                if ($con_type == 3 && $con_key == 1) {
                                    $where = 'AND type_y=1 ORDER BY id DESC';
                                }
                                if ($con_type == 4 && $con_key == 1) {
                                    $where = 'AND type_i=1 ORDER BY id DESC';
                                }
                                if ($con_type == 1 && $con_key == 2) {
                                    $where = 'ORDER BY hits DESC';
                                }
                                if ($con_type == 2 && $con_key == 2) {
                                    $where = 'AND type_x=1 ORDER BY hits DESC';
                                }
                                if ($con_type == 3 && $con_key == 2) {
                                    $where = 'AND type_y=1 ORDER BY hits DESC';
                                }
                                if ($con_type == 4 && $con_key == 2) {
                                    $where = 'AND type_i=1 ORDER BY hits DESC';
                                }
                                if ($con_type == 1 && $con_key == 3) {
                                    $where = 'ORDER BY num DESC';
                                }
                                if ($con_type == 2 && $con_key == 3) {
                                    $where = 'AND type_x=1 ORDER BY num DESC';
                                }
                                if ($con_type == 3 && $con_key == 3) {
                                    $where = 'AND type_y=1 ORDER BY num DESC';
                                }
                                if ($con_type == 4 && $con_key == 3) {
                                    $where = 'AND type_i=1 ORDER BY num DESC';
                                }
                                $list = Db::query("SELECT * FROM ims_sudu8_page_products WHERE `type` = 'showArt' AND `is_sale`=0 AND  `uniacid` = {$uniacid} AND `flag` = 1 AND (`cid` = {$sourceid} or `pcid` = {$sourceid} ) " . $where . " LIMIT 0,{$count}");
                                if ($list) {
                                    foreach ($list as $kk => $vv) {
                                        $count = Db::table("ims_sudu8_page_comment")->where("uniacid", $uniacid)->where("aid", $vv['id'])->count();
                                        $list[$kk]['comments'] = $count;
                                        $list[$kk]['linkurl'] = "/sudu8_page/showArt/showArt?id=" . $vv['id'];
                                        if (strpos($vv['thumb'], 'http') === false && $vv['thumb'] != "") {
                                            $list[$kk]['thumb'] = remote($uniacid, $vv['thumb'], 1);
                                        }
                                        $list[$kk]['ctime'] = date('Y年m月d日', $vv['ctime']);
                                    }
                                    $data['items'][$k]['data'] = $list;
                                } else {
                                    $data['items'][$k]['data'] = [];
                                }
                            }
                        }else if (isset($v['params']['noticedata']) && intval($v['params']['noticedata']) == 0) {
                            /*读取系统公告*/
                            if (isset($v['params']['sourceid']) && $v['params']['sourceid'] != "") {
//                                $sourceid = explode(':', $v['params']['sourceid'])[1];
                                $sourceid = explode(':', $v['params']['sourceid'])[0];
                                $count = $v['params']['noticenum'];
                                $list = Db::query("SELECT id,title FROM ims_sudu8_page_products WHERE `uniacid` = {$uniacid} AND `type` = 'showArt' AND `is_sale`=0 AND (`cid` = {$sourceid} or `pcid` = {$sourceid} ) ORDER BY id DESC LIMIT 0,{$count}");
                                if ($list) {
                                    foreach ($list as $kk => $vv) {
                                        if ($v['params']['noticedata'] == 0) {
                                            $list[$kk]['linktype'] = 'page';
                                        }
                                        $list[$kk]['linkurl'] = "/sudu8_page/showArt/showArt?id=" . $vv['id'];
                                    }
                                    $data['items'][$k]['data'] = $list;
                                } else {
                                    $data['items'][$k]['data'] = [];
                                }
                            }
                        }else if ($v['id'] == "kpgg" || $v['id'] == "tcgg") {
                            if (intval($v['params']['navstyle']) == 0) {
                                $data['sec'] = $v['params']['sec'];
                            }
                        }else if ($v['id'] == "goods") {
                            if (isset($v['params']['sourceid']) && $v['params']['sourceid'] != "") {
                                $sourceid = explode(':', $v['params']['sourceid'])[1]; //这是商品栏目的分类id
                                $count = intval($v['params']['goodsnum']) +1; //goodsnum数据分组
                                $con_type = $v['params']['con_type']; //
                                $con_key = $v['params']['con_key'];
                                if(!empty($v['params']['goodsiconsrc'])){
                                    $v['params']['goodsiconsrc'] =config("domain.url").$v["params"]["goodsiconsrc"];  //自定义图标
                                }

                                //在这里返回数据
                                $member_grade_name = input("member_grade_name");; //会员等级
                                $member_id =  input("open_id");  //open-ID
                                $list = db("goods")
                                    ->where(['pid'=>$sourceid,'status'=>1,'store_id'=>$uniacid,'limit_goods'=>'0'])
                                    ->limit(1,$count)
                                    ->field("goods_name title,id,goods_selling,goods_show_image,goods_new_money,scope,goods_volume,goods_standard,goods_bottom_money")
                                    ->select();
                                $member_grade_id = db("member")
                                    ->where("member_openid", $member_id)
                                    ->value("member_grade_id");
                                $discount = db("member_grade")
                                    ->where("member_grade_id", $member_grade_id)
                                    ->value("member_consumption_discount");
                                $member_grade_img = db("member_grade")
                                    ->where("member_grade_id", $member_grade_id)
                                    ->value("member_grade_img");
                                foreach ($list as $kks => $vvs) {
                                    if (!empty($list[$kks]["scope"])) {
                                        $list[$kks]["scope"] = explode(",", $list[$kks]["scope"]);
                                    }
                                    if($goods[$k]["goods_member"] != 1){
                                        $discount = 1;
                                    }
                                    $list[$kks]['linkurl'] = "/pages/goods_detail/goods_detail?title=" . $vvs["id"]; //跳转详情链接
                                    $list[$kks]['sale_num'] = $vvs['goods_volume']; //销量
                                    if ($list[$kks]["goods_standard"] == 1) {
                                        $standard[$kks] = db("special")->where("goods_id", $list[$kks]['id'])->select();
                                        $min[$kks] = db("special")->where("goods_id", $list[$kks]['id'])->min("price") * $discount;//最低价格
                                        $list[$kks]["goods_standard"] = $standard[$kks];
                                        $list[$kks]["thumb"] = config("domain.url")."/uploads/".$list[$kks]["goods_show_image"]; //图片
                                        $list[$kks]["member_grade_img"] =config("domain.url")."/uploads/".$member_grade_img;
                                        $list[$kks]['sale_num'] = $vvs['goods_volume']; //销量
                                        $list[$kks]["price"] = $min[$kks]; //价钱
                                        if (!empty($list[$kks]["scope"])) {
                                            if (!in_array($member_grade_name, $list[$kks]["scope"])) {
                                                unset($list[$kks]);
                                            }
                                        }
                                    } else {
                                        $list[$kks]["price"] = $list[$kks]["goods_new_money"] * $discount;
                                        $list[$kks]["thumb"] = config("domain.url")."/uploads/".$list[$kks]["goods_show_image"]; //图片
                                        $list[$kks]["member_grade_img"] =config("domain.url")."/uploads/".$member_grade_img;
                                        if (!empty($list[$kks]["scope"])) {
                                            if (!in_array($member_grade_name, $list[$kks]["scope"])) {
                                                unset($list[$kks]);
                                            }
                                        }
                                    }
                                }
                                $list = array_values($list);
                                $data['items'][$k]['data'] = $list;
                            }else {
                                    $data['items'][$k]['data'] = [];
                                }
                        }else if($v['id'] == "anniu"){
                            if(isset($v['params']['linktype'])){
                                if($v['params']['linktype'] == 'mini'){
                                    if(strpos($v['params']['link'], ",") !== false){
                                        $link = explode(",", $v['params']['link']);
                                        $data['items'][$k]['params']['link'] = substr($link[0], 6);
                                        $data['items'][$k]['params']['pageurl'] = substr($link[1], 9);
                                    }else{
                                        $data['items'][$k]['params']['link'] = substr($data['items'][$k]['params']['link'], 6);
                                        $data['items'][$k]['params']['pageurl'] = "";
                                    }
                                }
                            }
                        //菜单
                        }else if($v['id'] == "menu"){
                            foreach($v['data'] as $ky => &$vy){
                                if(isset($vy['linktype'])){
                                    if($vy['linktype'] == 'mini'){
                                        if(strpos($vy['linkurl'], ",") !== false){
                                            $link = explode(",", $vy['linkurl']);
                                            $vy['linkurl'] = substr($link[0], 6);
                                            $vy['pageurl'] = substr($link[1], 9);
                                        }else{
                                            $vy['linkurl'] = substr($data['items'][$k]['data'][$ky]['linkurl'], 6);
                                            $vy['pageurl'] = "";
                                        }
                                    }
                                }

                            }
                        }else  if ($v['id'] == "menu2") {
                            $count = count($v['data']);
                            $data['items'][$k]['count'] = $count;
                            foreach($v['data'] as $ky => $vy){
                                if(isset($vy['linktype'])){
                                    if($vy['linktype'] == 'mini'){
                                        if(strpos($vy['linkurl'], ",") !== false){
                                            $link = explode(",", $vy['linkurl']);
                                            $vy['linkurl'] = substr($link[0], 6);
                                            $vy['pageurl'] = substr($link[1], 9);
                                        }else{
                                            $vy['linkurl'] = substr($data['items'][$k]['data'][$ky]['linkurl'], 6);
                                            $vy['pageurl'] = "";
                                        }
                                    }
                                }

                            }
                        }else if ($v['id'] == "picturew") {
                            $count = count($v['data']);
                            $data['items'][$k]['count'] = $count;
                            if ($v['params']['row'] == 1) {
                                for ($i = 0; $i <= $count; $i++) {
                                    $data['items'][$k]['data'] = array_values($v['data']);
                                }
                            } else {
                                $v['data'] = array_values($v['data']);
                                $imginfo = explode(" ", getimagesize($v['data'][0]['imgurl'])[3]);
                                $v['imgw'] = explode('"', $imginfo[0])[1];
                                $v['imgh'] = explode('"', $imginfo[1])[1];
                            }
                        }else if ($v['id'] == "tabbar") {
                            $datas = array();
                            $i = 0;
                            foreach ($v['data'] as $kk => $vv) {
                                $data['items'][$k]['datas'][$i] = $vv;
                                $i++;
                            }
                            $count = count($v['data']);
                            $data['items'][$k]['count'] = $count;
                        }else if ($v['id'] == "xxk") {
                            $datas = array();
                            $i = 0;
                            foreach ($v['data'] as $kk => $vv) {
                                $data['items'][$k]['datas'][$i] = $vv;
                                $i++;
                            }
                            $count = count($v['data']);
                            $data['items'][$k]['count'] = $count;
                        }else if ($v['id'] == "video") {
                            $videourl = $v['params']['videourl'];

                            if ($videourl) {
                                if (strpos($videourl, ".mp4") !== false || strpos($videourl,".MP4")!==false) {
                                    $videodata = $videourl;
                                } else {

                                    $videodata = $videoInfo->getVideoInfo($videourl);
                                    $videodata = $videodata['url'];
                                    if(strpos($videodata,"http")===false){
                                        $videodata = 'http://video.dispatch.tc.qq.com/'.$videodata;
                                    }
                                }
                                $v['params']['videourl'] = $videodata;
                            }
                        }else if ($v['id'] == "yhq") {
                            $counts_yhq = $v['style']['counts'];
                            $v['coupon'] = Db::table("ims_sudu8_page_coupon")->where("flag", 1)->where("uniacid", $uniacid)->limit(0, $counts_yhq)->select();
                        }else if ($v['id'] == "xnlf") {
                            $avatars = Db::table("ims_sudu8_page_user")->where("avatar", "neq", "")->where("uniacid", $uniacid)->order("id desc")->limit(0, 5)->field("avatar")->select();
                            $v['avatars'] = $avatars;
                        }else if($v['id'] == "multiple"){
                            if(!isset($v['style']['showtype'])){
                                $data['items'][$k]['style']['showtype'] = 0;
                            }
                            $tjnum = $v['style']['rownum'];
                            $content_type = $v['params']['content_type'];
                            $content_type = $v['params']['content_type'];
                            if($content_type == 1){
                                $orderby = " createtime desc ";
                            }
                            if($content_type == 2){
                                $orderby = " star desc ";
                            }

                            $store['storeHot'] =  $store['storeHot'] = Db::query("SELECT id,uniacid,name,logo,hot FROM ims_sudu8_page_shops_shop WHERE `flag` = 1 AND `uniacid` = {$uniacid} AND `hot` = 1 ORDER BY " . $orderby . " LIMIT 0," . $tjnum);
                            $num2 = count($store['storeHot']);
                            for($i = 0; $i < $num2; $i++){
                                if (stristr($store['storeHot'][$i]['logo'], 'http')) {
                                    $store['storeHot'][$i]['logo'] = $store['storeHot'][$i]['logo'];
                                } else {
                                    $store['storeHot'][$i]['logo'] = remote($uniacid, $store['storeHot'][$i]['logo'], 1);
                                }
                            }
                            $data['items'][$k]['data'] = $store;
                        }else if ($v['id'] == "mlist") {
                            $store['catelist'] = Db::table("ims_sudu8_page_shops_cate")->where("uniacid", $uniacid)->where('flag', 1)->field("id,num,name")->order("num desc")->select();
                            if(isset($v['style']['viewcount'])){
                                $tjnum = $v['style']['viewcount'];
                            }else{
                                $tjnum = 4;
                            }

                            if(isset($v['params']['content_type'])){
                                $content_type = $v['params']['content_type'];
                            }else{
                                $content_type = 1;
                            }

                            if ($content_type == 1) {
                                $orderby = " createtime desc ";
                            }
                            if ($content_type == 2) {
                                $orderby = " star desc ";
                            }
                            $store['storeHot'] = Db::query("SELECT id,uniacid,name,logo,hot,tel,address FROM ims_sudu8_page_shops_shop WHERE `flag` = 1 AND `uniacid` = {$uniacid} AND `hot` = 1 ORDER BY " . $orderby . " LIMIT 0," . $tjnum);
                            $num2 = count($store['storeHot']);
                            for ($i = 0; $i < $num2; $i++) {
                                if (stristr($store['storeHot'][$i]['logo'], 'http')) {
                                    $store['storeHot'][$i]['logo'] = $store['storeHot'][$i]['logo'];
                                } else {
                                    $store['storeHot'][$i]['logo'] = remote($uniacid, $store['storeHot'][$i]['logo'], 1);
                                }
                            }
                            $data['items'][$k]['data'] = $store;
                        }
                        //底部菜单
                        if ($v['id'] == "footmenu") {
                            $count = count($v['data']);
                            $data['items'][$k]['count'] = $count;

                            $text_is = $v['params']['textshow'];
                            if ($text_is == 1) {
                                $data['footmenuh'] = $v['style']['paddingleft'] * 2 + $v['style']['textfont'] + $v['style']['paddingtop'] * 2 + $v['style']['iconfont'] + 1;
                                $data['foottext'] = 1;
                            } else {
                                $data['footmenuh'] = $v['style']['paddingtop'] * 2 + $v['style']['iconfont'] + 1;
                                $data['foottext'] = 0;
                            }
                            $data['footmenu'] = 1;
                        }

                        if($v['id'] == "personlist"){
                            $count = $v['params']['goodsnum'];
                            // $data['items'][$k]['data'] = pdo_fetchall("SELECT * FROM ".tablename('sudu8_page_staff')." WHERE `uniacid` = {$_W['uniacid']}  order by id desc limit 0, {$count}");
                            $data['items'][$k]['data'] = Db::table('ims_sudu8_page_staff') ->where('uniacid', $uniacid) ->order('sort desc') ->limit($count) ->select();
                            foreach ($data['items'][$k]['data'] as $kkk => $vvv) {
                                if(strpos($vvv['pic'],'http') === false && $vvv['pic'] != ""){
                                    $data['items'][$k]['data'][$kkk]['pic'] = remote($uniacid, $data['items'][$k]['data'][$kkk]['pic'], 1);
                                }
                                $data['items'][$k]['data'][$kkk]['score'] = intval($vvv['score']);
                            }
                        }

                    }
                }
            }
        }

        $pageset = Db::table("ims_sudu8_page_diypageset")->where("uniacid", $uniacid)->find();
        if ($pageset) {
            if (strpos($pageset['kp'], 'http') === false) {
                $pageset['kp'] = remote($uniacid, $pageset['kp'], 1);
            }
            if (strpos($pageset['tc'], 'http') === false) {
                $pageset['tc'] = remote($uniacid, $pageset['tc'], 1);
            }
        } else {
            $pageset['kp'] = "";
            $pageset['tc'] = "";
        }
        $arr=Db::table("ims_sudu8_page_base")->where("uniacid",$uniacid)->field("diy_bg_music")->find();
//        $diy_bg_music = pdo_getcolumn("sudu8_page_base", array("uniacid" => $uniacid), "diy_bg_music");
        $pageset['diy_bg_music'] = $arr["diy_bg_music"];
        $data['pageset'] = $pageset;
        $result['data'] = $data;
        $result['data']['test_name'] =$da_change;  
        return json_encode($result);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:获取底部导航的数据
     **************************************
     * @return string
     */
    public function doPageGetFoot()
    {
        $uniacid = input("uniacid");
        $foot = input('foot');
        if ($foot == 1) {
            $baseInfo = Db::table('ims_sudu8_page_base')->where("uniacid", $uniacid)->find();
            if ($baseInfo['copyimg']) {
                $baseInfo['copyimg'] = remote($uniacid, $baseInfo['copyimg'], 1);
            }
            $baseInfo['tabbar'] = unserialize($baseInfo['tabbar_new']);
            $baseInfo['tabnum'] = $baseInfo['tabnum_new'];
            if ($baseInfo['tabnum'] > 0) {
                for ($i = 0; $i < 5; $i++) {
                    if(isset($baseInfo['tabbar'][$i])){
                        $baseInfo['tabbar'][$i] = unserialize($baseInfo['tabbar'][$i]);
                        if ($baseInfo['tabbar'][$i]) {
                            if($baseInfo['tabbar'][$i]['tabbar'] == 1){
                                if(!empty($baseInfo['tabbar'][$i]['tabimginput_1'])){
                                    $baseInfo['tabbar'][$i]['tabimginput_1'] = remote($uniacid,$baseInfo['tabbar'][$i]['tabimginput_1'],1);
                                }
                                if(!empty($baseInfo['tabbar'][$i]['tabimginput_2'])){
                                    $baseInfo['tabbar'][$i]['tabimginput_2'] = remote($uniacid,$baseInfo['tabbar'][$i]['tabimginput_2'],1);
                                }
                            }

                            if ($baseInfo['tabbar'][$i]['tabbar_linktype'] == "tel") {
                                $baseInfo['tabbar'][$i]['tabbar_type'] = "tel";
                            } elseif ($baseInfo['tabbar'][$i]['tabbar_linktype'] == "map") {
                                $baseInfo['tabbar'][$i]['tabbar_type'] = "map";
                            } elseif ($baseInfo['tabbar'][$i]['tabbar_linktype'] == "web") {
                                $baseInfo['tabbar'][$i]['tabbar_type'] = "web";
                            } elseif ($baseInfo['tabbar'][$i]['tabbar_linktype'] == "server") {
                                $baseInfo['tabbar'][$i]['tabbar_type'] = "server";
                            } else {
                                $baseInfo['tabbar'][$i]['tabbar_type'] = "Article";
                            }
                        }
                    }else{
                        $baseInfo['tabbar'][$i] = "";
                    }
                }
            } else {
                $baseInfo['tabbar'][0] = "";
                $baseInfo['tabbar'][1] = "";
                $baseInfo['tabbar'][2] = "";
                $baseInfo['tabbar'][3] = "";
                $baseInfo['tabbar'][4] = "";
            }
            $baseInfo['color_bar'] = "1px solid " . $baseInfo['color_bar'];
            $result['data'] = $baseInfo;
            return json_encode($result);
        } else {
            $data = Db::table("ims_sudu8_page_diypage")
                ->where("index", 1)
                ->where("uniacid", $uniacid)
                ->find();
            $baseInfo = Db::table('ims_sudu8_page_base')->where("uniacid", $uniacid)->find();
            if ($baseInfo['copyimg']) {
                $baseInfo['copyimg'] = remote($uniacid, $baseInfo['copyimg'], 1);
            }
            if ($data['items'] != '') {
                $data['items'] = unserialize($data['items']);
                foreach ($data['items'] as $k => &$v) {
                    if ($v['id'] == "footmenu") {
                        $count = count($v['data']);
                        $res['count'] = $count;
                        $res['params'] = $v['params'];
                        $res['style'] = $v['style'];
                        $res['data'] = $v['data'];
                        $text_is = $v['params']['textshow'];
                        if ($text_is == 1) {
                            $res['footmenuh'] = $v['style']['paddingleft'] * 2 + $v['style']['textfont'] + $v['style']['paddingtop'] * 2 + $v['style']['iconfont'] + 1;
                            $res['foottext'] = 1;
                        } else {
                            $res['footmenuh'] = $v['style']['paddingtop'] * 2 + $v['style']['iconfont'] + 1;
                            $res['foottext'] = 0;
                        }
                        $res['footmenu'] = 1;
                    }
                }
            }
            $result['data'] = $res;
            return json_encode($result);
        }
    }

    public function doPagebindfxs()
    {
        $uniacid = input("uniacid");
        $openid = input("openid");
        $fxsid = input("fxsid");
        // dump(88888);die;
        // 分销商的关系[1.绑定上下级关系 ]
        $userinfo = Db::table('ims_sudu8_page_user')->where("openid", $openid)->where("uniacid", $uniacid)->find();
        // 分销商的信息
        $fxsinfo = Db::table('ims_sudu8_page_user')->where("openid", $fxsid)->where("uniacid", $uniacid)->find();
        //获取该小程序的分销关系绑定规则
        $guiz = Db::table('ims_sudu8_page_fx_gz')->where("uniacid", $uniacid)->field("fx_cj,sxj_gx,uniacid")->find();
        // 1.先进行上下级关系绑定[判断是不是点击即成分销商]
        if ($guiz['fx_cj'] != 4 && $guiz['sxj_gx'] == 1 && $userinfo['parent_id'] == '0' && $fxsid != '0' && $userinfo['fxs'] != 2 && $fxsinfo['fxs'] == 2) {
            $p_fxs = $fxsinfo['parent_id'];  //分销商的上级
            $p_p_fxs = $fxsinfo['p_parent_id']; //分销商的上上级
            // 判断启用几级分销
            $fx_cj = $guiz['fx_cj'];
            // 分别做判断
            if ($fx_cj == 1) {
                $uuser = Db::table('ims_sudu8_page_user')->where("uniacid", $uniacid)->where("openid", $openid)->update(array("parent_id" => $fxsid));
            }
            if ($fx_cj == 2) {
                $uuser = Db::table('ims_sudu8_page_user')->where("uniacid", $uniacid)->where("openid", $openid)->update(array("parent_id" => $fxsid, "p_parent_id" => $p_fxs));
            }
            if ($fx_cj == 3) {
                $uuser = Db::table('ims_sudu8_page_user')->where("uniacid", $uniacid)->where("openid", $openid)->update(array("parent_id" => $fxsid, "p_parent_id" => $p_fxs, "p_p_parent_id" => $p_p_fxs));
            }
        }
        $adata['guiz'] = Db::table('ims_sudu8_page_fx_gz')->where("uniacid", $uniacid)->field("one_bili,two_bili,three_bili,uniacid")->find();
        //return $this->result(0, 'success',$isbindfxs);
    }

    // 获取全局情况
    public function dopageglobaluserinfo()
    {
        $uniacid = input('uniacid');
        $openid = input('openid');
        $newuserinfo = Db::table('ims_sudu8_page_user')->where("uniacid", $uniacid)->where("openid", $openid)->find();
        $parent_id = $newuserinfo['parent_id'];
        if ($parent_id != '0') {
            $tjr = Db::table('ims_sudu8_page_user')->where("uniacid", $uniacid)->where("openid", $parent_id)->field("openid,fxs")->find();
            $tjrname = Db::table('ims_sudu8_page_user')->where("uniacid", $uniacid)->where("openid", $tjr['openid'])->field("nickname")->find();
            if ($tjr['fxs'] == 2) {
                $newuserinfo['tjr'] = rawurldecode($tjrname['nickname']);
            } else {
                $newuserinfo['tjr'] = "您是由平台方推荐";
            }
        } else {
            $newuserinfo['tjr'] = "您是由平台方推荐";
        }

        if(isset($newuserinfo['nickname'])){
            $newuserinfo['nickname'] = rawurldecode($newuserinfo['nickname']);
        }

        $res['data'] = $newuserinfo;
        return json_encode($res);
    }
    /*原默认方法*/
    public function doPageAppbase()
    {
        $uniacid = input("uniacid");
        $code = input("code");
        $app = Db::table('applet')->where("id", $uniacid)->find();
        $appid = $app['appID'];
        $appsecret = $app['appSecret'];
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $appid . "&secret=" . $appsecret . "&js_code=" . $code . "&grant_type=authorization_code";
        $weixin = file_get_contents($url);
        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
        $array = get_object_vars($jsondecode);//转换成数组
        if (isset($array['errcode'])) {
            $data['res'] = 2;
            $result['data'] = $data;
            return json_encode($result);
            exit;
        }
        $openid = $array['openid'];//输出openid
        if ($openid) {
            $data = array(
                "uniacid" => $uniacid,
                "openid" => $openid,
                "createtime" => time(),
            );
            $userinfo = Db::table('ims_sudu8_page_user')->where("openid", $openid)->where("uniacid", $uniacid)->find();
            if (count($userinfo) == 0) {
                Db::table('ims_sudu8_page_user')->insert($data);
                $data['res'] = 1;
                $adata['data'] = $data;
                return json_encode($adata);
            } else {
                $adata['data'] = $userinfo;
                return json_encode($adata);
            }
        }
    }
    /**
     * lilu
     * 秒杀列表 
     */
    public function limit_goods_more(){
        //获取参数
        $uniacid = input("uniacid");   //小城序id
        $pageid = input("pageid");    //页面id
        //获取小程序的diypage
        $data = Db::table('ims_sudu8_page_diypage')->where("id", $pageid)->where("uniacid", $uniacid)->find();
        if ($data['items'] != '') {
            $data['items'] = array_values(unserialize($data['items']));
            foreach ($data['items'] as $k => $v) {
                if (isset($v['params']['sourceid']) && $v['params']['sourceid'] != "") {
                    $sourceid = explode(':', $v['params']['sourceid']);
                    // $count = $v['params']['goodsnum']; //数量
                    // $con_type = $v['params']['con_type'];
                    // $con_key = $v['params']['con_key'];
                    // $where = "";
                    // if ($con_type == 1 && $con_key == 1) {
                    //     $where = 'ORDER BY id DESC';
                    // }
                    // if ($con_type == 2 && $con_key == 1) {
                    //     $where = 'AND type_x=1 ORDER BY id DESC';
                    // }
                    // if ($con_type == 3 && $con_key == 1) {
                    //     $where = 'AND type_y=1 ORDER BY id DESC';
                    // }
                    // if ($con_type == 4 && $con_key == 1) {
                    //     $where = 'AND type_i=1 ORDER BY id DESC';
                    // }
                    // if ($con_type == 1 && $con_key == 2) {
                    //     $where = 'ORDER BY hits DESC';
                    // }
                    // if ($con_type == 2 && $con_key == 2) {
                    //     $where = 'AND type_x=1 ORDER BY hits DESC';
                    // }
                    // if ($con_type == 3 && $con_key == 2) {
                    //     $where = 'AND type_y=1 ORDER BY hits DESC';
                    // }
                    // if ($con_type == 4 && $con_key == 2) {
                    //     $where = 'AND type_i=1 ORDER BY hits DESC';
                    // }
                    // if ($con_type == 1 && $con_key == 3) {
                    //     $where = 'ORDER BY num DESC';
                    // }
                    // if ($con_type == 2 && $con_key == 3) {
                    //     $where = 'AND type_x=1 ORDER BY num DESC';
                    // }
                    // if ($con_type == 3 && $con_key == 3) {
                    //     $where = 'AND type_y=1 ORDER BY num DESC';
                    // }
                    // if ($con_type == 4 && $con_key == 3) {
                    //     $where = 'AND type_i=1 ORDER BY num DESC';
                    // }
                    $list=db('limited')->where('store_id',$uniacid)->select();
                    if ($list) {
                        foreach ($list as $kk => $vv) {
                            // $count = Db::table("ims_sudu8_page_order")->where("uniacid", $uniacid)->where("pid", $vv['id'])->where("flag", "neq", 1)->field("id")->count();
                            $list2[$kk]['title']=$vv['goods_name'];     //title
                            $list2[$kk]['linkurl'] = "/pages/goods_detail/goods_detail?title=" . $vv['goods_id'];
                            $list2[$kk]['linktype'] = "page";
                            $jianjie=json_decode($vv['limit_condition'],true);
                            $list2[$kk]['goods_selling']=$jianjie['label']['label'];
                            if($vv['end_time']==0)
                            {
                                $list2[$kk]['emd_time']='0';
                            }else{
                                $list2[$kk]['end_time']=$vv['end_time']-time();
                                if($list2[$kk]['end_time']<=0)     //秒杀商品时间已结束
                                {
                                    //删除秒杀商品
                                    $rr=db('limited')->where('id',$vv['id'])->delete();
                                    //修改商品秒杀特性
                                    $mm['limit_goods']='0';
                                    $res=db('goods')->where('id',$vv['goods_id'])->update($mm);
                                    //去除该秒杀商品
                                     unset($list2[$kk]);
                                     continue;
                                }
                            }
                            $list2[$kk]['sale_time']=$vv['create_time'];
                            $list2[$kk]['sale_end_time']=$vv['end_time'];
                            $list2[$kk]['pro_kc']=$vv['goods_repertory'];      //商品库存
                            $goods_images='//uploads/'.$vv['goods_show_images'];
                            if (strpos($goods_images, 'http') === false && $goods_images != "") {
                                $list2[$kk]['thumb'] = remote($uniacid, $goods_images, 1);
                            }
                            $info=db('goods')->where(['id'=>$vv['goods_id'],'store_id'=>$uniacid])->find();
                            $list2[$kk]['price']=$info['limit_price'];    //商品价格
                            $list2[$kk]['market_price']=$info['goods_bottom_money'];    //划线价
                            $list2[$kk]['video_link']=$info['video_link'];    //商品视频字段
                            //获取已出售的数量
                            $pp2['goods_id']=$vv['goods_id'];
                            $pp2['status']=array('between',array(2,8));
                            $num=db('order')->where($pp2)->count();
                            $list2[$kk]['sale_num']=$num;    //商品已出售数量
                        }
                        if($list2){
                            return   ajax_success('获取成功',$list2);
                        }
                    }
                }

            }
        }
    }

}