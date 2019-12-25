<?php


$url=$_GET['url'];
$data=file_get_contents($url);
header('content-type:image/jpeg;');
echo $date;



