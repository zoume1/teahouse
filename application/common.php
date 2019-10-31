<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * Created by PhpStorm.
 * User: CHEN
 * Date: 2018/5/26
 * Time: 10:53
 */
use think\paginator\driver\Bootstrap;
use think\Db;
use think\Session;
use think\Request;
use app\city\model\StoreCommission;
use app\city\model\CitySetting;
use app\city\model\CityCopartner;
use app\city\model\CityRank;




/**
 * 状态码
 * 100-200
 */
define("ERROR_100",100);//登录失败, 账号或密码错误
define("ERROR_101",101);//合伙人申请还在审核中
define("ERROR_103",103);//合伙人申请被拒绝
define("ERROR_104",104);//合伙人未购买套餐
define("ERROR_105",105);//合伙人购买套餐正在审核中
define("ERROR_106",106);//购买的城市套餐汇款未到账
define("ERROR_107",107);//账号已冻结




//手机验证码
function phone($account= "",$password = '', $phone = "" ,$content = ""){
    $url = "http://117.48.217.182:8000/interface/smssend.aspx";    //117.48.217.182   120.26.38.54
    $post_data = array ("account" => $account,"password" => $password,"mobile"=>$phone,"content"=>$content);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}


function objectToArray($object) {
    //先编码成json字符串，再解码成数组
    return json_decode(json_encode($object), true);
}

function arrayToObject($arr){
    if(is_array($arr)){
        return (object) array_map(__FUNCTION__, $arr);
    }else{
        return $arr;
    }
}

//生成树形图
function genTree($items,$id='id',$pid='pid',$son = 'children'){
    $tree = array(); //格式化的树
    $tmpMap = array();  //临时扁平数据

    foreach ($items as $item) {
        $tmpMap[$item[$id]] = $item;
    }

    foreach ($items as $item) {
        if (isset($tmpMap[$item[$pid]])) {
            $tmpMap[$item[$pid]][$son][] = &$tmpMap[$item[$id]];
        } else {
            $tree[] = &$tmpMap[$item[$id]];
        }
    }
    unset($tmpMap);
    return $tree;
}

/**
 * 分类排序（降序）
 */
function _tree_sort($arr,$cols){
    //子分类排序
    foreach ($arr as $k => &$v) {
        if(!empty($v['sub'])){
            $v['sub']=_tree_sort($v['sub'],$cols);
        }
        $sort[$k]=$v[$cols];
    }
    if(isset($sort))
        array_multisort($sort,SORT_ASC,$arr);
    return $arr;
}
/**
 * 横向分类树
 */
function _tree_hTree($arr,$pid=0){
    foreach($arr as $k => $v){
        if($v['pid']==$pid){
            $data[$v['id']]=$v;
            $data[$v['id']]['sub']=_tree_hTree($arr,$v['id']);
        }
    }
    return isset($data)?$data:array();
}
/**
 * 纵向分类树
 */
function _tree_vTree($arr,$pid=0){
    foreach($arr as $k => $v){
        if($v['pid']==$pid){
            $data[$v['id']]=$v;
            $data+=_tree_vTree($arr,$v['id']);
        }
    }
    return isset($data)?$data:array();
}

function ajax_error($msg = '服务器错误，可刷新页面重试',$data=array()){
    $return = array('status'=>'0');
    $return['info'] = $msg;
    $return['data'] = $data;

    exit(json_encode($return,JSON_UNESCAPED_UNICODE));
}

function ajax_success($msg = '提交成功',$data=array()){
    $return = array('status'=>'1');
    $return['info'] = $msg;
    $return['data'] = $data;
    exit(json_encode($return,JSON_UNESCAPED_UNICODE));
}

/**
 * 打印调试函数
 * @param mixed $var 打印的东西
 */
function p($var = null,$debugger = 0){
    $str = '<pre style="border:1px solid #ccc; padding:10px; font-size:16px; line-height:28px; border-radius:5px; background:#eaebe6;">%str%</pre>';
    $replace = print_r($var, true);
    if(is_null($var)){
        $replace = '__NULL__';
    }elseif(is_bool($var)){
        $var = $var === true ? 'true' : 'false';
        $replace = '(bool)'.$var;
    }elseif(is_string($var) && trim($var) === ''){
        $replace = '空';
    }
    $str = str_replace('%str%', $replace, $str);
    echo $str;
    if($debugger) exit;
}


/**
 * 将中文转换成首字母大写
 * 获取首字母
 */
function getfirstchar($s0){
    $fchar = ord($s0{0});
    if($fchar >= ord("A") and $fchar <= ord("z") )return strtoupper($s0{0});
    $s1 = @iconv("UTF-8","gbk", $s0);
    $s2 = iconv("gbk","UTF-8", $s1);
    if($s2 == $s0){$s = $s1;}else{$s = $s0;}
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if($asc >= -20319 and $asc <= -20284) return "A";
    if($asc >= -20283 and $asc <= -19776) return "B";
    if($asc >= -19775 and $asc <= -19219) return "C";
    if($asc >= -19218 and $asc <= -18711) return "D";
    if($asc >= -18710 and $asc <= -18527) return "E";
    if($asc >= -18526 and $asc <= -18240) return "F";
    if($asc >= -18239 and $asc <= -17923) return "G";
    if($asc >= -17922 and $asc <= -17418) return "H";
    if($asc >= -17417 and $asc <= -16475) return "J";
    if($asc >= -16474 and $asc <= -16213) return "K";
    if($asc >= -16212 and $asc <= -15641) return "L";
    if($asc >= -15640 and $asc <= -15166) return "M";
    if($asc >= -15165 and $asc <= -14923) return "N";
    if($asc >= -14922 and $asc <= -14915) return "O";
    if($asc >= -14914 and $asc <= -14631) return "P";
    if($asc >= -14630 and $asc <= -14150) return "Q";
    if($asc >= -14149 and $asc <= -14091) return "R";
    if($asc >= -14090 and $asc <= -13319) return "S";
    if($asc >= -13318 and $asc <= -12839) return "T";
    if($asc >= -12838 and $asc <= -12557) return "W";
    if($asc >= -12556 and $asc <= -11848) return "X";
    if($asc >= -11847 and $asc <= -11056) return "Y";
    if($asc >= -11055 and $asc <= -10247) return "Z";
    return null;
}

/**
 * 将中文转换成首字母大写
 * 中文字符转英文字符
 */
