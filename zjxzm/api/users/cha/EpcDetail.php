<?php
/**
 * epc结构图详情(比如纵梁前)
 * 接口参数: 8段 * userid * epcid
 * author zq
 * date 2017-06-08
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
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
$epcid= trim($reqlist[9]);
if(empty($epcid)){
    forExit($lock_array);
    toExit(21, $return_list);
}
if($epcid < 1 || $epcid > 4294967296){
    forExit($lock_array);
    toExit(19, $return_list);
}
//$epcid = "1";

//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=1";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

$count =dbCount("zj_oem", $con, $where = "epcid='$epcid'");
if($count>=1){
    $sql="select position,name,oem,id from zj_oem where epcid='$epcid'";
    $result = dbLoad(dbQuery($sql, $con));

    forExit($lock_array, $con);
    $return_list['data'] = json_encode($result);
    toExit(0, $return_list, false);

}else{
    forExit($lock_array, $con);
    toExit(22, $return_list);
}



