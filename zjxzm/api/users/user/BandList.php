<?php
/** 
 * 推广用户列表
 * 参数：8段 * userid * 页码
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$page = intval(trim($reqlist[9]));
if($page < 1){
    forExit($lock_array);
    toExit(25, $return_list);
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

//验证userid是否存在
$count = dbCount("hd_users", $con, "id = $userid");
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

$limit = " limit ".(($page - 1)*10).",10";
$sql = "select userid,tjtime from hd_tj_record where parentid = $userid".$limit;
$list = dbLoad(dbQuery($sql, $con));
if(count($list) > 0){
    foreach($list as &$item){
        $sql = "select a.name, a.username, b.group_id from hd_users as a, hd_user_usergroup_map as b where a.id = ".
            $item['userid']." and b.user_id = ".$item['userid'];

        $user_info = dbLoad(dbQuery($sql, $con), true);

        $item['name'] = $user_info['name'];
        $item['tel'] = $user_info['username'];

        if($user_info['group_id'] == 9){
            $item['type'] = 1;
        }
        else{
            $item['type'] = 0;
        }

        $item['tjtime'] = date("Y-m-d", $item['tjtime']);

        $count = dbCount('hd_order', $con, "userid = ".$item['userid']." and status > 0");
        $item['amount'] = $count;
    }
}else{
    $list = array();
}


forExit($lock_array, $con);
$return_list['data'] = json_encode($list);
toExit(0, $return_list, false);

?>
