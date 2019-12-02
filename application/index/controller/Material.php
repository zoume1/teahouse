<?php
namespace app\index\controller;

use think\Controller;
use think\Cache;
use think\Session;
use think\Db;

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
            $store_id=$goods_info['store_id'];
            if (file_exists(ROOT_PATH . 'public' . DS . 'uploads'.DS.$input['code'].'.txt')) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                $re=file_get_contents(ROOT_PATH . 'public' . DS . 'uploads'.DS.$input['code'].'.txt');
            }else{
                 //获取用户的信息
                //获取携带参数的小程序的二维码
                $page='pages/logs/logs';
                $qrcode=$this->mpcode($page,$input['code'],$store_id);
                //把qrcode文件写进文件中，使用的时候拿出来
                $dateFile =$store_id . "/";  //创建目录
                $new_file = ROOT_PATH . 'public' . DS . 'uploads'.DS.$input['code'].'.txt';
                if (file_put_contents($new_file, $qrcode)) {
                    $re=file_get_contents(ROOT_PATH . 'public' . DS . 'uploads'.DS.$input['code'].'.txt');
                } else {
                    return ajax_success('获取失败');
                }
            }
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
     /*上面生成的是数量限制10万的二维码，下面重写数量不限制的码*/
    /*getWXACodeUnlimit*/
    /*码一，圆形的小程序二维码，数量限制一分钟五千条*/
    /*45009    调用分钟频率受限(目前5000次/分钟，会调整)，如需大量小程序码，建议预生成。
    41030    所传page页面不存在，或者小程序没有发布*/
    public function mpcode($page,$cardid,$uniacid){
        //参数----会员id
        $postdata['scene']=$cardid;
        // 宽度
        $postdata['width']=430;
        // 页面
        $postdata['page']=$page;     //扫码后进入的小程序页面
//        $postdata['page']="pages/postcard/postcard";
        // 线条颜色
        $postdata['auto_color']=false;
        //auto_color 为 false 时生效
        $postdata['line_color']=['r'=>'0','g'=>'0','b'=>'0'];
        // 是否有底色为true时是透明的
        $postdata['is_hyaline']=true;
        $post_data = json_encode($postdata);
        $access_token=$this->getAccesstoken($uniacid);
        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
        $result=$this->api_notice_increment($url,$post_data);
        $data='image/png;base64,'.base64_encode($result);
       
        return $data;
//        echo '<img src="data:'.$data.'">';
    }



}
