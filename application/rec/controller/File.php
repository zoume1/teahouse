<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/19
 * Time: 10:57
 */
namespace app\rec\controller;
vendor('qiniu.autoload');
use Qiniu\Auth as Auth;
use Qiniu\Storage\UploadManager;
use think\Controller;

class File extends Controller{

    private $accesskey = 'Rf_gkgGeg_lYnq30jPAa725UQax5JYYqt_D-BbMZ';
    private $secrectkey = 'P7MWrpaKYM65h1qCIM0GW-uFkkNgbhkGvM5oKqeB';
    public $bucket = 'goods';
    public $domain='http://teahouse.siring.cn';

    public function upload()
    {
        if(request()->isPost()){
            $file = request()->file('image');
            // 要上传图片的本地路径
            $filePath = $file->getRealPath();
            $ext = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);  //后缀
            // 上传到七牛后保存的文件名
            $key =substr(md5($file->getRealPath()) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
            // 需要填写你的 Access Key 和 Secret Key
            $accessKey1 = $this->accesskey;
            $secretKey1 = $this->secrectkey;
            // 构建鉴权对象
            $auth = new Auth($accessKey1, $secretKey1);
            // 要上传的空间
            $bucket = $this->bucket;
            //解析域名地址
            $domain1 =$this->domain;
            $token = $auth->uploadToken($bucket);
            // 初始化 UploadManager 对象并进行文件的上传
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
            if ($err !== null) {
                return ["code"=>0,"msg"=>$err,"data"=>""];
            } else {
                //返回图片的完整URL
                return json(["code"=>1,"msg"=>"上传完成","data"=>($domain1 . '/'.$ret['key'])]);
            }
        }
    }

}