<?php
/**
 * 车系
 * 接口参数: 8段 * userid * brandid
 * author pwj
 * date 2017-06-01
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}
$brandid = trim($reqlist[9]);
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
/*测试数据
$brandid = 13;
$userid = 1;
$s_url = 'http://192.168.118/hondo_wx/';
*/
$sql = "select bname from zj_brand where brandid = $brandid";
$bname = dbLoad(dbQuery($sql, $con),true);
$sql = "select serialid,sname,simage from zj_serial where brandid = $brandid";
$serialinfo = dbLoad(dbQuery($sql, $con));
foreach($serialinfo as &$v)
{
    $v['simage'] = $s_url.$v['simage'];
}

$r_list['bname'] = $bname['bname'];
$r_list['serialinfo'] = $serialinfo;

forExit($lock_array, $con);
$return_list['data'] = json_encode($r_list);
toExit(0, $return_list, false);