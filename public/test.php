<?php


$url=$_GET['url'];
$data=file_get_contents($url);
$ip = $_SERVER["REMOTE_ADDR"];
header('Content-Type:image/jpeg;');

echo $date;



