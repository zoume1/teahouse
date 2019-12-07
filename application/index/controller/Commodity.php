<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;
/**
 * lilu
 * 商品列表控制器
 */
class Commodity extends Controller
{

    /**
     * 商品分类 
     * GY
     */
    public function commodity_index(Request $request)
    {
        if($request->isPost()) {   
            $store_id = $request->only(['uniacid'])['uniacid'];     //当前店铺
            $member_grade_name = $request->only(["member_grade_name"])["member_grade_name"]; //会员等级
            $goods_type = db("wares")->where("status", 1)->where("store_id","EQ",$store_id)	->select();
            $goods_type = _tree_sort(recursionArr($goods_type), 'sort_number');
            foreach($goods_type as $key => $value)
            {
                $child=count($value['child']);
                if($child==0){
                    //没有二级分类
                    $goods_type[$key]['child'] = db("goods")->where("pid",$goods_type[$key]['id'])->where("store_id","EQ",$store_id)->where("label",1)->where('limit_goods','0')->field('id,goods_name,goods_show_image,scope')->select();
                    foreach($goods_type[$key]['child'] as $k => $v){
                        if(!empty($goods_type[$key]['child'][$k]["scope"])){
                            $goods_type[$key]['child'][$k]["scope"] = explode(",",$goods_type[$key]['child'][$k]["scope"]);
                            if(!in_array($member_grade_name,$goods_type[$key]['child'][$k]["scope"])){ 
                                unset($goods_type[$key]['child'][$k]);
                            }
                        }
                    }
                    $goods_type[$key]['is_good_type']=0;   //0   商品      1    商品分类
                }else{
                    //有二级分类
                    $goods_type[$key]['is_good_type']=1;   //0   商品      1    商品分类
                }
                $goods_type[$key]['child'] = array_values($goods_type[$key]['child']);
            }
   
            return ajax_success("获取成功",array("goods_type"=>$goods_type));
        }
        
    }





    /**
     * 商品首页推荐
     * GY
     */
    public function commodity_recommend(Request $request)
    {

        if ($request->isPost()) {
            $member_id = $request->only(["open_id"])["open_id"];
            $store_id = $request->only(['uniacid'])['uniacid'];
            $member_grade_name = $request->only(["member_grade_name"])["member_grade_name"]; //会员等级
            $member_grade_id = db("member")->where("member_openid", $member_id)->value("member_grade_id");
            $discount = db("member_grade")->where("member_grade_id", $member_grade_id)->value("member_consumption_discount");
            $goods = db("goods")->where("status",1)->where("store_id","EQ",$store_id)->where("label",1)->select();

            foreach ($goods as $k => $v) //所有商品
            {
                if(!empty($goods[$k]["scope"])){
                    $goods[$k]["scope"] = explode(",",$goods[$k]["scope"]);
                }
                if($goods[$k]["goods_member"] != 1){
                    $discount = 1;
                }
                if($goods[$k]["goods_standard"] == 1){
                    $standard[$k] = db("special")->where("goods_id", $goods[$k]['id'])->select();
                    $max[$k] = db("special")->where("goods_id", $goods[$k]['id'])-> max("price") * $discount;//最高价格
                    $min[$k] = db("special")->where("goods_id", $goods[$k]['id'])-> min("price") * $discount;//最低价格
                    $line[$k] = db("special")->where("goods_id", $goods[$k]['id'])-> min("line");//最低价格
                    $goods[$k]["goods_standard"] = $standard[$k];
                    $goods[$k]["goods_show_images"] = explode(",",$goods[$k]["goods_show_images"]);
                    $goods[$k]["max_price"] = $max[$k];
                    $goods[$k]["min_price"] = $min[$k];
                    $goods[$k]["line"] = $line[$k];

                if(!empty($goods[$k]["scope"])){
                    if(!in_array($member_grade_name,$goods[$k]["scope"])){ 
                        unset($goods[$k]);
                    }
                }              
                } else {
                    $goods[$k]["goods_new_money"] = $goods[$k]["goods_new_money"] * $discount;
                    $goods[$k]["goods_show_images"] = explode(",",$goods[$k]["goods_show_images"]);
                    if(!empty($goods[$k]["scope"])){
                        if(!in_array($member_grade_name,$goods[$k]["scope"])){ 
                            unset($goods[$k]);
                        }
                    }
                }      
            }
            $goods_new = array_values($goods);
            if (!empty($goods_new) && !empty($member_id)) {
                return ajax_success("获取成功", $goods_new);
            } else {
                return ajax_error("获取失败");
            }
        }

    }




