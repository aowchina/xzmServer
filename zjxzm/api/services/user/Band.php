<?php
/**
 * 用户申请(注册)
 * 接口参数: 8段 * userid * 优惠码
 * author wangrui@min-fo.com
 * date 2015-11-13
 */
include_once("../functions_mut.php");
include_once("../functions_mcheck.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$yh_code = trim($reqlist[9]);
if(!isPsw($yh_code)){
    forExit($lock_array);
    toExit(21, $return_list);
}

//验证userid
$userid = trim($reqlist[8]);
if($userid < 1 || $userid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
}

//打用户锁
$user_path = $j_path.'user/'.getSubPath($userid, 4, true);
if(is_file($user_path."lock")){
    forExit($lock_array);
    toExit(11, $return_list);
}
if(!file_put_contents($user_path."lock", " ", LOCK_EX)){
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

//先判断用户是否还能绑定
$count = dbCount("hd_tj_record", $con, "userid = $userid");
if($count > 0){
    forExit($lock_array, $con);
    toExit(52, $return_list);
}

$parentid = 0;
$nowtime = time();

$condition = "tj_code = '$yh_code' and block = 0 and can_tj = 1 and ($nowtime between tj_stime and tj_etime)";
$count = dbCount('hd_users', $con, $condition);
if($count <= 0){
    forExit($lock_array, $con);
    toExit(20, $return_list);
}

$sql = "select id from hd_users where $condition";
$result = dbLoad(dbQuery($sql, $con), true);
$parentid = $result['id'];

if($parentid != 0){
    if($parentid == $userid){
        forExit($lock_array, $con);
        toExit(53, $return_list);
    }

    //查询用户的群组
    $sql = "select group_id from hd_user_usergroup_map where user_id = $userid";
    $group_info = dbLoad(dbQuery($sql, $con), true);
    $group_id = $group_info['group_id'];

    //如果是微商，不让绑
    if($group_id == 9){
        forExit($lock_array, $con);
        toExit(54, $return_list);
    }

    $data = array();
    $data['userid'] = $userid;
    $data['parentid'] = $parentid;
    $data['tjtime'] = $nowtime;
    dbAdd($data, 'hd_tj_record', $con);

    if($group_id == 2){
        $data = array();
        $data['group_id'] = 3;

        dbUpdate($data, 'hd_user_usergroup_map', $con, "user_id = $userid");
    }
}

forExit($lock_array, $con);
toExit(0, $return_list);

?>
