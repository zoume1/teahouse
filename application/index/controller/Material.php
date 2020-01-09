<?php
namespace app\index\controller;

use think\Controller;
use think\Cache;
use think\Session;
use think\Db;
use think\View;
use app\index\controller\My;

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
            // if($input['uniacid']){}
            $goods_info=db('anti_parent_code')->alias('a')->join('tb_anti_goods w','a.pid = w.id')->where('child_code|parent_code',$input['code'])
            ->find();
            if(!$goods_info){
                return ajax_error('获取失败，未发现商品信息');
            }
            $store_id=db('anti_parent_code')->where('pid',$goods_info['id'])->value('store_id');
            $input['store_id']=$store_id;
            //判断小程序是否已发布
            $is_fabu=Db::table('applet')->where('id',$store_id)->value('is_fabu');
            if($is_fabu==0){
                $re='';
            }else{
                $my=new My();
                $re=$my->create_goods_code($input);
            }
            //获取商品图片
            if($goods_info['is_create_good']==1){
                $goods_info['goods_show_image']=db('goods')->where('goods_number',$goods_info['goods_number'])->value('goods_show_image');
            }else{
                $goods_info['goods_show_image']='';
            }
            //获取新用户注册奖励的积分
            $register_integral = db('recommend_integral')->where("store_id",$store_id)->value('register_integral');
            $goods_info['register_integral']=$register_integral;
            $goods_info['qr_img']=$re;
            $applet_name=Db::table('applet')->where('id',$store_id)->value('name');
            $goods_info['applet_name']=$applet_name;
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
            // $input['store_id']=$input['uniacid'];
            // unset($input['uniacid']);
            // $input['create_time']=time();
            // $res=db('humiture')->insert($input);
            // if($res){
            //     return  ajax_success('插入成功');
            // }else{
            //     return  ajax_error('插入失败');
            // }
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
            $time=db('humiture')->where($where)->column('create_time');
            foreach($time as $k=>$v){
                $data[0][]=date('Y/m/d',$v);
            }
            $data[1]=db('humiture')->where($where)->column('temperature');
            $data[2]=db('humiture')->where($where)->column('humidity');
            if($data){
                return  ajax_success('获取成功',$data);
            }else{
                return  ajax_error('选择的时间段内暂无数据');
            }
        }else{
            return  ajax_error('缺少必要的参数');
        }
    }
    /**
     * nfc/扫码增加次数
     * code    子标
     * type    1  nfc    2  扫码
     */
    public function inc_number()
    {
        $input=input();
        if(!$input)
        {
            return ajax_error('获取参数失败') ;
        }
        $pp=$input['code'];
        if($input['type']==1){
           db('anti_parent_code')->where('child_code',$input['code'])->setInc('nfc_num',1);
        }else{
            db('anti_parent_code')->where('child_code',$input['code'])->setInc('qr_num',1);
        }
        $pid=db('anti_parent_code')->where('child_code',$input['code'])->value('pid');
        db('anti_goods')->where('id',$pid)->setInc('sum_num',1);
        return ajax_success('更新成功');
    }
    



}
