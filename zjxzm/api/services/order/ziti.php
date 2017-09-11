<?php
/**
 * 选择物流
 * 接口参数: 8段 * userid * 订单号(order_id) * 仓库id
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
//$order_num="hondo_wx1468730412";
$order_num = trim($reqlist[9]);
if(!preg_match('/^hondo_wx[0-9]+$/', $order_num)){
    forExit($lock_array);
    toExit(35, $return_list);
}
//$ck_id=4;
$ck_id = intval(trim($reqlist[10]));
if(!is_int($ck_id)){
    forExit($lock_array);
    toExit(24, $return_list);
}
//$userid =546;

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

$data = array();

//仓库id是否合法
$count = dbCount('hd_cang', $con,"id='$ck_id' and state=1");
if($count != 1){
    forExit($lock_array, $con);
    toExit(24, $return_list);
}

$sql = "select  id from hd_wl where type=2";
$wlid = dbLoad(dbQuery($sql, $con),true);

$data['wl_price']=0;
$data['wl_id']=$wlid['id'];
$data['cang_id']=$ck_id;
if(!dbUpdate($data, 'hd_order', $con, "order_id = '$order_num'")){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

forExit($lock_array, $con);
toExit(0, $return_list, false);

?>
