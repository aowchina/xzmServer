<?php
/**
 * 添加好友
 * 接口参数: 8段 aid * 买家款id * sid * 卖家id *
 * author mo_yu
 * date 2017-06-23
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证userid
$sid = trim($reqlist[8]);
if($sid < 1 || $sid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
}


//验证买家id
$aid = trim($reqlist[9]);
if($aid < 1 || $aid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
}

//user打锁
$user_path = $j_path.'user/'.getSubPath($sid, 4, true);
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

$sql = 'select id from zj_friends where aid='.$aid.' and sid='.$sid;
if(dbLoad(dbQuery($sql,$con),true)){
    forExit($lock_array, $con);
    toExit(59, $return_list, false);//好友重复
}

//关联好友
$data = [];
$data['sid'] = $sid;
$data['aid'] = $aid;
$data['createtime'] = date('Y-m-d H:i:s');
if(!dbAdd($data, 'zj_friends', $con))
{
    forExit($lock_array, $con);
    toExit(302, $return_list);
}
forExit($lock_array, $con);
toExit(0, $return_list, false);

?>