    /**
     * 商品列表
     * lilu
     * uniacid
     * member_grade_name
     * id
     */
    public function commodity_list(Request $request)
    {

        if($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $member_grade_name = $request->only(["member_grade_name"])["member_grade_name"]; //会员等级
            $goods_pid = $request->only(["id"])["id"];
            $goods = db("goods")->where("pid",$goods_pid)->where("store_id","EQ",$store_id)->where("label",1)->select();
            foreach ($goods as $k => $v)
            {
                $goods[$k]["goods_show_images"] = (explode(",", $goods[$k]["goods_show_images"])[0]);
                if(!empty($goods[$k]["scope"])){
                    $goods[$k]["scope"] = explode(",",$goods[$k]["scope"]);
                    if(!in_array($member_grade_name,$goods[$k]["scope"])){ 
                        unset($goods[$k]);
                    }
                }
            }
            $new_goods = array_values($goods);
            if(!empty($new_goods) && !empty($goods_pid)){
                return ajax_success("获取成功",$new_goods);
            }else{
                return ajax_error("获取失败");
            }
        }

    }



    /**
     * lilu
     * 小程序---商品详情接口
     * id    商品id
     * uniacid   店铺id
     * open_id   会员id
     */
    public function commodity_detail(Request $request)
    {
        if ($request->isPost()) {
            $goods_id = $request->only(["id"])["id"];
            $store_id = $request->only(['uniacid'])['uniacid'];
            $member_id = $request->only(["open_id"])["open_id"];
            $member_grade_id = db("member")->where("member_openid", $member_id)->value("member_grade_id");   //
            $discount = db("member_grade")->where("member_grade_id", $member_grade_id)->value("member_consumption_discount");
            if(empty($discount)){
                $discount = 1;
            }
            $goods = db("goods")->where("id", $goods_id)->where("store_id",$store_id)->select(); // 获取商品信息
            if(empty($goods)){
                return ajax_error("获取商品出错");
            }
            //判断商品是否参加会员折扣
            if($goods[0]["goods_member"] != 1){
                $discount = 1;
            }
            //判断商品是否是限时限购商品
            $is_limit=db('limited')->where(['store_id'=>$store_id,'goods_id'=>$goods_id])->find();
            if($is_limit)
            {       //开启限时限购
                //判断限时时间
                if($is_limit['end_time']==0)  //活动不限时
                {    
                    //判断是否有库存
                    if($goods[0]['goods_repertory']>0)
                    {
                        $goods[0]['limit_condition']='1';   //限时限购开启
                        $goods[0]['limit_time']=0;          //不限时
                    }else{
                        $goods[0]['limit_condition']='0';
                    }
                }
                if($is_limit['end_time']!==0 && $is_limit['end_time']>=time()) //活动未结束
                {    
                    //判断是否有库存
                    if($goods[0]['goods_repertory']>0)
                    {
                        $goods[0]['limit_condition']='1';   //限时限购开启
                        $goods[0]['limit_time']=$is_limit['end_time'];          //距离活动结束的时间戳
                        // $goods[0]['limit_time']=$is_limit['end_time']-time();          //距离活动结束的时间戳
                    }else{
                        $goods[0]['limit_condition']='0';
                    }
                }elseif($is_limit['end_time']>0){
                       $goods[0]['limit_condition']='0';
                }
                $goods[0]['limit_number']=$is_limit['limit_number'];
                $goods[0]['goods_repertory']=$is_limit['goods_repertory'];
                $goods[0]['limit_price']=$goods[0]['limit_price'];
            }else{
                $goods[0]['limit_condition']=0;   //未开启限时限购
            }
            $goods_standard = db("special")->where("goods_id", $goods_id)->order("price asc")->select();
            $max_price = db("special")->where("goods_id", $goods_id)->max("price");
            $min_price = db("special")->where("goods_id", $goods_id)->min("price");
            $min_line = db("special")->where("goods_id", $goods_id)->min("line");
            $goods_volume  = db("special")->where("goods_id", $goods_id)->sum("volume");
            $goods_repertory = db("special")->where("goods_id", $goods_id)->sum("stock");
            $max_prices = $max_price * $discount;
            $min_prices = $min_price * $discount;
            if(!empty($goods[0]['goods_delivery'])){
                $goods[0]['goods_delivery'] = json_decode($goods[0]["goods_delivery"],true);
            }
            if(!empty($goods[0]['goods_sign'])){
                $goods[0]['goods_sign'] = json_decode($goods[0]["goods_sign"],true);
            }
            if(!empty($goods[0]['server'])){
                $goods[0]['server'] = json_decode($goods[0]["server"],true);
            }

            foreach ($goods_standard as $key => $value) {
                $goods_standard[$key]["price"] = $goods_standard[$key]["price"] * $discount;
            }
            $goods_franking = $this->get_goods_franking($goods_id);
            $goods[0]['goods_franking'] = $goods_franking;
            if ($goods[0]["goods_standard"] == 1) {      //多规格商品
                $goods[0]["goods_standard"] = $goods_standard;
                $goods[0]["goods_show_images"] = (explode(",", $goods[0]["goods_show_images"]));
                $goods[0]["max_price"] = $max_prices;
                $goods[0]["min_price"] = $min_prices;
                $goods[0]["min_line"] = $min_line;
                $goods[0]["element"] = $goods_standard[0]['element'];
                $goods[0]["unit"] = $goods_standard[0]['offer'];
                $goods[0]["goods_volume"] = $goods_volume;
                $goods[0]['goods_repertory'] = $goods_repertory;
            } else {
                $goods[0]["goods_new_money"] = $goods[0]["goods_new_money"] * $discount;
                $goods[0]["goods_show_images"] = (explode(",", $goods[0]["goods_show_images"]));
                $goods[0]["min_line"] = $goods[0]["goods_bottom_money"];
                $goods[0]["unit"] = $goods[0]["monomer"];

            }

            
            //获取当前商铺当前商品的所有评论
            $evolution=db('order_evaluate')->where(['store_id'=>$store_id,'goods_id'=>$goods_id])->order('create_time desc')->select();
            foreach($evolution as $k =>$v){
                //判断是否开启评价功能
                if($v['is_show']=='1'){
                    //开启评价功能
                    $evolution[$k]['images']=db('order_evaluate_images')->where('evaluate_order_id',$v['id'])->field('images')->select();
                    $evolution[$k]['head_pic']=db('member')->where('member_id',$v['user_id'])->value('member_head_img');
                    $evolution[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
                    $evolution[$k]['business_repay']=$v['business_repay'];
                }else{
                    unset($evolution[$k]);
                    continue;
                }
            }
            $goods[0]['evolution']=$evolution;
            if (!empty($goods) && !empty($goods_id)){
                return ajax_success("获取成功", $goods);
            } else {
                return ajax_error("获取失败");
            }
        }

    }


    /**
     **************郭杨*******************
     * @param Request $request
     * Notes:上门自提默认收获地址
     **************************************
     */
    public function approve_address(Request $request){
        if($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $data =Db::name("extract_address")->where("label",1)->where("store_id","EQ",$store_id)	
                ->find();            
            if(!empty($data)){
                return ajax_success("返回成功",$data);
            }else{
                return ajax_error("没有默认自提地址");
            }
        }
   }



    /**
     **************郭杨*******************
     * @param Request $request
     * Notes:默认自提地址列表
     **************************************
     */
    public function approve_list(Request $request){
        if($request->isPost()){
            $store_id = $request->only(['uniacid'])['uniacid'];
            $data = Db::name("extract_address")->where("store_id","EQ",$store_id)->select();            
            if($data){
                return ajax_success("返回成功",$data);
            }else{
                return ajax_error("没有默认自提地址");
            }
        }
    }





    /**
     **************郭杨*******************
     * @param Request $request
     * Notes:选择自提地址详情
     **************************************
     */
    public function approve_detailed(Request $request){
        if($request->isPost()){
            $id = $request->only(["id"])["id"];
            $data =Db::name("extract_address")->where("id",$id)
                ->find();            
            if(!empty($data)){
                return ajax_success("返回成功",$data);
            }else{
                return ajax_error("参数有误");
            }
        }
    }
    /**
     * ceshi
     * 比特币
     */
    // public function get_coinquotation(){
        /*//获取BTC当前最新行情 - Ticker(宝币网)
        $coin = $_GET['coin'];
        $btc_quotation = get_now_quotation($coin);
        return json($btc_quotation);*/
    //     $szUrl = "https://www.feixiaohao.com/#USD";
    //     $UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
    //     $curl = curl_init();
    //     curl_setopt($curl, CURLOPT_URL, $szUrl);
    //     curl_setopt($curl, CURLOPT_HEADER, 0);  //0表示不输出Header，1表示输出
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //     curl_setopt($curl, CURLOPT_ENCODING, '');
    //     curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
    //     curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    //     $content_strs = curl_exec($curl);
    
    
    //     $key1 = 'BTC-比特币';
    //     $key1_end = '/currencies/bitcoin/#markets target=_blank class=volume';
    //     $key2 = 'ETH-以太坊';
    //     $key2_end = '/currencies/ethereum/#markets target=_blank class=volume';
    
    
    //     $ex = "/\d+/";
    //     $txt1=getNeedBetween($content_strs, $key1 , $key1_end );
    //     $arr1 = [];
    //     preg_match_all($ex,$txt1,$arr1);
    //     $btclast = $arr1[0][5];
    
    //     $txt2=getNeedBetween($content_strs, $key2 , $key2_end );
    //     $arr2 = [];
    //     preg_match_all($ex,$txt2,$arr2);
    //     $ethlast = $arr2[0][5];
    //     $data = array([
    //         'btclast' => $btclast,
    //         'ethlast' => $ethlast,
    //     ]);
    //     return json_encode($data);
    // }

    /**
     * 小程序前端搜索框（商品）
     * GY
     */
    public function getSearchGood(Request $request)
    {

        if ($request->isPost()) {
            $member_id = $request->only(["member_id"])["member_id"];        //会员id
            $store_id = $request->only(['uniacid'])['uniacid'];             //店铺id
            $goods_name = $request->only(['goods_name'])['goods_name'];      //商品名

            if(isset($member_id) && isset($store_id) && isset($goods_name)){
                $member_grade_id = db("member")->where("member_id", $member_id)->find();
                $member_grade_name = $member_grade_id['member_grade_name'];
                $discount = db("member_grade")->where("member_grade_id", $member_grade_id['member_grade_id'])->value("member_consumption_discount");
                $goods = db("goods")
                        ->field("id,goods_name,goods_member,goods_show_image,goods_new_money,goods_bottom_money,goods_volume,goods_standard,scope,goods_selling")
                        ->where("status",1)
                        ->where("store_id","EQ",$store_id)
                        ->where("label",1)
                        ->where("goods_name", "like","%" .$goods_name ."%")
                        ->select();

                foreach ($goods as $k => $v) //所有商品
                {
                    if(!empty($goods[$k]["scope"])){
                        $goods[$k]["scope"] = explode(",",$goods[$k]["scope"]);
                    }
                    if($goods[$k]["goods_member"] != 1){
                        $discount = 1;
                    }
                    if($goods[$k]["goods_standard"] == 1){
                        $standard = db("special")->where("goods_id", $goods[$k]['id'])->order('price asc')->find();
                        $goods[$k]["goods_new_money"] = $standard['price'] * $discount ;//最低价格
                        $goods[$k]["goods_bottom_money"] = $standard['line'] ;//划线价

                    if(!empty($goods[$k]["scope"])){
                        if(!in_array($member_grade_name,$goods[$k]["scope"])){ 
                            unset($goods[$k]);
                        }
                    }              
                    } else {
                        $goods[$k]["goods_new_money"] = $goods[$k]["goods_new_money"] * $discount;
                        if(!empty($goods[$k]["scope"])){
                                if(!in_array($member_grade_name,$goods[$k]["scope"])){ 
                                    unset($goods[$k]);
                                }
                            }
                        }
                    }      
                }
                $goods_new = array_values($goods);
                if (!empty($goods_new) && !empty($member_id)) {
                    return ajax_success("获取成功", $goods_new);
                } else {
                    return ajax_error("获取失败");
                }
            } else {
                return ajax_error("请检查参数是否正确");
            }
        }
        /**
         * lilu
         * 分类页面获取二级分类下面的商品信息
         * pid    二级分类id
         * order   排序规则    1   默认  综合   2  最新   3  价格    4  价格
         */
        public function get_second_type_list(){
            $input=input();
            if(empty($input)){
                return ajax_error('传递参数错误');
            }else{
                $where['store_id']=$input['uniacid'];
                $where['pid']=$input['pid'];    //二级分类id
                $where['label']=1;    //上下架
                //获取当前会员的会员级别
                $member_grade=db('member')->where('member_openid',$input['open_id'])->value('member_grade_name');
                if($input['order']==1){     //综合排序
                    $goods_list=db('goods')->where($where)->field('goods_selling,id,goods_name,goods_show_image,goods_member,goods_new_money,scope')->select();
                }elseif($input['order']==2){    //最新
                    $goods_list=db('goods')->where($where)->order('date desc')->field('goods_selling,id,goods_name,goods_show_image,goods_member,goods_new_money,scope')->select();
                }elseif($input['order']==3){     //价格  --升序
                    $goods_list=db('goods')->where($where)->field('goods_selling,id,goods_name,goods_show_image,goods_member,goods_new_money,scope')->select();
                }elseif($input['order']==4){     //价格-- 降序
                    $goods_list=db('goods')->where($where)->field('goods_selling,id,goods_name,goods_show_image,goods_member,goods_new_money,scope')->select();
                }elseif($input['order']==5){     //销量----升序
                    $goods_list=db('goods')->where($where)->order('goods_volume asc')->field('goods_selling,id,goods_name,goods_show_image,goods_member,goods_new_money,scope')->select();
                }elseif($input['order']==6){     //销量----降序
                    $goods_list=db('goods')->where($where)->order('goods_volume desc')->field('goods_selling,id,goods_name,goods_show_image,goods_member,goods_new_money,scope')->select();
                }
                if($goods_list){
                    foreach($goods_list as $k=>$v){
                        $grade=explode(',',$v['scope']);
                        if(!in_array($member_grade,$grade)){
                            unset($goods_list[$k]);
                            continue;
                        }
                        $member_grade_id = db("member")->where("member_openid", $input['open_id'])->value("member_grade_id");
                        $discount = db("member_grade")->where("member_grade_id", $member_grade_id) ->value("member_consumption_discount");
                        if($v['goods_member']==1){   //参加折扣
                            $discount=$discount;
                        }else{
                            $discount=1;
                        }
                        $goods_list[$k]['price']=round($v['goods_new_money']*$discount,2);
                    }
                    if($input['order']==3){
                        array_multisort(array_column($goods_list,'price'),SORT_ASC,$goods_list);
                    }elseif($input['order']==4){
                        array_multisort(array_column($goods_list,'price'),SORT_DESC,$goods_list);
                    }
                    return ajax_success('获取成功',$goods_list);
                }else{
                    return ajax_success('获取成功');
                }
                
            }
        }


    /**
     * 获取当前商品邮费
     * @param $goods_id 商品id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function get_goods_franking($goods_id){

        //判断该用户是否有运费模板
        //是否多规格
        //有则通过单位找到对应运费模板计算价格
        //无则显示用户设置的运费价格
        //包邮则为0

        $goods = Db::name('goods')->where('id',$goods_id)->find();
        if(empty($goods['templet_name']) && $goods['templet_id']){
            $templet_name = explode(',',$$goods['templet_name']);
            $templet_id  = explode(',',$goods['templet_id']);
            //普通
            if($goods['goods_standard'] == 0){
                $key = array_search($goods['monomer'],$templet_name);
                $franking = Db::name('express')->where('id',$templet_id[$key])->value('price');
                return $franking;
            } else {
                //特殊
                $offer = Db::name('special')->where('goods_id',$goods_id)->value('offer');
                $key = array_search($offer,$templet_name);
                $franking = Db::name('express')->where('id',$templet_id[$key])->value('price');
                return $franking;              
            }
        }
        $franking = $goods['goods_franking'];
        return $franking;
    }
    
}
