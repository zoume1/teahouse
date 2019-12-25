<?php


$url=$_GET['url'];
$data=file_get_contents($url);
$ip = $_SERVER["REMOTE_ADDR"];
echo $ip;
// header('Content-Type:image/jpeg;');

// echo $date;