function make_semiangle($str){
    $arr = array('0' => '0', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', 'A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'F' => 'F', 'G' => 'G', 'H' => 'H', 'I' => 'I', 'J' => 'J', 'K' => 'K', 'L' => 'L', 'M' => 'M', 'N' => 'N', 'O' => 'O', 'P' => 'P', 'Q' => 'Q', 'R' => 'R', 'S' => 'S', 'T' => 'T', 'U' => 'U', 'V' => 'V', 'W' => 'W', 'X' => 'X', 'Y' => 'Y', 'Z' => 'Z', 'a' => 'a', 'b' => 'b', 'c' => 'c', 'd' => 'd', 'e' => 'e', 'f' => 'f', 'g' => 'g', 'h' => 'h', 'i' => 'i', 'j' => 'j', 'k' => 'k', 'l' => 'l', 'm' => 'm', 'n' => 'n', 'o' => 'o', 'p' => 'p', 'q' => 'q', 'r' => 'r', 's' => 's', 't' => 't', 'u' => 'u', 'v' => 'v', 'w' => 'w', 'x' => 'x', 'y' => 'y', 'z' => 'z', '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[', '】' => ']', '〖' => '[', '〗' => ']', '“' => '"', '”' => '"', '‘' => '\'', '’' => '\'', '｛' => '{', '｝' => '}', '《' => '<', '》' => '>', '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-', '：' => ':', '。' => '.', '、' => ',', '，' => ',', '；' => ';', '？' => '?', '！' => '!', '…' => '...', '‖' => '|', '｜' => '|', '〃' => '"', '　' => ' ');
    return strtr($str, $arr);
}

/**
 * 将中文转换成首字母大写
 * 输入中文转换首字母英文
 */
function zh2pinyin($zh){
    $zh = make_semiangle($zh);
    $ret = "";
    $s1 = iconv("utf-8","gbk", $zh);
    $s2 = iconv("gbk","utf-8", $s1);
    if($s2 == $zh){$zh = $s1;}
    for($i = 0; $i < strlen($zh); $i++){
        $s1 = substr($zh,$i,1);
        $p = ord($s1);
        if($p > 160){
            $s2 = substr($zh,$i++,2);
            $ret .= getfirstchar($s2);
        }else{
            $ret .= $s1;
        }
    }
    return $ret;
}

// base64 上传图片
function base64_upload($type,$field,$callback = ''){
    $sBase64 = request($field);
    $file_name_pre = '/storage/'.$type.'/'.date('Y-m-d').'/';
    $return = array();
    if(empty($sBase64)){
        @$callback([]);
        return false;
    }
    if(!is_array($sBase64)){
        $sBase64 = array($sBase64);
    }
    foreach($sBase64 as $base64){
        if(strpos($base64 , "base64,")){
            $base64 = explode('base64,' , $base64)[1];
        }
        $base64 = base64_decode($base64);
        if(empty($base64)) continue;

        $file_name = $file_name_pre.uniqid(date('Ymd').rand('1000','9999')).'.png';
        $save_path = '.'.$file_name;
        if(!is_dir('.'.$file_name_pre)){
            mkdir('.'.$file_name_pre,0777,true);
        }
        file_put_contents($save_path,$base64);
        $return[] = $file_name;
    }
    $callback($return);
    return true;
}

/**
 * @param $files
 * @return mixed
 * 上传到文件服务器
 */
function curl_upfile($files){
    $cwd = rtrim(getcwd(), '/') . '/';

    $ch = curl_init();

    $post = array();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,array('User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15','Content-Type: multipart/form-data'));
    curl_setopt($ch, CURLOPT_URL,env('FILE_SERVER_URL'));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
// same as <input type="file" name="file_box">
    $post = array(
        'AppId'		=> env('FILE_SERVER_APPID'),
        'SafeCode'	=> env('FILE_SERVER_SAFECODE'),
        'Thumnail'	=> '0',
//		'file_box'	=> "@".getcwd().$files,
        'image[]'	=> curl_file_create($cwd . $files),
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $response = curl_exec($ch);
    if(curl_errno($ch)){	//出错则显示错误信息
        throw new Exception('上传图片文件过大');
    }
    curl_close($ch); //关闭curl链接
    unlink($cwd . $files);
    preg_match('/<fullpath.*fullpath>/i',$response,$match);
    return strip_tags($match[0]);
}

/**
 * @param $add_status array
 * @return bool
 * 检查事务是否都是插入成功的。
 */
function checkTrans($add_status)
{
    foreach ($add_status as $v) {
        if (!$v && $v !== 0) {
            return false;
        }
        return true;
    }
}



/**
 * 查询快递信息
 * @param $com 物流公司信息，拼音
 * @param $no 快递单号
 *  常见快递公司编码：
公司名称 	公司公司编码
邮政包裹/平邮 	youzhengguonei
国际包裹 	youzhengguoji
EMS 	ems
EMS-国际件 	emsguoji
EMS-国际件 	emsinten
北京EMS 	bjemstckj
顺丰 	shunfeng
申通 	shentong
圆通 	yuantong
中通 	zhongtong
汇通 	huitongkuaidi
韵达 	yunda
宅急送 	zhaijisong
天天 	tiantian
德邦 	debangwuliu
国通 	guotongkuaidi
增益 	zengyisudi
速尔 	suer
中铁物流 	ztky
中铁快运 	zhongtiewuliu
能达 	ganzhongnengda
优速 	youshuwuliu
全峰 	quanfengkuaidi
京东 	jd
 */
function kuaidi($com , $no){
    $host = "http://express.woyueche.com";
    $path = "/query.action";
    $appcode = "ece8cce0e2e84443b684286e65965c89"; // porter的阿里云
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    //根据API的要求，定义相对应的Content-Type
    array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded;charset=UTF-8");

    $url = $host . $path;
    $result = curl_post($url , ['express'=>$com , 'trackingNo'=>$no] , $headers);
    $result =explode("\n" , $result);
    return json_decode(array_pop($result), true);
}


// curl 模拟 post 请求
function curl_post($url,$post_data = array() , $header = false){
    $ch = curl_init(); //初始化curl
    curl_setopt($ch, CURLOPT_URL, $url);//设置链接
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
    curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
    if($header){
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));//POST数据
    $response = curl_exec($ch);//接收返回信息
    if(curl_errno($ch)){	//出错则显示错误信息
        print curl_error($ch);
    }
    curl_close($ch); //关闭curl链接
    return $response;
}

/**
 * 将数据导出EXCEL
 * @param  [array 一维数组] $title   [标题]
 * @param  [array 二维数组] $content [导出内容]
 * @param  [string] $filename [文件名,默认为data.xls]
 */
function exportData($title , $content , $filename = 'data'){
//	$title = array('标题a' , '标题b' , '标题c');
//	$content = array(
//		array('aa' , 'bb' , 'cc'),
//		array('dd' , 'ee' , 'ff'),
//		array('gg' , 'hh' , 'ii'),
//	);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=' . $filename . '.xls');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo iconv('utf-8', 'gbk', implode("\t", $title)), "\n";
    foreach ($content as $value) {
        echo iconv('utf-8', 'gbk', implode("\t", $value)), "\n";
    }
    exit();
}

function q($tip){
    if(is_object($tip) || is_string($tip)){
        echo "<pre>";
        var_dump($tip);
    }else{
        echo"<pre>";
        print_r($tip);
    }
}

function ajaxSuccess($msg = '操作成功',$url = '',$data = []){
    $return = ['status'=>1,'url'=>$url,'data'=>$data,'info'=>$msg];
    return response()->json($return);
}

