<?php
/**
 * 给订单绑定收货地址
 * 接口参数: 8段 * userid * 订单号(order_id) * 地址id
 * author wangrui@min-fo.com
 * date 2015-11-13
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 11)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$order_num = trim($reqlist[9]);
if(!preg_match('/^hondo_wx[0-9]+$/', $order_num)){
    forExit($lock_array);
    toExit(35, $return_list);
}

$address_id = intval(trim($reqlist[10]));
if($address_id < 1){
    forExit($lock_array);
    toExit(24, $return_list);
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

$count = dbCount('hd_order', $con, "order_id = '".$order_num."' and userid = $userid and status = 0");
if($count != 1){
    forExit($lock_array, $con);
    toExit(34, $return_list);
}

$count = dbCount("hd_user_address", $con, "userid = $userid and id = $address_id");
if($count != 1){
    forExit($lock_array, $con);
    toExit(39, $return_list);
}

$sql = "select user_pid,user_cid,user_qid,user_name,user_address,user_tel from hd_user_address where id = $address_id";
$address_info = dbLoad(dbQuery($sql, $con), true);

$data = array();
$data['user_pid'] = $address_info['user_pid'];
$data['user_cid'] = $address_info['user_cid'];
$data['user_qid'] = $address_info['user_qid'];
$data['user_name'] = $address_info['user_name'];
$data['user_address'] = $address_info['user_address'];
$data['user_tel'] = $address_info['user_tel'];

if(!dbUpdate($data, 'hd_order', $con, "order_id = '$order_num'")){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

forExit($lock_array, $con);
toExit(0, $return_list, false);

?>
