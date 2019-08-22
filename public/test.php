<?php

$url2=$_GET['url'];
$store=$_GET['store'];
//  //这就是1张图 Content-Type: image/jpeg
 $data=file_get_contents($url2);
 header('Content-Type: image/jpeg;');
 //将文件保存在uploads目录
//  $new_file = '/data/wwwroot/www.zhihuichacang.com/teahouse/public' . DS . 'uploads'.DS.'TY'.$store.'.txt';
//  if (!file_exists($new_file)) {
//      //检查是否有该文件夹，如果没有就创建，并给予最高权限
//      mkdir($new_file, 777);
//  }
//  file_put_contents($new_file, $data);

 
 echo $data;
//  echo $data;