function getSelectList($table , $pid = 0 ,&$result = [] , $spac = -4){
    $spac += 4;
    $list = db($table)->where(["pid"=>$pid,"status"=>1])->field("pid,id,name")->select();     //传递条件数组
    $list = objectToArray($list);
    foreach($list as $value){
        $value["name"] = str_repeat("&nbsp;",$spac).$value["name"];
        $result[] = $value;
        getSelectList($table , $value["id"] , $result , $spac);
    }
    return $result;
}



function postSelectList($table , $pid = 0,&$result = [] , $spac = -4){
    $spac += 4;
    $list = db($table)->where(["pid"=>$pid,"status"=>1])->field("name")->select();     //传递条件数组
    $list = objectToArray($list);
    foreach($list as $value){
        $value["name"] = str_repeat("&nbsp;",$spac).$value["name"];
        $result[] = $value;
        postSelectList($table , $value["id"] , $result , $spac);
    }
    return $result;
}

function recursionArr($arr,$pid = 0) {
    $array = [];
    foreach ($arr as $value) {
        if ($value['pid'] == $pid) {
            $value['child'] = recursionArr($arr,$value['id']);
            $array[] = $value;
        }
    }
    return $array;
}

function getSelectListes($table , $pid = 0 ,&$result = [] , $spac = -4){
    $store_id = Session::get("store_id");
    $spac += 4;
    $list = db($table)->where(["pid"=>$pid,"status"=>1])->where("store_id",'EQ',$store_id)->field("pid,id,name")->select();     //传递条件数组
    $list = objectToArray($list);
    foreach($list as $value){
        $value["name"] = str_repeat("&nbsp;",$spac).$value["name"];
        $result[] = $value;
        getSelectListes($table , $value["id"] , $result , $spac);
    }
    return $result;
}

function recursionGoods($arr) {
    $array = [];
    foreach ($arr as $value) {
        if (isset($value['sub'])) {
            $value['sub'] = recursionGoods($arr);
            $array[] = $value;
        }
    }
    return $array;
}

function _tree_sorts($arr){
    //子分类排序
    foreach ($arr as $key=>$value){
        $arr[$key] = _tree_sorts($value);
    }
    return $arr;
}


function show_category($arr){
    if(!empty($arr)){
        foreach ($arr as $value){
            echo '<ul id="rootUL">';
                echo '<li data-name="ZKCH" class="parent_li" data-value='."{$value["id"]}".'><span title="关闭"><i class="icon-th icon-minus-sign">';
                echo '</i>';
                echo $value["name"];
                echo '</span>' ;
                    if($value["sub"]) {
                        show_category($value["sub"]);
                    }
                echo "</li>";
            echo '</ul>' ;
        }
    }
}

function recursionArre($arr,$pid = 0,$level=0) {
    $array = [];
    foreach ($arr as $value) {
        if ($value['inviter_id'] == $pid) {
            $value['level'] = $level;
            $value['child'] = recursionArre($arr,$value['member_id'],$level+1);
            $value['member_grade_id'] = count($value['child']);
            $array[] = $value;           
        }
    }
    //$array['shuliang'] = count($array['child']);
    return $array;
}



       



/**
 * 发送HTTP请求方法
 * @param  string $url    请求URL
 * @param  array  $params 请求参数
 * @param  string $method 请求方法GET/POST
 * @return array  $data   响应数据
 */
function httpCurl($url, $params, $method = 'POST', $header = array(), $multi = false){
    date_default_timezone_set('PRC');
    $opts = array(
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => $header,
        CURLOPT_COOKIESESSION  => true,
        CURLOPT_FOLLOWLOCATION => 0,
        CURLOPT_COOKIE         =>session_name().'='.session_id(),
    );
    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method)){
        case 'GET':
            // $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            // 链接后拼接参数  &  非？
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        case 'POST':
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
            throw new Exception('不支持的请求方式！');
    }
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error) throw new Exception('请求发生错误：' . $error);
    return  $data;
}


/**
 * 微信信息解密
 * @param  string  $appid  小程序id
 * @param  string  $sessionKey 小程序密钥
 * @param  string  $encryptedData 在小程序中获取的encryptedData
 * @param  string  $iv 在小程序中获取的iv
 * @return array 解密后的数组
 */
function decryptData( $appid , $sessionKey, $encryptedData, $iv ){
    $OK = 0;
    $IllegalAesKey = -41001;
    $IllegalIv = -41002;
    $IllegalBuffer = -41003;
    $DecodeBase64Error = -41004;

    if (strlen($sessionKey) != 24) {
        return $IllegalAesKey;
    }
    $aesKey=base64_decode($sessionKey);

    if (strlen($iv) != 24) {
        return $IllegalIv;
    }
    $aesIV=base64_decode($iv);

    $aesCipher=base64_decode($encryptedData);

    $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
    $dataObj=json_decode( $result );
    if( $dataObj  == NULL )
    {
        return $IllegalBuffer;
    }
    if( $dataObj->watermark->appid != $appid )
    {
        return $DecodeBase64Error;
    }
    $data = json_decode($result,true);

    return $data;
}


/**
 * 请求过程中因为编码原因+号变成了空格
 * 需要用下面的方法转换回来
 */
function define_str_replace($data)
{
    return str_replace(' ', '+', $data);
}


/**
 * [茶圈活动管理标签显示]
 * 郭杨
 */
function show_lable_repay($label){
    switch ($label){
        case '0':
            return "未发布";
            break;
        case '1':
            return "已发布";
            break;
    }
}

/**
 **************李火生*******************
 * @param Request $request
 * Notes:后台初始订单类型
 **************************************
 * @param $label
 * @return string
 */
function order_type_status($label){
    switch ($label){
        case '1':
            return "选择直邮";
            break;
        case '2':
            return "到店自提";
            break;
        case '3':
            return "选择存茶";
            break;
    }
}

/**
 **************李火生*******************
 * @param Request $request
 * Notes:后台初始订单类型
 **************************************
 * @param $label
 * @return string
 */
function pay_types($label){
    switch ($label){
        case '1':
            return "微信支付";
            break;
        case '2':
            return "支付宝支付";
            break;
        case '3':
            return "余额支付";
            break;
    }
}


/**
 **************李火生*******************
 * @param Request $request
 * Notes:初始订单后台显示订单状态
 **************************************
 * @param $status
 */
