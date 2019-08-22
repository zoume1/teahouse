<?php

//获取传递的参数
$pp=$_GET;
echo $pp;
 $url2='https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1566456234027&di=aea1f4e7b7a82bb83abac8fcc66a45e4&imgtype=0&src=http%3A%2F%2Fb-ssl.duitang.com%2Fuploads%2Fblog%2F201402%2F08%2F20140208125848_mL3Jw.jpeg';
//  $url2="https://api.weixin.qq.com/wxa/get_qrcode?access_token=".$timeout['authorizer_access_token'];
 
 //这就是1张图 Content-Type: image/jpeg
 $data=file_get_contents($url2);
 // echo '<img src="data:'.$data.'">';
 // header('Cache-Control: public');
 // header('Last-Modified: '.$_SERVER['REQUEST_TIME']);
 header('Content-Type: image/jpeg;');
 echo $data;




