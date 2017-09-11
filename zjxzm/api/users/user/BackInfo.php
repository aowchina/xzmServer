<?php
/**
 * 钱包与返点记录
 * 接口参数: 8段 * userid
 * author wangrui@min-fo.com
 * date 2015-11-13
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 9)){
    forExit($lock_array);
    toExit(9, $return_list);
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

//查询全部返点和差价
$sql = "select sum(money) + sum(oprice) as money from hd_back_in where userid = $userid";
$in_info = dbLoad(dbQuery($sql, $con), true);
if($in_info['money']){
    $in_money = $in_info['money'];
}
else{
    $in_money = 0;
}
//查询全部未拒绝的返点记录
$sql = "select sum(money) as money from hd_back_out where userid = $userid and (status = 1 or status = 2 or status = 0)";
$out_info = dbLoad(dbQuery($sql, $con), true);
if($out_info['money']){
    $out_money = $out_info['money'];
}
else{
    $out_money = 0;
}

$r_data['money'] = $in_money - $out_money;
//查询全部返点记录

$sql = "select money,intime,status from hd_back_out where userid = $userid";
$out_list = dbLoad(dbQuery($sql, $con));

if(count($out_list) > 0){
    foreach($out_list as &$out_item){
        $out_item['intime'] = date("Y-m-d H:i:s", $out_item['intime']);
    }
}
else{
    $out_list = array();
}
$r_data['records'] = $out_list;

$return_list['data'] = json_encode($r_data);
forExit($lock_array, $con);
toExit(0, $return_list);
?>