//function ($status){
//    if($status==0){
//        echo '<button type="button" class="state   close-btu" >已关闭</button>';
//    }else if($status==1){
//        echo '<button type="button" class="state  obligation" >待支付</button>';
////    }else if($status==2){
////        echo '<button type="button" class="state  payment-has-been" >已付款</button>';
//    }else  if($status==2 || $status==3){
//        echo '<button type="button" class="state  shipmenting-btu" >待发货</button>';
////    }else  if($status==4){
////        echo '<button type="button" class="state  shipmented-btu" >已发货</button>';
//    }else  if($status==4 || $status==5){
//        echo '<button type="button" class="state  gooding-btu" >待收货</button>';
//    }else  if($status==6){
//        echo '<button type="button" class="state  gooded-btu" >已收货</button>';
//    }else  if($status==7){
//        echo '<button type="button" class="state  obligation" >待评价</button>';
//    } else  if($status==8){
//        echo '<button type="button" class="state  finish-btu" >已完成</button>';
////    }else  if($status==9){
////        echo '<button type="button" class="state  cancel-btu" >取消订单</button>';
//    }else  if($status==9 || $status==10){
//        echo '<button type="button" class="state  cancel-btu" >已关闭</button>';
//    }else  if($status==11){
//        echo '<button type="button" class="state  cancel-btu" >退货</button>';
//    }
//}
/**
 **************李火生*******************
 * @param Request $request
 * Notes:初始订单后台显示订单状态
 **************************************
 * @param $status
 */
function show_order_status($status){
    if($status==0){
        echo '<button type="button" class=" layui-btn layui-btn-mini color9" >已关闭</button>';
    }else if($status==1){
        echo '<button type="button" class=" layui-btn layui-btn-mini color1" >待付款</button>';
//    }else if($status==2){
//        echo '<button type="button" class="state  payment-has-been" >已付款</button>';
    }else  if($status==2 || $status==3){
        echo '<button type="button" class=" layui-btn layui-btn-mini color2" >待发货</button>';
//    }else  if($status==4){
//        echo '<button type="button" class="state  shipmented-btu" >已发货</button>';
    }else  if($status==4 ||$status==5 ||$status==6 ||$status==7 ){
        echo '<button type="button" class=" layui-btn layui-btn-mini color3" >已发货</button>';
    // }else  if($status==5){
    //     echo '<button type="button" class="state color3" >待收货</button>';
    // }else  if($status==7){
    //     echo '<button type="button" class="state color3" >待评价</button>';
    // }
    }else  if($status==8){
        echo '<button type="button" class=" layui-btn layui-btn-mini color4" >已完成</button>';
//    }else  if($status==9){
//        echo '<button type="button" class="state  cancel-btu" >取消订单</button>';
    }
}

/**
 * lilu
 * 店铺增值订单
 */
function show_order_statues($status){
    if($status==0){
        echo '<button type="button" class=" layui-btn layui-btn-mini   close-btu static_btus" >已关闭</button>';
    }else if($status==1){
        echo '<button type="button" class=" layui-btn layui-btn-mini  payment-has-been static_btus" >已付款</button>';
    }else  if($status==2){
        echo '<button type="button" class=" layui-btn layui-btn-mini  shipmenting-btu  btn-dfh" >待发货</button>';
    }else  if($status==3){
        echo '<button type="button" class=" layui-btn layui-btn-mini  shipmented-btu btn-yfh static_btus" >已发货</button>';
    }else  if($status==4){
        echo '<button type="button" class=" layui-btn layui-btn-mini  gooding-btu  btn-ywc static_btus" >待收货</button>';
    }else  if($status==5){
        echo '<button type="button" class=" layui-btn layui-btn-mini gooded-btu static_btus" >已收货</button>';
    }else  if($status==7){
        echo '<button type="button" class=" layui-btn layui-btn-mini obligation static_btus btn-yqx" >待评价</button>';
    } else  if($status==8){
        echo '<button type="button" class=" layui-btn layui-btn-mini finish-btu btn-ywc static_btus" >已完成</button>';
    }else  if($status==9){
        echo '<button type="button" class=" layui-btn layui-btn-mini cancel-btu static_btus" >取消订单</button>';
    }else  if($status==10){
        echo '<button type="button" class=" layui-btn layui-btn-mini cancel-btu static_btus" >取消订单</button>';
    }else  if($status==11){
        echo '<button type="button" class=" layui-btn layui-btn-mini cancel-btu static_btus" >退货</button>';
    }else  if($status==12){
        echo '<button type="button" class=" layui-btn layui-btn-mini cancel-btu static_btus btn-ytk" >待服务</button>';
    }
}
/**
 * lilu
 * 店铺积分订单
 */
function integral_order_statues($status){
    if($status==0){
        echo '<button type="button" class=" layui-btn layui-btn-mini static_btus" >已关闭</button>';
    }else if($status==1){
        echo '<button type="button" class=" layui-btn layui-btn-mini static_btus color2" >待发货</button>';
    }else  if($status==3){
        echo '<button type="button" class=" layui-btn layui-btn-mini static_btus color3" >已发货</button>';
    }else  if($status==5){
        echo '<button type="button" class=" layui-btn layui-btn-mini static_btus color4" >已完成</button>';
    }else  if($status==9){
        echo '<button type="button" class=" layui-btn layui-btn-mini static_btus" >取消订单</button>';
    }
}
/**
 * lilu
 * 总控增值订单
 */
function show_order_statues2($status){
    if($status==0){
        echo '<button type="button" class=" layui-btn layui-btn-mini   close-btu static_btus" >已关闭</button>';
    }else if($status==1){
        echo '<button type="button" class=" layui-btn layui-btn-mini  payment-has-been static_btus" >已付款</button>';
    }else  if($status==2){
        echo '<button type="button" class=" layui-btn layui-btn-mini  shipmenting-btu static_btus  btn-dfh" >待发货</button>';
    }else  if($status==3){
        echo '<button type="button" class=" layui-btn layui-btn-mini  shipmented-btu btn-yfh static_btus" >已发货</button>';
    }else  if($status==4){
        echo '<button type="button" class=" layui-btn layui-btn-mini  gooding-btu  btn-ywc static_btus" >待收货</button>';
    }else  if($status==5){
        echo '<button type="button" class=" layui-btn layui-btn-mini gooded-btu static_btus" >已收货</button>';
    }else  if($status==7){
        echo '<button type="button" class=" layui-btn layui-btn-mini obligation static_btus btn-yqx" >待评价</button>';
    } else  if($status==8){
        echo '<button type="button" class=" layui-btn layui-btn-mini finish-btu btn-ywc static_btus" >已完成</button>';
    }else  if($status==9){
        echo '<button type="button" class=" layui-btn layui-btn-mini cancel-btu static_btus" >取消订单</button>';
    }else  if($status==10){
        echo '<button type="button" class=" layui-btn layui-btn-mini cancel-btu static_btus" >取消订单</button>';
    }else  if($status==11){
        echo '<button type="button" class=" layui-btn layui-btn-mini cancel-btu static_btus" >退货</button>';
    }else  if($status==12){
        echo '<button type="button" class=" layui-btn layui-btn-mini cancel-btu static_btus btn-ytk" >待服务</button>';
    }
}



