<?php
/**
 * 求购详情
 * 接口参数: 8段 * userid * 配件id
 * author pwj
* date 2017-06-13
*/

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证求购id
$buyid = trim($reqlist[9]);
if($buyid < 1 || $buyid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
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
/*测试数据
$buyid = 2;
$s_url = 'http://192.168.1.110/zjxzm/';
*/
//验证求购配件是否存在
$where = "bid = $buyid";
$count = dbCount('zj_border', $con, $where);
if($count != 1)
{
    forExit($lock_array, $con);
    toExit(30, $return_list);
}

$sql = "select appuid, bname, sname, cname,jname, img, type, vin, pinpai, otherpz,picture,bid from zj_border where $where";
$buyDetail = dbLoad(dbQuery($sql, $con),true);

if(!empty($buyDetail['picture']))
{
    $buyImg = json_decode($buyDetail['picture']);
    $buyDetail['picture'] = $buyImg;
}

if(!empty($buyDetail['type']) || $buyDetail['type'] == 0)
{
    $buyDetail['type'] = explode(',',$buyDetail['type']);
}

$buyInfo['info'] = $buyDetail;
forExit($lock_array, $con);
$return_list['data'] = json_encode($buyInfo);
toExit(0, $return_list, false);
?>