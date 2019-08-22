<?php

$url2=$_GET['url'];
//  $url2='https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1566456234027&di=aea1f4e7b7a82bb83abac8fcc66a45e4&imgtype=0&src=http%3A%2F%2Fb-ssl.duitang.com%2Fuploads%2Fblog%2F201402%2F08%2F20140208125848_mL3Jw.jpeg';
//  $url2="https://api.weixin.qq.com/wxa/get_qrcode?access_token=24_i9sxgFjI0DzSNsHJUKNUocfLmHV4L2nmZdQNW81dOyWIQ1y5r7faUpHKRStJnnJM_HO9vd1aRzBiIAIQ3aHL8vg4boW8VTwry4q_kCy68yUJTsH9CwuOCR-18KkXU8PKeJ8_g83UDbFvyrNrKOShAFDYCG";
 
//  //这就是1张图 Content-Type: image/jpeg
 $data=file_get_contents($url2);
//  // echo '<img src="data:'.$data.'">';
//  // header('Cache-Control: public');
//  // header('Last-Modified: '.$_SERVER['REQUEST_TIME']);
 header('Content-Type: image/jpeg;');
 echo $data;