/*入驻套餐审核状态显示*/
function enter_status($status){
    if($status==-1){
        echo '<text style="color:red;">入驻不通过</text>';
    }else if($status==1){
        echo '<text style="color:#669900;">入驻已通过</text>';
    }else  if($status==2){
        echo '<text style="color:#199ED8;">入驻待审核</text>';
    }else  if($status==3){
        echo '<text style="color:#999;">入驻已关闭</text>';
    }
}
/**
 **************李火生*******************
 * @param Request $request
 * Notes:提现审核状态
 **************************************
 * @param $status
 */
function operation_recharge_status($status){
    if($status==1){
        echo '通过';
    }elseif ($status==-1){
        echo '<span class="layui-btn layui-btn-mini">不通过</span>';
    }elseif($status==2){
        echo '<span class="layui-btn layui-btn-mini">待审核</sapn>';
    }
}

/**
 **************李火生*******************
 * @param Request $request
 * Notes:售后审核状态
 **************************************
 * @param $status
 */
function after_sale_status($status,$who_handle){
   if($status==1){
        echo '<div type="button" class="layui-btn layui-btn-mini  all1 " >申请中</div>';
    }else  if($status==2){
        echo '<div  type="button" class="layui-btn layui-btn-mini  all2" >收货中</button>';
    }else  if($status==3){
        echo '<div  type="button" class="layui-btn layui-btn-mini  all1" >处理中</button>';
    }else  if($status==4){
        echo '<div  type="button" class="layui-btn layui-btn-mini  all3" >换货成功</button>';
    }else  if($status==5 && $who_handle ==1){
        echo '<div  type="button" class="layui-btn layui-btn-mini  all5" >用户撤销</button>';
    }else  if($status==5 && $who_handle ==2){
       echo '<div  type="button" class="layui-btn layui-btn-mini  all5" >用户撤销</button>';
   }else  if($status==5 && $who_handle ==3){
       echo '<div  type="button" class="layui-btn layui-btn-mini  all5" >拒绝</button>';
   }else  if($status==7){
       echo '<div  type="button" class="layui-btn layui-btn-mini  all5" >已退货</button>';
    }else{
        echo '<div  type="button" class="layui-btn layui-btn-mini  all5" >完成退款</button>';

   }
}


/**
 **************郭杨*******************
 *            单位组合
 **************************************
 */
function unit_comment($num,$unit){
    $count = count($num);
    switch($count)
    {
        case 1:
            $new = $num[0].','.$unit[0];
            return $new;
            break;
        case 2:
            $new = $num[0].','.$unit[0].','.$num[1].','.$unit[1];
            return $new;
            break;
        case 3:
            $new = $num[0].','.$unit[0].','.$num[1].','.$unit[1].','.$num[2].','.$unit[2];
            return $new;
            break;
        case 4:
            $new = $num[0].','.$unit[0].','.$num[1].','.$unit[1].','.$num[2].','.$unit[2].','.$num[3].','.$unit[3];
            return $new;
            break;
        case 5:
            $new = $num[0].','.$unit[0].','.$num[1].','.$unit[1].','.$num[2].','.$unit[2].','.$num[3].','.$unit[3].','.$num[4].','.$unit[4];
            return $new;
            break;
        case 6:
            $new = $num[0].','.$unit[0].','.$num[1].','.$unit[1].','.$num[2].','.$unit[2].','.$num[3].','.$unit[3].','.$num[4].','.$unit[4].','.$num[5].','.$unit[5];
            return $new;
            break;
        default:
            $new = null;
            return $new;
    }
   
}

/**
 **************李火生*******************
 * @param Request $request
 * Notes:快递名称改变
 **************************************
 * @param $str
 * @return string
 */
function str_to_chinese($str){
    $arr = array(
        'ems'=>'EMS',
        'youzhengguonei'=>'邮政包裹/平邮',
        'bjemstckj'=>'北京EMS',
        'shunfeng'=> '顺丰',
        'shentong'=> '申通 ',
        'yuantong'=>'圆通',
        'zhongtong'=> '中通',
        'huitongkuaidi'=> '百世汇通',
        'baishiwuliu'=>'百世物流',
        'yunda'=>'韵达',
        'zhaijisong'=> '宅急送',
        'tiantian'=>'天天',
        'debangwuliu'=>'德邦',
        'guotongkuaidi'=>'国通',
        'zengyisudi'=>'增益',
        'suer'=>'速尔',
        'ztky'=>'中铁物流',
        'zhongtiewuliu'=>'中铁快运',
        'ganzhongnengda'=>'能达',
        'youshuwuliu'=>'优速',
        'quanfengkuaidi'=>'全峰',
    );
    return strtr($str, $arr);
}

/**
 **************李火生*******************
 * @param Request $request
 * Notes:
 **********************图片上传****************
 * @param $base64
 * @return bool|string
 */
function base64_upload_flie($base64) {
    $base64_image = str_replace(' ', '+', $base64);
    //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
        //匹配成功
        if($result[2] == 'jpeg'){
            $image_name = '.jpg';
            //纯粹是看jpeg不爽才替换的
        }else{
            $image_name = '.'.$result[2];
        }
        $dir =ROOT_PATH . 'public' . DS . 'uploads'."/store_pingzheng";
        $file_names ='store_pingzheng' . DS . md5(microtime(true)).$image_name;
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
            $image_file = ROOT_PATH . 'public' . DS . 'uploads'. "/" .$file_names;
        //服务器文件存储路径
        if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))){
            return ROOT_PATH . 'public' . DS . 'uploads'. DS .$file_names;
            // return $file_names;
        }else{
            return false;
        }
    }else{
        return false;
    }
}


/**
 **************郭杨*******************
 *  分页函数 
 */
function paging_data($data,$url,$pag_number){
    $all_idents = $data;//这里是需要分页的数据
    $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
    $listRow = $pag_number;//每页20行记录
    $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
    $data = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
        'var_page' => 'page',
        'path' => url($url),//这里根据需要修改url
        'query' => [],
        'fragment' => '',
    ]);
    return $data;
}

/**
 * 单位分类
 */
function unit_list($unit){
    foreach($unit as $kk => $yy)
    {
        $unit_liste[] = $yy['name'];
    }
    $list = array_values($unit_liste);
    return $list;
}

/*TODO:小程序开始*/
//远程图片链接处理

