<?php
namespace app\city\controller;
vendor('qiniu.autoload');
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;


class Picture extends Controller
{
    private  $accesskey = 'Rf_gkgGeg_lYnq30jPAa725UQax5JYYqt_D-BbMZ';
    private  $secrectkey = 'P7MWrpaKYM65h1qCIM0GW-uFkkNgbhkGvM5oKqeB';
    private  $bucket = 'goods';
    private  $domain='teahouse.siring.cn';


    /**
     * 总控上传图片
     * Class Picture
     * @package app\city\controller
     */
    public static function upload_picture($images)
    {
        $file = request()->file($images);
        if (!empty($file) && is_array($file)) {              
            foreach ($file as $k=>$v) {
                $picture_list = self::photo_pin($v);
            }    
              
        } elseif (!empty($file)){
            $picture_list = self::photo_pin($file);
        }

        return isset($picture_list) ? $picture_list : false;
    }



    /**
     * 总控上传单张图片
     * Class Picture
     * @package app\city\controller
     */
    public static function photo_pin($file){
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');    //本地保存
        $filePath = $info->getPathName();
        // 要上传图片的本地路径
        $ext = pathinfo($info->getInfo('name'), PATHINFO_EXTENSION);  //后缀
        //获取当前控制器名称
        $controllerName = 'index';
        // 上传到七牛后保存的文件名
        $key =substr(md5($info->getRealPath()) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
        // 需要填写你的 Access Key 和 Secret Key
        // 构建鉴权对象
        $auth = new Auth(self::accesskey,self::secrectkey);
        // 要上传的空间
        $token = $auth->uploadToken(self::bucket);
        // 初始化 UploadManager 对象并进行文件的上传
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if ($err !== null) {
            return false;
        } 
        $domain = self::domain;
        $list[] = 'http://'.$domain.'/'.$ret['key'];
        return $list;
    }


}