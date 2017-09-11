<?php

$j_path = "/data/pubout/minfo/zjxzm/sellerdata/";      //Josn文件保存路径
$s_url = "http://".$_SERVER['HTTP_HOST']."/";     //后台URL
$s_path = '/data/pubout/minfo/zjxzm/';           //后台URL
$t_path = '/data/pubout/minfo/zjxzm/data/';
$appid_name_array = array("7000000009"=>"zjxzm");
$appid_key = array("7000000009" => "MINFOCARREPAIRGUOZHANGTONGPEI");

$hx=array();//环信相关
$hx['client_id'] = 'YXA6O6z7cF-uEeew3ysidJrxqw';
$hx['client_secret'] = 'YXA6p1cwhg6pqQcOa1VRRi6oh3mDFdk';
$hx['org_name'] = '1124170630115118';
$hx['app_name'] = 'carautorepair';

$return_list = array();    //返回数组
$reqlist = array();        //请求的参数数组

$deviceid = '';            //请求接口的设备号
$dev_path = '';            //请求接口的设备的目录

$userid = 0;               //请求接口的用户的userid
$user_path = '';           //请求接口的用户的个人目录

$lock_array = array();

/*--------------------------------公共验证开始---------------------------------------*/
//验证p0
$p0 = intval(trim($_POST['p0']));
if($p0 < 3){
    toExit(1, $return_list);
}

//验证p1
$p1 = trim($_POST['p1']);
if(!isset($appid_key[$p1])){
    toExit(2, $return_list);
}

//验证p2
$p2 = trim($_POST['p2']);
if(!($p2 == 1 || $p2 == 2)){
    toExit(3, $return_list);
}

$str = "";
$command = "";
$result_list = array();

//验证p3
$p3 = trim($_POST['p3']);
if(empty($p3)){
    toExit(4, $return_list);
}

//解密
$command = "../Alg ".$p2." ".$p3." ".md5($appid_key[$p1]);
$str = exec($command);
$result_list = explode('*', trim($str));

//验证解密后appid
$appid = trim($result_list[0]);
if($appid != $p1){
    toExit(5, $return_list);
}

//解密p4-pn
for($i = 4; $i < $p0; $i++){
    $str_tmp = "";
    $command = "";
    $command = "../Alg ".$p2." ".$_POST['p'.$i]." ".md5($appid_key[$p1]);
    $str_tmp = exec($command);
    $str = $str.trim($str_tmp);
}

$reqlist = explode('*', trim($str));

//验证deviceid
$deviceid = trim($reqlist[1]);
if(empty($deviceid) || !preg_match("/^[0-9a-zA-Z-]+$/", $deviceid)){
    toExit(6, $return_list);
}

//生成设备号文件锁
$dev_path = $j_path.'device/'.getSubPath($deviceid, 4, true);
if(!mkdirs($dev_path)){
    toExit(7, $return_list);
}

if(is_file($dev_path.'lock')){
    toExit(8, $return_list);
}

if(!file_put_contents($dev_path.'lock', ' ', LOCK_EX)){
    toExit(8, $return_list);
}

$lock_array[] = $dev_path.'lock';


/*--------------------------------公共验证结束---------------------------------------*/

function sendDx($tel){
    $smsapi = "http://api.smsbao.com/";
    $user = "wangrui";                    //短信平台帐号
    $pass = md5("111111");                //短信平台密码
    $content = "【浙江心之盟】您的浙江心之盟平台密码为:".$tel."，请妥善保管！"; //要发送的短信内容

    $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$tel."&c=".urlencode($content);
    $result = file_get_contents($sendurl);
    if($result == 0){
        return true;
    }
    return false;
}

function getStrFromByte($mByteStr){
    $str = "";
    $byte_list = explode("#", $mByteStr);
    for($i = 0; $i < count($byte_list) - 1; $i++){
        $str .= chr($byte_list[$i]);
    }

    return trim($str);
}

function getDefault($parent_array, $key_name, $default_value = ''){
    if(isset($parent_array[$key_name]) && $parent_array[$key_name] != null && $parent_array[$key_name] != ''){
        return $parent_array[$key_name];
    }
    return $default_value;
}

