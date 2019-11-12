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
    public function qrcode($goods_id)
    {
        $ACCESS_TOKEN = $this->gettoken();
        $puthc = 'pages/logs/logs?goods=share&title='.$goods_id;//小程序的路径 可以带参数
        $qcode ="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$ACCESS_TOKEN['access_token'];
        $param = json_encode(array("path"=>$puthc,"width"=> 150));
        $result = $this->httpRequest( $qcode,$param,"POST");
        $puth = ROOT_PATH . 'public' . DS . 'share'.DS.'D'.time().rand(100000,999999).'.png';
        file_put_contents($puth,$result);

        $image_url = (new Picture())->photo_pins($puth);
        $resultes = db('goods')->where('id','=',$goods_id)->update(['share_code'=>$image_url]);
        unlink($puth);
        return $resultes;
    }
 
}