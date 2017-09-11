<?php
/** 
 * 修改优惠码
 * 参数：8段 * userid * 优惠码
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$code = trim($reqlist[9]);
if(!isPsw($code)){
    forExit($lock_array);
    toExit(21, $return_list);
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

//验证用户是否有推荐权限
$sql = "select can_tj, tj_stime, tj_etime, tj_code from hd_users where id = $userid";
$user_info = dbLoad(dbQuery($sql, $con), true);
if($user_info['can_tj'] != 1){
    forExit($lock_array, $con);
    toExit(44, $return_list);
}

if(!empty($user_info['tj_code'])){
    forExit($lock_array, $con);
    toExit(48, $return_list);
}

$now_time = time();
if($now_time > $user_info['tj_etime']){
    forExit($lock_array, $con);
    toExit(45, $return_list);
}

//验重
$count = dbCount("hd_users", $con, "id <> $userid and tj_code = '$code'");
if($count > 0){
    forExit($lock_array, $con);
    toExit(46, $return_list);
}

$data = array();
$data['tj_code'] = $code;
if(!dbUpdate($data, 'hd_users', $con, "id = $userid")){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

forExit($lock_array, $con);
toExit(0, $return_list, false);

?>
