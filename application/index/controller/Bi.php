<?php
header("Content-Type:text/html;charset=UTF-8");
date_default_timezone_set("PRC");
$showapi_appid = '99189';  //替换此值,在官网的"我的应用"中找到相关值
$showapi_secret = '7420a80bc3784cec92f86e2113fa1171';  //替换此值,在官网的"我的应用"中找到相关值
$paramArr = array(
'showapi_appid'=> $showapi_appid,
	'coin'=> "btcusdt,ltcusdt"
//添加其他参数
);

//创建参数(包括签名的处理)
function createParam ($paramArr,$showapi_secret) {
    $paraStr = "";
    $signStr = "";
    ksort($paramArr);
    foreach ($paramArr as $key => $val) {
    if ($key != '' && $val != '') {
    $signStr .= $key.$val;
    $paraStr .= $key.'='.urlencode($val).'&';
    }
    }
    $signStr .= $showapi_secret;//排好序的参数加上secret,进行md5
    $sign = strtolower(md5($signStr));
    $paraStr .= 'showapi_sign='.$sign;//将md5后的值作为参数,便于服务器的效验
    echo "排好序的参数:".$signStr."\r\n";
    return $paraStr;
}

$param = createParam($paramArr,$showapi_secret);
$url = 'http://route.showapi.com/1614-2?'.$param;
echo "请求的url:".$url."\r\n";
$result = file_get_contents($url);
echo "返回的json数据:\r\n";
print $result.'\r\n';
$result = json_decode($result);
echo "\r\n取出showapi_res_code的值:\r\n";
print_r($result->showapi_res_code);
echo "\r\n";
?>