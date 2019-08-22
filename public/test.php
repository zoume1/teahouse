<?php

$url2=$_GET['url'];
$store=$_GET['store'];
//  //这就是1张图 Content-Type: image/jpeg
 $data=file_get_contents($url2);
 header('Content-Type: image/jpeg;');
 
 echo $data;