function remote($uniacid,$url,$type){

    $remote = DB::table("ims_sudu8_page_base")
        ->where("uniacid",$uniacid)
        ->field("remote")
        ->find()['remote'];

    if($remote == 1) {

        if($type==1){   //1是取   2是写

            if(strpos($url,'http') === false){

                $host_rul = ROOT_HOST;
                $temp_a = explode(":", $host_rul);

                if($temp_a[0] == 'http'){
                    $temp_a[0] = 'https';
                    $host_rul = implode(':', $temp_a);
                }

                $url = $host_rul.$url;

            }else{
                $temp_a = explode(":", $url);

                if($temp_a[0] == 'http'){
                    $temp_a[0] = 'https';
                    $url = implode(':', $temp_a);
                }
            }

        }else{
            if(strpos($url,'http') !== false){
                if(strpos($url,'/upimages') !== false){
                    $url = "/upimages".explode("/upimages",$url)[1];
                }else if(strpos($url,'diypage/resource') !== false){
                    $url = "/diypage/resource".explode("diypage/resource",$url)[1];
                }

            }
        }

    }else if ($remote == 2) {

        $qiniu = DB::table("ims_sudu8_page_remote")
            ->where("uniacid",$uniacid)
            ->where('type',2)
            ->find();
        if($type==1){

            if(strpos($url,'http') === false){
                if(strpos($url,'/diypage/img/blank.jpg') !== false){
                    $url = $url;
                }else if(strpos($url,'/diypage/resource/images/diypage/default/default_start.jpg') !== false){
                    $url = $url;
                }else if(strpos($url,'/diypage/resource/images/diypage/default/tcgg.jpg') !== false){
                    $url = $url;
                }else{
                    $url = $qiniu['domain'].$url;
                }
            }
        }else{
            if(strpos($url,$qiniu['domain']) !== false){
                $url = explode($qiniu['domain'],$url)[1];
            }

        }

    }else if ($remote == 3) {



    }

    return $url;

}


//定义上传图片的默认路径
function upload_img(){
    //1.设置上传路径
    $dir = ROOT_PATH."public/upimages/";
    return $dir;
}
/*TODO:小程序结束*/

/**
 * 求两个日期之间相差的天数
 * (针对1970年1月1日之后，求之前可以采用泰勒公式)
 * @param string $day1
 * @param string $day2
 * @return number
 */
function diffBetweenTwoDays ($second1, $second2)
{  
  if ($second1 < $second2) {
    $tmp = $second2;
    $second2 = $second1;
    $second1 = $tmp;
  }
  return ($second1 - $second2) / 86400;
}

/**
 **************李火生*******************
 * @param Request $request
 * Notes:总控套餐订单审核
 **************************************
 */
function audit_status($status){
    if($status==0){
        echo '<button type="button" class="state payment-has-been" >审核已通过</button>';
    }else  if($status==1){
        echo '<button type="button" class="state payment-has-been" >审核已通过</button>';
    }else  if($status==-1){
        echo '<button type="button" class="state payment-has-been" >审核已通过</button>';
    }
}

function audit_statues($status){
    if($status==1){
        echo '审核已通过';
    }else  if($status==0){
        echo '审核待通过';
    }else  if($status==-1){
        echo '审核未通过';
    }
}
function center_statues($status){
    if($status==1){
        echo '<button type="button" class="state payment-has-been" >审核已通过</button>';
    }else  if($status==0){
        echo '<button type="button" class="state shipmenting-btu">审核待通过</button>';
    }else  if($status==-1){
        echo '<button type="button" class="state close-btu">审核未通过</button>';
    }
}

/**
 **************李火生*******************
 * @param Request $request
 * Notes:判断是商家还是个人
 **************************************
 */
function is_business($status){
    if($status==1){
        echo '个人';
    }else  if($status==2){
        echo '公司';
    }
}

/**
 **************李火生*******************
 * @param Request $request
 * Notes:支付类型（1扫码支付，2汇款支付，3余额支付）
 **************************************
 */
function pay_type($status){
    if($status==1){
        echo '扫码支付';
    }else  if($status==2){
        echo '汇款支付';
    }else if($status==3){
        echo '余额支付';
    }else{
        echo '未支付';
    }
}

/**
 **************李火生*******************
 * @param Request $request
 * Notes:到账状态
 **************************************
 * @param $status
 */
function pay_status($status){
    if($status==1){
        echo '已到账';
    }else {
        echo '待审核';
    }
}

/**
**************李火生*******************
* @param Request $request
* Notes:到账状态
**************************************
* @param $status
*/
function city_pay_status($status){
   if($status==1){
       echo '已到账';
   }else if($status==2){
       echo '待审核';
   }else if($status==3){
    echo '未到账';
}
}  
/**
 * [商品列表组修改]
 * GY
 */
function MemberFristAdd($store_id)
{
    $store = config("store_id");
    //默认会员等级
    $memeber_grade_data = db("member_grade")->where("store_id",'EQ',$store)->select();
    foreach($memeber_grade_data as $key => $value){
        unset($memeber_grade_data[$key]['member_grade_id']);
        $memeber_grade_data[$key]['store_id'] = $store_id;
    }  
    foreach($memeber_grade_data as $k => $v){
        $bool = db("member_grade")->insert($v);
    }
    
    //默认商品
    $goods = db('goods')->where("store_id",'EQ',$store)->select();
    $special = db('special')->where("goods_id",'EQ',273)->select();
    foreach($goods as $ky => $val){
        unset($goods[$ky]['id']);
        $goods[$ky]['store_id'] = $store_id;
        
    }
    foreach($goods as $k => $v){
        $bool = db("goods")->insertGetId($v);
    }
    foreach($special as $y => $l){
        unset($special[$y]['id']);
        $special[$y]['goods_id'] = $bool;
        
    }
    foreach($special as $ka => $va){
        $boole = db("special")->insertGetId($va);
    }


    //活动分类
    $category = db("goods_type")->where("store_id","EQ",$store)->select();
    foreach($category as $kk => $val){
        unset($category[$kk]['id']);
        $category[$kk]['store_id'] = $store_id;
        
    }

    foreach($category as $kv => $ve){
        $boole = db("goods_type")->insert($ve);
    }

    $ppid = db("goods_type")->where("store_id",'EQ',$store_id)->where('pid',0)->value('id');
    $bb =  db("goods_type")->where("store_id",'EQ',$store_id)->where('pid','>',0)->update(['pid'=>$ppid]);
    


    return $boole;
}
/**
 * lilu
 * 检测用户权限
 */
function powerget()
{
    return true;
    $uid = Session::get('uid');
	$usergroup = Session::get('usergroup');
    $appletid = input("appletid");
	//允许条件:1.登录状态  2.管理员身份  3.小程序管理员身份
	if(!$appletid){
		return false;   //没有appletid 表示直接输入的网址，精确不到具体的小程序
	}
	if($usergroup==1){   //用户组为1的时候，为普通管理员，需判断该用户是不是该小程序的管理员
		$res = Db::table('applet')->where('id',$appletid)->find();
		if($res['adminid']==$uid){
			return  true;   
		}else{
			return false;
		}
	}
	if($usergroup==3){   //用户组为3的时候，为经销商，需判断该用户是不是该小程序的经销商管理员
		$res = Db::table('applet')->where('id',$appletid)->find();
		if($res['jxs']==$uid){
			return  true;   
		}else{
			return false;
		}
	}
	if($usergroup==2){
		return true;
	}
}
/**
 * lilu
 * 检测登录
 */
//检查是否登录
function check_login(){
	$uid = Session::get('user_id');
	// 检测更新
    $version = 'admin/controller/Version.php';
    $ver = include($version);
    $ver = $ver['ver'];
    $ver = substr($ver,-4);
    if(!defined('VERSION_APP')){
        define("VERSION_APP", $ver);
    }
	if(!$uid){
		return false;
	}else{
		return true;
	}
}


    /**
     * @param int $status
     * [存茶订单后台显示状态]
     * @return 成功时返回，其他抛异常
     */

