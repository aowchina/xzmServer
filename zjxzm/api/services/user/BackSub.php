<?php
/**
 * 申请提现
 * 接口参数: 8段 * userid * apply_money
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

//验证userid
$userid = trim($reqlist[8]);
if($userid < 1 || $userid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
}

//接收用户申请金额
$apply_money = trim($reqlist[9]);
if($apply_money < 0 || empty($apply_money))
{
    forExit($lock_array);
    toExit(61, $return_list);
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

//查询全部返点
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

$sub_money = $in_money - $out_money;

if($sub_money <= 0){
    forExit($lock_array, $con);
    toExit(47, $return_list);
}

if($apply_money > $sub_money)
{
    forExit($lock_array, $con);
    toExit(61, $return_list);
}
$data = array();
$data['money'] = round($apply_money, 2);
$data['sub_time'] = $data['intime'] = time();
$data['status'] = 0;
$data['userid'] = $userid;

if(!dbAdd($data, 'hd_back_out', $con)){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

forExit($lock_array, $con);
toExit(0, $return_list);

?>
