<?php
/**
* 添加收藏
* 接口参数: 8段 * appuid(用户id) goodid
* author zq
* date 2017-06-16
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

$goodid = trim($reqlist[9]);
if($goodid < 1 || $goodid > 4294967296){
    forExit($lock_array);
    toExit(19, $return_list);
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
//$goodid="3";
//$userid = "1";
//用户是否存在
$condition = "appuid = '$userid'";
$count = dbCount('zj_appuser', $con, $condition);

if($count != 1) {
forExit($lock_array, $con);
toExit(11, $return_list);
}

//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=1";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

//收藏中是否有此记录
$condition = "appuid = '$userid' and goodid= $goodid";
$count = dbCount('zj_collect', $con, $condition);

if($count < 1){
//没有此用户记录收藏为空
    $data['goodid'] = $goodid;
    $data['appuid'] = $userid;

    if(!dbAdd($data, 'zj_collect', $con)){
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }
}

forExit($lock_array, $con);
toExit(0, $return_list);


?>
