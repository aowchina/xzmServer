<?php
/**
 * 钱包(余额)
 * 接口参数: 8段 * sellerid(用户id) * tid
 * author zq
 * date 2017-06-9
 * return 余额(money)
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 9)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证userid
//$userid =2;
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

//用户是否存在
$condition = "sellerid = '$userid'";
$count = dbCount('zj_seller', $con, $condition);

if($count != 1) {
    forExit($lock_array, $con);
    toExit(11, $return_list);
}

//钱包中是否有此记录
$condition = "userid = '$userid' and tid ='2'";
$count = dbCount('zj_wallet', $con, $condition);

if($count != 1){
    //没有此用户记录钱包为0
    $money=0;
}else{
    //用户余额
    $sql="select money from zj_wallet where userid =$userid";
    $res= dbLoad(dbQuery($sql, $con), true);
    $money=$res['money'];
}

$result_data['money'] = $money;
$return_list['data'] = json_encode($result_data);
forExit($lock_array, $con);
toExit(0, $return_list);

?>