function show_house_order($status){
    if($status==0){
        echo '<button type="button" class=" layui-btn layui-btn-mini close-btu" >已关闭</button>';
    }else if($status==1){
        echo '<button type="button" class=" layui-btn layui-btn-mini color1" >待付款</button>';
        //暂时显示到入仓状态
    }else  if($status==2){
        echo '<button type="button" class=" layui-btn layui-btn-mini states enter-btu" >已入仓</button>';
    }else  if($status==3){
        echo '<button type="button" class=" layui-btn layui-btn-mini shipmented-btu" >已发货</button>';
    }
}

function show_house_orderer($status){
    if($status==0){
        echo '<button type="button" class="layui-btn layui-btn-mini close-btu" >已关闭</button>';
    }else if($status==1){
        echo '<button type="button" class="layui-btn layui-btn-mini color1" >待付款</button>';
        //暂时显示到入仓状态
    }else  if($status==2){
        echo '<button type="button" class="layui-btn layui-btn-mini color2" >待发货</button>';
    }else  if($status==3){
        echo '<button type="button" class="layui-btn layui-btn-mini close-btu" >已入仓</button>';
    }
}


/**
 * google api 二维码生成【QRcode可以存储最多4296个字母数字类型的任意文本，具体可以查看二维码数据格式】
 * @param string $chl 二维码包含的信息，可以是数字、字符、二进制信息、汉字。不能混合数据类型，数据必须经过UTF-8 URL-encoded.如果需要传递的信息超过2K个字节，请使用POST方式
 * @param int $widhtHeight 生成二维码的尺寸设置
 * @param string $EC_level 可选纠错级别，QR码支持四个等级纠错，用来恢复丢失的、读错的、模糊的、数据。
 *                 L-默认：可以识别已损失的7%的数据
 *                 M-可以识别已损失15%的数据
 *                 Q-可以识别已损失25%的数据
 *                 H-可以识别已损失30%的数据
 * @param int $margin 生成的二维码离图片边框的距离
 */
 function erweima($chl,$x ='150',$level='L',$margin='0') {
    return '<img src="http://chart.apis.google.com/chart?chs='.$x.'x'.$x.'&cht=qr&chld='.$level.'|'.$margin.'&chl='.urlencode($chl).'" />';
}


/**
 * @param string $content  短信内容
 * @param string $mobile   手机号
 * @return 成功时返回，其他抛异常
 */

function sendMessage($content,$mobile)
{
    // $content = '【智慧茶仓】短信内容';//带签名的短息内容
    // $mobile = '18309224319';//手机号
    $url = "http://117.48.217.182:8860/sendSms";//请求URL
    $api_code = "240001";//对接协议中的API代码
    $api_secret = "4SFE6PW1GL";//对接协议中的API密码
    $sign = md5($content.$api_secret);//md加密后短信内容+API密码 获得签名
    $bodys = [
        'cust_code'=>$api_code,
        'content' => $content,
        'destMobiles' => $mobile,
        'sign' => $sign,
    ];
    $data_string = json_encode($bodys);
    if (!function_exists('curl_init'))
    {
        return '';
    }
    //设置url
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

    curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: text/html'));// 文本提交方式，必须声明请求头
    $data = curl_exec($ch);
    if($data === false){
        var_dump(curl_error($ch));
    }else{
        curl_close($ch);
    }
    return $data;
}

