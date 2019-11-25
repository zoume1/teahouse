<?php
namespace app\index\controller;

use think\Controller;
use think\Cache;
use think\Session;

/**
 * lilu
 * 防伪溯源
 */
class Material extends Controller
{
    
    /**
     * lilu
     * 防伪溯源获取商品信息
     * param   uniacid    店铺id
     * code    子标
     */
    public function get_anti_fake_info()
    {
        $input =input();
        if($input){
            //获取商品的信息
            $goods_info=db('anti_parent_code')->alias('a')->join('tb_anti_goods w','a.pid = w.id')->where('child_code|parent_code',$input['code'])
            ->field('*')->find();
            if($goods_info){
                return ajax_success('获取成功',$goods_info);
            }else{
                return ajax_error('获取失败，未发现商品信息');
            }
        }else{
            return ajax_error('缺少必要的参数');
        }

    }
    /**
     * lilu
     * 插入温湿度历史记录
     * param   uniacid    店铺id
     * param   instrument   设备系列号
     * param   temperature  温度
     * param   humidity     湿度
     */
    public function get_humiture()
    {
        //获取参数
        $input=input();
        if($input)
        {
            $input['store_id']=$input['uniacid'];
            unset($input['uniacid']);
            $input['create_time']=time();
            $res=db('humiture')->insert($input);
            if($res){
                return  ajax_success('插入成功');
            }else{
                return  ajax_error('插入失败');
            }
        }else{
            return  ajax_error('缺少必要的参数');
        }
    }
    /**
     * lilu
     * 获取温湿度历史记录
     * param   stime    开始时间
     * param   etime    结束时间
     * param   uniacid    店铺id
     */
    public function get_humiture_list()
    {
        //获取参数
        $input=input();
        if($input)
        {
            $where['create_time']=array('between',array(strtotime($input['stime']),strtotime($input['etime'])));
            $where['store_id']=$input['uniacid'];
            $res=db('humiture')->where($where)->limit(10)->select();
            if($res){
                return  ajax_success('获取成功',$res);
            }else{
                return  ajax_error('插入失败');
            }
        }else{
            return  ajax_error('缺少必要的参数');
        }
    }



}
