<?php
/**
 * 求购配件
 * 接口参数: 8段 * userid * 品牌id * 车系id * 车款id * vin * name * 品质类别
 * author pwj
 * date 2017-06-06
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");

//验证参数个数
if(!(count($reqlist) == 15)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证品牌id
$brandid = trim($reqlist[9]);
if($brandid < 1 || $brandid > 4294967296){
    forExit($lock_array);
    toExit(40, $return_list);
}

//验证车系id
$serialid = trim($reqlist[10]);
if($serialid < 1 || $serialid > 4294967296){
    forExit($lock_array);
    toExit(41, $return_list);
}

//验证车款id
$carid = trim($reqlist[11]);
if($carid < 1 || $carid > 4294967296){
    forExit($lock_array);
    toExit(42, $return_list);
}

//验证vin
$vin = trim($reqlist[12]);
if(empty($vin)){
    forExit($lock_array);
    toExit(60, $return_list);
}

//验证配件名称
$name = trim($reqlist[13]);
if(!empty($name)){
    forExit($lock_array);
    toExit(61, $return_list);
}

//验证类别
$type = trim($reqlist[14]);
if($type != 1 || $type != 2){
    forExit($lock_array);
    toExit(62, $return_list);
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

//验证配件是否重复
$count = dbCount('zj_wtbuy', $con, "sellerid = $userid and brandid = $brandid and serialid = $serialid and carid = $carid and type = $type and name = '$name'");
if($count == 1)
{
    forExit($lock_array);
    toExit(70, $return_list);
}

$data = [];
$data['sellerid'] = $userid;
$data['brandid'] = $brandid;
$data['serialid'] = $serialid;
$data['carid'] = $carid;
$data['vin'] = $vin;
$data['jname'] = $name;
$data['type'] = $type;
if(!dbAdd($data, 'zj_border', $con))
{
    forExit($lock_array);
    toExit(302, $return_list);
}

forExit($lock_array, $con);
toExit(0, $return_list, false);
?>