function show_ds_orderer($status){
    switch($status)
    {
        case '1':
            echo '未支付';
        break;
            case '2':
        echo '未开奖';
        break;
            case '3':
        echo '已中奖';
        break;
        case '4':
            echo '未中奖';
        break;
    }

}
    /**
     * gy
     * 生成密码hash值
     * @param $password
     * @return string
     */
    function changcang_hash($password)
    {
        return md5(md5($password) . 'zhihui_chang_cang');
    }
    //没有使用TP的正则验证，集中在一处方便以后修改
    //不推荐使用正则，因为复用性太差
    //手机号的验证规则
    function isMobile($value)
    {
        $rule = '^1(3|4|5|7|8|9)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * lilu
     * 生成消费记录日志
     */
    function create_captical_log($order_numbner,$member_account,$income,$pay,$cap_type,$store_id)
    {
        //获取参数
        $input['order_number']=$order_numbner;
        $input['member_account']=$member_account;
        $input['income']=$income;
        $input['pay']=$pay;
        $input['cap_type']=$cap_type;
        $input['store_id']=$store_id;
        $input['create_time']=time();
        if($input){
            $re=db('captical')->insert($input);
            return true;
        }else{
            return false;
        }

    }

function    returnJson($code,$msg,$data = null,$page = null){
    $json = array(
        'code' => $code,
        'msg' => $msg,
    );
    if($data)$json['data'] = $data;
    if($page)$json['page'] = $page;
    echo json_encode($json);exit;
}


function city_status($status){
    if($status==1){
        echo '<div class=" layui-btn-mini all4"  style="width:100px;color:#fff;">入驻审核已通过</div>';
    }else  if($status==2){
        echo '<div class=" layui-btn-mini shipmenting-btu" style="width:100px;color:#fff;">入驻待审核</div>';
    }else  if($status==3){
        echo '<div class=" layui-btn-mini close-btu" style="width:100px;color:#fff;">入驻审核未通过</div>';
    }
}

function city_pay($status){
    if($status==1){
        echo '微信';
    }else  if($status==2){
        echo '支付宝';
    }else  if($status==3){
        echo '汇款';
    }
}

function city_enter_status($status){
    if($status==1){
        echo '微信';
    }else  if($status==2){
        echo '支付宝';
    }else  if($status==3){
        echo '汇款';
    }
}

function city_remit($status){
    if($status==1){
        echo '<button type="button" class="layui-btn layui-btn-mini  all4"  style="width: 100px;">汇款已到账</button>';
    }else  if($status==2){
        echo '<button type="button" class="layui-btn layui-btn-mini  color7" style="width: 100px;">汇款待审核</button>';
    }else  if($status==3){
        echo '<button type="button" class="layui-btn layui-btn-mini  all3" style="width: 100px;">汇款未到账</button>';
    }
}

function returnArray($data){
    $data ? $data->toArray() : returnJson(0,'数据有误');
}


    /**
     * 返回封装后的 API 数据到客户端
     * @param int $code
     * @param string $msg
     * @param string $url
     * @param array $data
     * @return array
     */
   function renderJsonData($code , $msg = '', $data = [])
    {
        if(!empty($data))
        {
            return json(compact('code', 'msg', 'data'));
        } else {
            return json(compact('code', 'msg'));
        }
    }

    /**
     * 返回操作成功json
     * @param string $msg
     * @param string $url
     * @param array $data
     * @return array
     */
  function jsonSuccess($msg = 'success', $data = [],$code = 1)
    {
        return renderJsonData($code, $msg,  $data);
    }

    /**
     * 返回操作失败json
     * @param string $msg
     * @param string $url
     * @param array $data
     * @return array
     */
  function jsonError($msg = 'error', $data = [],$code = 0)
    {
        
        return renderJsonData($code, $msg, $data);
    }


    /**
     * 返回操作失败json
     * @param string $msg
     * @param string $url
     * @param array $data
     * @return array
     */
  function enter_name($enter_all_id)
  {
      switch($enter_all_id){
        case 1:
            $name = "万用版30天试用期套餐";
            return $name;
            break;
        case 2:
            $name = "万用版1年套餐";
            return $name;
            break;
        case 3:
            $name = "万用版2年套餐";
            return $name;
            break;
        case 4:
            $name = "万用版3年套餐";
            return $name;
            break;
        case 5:
            $name = "万用版4年套餐";
            return $name;
            break;        
        case 6:
            $name = "万用版5年套餐";
            return $name;
            break;        
        case 12:
            $name = "茶行业版30天试用套餐";
            return $name;
            break;
        case 13:
            $name = "茶行业版1年套餐";
            return $name;
            break; 
        case 14:
            $name = "茶行业版2年套餐";
            return $name;
            break; 
        case 15:
            $name = "茶行业版3年套餐";
            return $name;
            break; 
        case 16:
            $name = "茶行业版4年套餐";
            return $name;
            break; 
        case 17:
            $name = "茶行业版5年套餐";
            return $name;
            break; 
        case 18:
            $name = "茶进阶版30天试用套餐";
            return $name;
            break; 
        case 19:
            $name = "茶进阶版1年套餐";
            return $name;
            break; 
        case 20:
            $name = "茶进阶版2年套餐";
            return $name;
            break; 
        case 21:
            $name = "茶进阶版3年套餐";
            return $name;
            break; 
        case 22:
            $name = "茶进阶版4年套餐";
            return $name;
            break; 
        case 23:
            $name = "茶进阶版5年套餐";
            return $name;
            break; 
        default:
            break;       
         
      }
  }

  function find_rank_data($highe_share_code,$pay_money,$city_user_id)
  {
      $one = db('store')->where('share_code',$highe_share_code)->find(); //上级是商户
      $two = db('city_copartner')->where('my_invitation',$highe_share_code)->where("user_id",$city_user_id)->find();//上级是合伙人
      $return_data = [
        'commision' => 0,
        'higher_phone' => '',
        'base_commision' => 0,
        'reach_commision' => 0,
    ];
      if(!empty($one)){
          //商户一级分销
         $setting = StoreCommission::commission_setting();//找到商户分销设置比例
         $commission_money = ($setting['commission'] * $pay_money)/100;//分销金额
         //给上一级商户返钱
         $three = db('store')->where('share_code',$highe_share_code)->setInc('store_wallet',$commission_money);
         $return_data = [
            'commision' => $commission_money,
            'higher_phone' => $one['phone_number'],
            'base_commision' => 0,
            'reach_commision' => 0,
        ];
         //有无城市合伙人
         if($city_user_id > 0){
            $city_setting = CitySetting::city_setting();
            $base_commision =  ($city_setting['commission'] * $pay_money)/100;//保底佣金
            // $reach_commision = ($city_setting['reach_commission'] * $pay_money)/100;//达标佣金
            // $four = db('city_copartner')->where('user_id',$city_user_id)->setInc('reach_commission',$reach_commision);//给合伙人返达标拥金
            $five = db('city_copartner')->where('user_id',$city_user_id)->setInc('commission',$base_commision);//给合伙人防保底拥金
            $six = db('city_copartner')->where('user_id',$city_user_id)->setInc('member_wallet',$base_commision);//给合伙人防增加拥金
            $seven = db('city_copartner')->where('user_id',$city_user_id)->setInc('city_store_number',1);//给合伙人增加店铺数
            $return_data = [
                'commision' => $commission_money,
                'higher_phone' => $one['phone_number'],
                'base_commision' =>$base_commision,
                'reach_commision' =>0,
            ];
         }

         return $return_data;
      } elseif(!empty($two)){

        //有无城市合伙人
         if($city_user_id > 0){
            $city_setting = CitySetting::city_setting();
            $base_commision =  ($city_setting['commission'] * $pay_money)/100;//保底佣金
            $reach_commision = ($city_setting['reach_commission'] * $pay_money)/100;//达标佣金
            $four = db('city_copartner')->where('user_id',$city_user_id)->setInc('reach_commission',$reach_commision);//给合伙人返达标拥金
            $five = db('city_copartner')->where('user_id',$city_user_id)->setInc('commission',$base_commision);//给合伙人防保底拥金
            $six = db('city_copartner')->where('user_id',$city_user_id)->setInc('member_wallet',$base_commision);//给合伙人防增加拥金
            $seven = db('city_copartner')->where('user_id',$city_user_id)->setInc('city_store_number',1);//给合伙人增加店铺数
            $night = db('city_copartner')->where('my_invitation',$highe_share_code)->setInc('invitation_store_number',1);//合伙人添加分享人数
            $return_data = [
                'commision' => 0,
                'higher_phone' => $two['phone_number'],
                'base_commision' =>$base_commision,
                'reach_commision' =>$reach_commision,
            ];
         }
        return $return_data; 
      } else {
        return $return_data;
      }
  }


  function meal_name($rank_city){
        switch($rank_city){
            case 2:
                $meal_name = "特级城市";
                break;
            case 3:
                $meal_name = "一级城市";
                break;
            case 4:
                $meal_name = "二级城市";
                break;
            case 5:
                $meal_name = "三级城市";
                break;
            default:
                break;
        }
        return $meal_name;
  }
  /**
   * lilu
   * 获取用户的默认收货地址
   */
  function get_default_address($member_id){
        $address_list=db('user_address')->where(['user_id'=>$member_id,'status'=>1])->find();
        if($address_list){
            $address=$address_list['address_name'].$address_list['harvester_real_address'];
            return $address;
        }else{
            $address_list2=db('user_address')->where('user_id',$member_id)->find();
            if($address_list2){
                db('user_address')->where('user_id',$member_id)->update(['status'=>1]);
                $address=$address_list2['address_name'].$address_list2['harvester_real_address'];
                return $address;
            }else{
                return '暂无收货地址';
            }
        }
        
  }

/**
   * 城市对应茶商数量
   * 
   */
  function city_store_number($city){
    $city_data = CityRank::rank_find($city);
    return $city_data ? $city_data['store_number'] : 0;
    
}


function serail_status($status){
    switch($status){
        case 1:
            echo "订单";  
            break;
        case 2:
            echo "退款";
            break;
        case 3:
            echo "代理收入";
            break;
        case 4:
            echo "充值";
            break;
        case 5:
            echo "提现";
            break;
        case 6:
            echo "退货";
            break;
        default:
            break;

    }
}



    



