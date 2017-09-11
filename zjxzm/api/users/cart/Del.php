<?php
/**
 * 购物车删除
 * 接口参数: 8段 * userid * 购物车列表的id(如果是多个，用“,”分开，注意结尾没有“,”)
 * author wangrui@min-fo.com
 * date 2015-11-13
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$ids = trim($reqlist[9]);
if(strpos($ids, ',') !== false){
    $id_list = explode(",", $ids);
}else{
    $id_list[] = $ids;
}
foreach($id_list as $id_item){
    if(intval($id_item) < 1){
        forExit($lock_array);
        toExit(24, $return_list);
    }
}
$id_list = array_values($id_list);

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
    toExit(12, $return_list);
}
if(!file_put_contents($user_path."lock", " ", LOCK_EX)){
    forExit($lock_array);
    toExit(12, $return_list);
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

//验证记录是否存在
foreach($id_list as $id_item){
    $count = dbCount("hd_cart", $con, 'id = '.$id_item." and userid = ".$userid);
    if($count != 1){
        forExit($lock_array, $con);
        toExit(30, $return_list);
    }

    $sql = "delete from hd_cart where id = ".$id_item;
    if(!dbQuery($sql, $con)){
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }
}

forExit($lock_array, $con);
toExit(0, $return_list);

?>