//获取图片后缀名(图片格式)
function getImgType($imgname){
    $list = explode(".", $imgname);
    return $list[count($list) - 1];
}

/**
 * 递归创建指定目录
 * @param dir：要创建的目录的绝对路径
 * @return 目录创建是否成功，true成功，false不成功
 * author wangrui@min-fo.com
 */
function mkdirs($dir){
    return is_dir($dir) or (mkdirs(dirname($dir)) and mkdir($dir, 0777));
}

/**
 * 根据 $baseStr，截取 $subAmount 次，每次截取2位，拼接成相对路径
 * @param $baseStr: 被截取的字符串
 * @param $subAmount: 被截取次数
 * @param $includeStr: 是否包括$baseStr
 * author wangrui@min-fo.com
 * date 2015-05-01
 */
function getSubPath($baseStr, $subAmount, $includeStr){
    $str = md5($baseStr);
    $re_path = '';
    for($i = 0; $i < $subAmount; $i++){
        $re_path = $re_path.substr($str, $i*2, 2).'/';
    }
    if($includeStr){
        $re_path = $re_path.$baseStr.'/';
    }
    return trim($re_path);
}

/**
 * 数据加密
 * @author wangrui
 */
function pswData($data_str){
    $data_len = strlen($data_str);
    $data_array = array();

    if(($data_len % 60) != 0){
        $data_array['cnt'] = ceil($data_len / 60);
    }else{
        $data_array['cnt'] = $data_len / 60;
    }

    for($i = 0; $i < $data_array['cnt']; $i++){
        $sub_data_str = "";
        $sub_data_str = substr($data_str, $i * 60, 60);
        $command = "";
        $command = "../Alg 1 '".$sub_data_str."' ".md5("MINFOCARREPAIRGUOZHANGTONGPEI");
        $data_array[$i] = exec($command);
    }

    return $data_array;
}

/**
 * 解锁
 * @author wangrui
 */
function unLock($lock_filename){
    if(is_file($lock_filename)){
        unlink($lock_filename);
    }
}

/**
 * 结束前处理
 * @param $lock_file_array: 锁文件数组
 * @param $db_con: 数据库连接
 * @author wangrui
 */
function forExit($lock_file_array, $con = ""){
    if(count($lock_file_array) > 0){
        for($i = 0; $i < count($lock_file_array); $i++){
            unLock($lock_file_array[$i]);
        }
    }

    if($con){
        mysql_close($con);
    }
}

/**
 * 结束前处理
 * @author wangrui
 * returnType 0:string 1:array 
 */
function toExit($errorcode, $return_list, $needpsw = true, $returnType=0){//returnType 返回类型控制
    $return_list['errorcode'] = $errorcode;

    if(isset($return_list['data']) && !empty($return_list['data'])){
        if($needpsw){
            $return_list['active'] = 1;
            $return_list['data'] = pswData($return_list['data']);
        }else{
            $return_list['active'] = 2;
        }
    }else{
        if($needpsw){
            $return_list['active'] = 1;
        }else{
            $return_list['active'] = 2;
        }

        $return_list['data'] = $returnType == 0 ? '' : ($returnType == 1 ? array() : $returnType);//返回类型控制(针对app端设置)
    }

    echo json_encode($return_list);
    exit;
}

/**
 * 生成随机验证码
 * @author wangrui
 */
function createCode(){
    $str = '';
    for($i = 0; $i < 4; $i ++){
        $str .= rand(0,9);
    }

    return $str;
}

/**
 * 发送短信
 * @author wangrui
 */

function sendMsg($tel,$code){
    $smsapi = "http://api.smsbao.com/";
    $user = "wangrui"; //短信平台帐号
    $pass = md5("111111"); //短信平台密码
    $content="【浙江心之盟】验证码为:".$code."，有效期为60秒";//要发送的短信内容
    $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$tel."&c=".urlencode($content);
    $result =file_get_contents($sendurl);
    if($result == 0){
        return true;
    }
    return false;
}
?>
