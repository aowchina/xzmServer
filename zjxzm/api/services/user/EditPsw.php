<?php
/** 
 * 修改密码
 * 参数：8段 * userid * 旧密码 * 新密码 * 确认密码
 * author pwj
 * date 2017-06-03
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");

//验证参数个数
if(!(count($reqlist) == 12)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$psw = trim($reqlist[9]);
if(!isPsw($psw)){
    forExit($lock_array);
    toExit(14, $return_list);
}

$new_psw = trim($reqlist[10]);
if(!isPsw($new_psw)){
    forExit($lock_array);
    toExit(14, $return_list);
}

$config_psw = trim($reqlist[11]);
if($new_psw != $config_psw){
    forExit($lock_array);
    toExit(18, $return_list);
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

$sql = "select password from zj_seller where sellerid = $userid";
$user_info = dbLoad(dbQuery($sql, $con), true);
if(!password_verify($psw, $user_info['password'])){
    forExit($lock_array, $con);
    toExit(17, $return_list);
}

$data = array();
$data['password'] = password_hash($new_psw, PASSWORD_DEFAULT);
if(!dbUpdate($data, 'zj_seller', $con, "sellerid = $userid")){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

forExit($lock_array, $con);
toExit(0, $return_list, false);

?>
