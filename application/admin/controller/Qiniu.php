<?php
namespace app\admin\controller;
use think\Controller;
vendor('qiniu.autoload');
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use think\Request;
// include('../vendor/qiniu/src/Qiniu/Auth.php');

/**
 * lilu
 */
class Qiniu extends Controller
{
    public $accesskey = 'Rf_gkgGeg_lYnq30jPAa725UQax5JYYqt_D-BbMZ';
    public $secrectkey = 'P7MWrpaKYM65h1qCIM0GW-uFkkNgbhkGvM5oKqeB';
    public $bucket = 'goods';
    public $domain='teahouse.siring.cn';
    // /**
    //  * 上传图片到七牛云
    //  */
    public static function uploadimg($accessKey,$secrectkey,$bucket,$domain,$images)
    {
        $file = request()->file($images);
        if (!empty($file) && is_array($file)) {              
            foreach ($file as $k=>$v) {
                $info = $v->move(ROOT_PATH . 'public' . DS . 'uploads');    //本地保存
                $filePath = $info->getPathName();
                // 要上传图片的本地路径
                $ext = pathinfo($info->getInfo('name'), PATHINFO_EXTENSION);  //后缀
                // 上传到七牛后保存的文件名
                $key =substr(md5($info->getRealPath()) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
                // 需要填写你的 Access Key 和 Secret Key
                // 构建鉴权对象
                $auth = new Auth($accessKey,$secrectkey);
                // 要上传的空间
                $token = $auth->uploadToken($bucket);
                // 初始化 UploadManager 对象并进行文件的上传
                $uploadMgr = new UploadManager();
                // 调用 UploadManager 的 putFile 方法进行文件的上传
                list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
                if ($err !== null) {
                     return false;
                } 
                $list[] = 'http://'.$domain.'/'.$ret['key'];
            }    
            return $list;    
        }elseif(!empty($file)){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');    //本地保存
            $filePath = $info->getPathName();
            // 要上传图片的本地路径
            $ext = pathinfo($info->getInfo('name'), PATHINFO_EXTENSION);  //后缀
            // 上传到七牛后保存的文件名
            $key =substr(md5($info->getRealPath()) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
            // 需要填写你的 Access Key 和 Secret Key
            // 构建鉴权对象
            $auth = new Auth($accessKey,$secrectkey);
            // 要上传的空间
            $token = $auth->uploadToken($bucket);
            // 初始化 UploadManager 对象并进行文件的上传
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
            if ($err !== null) {
                return false;
            } 
            $list[] = 'http://'.$domain.'/'.$ret['key'];
            return $list;
        }
    }
    /**
     * lilu
     * 上传店铺凭证
     */
    public static function uploadimg2($accessKey,$secrectkey,$bucket,$domain,$filePath)
    {
          
            $ext_name=explode('.',$filePath);
            $ext = $ext_name[1];  //后缀
            // 上传到七牛后保存的文件名
            $key =substr(md5($filePath) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
            // 需要填写你的 Access Key 和 Secret Key
            // 构建鉴权对象
            $auth = new Auth($accessKey,$secrectkey);
            // 要上传的空间
            $token = $auth->uploadToken($bucket);
            // 初始化 UploadManager 对象并进行文件的上传
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
            if ($err !== null) {
                return false;
            } 
            $list[] = 'http://'.$domain.'/'.$ret['key'];
            return $list;
    }
}
