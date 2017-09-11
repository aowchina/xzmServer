<?php
/**
 * 配件的上架和下架
 * 接口参数: 8段 * userid * 商品id * 上架(下架:0 上架:1)
 * author pwj
 * date 2017-06-01
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 11)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证状态
$is_sj = intval(trim($reqlist[10]));
if(!in_array($is_sj, [0,1]))
{
    forExit($lock_array);
    toExit(26, $return_list);
}

//验证userid
$goodid = trim($reqlist[9]);
if($goodid < 1 || $goodid > 4294967296){
    forExit($lock_array);
    toExit(13, $return_list);
}

//验证userid
$userid = trim($reqlist[8]);
if($userid < 1 || $userid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
}
$user_path = $j_path.'user/'.getSubPath($userid, 4, true);
if(!is_dir($user_path)){
    forExit($lock_array);
    toExit(11, $return_list);
}
if(is_file($user_path."lock")){
    forExit($lock_array);
    toExit(11, $return_list);
}
if(!file_put_contents($user_path."lock", " ", LOCK_EX)){
    forExit($lock_array);
    toExit(11, $return_list);
}
$lock_array[] = $user_path."lock";

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

$where = "goodid = $goodid and state = 1";
$count = dbCount('zj_good', $con, $where);
if($count != 1)
{
    forExit($lock_array, $con);
    toExit(30, $return_list);
}


$data['is_sj'] = $is_sj;
if(!dbUpdate($data, 'zj_good', $con, "goodid = $goodid"))
{
    forExit($lock_array, $con);
    toExit(302, $return_list);
}


forExit($lock_array, $con);
toExit(0, $return_list, false);
?>