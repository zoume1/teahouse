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
        $ACCESS_TOKEN = (new My())->getAccesstoken($store_id);
        $puthc = 'pages/logs/logs';//小程序的路径 可以带参数
        $qcode ="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$ACCESS_TOKEN;
        $qrcode_data['scene'] = 'house_order_id='.$order_id;
        $qrcode_data['width'] = 430;
        $qrcode_data['page'] = $puthc;     //扫码后进入的小程序页面
        $qrcode_data['auto_color']= false;
        $qrcode_data['line_color']= ['r'=>'0','g'=>'0','b'=>'0'];
        $qrcode_data['is_hyaline']= true;
        $param = json_encode($qrcode_data);
        $result = (new My()) -> api_notice_increment($qcode,$param);
        $datas='image/png;base64,'.base64_encode($result);
        $new_file = ROOT_PATH . 'public' . DS . 'shareorder'.DS.$order_id.'.txt';
        if (file_put_contents($new_file, $datas)) {
            $re = file_get_contents($new_file);
            return $re;
        } else {
            return false;
        }
    }
 
    /**
     * 送存商品编码搜索
     * @param $goods_number
     * @return Apply|static
     * @throws \think\exception\DbException
     */
    public static function accompany_goods($goods_number,$status=0)
    {
        $store_id = Session :: get('store_id');
        if(!isset($goods_number) || empty($goods_number)) return jsonError('商品编码不能为空');
        $accompany_data = Db::name('goods')->where('goods_number|goods_name', 'like', '%' . trim($goods_number) . '%')->where('store_id',$store_id)->find();

        if(!empty($accompany_data)){
            if($status == 1){
                return $accompany_data;
            }
            if($accompany_data['goods_standard'] == 0)  return jsonSuccess('搜索成功',$accompany_data);
            return jsonError('该商品为多规格商品，请输入单规格商品编码');
        } 
        return jsonError('没有该商品编码，请仔细核对再搜索');
    }


        //生成送存商品全向码
        public  function unique_qrcode($id,$accompany_id)
        {
            //$id 为生成的全向码id
            $ACCESS_TOKEN = $this->gettoken();
            $puthc = 'pages/logs/logs?code_id='.$id;//小程序的路径 可以带参数
            $qcode ="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$ACCESS_TOKEN['access_token'];
            $param = json_encode(array("path"=>$puthc,"width"=> 150));
            $result = $this->httpRequest($qcode,$param,"POST");
            $puth = ROOT_PATH . 'public' . DS . 'uniquecode'.DS.'D'.time().rand(100000,999999).'.png';
            file_put_contents($puth,$result);
            $file_name = basename($puth,'.png');
            $image_url = '/uniquecode/'.$file_name.'.png';
            $bool  = Db::name('accompany')->where('id','=',$accompany_id)->update(['image_url'=> $image_url]);
            return $bool ? $bool : false; 
        }

        //生成送存商品定向码
        public  function directional_qrcode($id,$rest_id)
        {
            ini_set('max_execution_time', '1000');
            $ACCESS_TOKEN = $this->gettoken();
            $puthc = 'pages/logs/logs?code_id='.$id;//小程序的路径 可以带参数
            $qcode ="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$ACCESS_TOKEN['access_token'];
            $param = json_encode(array("path"=>$puthc,"width"=> 150));
            $result = $this->httpRequest($qcode,$param,"POST");
            $puth = ROOT_PATH . 'public' . DS . 'directional'. DS . $rest_id .DS.'D'.time().rand(100000,999999).'.png';
            $bool = file_put_contents($puth,$result);
            return $bool ? $bool : false; 
        }

        public  function addFileToZip($id) {
            $zip_name = $id.'.zip';
            $path = ROOT_PATH . 'public' . DS . 'directional'. DS . $id; //打开文件夹路径
            try {
                $zip = new \ZipArchive();
                if ($zip->open($zip_name, $zip::OVERWRITE) === TRUE) {
                    $handler = opendir($path); 
                    while (($filename = readdir($handler)) !== false){
                        if ($filename != "." && $filename != "..") {   
                                $zip->addFile($path . "/" . $filename);
                            }
                        }
                    }
                    @closedir($path);
                $zip->close(); //关闭处理的zip文件
                return $zip;
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                halt($this->error);
                return false;
            }
        }

        


}