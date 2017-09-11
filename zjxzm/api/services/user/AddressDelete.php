<?php
/**
 * 删除收货地址
 * 接口参数：8段 * userid * 地址id
 * author pwj
 * date 2017-06-03
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//地址id
$id = intval(trim($reqlist[9]));
if($id < 1){
    forExit($lock_array);
    toExit(39, $return_list);
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

$condition = "userid = $userid and id = $id and is_app = 0";
$count = dbCount('zj_user_address', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(39, $return_list);
}

$sql = "delete from zj_user_address where $condition";
if(!dbQuery($sql, $con)){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

forExit($lock_array, $con);
toExit(0, $return_list);

?>
