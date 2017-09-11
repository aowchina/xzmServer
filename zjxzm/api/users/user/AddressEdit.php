<?php
/**
 * 编辑收货地址
 * 接口参数：8段 * userid * 姓名(需转) * 手机号 * 省id * 市id * 区id * 详细地址(需转) * 地址id
 * author pwj
 * date 2017-06-03
 */
include_once("../functions_mut.php");
include_once("../functions_mcheck.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 16)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//姓名
$name = getStrFromByte(trim($reqlist[9]));
if(!isName($name)){
    forExit($lock_array);
    toExit(36, $return_list);
}

//手机号
$tel = trim($reqlist[10]);
if(!isMobel($tel)){
    forExit($lock_array);
    toExit(13, $return_list);
}

//省id
$pid = intval(trim($reqlist[11]));
if($pid < 1){
    forExit($lock_array);
    toExit(14, $return_list);
}

//市
$cid = intval(trim($reqlist[12]));
if($cid < 1){
    forExit($lock_array);
    toExit(15, $return_list);
}

//区
$aid = intval(trim($reqlist[13]));
if($aid < 0){
    forExit($lock_array);
    toExit(16, $return_list);
}

//详细地址
$address = getStrFromByte(trim($reqlist[14]));
if(!isAddress($address)){
    forExit($lock_array);
    toExit(37, $return_list);
}

$address_id = intval(trim($reqlist[15]));
if($address_id < 1){
    forExit($lock_array);
    toExit(24, $return_list);
}

//验证userid
$userid = intval(trim($reqlist[8]));
if(!($userid >= 1)){
    forExit($lock_array);
    toExit(10, $return_list);
}

//userid打锁
$user_path = $j_path.'user/'.getSubPath($userid, 4, true);
if(!is_dir($user_path)){
    forExit($lock_array);
    toExit(11, $return_list);
}
if(is_file($user_path.'lock')){
    forExit($lock_array);
    toExit(11, $return_list);
}
if(!file_put_contents($user_path.'lock', " ", LOCK_EX)){
    forExit($lock_array);
    toExit(11, $return_list);
}
$lock_array[] = $user_path.'lock';

//连接db
$con = conDb();
if($con == ''){
    forExit($lock_array);
    toExit(300, $return_list);
}

//检查连接数
if(!checkDbCon($con)){
    forExit($lock_array, $con);
    toExit(301, $return_list);
}

$count = dbCount("zj_user_address", $con, "userid = $userid and id = $address_id and is_app = 1");
if($count != 1){
    forExit($lock_array, $con);
    toExit(39, $return_list);
}

//验重
$condition = "userid = $userid and is_app = 1 and pid = $pid and cid = $cid and aid = $aid and address = '".$address."' and id <> $address_id";
$count = dbCount("zj_user_address", $con, $condition);
if($count > 0){
    forExit($lock_array, $con);
    toExit(38, $return_list);
}

$data['userid'] = $userid;
$data['user_pid'] = $pid;
$data['user_cid'] = $cid;
$data['user_qid'] = $aid;
$data['user_address'] = $address;
$data['user_tel'] = $tel;
$data['user_name'] = $name;

if(!dbUpdate($data, 'zj_user_address', $con, "userid = $userid and id = $address_id and is_app = 1")){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

forExit($lock_array, $con);
toExit(0, $return_list);

?>
