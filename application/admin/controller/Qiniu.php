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
                //获取当前控制器名称
                $controllerName = 'index';
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
                    echo ["err"=>1,"msg"=>$err,"data"=>""];
                } else {
                    //返回图片的完整URL
                    // return $ret[''];
                }
                $list[] = 'http://'.$domain.'/'.$ret['key'];

            }    
            return $list;    
        }elseif(!empty($file)){
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
            $auth = new Auth($accessKey,$secrectkey);
            // 要上传的空间
            $token = $auth->uploadToken($bucket);
            // 初始化 UploadManager 对象并进行文件的上传
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
            if ($err !== null) {
                echo ["err"=>1,"msg"=>$err,"data"=>""];
            } else {
                //返回图片的完整URL
                // return $ret[''];
            }
            $list[] = 'http://'.$domain.'/'.$ret['key'];
            return $list;
        }
    }
    //   public static function uploadimg($accessKey,$secrectkey,$bucket,$domain,$images)
    // {
    //     if(request()->isPost()){
    //         $file = request()->file($images);
    //         // 要上传图片的本地路径
    //         $info = $file[0]->move(ROOT_PATH . 'public' . DS . 'uploads');    //本地保存
    //         $filePath = $info->getInfo('tmp_name');
    //         $ext = pathinfo($file[0]->getInfo('name'), PATHINFO_EXTENSION);  //后缀
    //         // 上传到七牛后保存的文件名
    //         $key =substr(md5($file[0]->getRealPath()) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
    //         // require_once APP_PATH . '/../vendor/qiniu-php-sdk-7.2.2/autoload.php';
    //         // 需要填写你的 Access Key 和 Secret Key
    //         // $accessKey = config('ACCESSKEY');
    //         // $secretKey = config('SECRETKEY');
    //         // 构建鉴权对象
    //         $auth = new Auth($accessKey, $secrectkey);
    //         // 要上传的空间
    //         // $bucket = config('BUCKET');
    //         // $domain = config('DOMAINImage');
    //         $token = $auth->uploadToken($bucket);
    //         // 初始化 UploadManager 对象并进行文件的上传
    //         $uploadMgr = new UploadManager();
    //         // 调用 UploadManager 的 putFile 方法进行文件的上传
    //         list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
    //         if ($err !== null) {
    //             return ["code"=>0,"msg"=>$err,"data"=>""];
    //         } else {
    //             //返回图片的完整URL
    //             return json(["code"=>1,"msg"=>"上传完成","data"=>($domain . $ret['key'])]);
    //         }
    //     }
    // }



}
