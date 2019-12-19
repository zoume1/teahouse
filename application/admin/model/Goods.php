<?php
namespace app\admin\model;
vendor('qiniu.autoload');
use think\Model;
use think\Session;
use think\Db;
use Qiniu\Auth as Auth;
use Qiniu\Storage\UploadManager;
use think\Controller;
use app\city\controller\Picture;
use app\index\controller\My;

class Goods extends Model
{
    protected $table = "tb_goods";


        
     /**
     * 销商申请记录详情
     * @param $where
     * @return Apply|static
     * @throws \think\exception\DbException
     */
    public static function detail($goods_id)
    {
        return self::get(['id'=>$goods_id,'distribution_status'=>1]);
    }

    /**
     * 判断商品是否设置分销
     * @param $goods_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getDistributionStatus($goods_id)
    {
        
        foreach($goods_id as $value)
        {
            $detail = self::detail($value);
            if($detail){
                $data[] = $value;
            }
        }

         return  isset($data) ? array_values($data) : null;
        
    }


    /**
     * 返回订单金额
     * @param $goods_first
     * @param $goods_second
     * @param $money
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getDistributionPrice($goods_first,$goods_second,$money)
    {
        
        foreach($goods_first as $key => $value)
        {
            if(in_array($value,$goods_second)){
                $data[] = $money[$key];
            }
        }

         return  isset($data) ? array_values($data) : null;
        
    }


    public function gettoken()
    {
        $store_id = Session::get("store_id");
        $applet = Db::table('applet')
                ->where('id','=',$store_id)
                ->find();
                
        $APPID = $applet['appID'];
        $APPSECRET =  $applet['appSecret'];
        $access_token = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$APPID."&secret=".$APPSECRET;
        $json = $this->httpRequest($access_token);
        return  json_decode($json,true);
    }

    //curl
    public function httpRequest($url, $data='', $method='GET'){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if($method=='POST')
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data != '')
            {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }


    //生成二维码
    public  function qrcode($goods_id)
    {
        $ACCESS_TOKEN = $this->gettoken();
        $puthc = 'pages/logs/logs?goods=share&title='.$goods_id;//小程序的路径 可以带参数
        $qcode ="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$ACCESS_TOKEN['access_token'];
        $param = json_encode(array("path"=>$puthc,"width"=> 150));
        $result = $this->httpRequest($qcode,$param,"POST");
        $puth = ROOT_PATH . 'public' . DS . 'share'.DS.'D'.time().rand(100000,999999).'.png';
        file_put_contents($puth,$result);
        $file_name = basename($puth,'.png');
        $image_url = '/share/'.$file_name.'.png';
        $resultes = db('goods')->where('id','=',$goods_id)->update(['share_code'=>$image_url]);
        return $puth;
    }

    public function gettokenes($store_id)
    {
        $applet = Db::table('applet')
                ->where('id','=',$store_id)
                ->find();
                
        $APPID = $applet['appID'];
        $APPSECRET =  $applet['appSecret'];
        $access_token = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$APPID."&secret=".$APPSECRET;
        $json = $this->httpRequest($access_token);
        return  json_decode($json,true);
    }

    //生成存茶分享码
    public function share_qrcode($order_id,$store_id)
    {
        $ACCESS_TOKEN = $this->gettokenes($store_id);
        $puthc = 'pages/logs/logs';//小程序的路径 可以带参数
        $qcode ="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$ACCESS_TOKEN['access_token'];
        
        // $color = [
        //     'r' => 0,
        //     'g' => 0,
        //     'b' => 0,
        // ];
        // $qrcode_data = [
        //     'scene' => 'order_id=' . $order_id ,
        //     'page' => $puthc,
        //     'width' => 430, 
        //     'auto_color' => false,  //自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调，默认 false
        //     'line_color' => json_encode($color),   //auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示
        //     'is_hyaline' => true,   //是否需要透明底色，为 true 时，生成透明底色的小程序
        // ];
        $qrcode_data['scene'] = $order_id;
        // 宽度
        $qrcode_data['width'] = 430;
        // 页面
        $qrcode_data['page'] = $puthc;     //扫码后进入的小程序页面
//        $postdata['page']="pages/postcard/postcard";
        // 线条颜色
        $qrcode_data['auto_color']=false;
        //auto_color 为 false 时生效
        $qrcode_data['line_color']=['r'=>'0','g'=>'0','b'=>'0'];
        // 是否有底色为true时是透明的
        $qrcode_data['is_hyaline']=true;
        $param = json_encode($qrcode_data);
        $result = (new My()) -> api_notice_increment($qcode,$param);
        $puth = ROOT_PATH . 'public' . DS . 'shareorder'.DS.'D'.time().rand(100000,999999).'.png';
        file_put_contents($puth,$result);
        $file_name = basename($puth,'.png');
        $image_url = '/shareorder/'.$file_name.'.png';
        return $puth;
    }
 
}