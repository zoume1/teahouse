<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\paginator\driver\Bootstrap;

class Information extends Controller{
    
    /**
     * [数据概况]
     * 郭杨
     */    
    public function data_index(){    
        $store_id=Session::get('store_id');
        //今日订单数
        $start_time=strtotime(date("Y-m-d"));
        $end_time=strtotime(date("Y-m-d H:i:s"));
        $where['order_create_time']=array('between',array($start_time,$end_time));
        $where['store_id']=Session::get('store_id');
        $data['order_num']=db('order')->where($where)->count();     //今日总订单
        $where['status']=array('between',array(2,8));
        $data['order_money']=db('order')->where($where)->sum('order_real_pay');   //今日总销售额     2-8
        $data['order_money']=round($data['order_money'],2);
        //昨日订单数
        $start_time2=strtotime(date("Y-m-d"))-24*3600;
        $end_time2=strtotime(date("Y-m-d"));
        $where2['order_create_time']=array('between',array($start_time2,$end_time2));
        $where2['store_id']=Session::get('store_id');
        $data['order_num2']=db('order')->where($where2)->count();     //昨日总订单
        $where2['status']=array('between',array(2,8));
        $data['order_money2']=db('order')->where($where2)->sum('order_real_pay');   //昨日总销售额     2-8
        $data['order_money2']=round($data['order_money2'],2);
        //七日订单数
        $start_time3=strtotime(date("Y-m-d "))-24*3600*7;
        $end_time3=strtotime(date("Y-m-d"));
        $where2['order_create_time']=array('between',array($start_time3,$end_time3));
        $where3['store_id']=Session::get('store_id');
        $where3['status']=array('between',array(2,8));
        $data['order_money3']=db('order')->where($where3)->sum('order_real_pay');   //七日总销售额     2-8
        $data['order_money3']=round($data['order_money3'],2);
        //待付款订单
        //1.普通订单
        $data['daifu_num']=db('order')->where(['store_id'=>$store_id,'status'=>1])->count();   //
        //2.众筹订单
        $data['daifu_num_crowd']=db('crowd_order')->where(['store_id'=>$store_id,'status'=>1])->count();   //
        $data['daifu_count']=$data['daifu_num']+$data['daifu_num_crowd'];     //代付订单的总和
        //待发货订单
        //1.普通订单
        $data['fahuo_num']=db('order')->where(['store_id'=>$store_id,'status'=>5])->count();   //
        //2.众筹订单
        $data['fahuo_num_crowd']=db('crowd_order')->where(['store_id'=>$store_id,'status'=>2])->count();   //
        $data['fahuo_count']=$data['fahuo_num']+$data['fahuo_num_crowd'];     //待发货订单的总和
        //已完成订单
        $data['yiwan_num']=db('order')->where(['store_id'=>$store_id,'status'=>8])->count();   //
        //待处理售后订单
        $pp['store_id']=$store_id;
        $pp['status']='1';
        $data['after_sale_num']=db('after_sale')->where($pp)->count();

        //商品模块
        $data['shang']=db('goods')->where(['store_id'=>$store_id,'label'=>1])->count();
        $data['xia']=db('goods')->where(['store_id'=>$store_id,'label'=>0])->count();
        $data['zong']=$data['shang']+$data['xia'];

        //统计会员模块
        $time['member_create_time']=array('between',array($start_time,$end_time));
        $time['store_id']=$store_id;
        $time['member_status']='1';
        $data['d_member_num']=db('member')->where($time)->count();     //当日会员新增数量
        //昨日新增会员数量
        $time2['member_create_time']=array('between',array($start_time2,$end_time2));
        $time2['store_id']=$store_id;
        $time2['member_status']='1';
        $data['w_member_num']=db('member')->where($time2)->count();     //当日会员新增数量
        //本月新增会员数量
        $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
        $endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
        $time3['member_create_time']=array('between',array($beginThismonth,$endThismonth));
        $time3['store_id']=$store_id;
        $time3['member_status']='1';
        $data['m_member_num']=db('member')->where($time3)->count();     //当日会员新增数量
        //总会员数量
        $time4['store_id']=$store_id;
        $time4['member_status']='1';
        $data['z_member_num']=db('member')->where($time4)->count();     //当日会员新增数量
        return view("data_index",['data'=>$data]);
    }
    /**
     * [溯源分析]
     * lilu
     */    
    public function analytical_index(){   
        //统计店铺防伪溯源信息
        $store_id=Session::get('store_id');
        $sql='select goods_name name,sum_num y from tb_anti_goods  where sum_num > 0 and store_id = '.$store_id.' order by sum_num desc limit 10 ' ;
        $list=Db::query($sql);
        return view("analytical_index",['data'=>$list]);
    }
    /**
     * lilu
     * 店铺----店铺订单分析
    */
    public function store_analyse()
    {
        //获取店铺id
        $store_id=Session::get('store_id');
        //统计本月的订单数/天
        $start_time=strtotime(date('Y-m-02'));  //获取本月第一天的时间戳
        $j = date("t");                         //获取当前月份天数
        $m = date("d");                         //获取当前月份天数
        $xData = array();                       //数组
        for($i=0;$i<$m;$i++)
        {
            $xData[] = $start_time+$i*86400; //每隔一天赋值给数组
        }
        //获取当月的订单
        $where['order_create_time']=array('between',array(strtotime(date('Y-m-01')),strtotime(date('Y-m-'.$j))+86400));
        $where['status']=array('between',array(2,8));
        $where['store_id']=$store_id;
        $order_list=db('order')->where($where)->order('order_create_time asc')->group('order_create_time')->select();
        $order_num=db('order')->where($where)->order('order_create_time asc')->group('order_create_time')->count();
        $last_month = date('Y-m', strtotime('last month'));
        $last['first'] =strtotime($last_month . '-01 00:00:00') ;
        $last['end'] =strtotime(date('Y-m-d H:i:s', strtotime("$last_month +0 month +$m day +23 hours +59 minutes +59 seconds"))) ;
        $where2['order_create_time']=array('between',array($last['first'],$last['end']));
        $where2['status']=array('between',array(2,8));
        $where2['store_id']=$store_id;
        $order_num2=db('order')->where($where2)->order('order_create_time asc')->group('order_create_time')->count();
        if($order_num2==0){
            $pre=0;
        }else{
            $pre=round($order_num/$order_num2*100,2);
        }
        if($order_list)
        {
            $arr=[];
            foreach($xData as $k =>$v)
            {
                $pp[$k]=0;
                foreach($order_list as $k2 =>$v2)
                {
                    if($v >$v2['order_create_time']){      //当天的订单数据
                        $pp[$k]++;
                        unset($order_list[$k2]);
                    }else{
                        $arr[$k]=$pp[$k];
                        break;
                    }
                }
                if(!array_key_exists($k,$arr)){
                    $arr[$k]=0;
                }
            }
            return ajax_success('获取成功',["arr"=>$arr,"precent"=>$pre,'num'=>$order_num]);
        }else{
            return ajax_error('获取失败');
        }
    }
    /**
     * lilu
     * 店铺----店铺销售额分析
    */
    public function store_money_analyse()
    {
        //获取店铺id
        $store_id=Session::get('store_id');
        //统计本月的订单数/天
        $start_time=strtotime(date('Y-m-02'));  //获取本月第一天的时间戳
        $j = date("t");                         //获取当前月份天数
        $m = date("d");                         //获取当前月份天数
        $xData = array();                       //数组
        for($i=0;$i<$m;$i++)
        {
            $xData[] = $start_time+$i*86400; //每隔一天赋值给数组
        }
        //获取当月的订单
        $where['order_create_time']=array('between',array(strtotime(date('Y-m-01')),strtotime(date('Y-m-'.$j))+86400));
        $where['status']=array('between',array(2,8));
        $where['store_id']=$store_id;
        $order_list=db('order')->where($where)->order('order_create_time asc')->group('order_create_time')->select();
        $order_num=round(db('order')->where($where)->sum('order_real_pay'),2);    //当月的订单总金额
        $last_month = date('Y-m', strtotime('last month'));
        $last['first'] =strtotime($last_month . '-01 00:00:00') ;
        $last['end'] =strtotime(date('Y-m-d H:i:s', strtotime("$last_month +0 month +$m day +23 hours +59 minutes +59 seconds"))) ;
        $where2['order_create_time']=array('between',array($last['first'],$last['end']));
        $where2['status']=array('between',array(2,8));
        $where2['store_id']=$store_id;
        $order_num2=db('order')->where($where2)->sum('order_real_pay');    //上月的订单总金额
        if($order_num2==0){
            $pre=0;
        }else{
            $pre=round($order_num/$order_num2*100,2);
        }
        if($order_list)
        {
            $arr=[];
            foreach($xData as $k =>$v)
            {
                $pp[$k]=0;
                foreach($order_list as $k2 =>$v2)
                {
                    if($v >$v2['order_create_time']){      //当天的订单数据
                        $pp[$k]=$pp[$k]+$v2['order_real_pay'];
                        unset($order_list[$k2]);
                    }else{
                        $arr[$k]=$pp[$k];
                        break;
                    }
                }
                if(!array_key_exists($k,$arr)){
                    $arr[$k]=0;
                }
            }
            return ajax_success('获取成功',["arr"=>$arr,"precent"=>$pre,'num'=>$order_num]);
        }else{
            return ajax_error('获取失败');
        }
    }
 }