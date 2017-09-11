<?php
/**
 * 下单(买家)
 * 接口参数: 8段 * appuid * goodid * shopid * 价格 * 数量
 * author zq
 * date 2017-6-14
 */
include_once("../functions_mcheck.php");
include_once("../functions_mut.php");
include_once("../functions_mdb.php");


//验证参数个数
if(!(count($reqlist) == 13)){
    forExit($lock_array);
    toExit(9, $return_list);
}



$goods_num = trim($reqlist[9]);
if($goods_num < 1 || $goods_num > 4294967296){
    forExit($lock_array);
    toExit(31, $return_list);
}

$shopid = trim($reqlist[10]);
if($shopid < 1 || $shopid > 4294967296){
    forExit($lock_array);
    toExit(32, $return_list);
}

$price = trim($reqlist[11]);
if(!isPoint($price, 8, 2)){
    forExit($lock_array);
    toExit(33, $return_list);
}

$amount = intval(trim($reqlist[12]));
if($amount <= 0){
    forExit($lock_array);
    toExit(34, $return_list);
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

//锁订单表
$order_lock = $j_path.'lock/order';
//锁表
if(!lockDb($order_lock, 3)){
    forExit($lock_array, $con);
    toExit(303, $return_list);
}
$lock_array[] = $j_path."lock/order";

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
//$id_list = [738,739];
//$goods_num="4";
//$amount="1";
//$price="35";
//$shopid="33";
//验证货号是否存在
$condition = "goodid = '".$goods_num."' and is_sj = 1";
$count = dbCount('zj_good', $con, $condition);
if($count == 0){
    forExit($lock_array, $con);
    toExit(35, $return_list);
}



$total_money= $price * $amount;

//生成订单号
$time = time();
$orderid = 'zj'.time().($userid + 1000).rand(1000000, 9999999);
$data['orderid'] = $orderid;
$data['shopid'] =$shopid;
$data['goodid'] = $goods_num;
$data['appuid'] = $userid;
$data['money'] = $total_money;  //直接取的商品价格
$data['status'] = 0;
$data['addtime'] = $time;

if(!dbAdd($data, 'zj_order', $con)){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}


$o_data['orderid'] = $orderid;
$o_data['shopid'] =$shopid;
$o_data['goodid'] = $goods_num;
$o_data['money'] = $price;
$o_data['amount'] = $amount;

if(!dbAdd($o_data, 'zj_order_goods', $con)){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}


$r_data['orderid'] = $orderid;
$r_data['addtime'] = $data['addtime'];
//$r_data['address'] = $data['address'];
//$r_data['sname'] = $data['sname'];
//$r_data['stel'] = $data['stel'] ;

$return_list['data'] = json_encode($r_data);
forExit($lock_array, $con);
toExit(0, $return_list);

?>
