<?php
/**
 * 车款下的分类
 * 接口参数: 8段 * userid * 车款id（1个或多个）
 * author pwj
 * date 2017-06-08
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
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

//验证车款id
$carids = trim($reqlist[9]);
if(!$carids){
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

//一级分类
// $sql="select tname,typeid from zj_type";
$sql="select tname,typeid from zj_type WHERE carid in (".$carids.")";
$sptype = dbLoad(dbQuery($sql, $con));

foreach($sptype as &$item){
     $sql="select id,name from zj_pt where typeid=$item[typeid]";
    $chtype = dbLoad(dbQuery($sql, $con));
    if(empty($chtype))
    {
        $chtype = [];
    }
    $item['child'] = $chtype;
}
$r_list['type'] = $sptype;



forExit($lock_array, $con);
$return_list['data'] = json_encode($r_list);
toExit(0, $return_list, false);
?>