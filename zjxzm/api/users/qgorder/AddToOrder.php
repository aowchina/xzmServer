<?php
/**
 * 求购下单(买家)
 * 接口参数: 8段 * appuid *  bid * type * price
 * author zq
 * date 2017-6-25
 */
include_once("../functions_mcheck.php");
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 12)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$bjid = trim($reqlist[9]);
if($bjid < 1 || $bjid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
}

$type = trim($reqlist[10]);

$prices = trim($reqlist[11]);

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

//锁订单表
$order_lock = $j_path.'lock/qgorder';
//锁表
if(!lockDb($order_lock, 3)){
    forExit($lock_array, $con);
    toExit(303, $return_list);
}
$lock_array[] = $j_path."lock/qgorder";

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

//$userid = 10;
//$bid="1";
//$type="1,2";
//$prices="30,40";

//$price = explode(',',$prices);
//$total_money= array_sum($price);


//生成订单号
$time = time();
$orderid = 'zjqg'.time().($userid + 1000).rand(1000000, 9999999);
$data['qgorderid'] = $orderid;
$data['bjid'] =$bjid;
$data['appuid'] = $userid;
$data['type']=$type;
$data['price'] = $prices;
$data['status'] = 0;
$data['addtime'] = $time;

if(!dbAdd($data, 'zj_qgorder', $con)){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

//返数据
$r_data['qgorderid'] = $orderid;
$r_data['qg_addtime'] = $data['addtime'];
//$r_data['address'] = $data['address'];
//$r_data['sname'] = $data['sname'];
//$r_data['stel'] = $data['stel'] ;

$return_list['data'] = json_encode($r_data);
forExit($lock_array, $con);
toExit(0, $return_list);

?>
