<?php
/**
 * 订单详情(卖家端)
 * 接口参数: 8段 * appuid * 订单号(qgorderid)
 * author zq
 * date 2015-11-13
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$order_num = trim($reqlist[9]);
if(!preg_match('/^zjqg[0-9]+$/', $order_num)){
    forExit($lock_array);
    toExit(29, $return_list);
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


//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=0";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

$count = dbCount('zj_qgorder', $con, "qgorderid = '".$order_num."'");
if($count != 1){
    forExit($lock_array, $con);
    toExit(30, $return_list);
}

$sql = "select * from zj_qgorder where qgorderid = '$order_num'";
$order_info = dbLoad(dbQuery($sql, $con), true);


//取商品详情
$sql=" select c.bname,c.sname,c.cname,c.jname,c.picture from zj_qgorder a
 left join zj_setmoney b on a.bjid=b.id
 left join zj_border c on b.bid=c.bid where a.qgorderid='$order_num'";
$order_list = dbLoad(dbQuery($sql, $con));

if(count($order_list)>0){
    foreach($order_list as &$order_item)
        $pictures=$order_item['picture'];
    $pictures=json_decode($pictures);
    $picture = trim($pictures[0]);
    $order_item['picture'] = $s_url.$picture;
}else{
    $order_list = array();
}


//算总价
$price = explode(',',$order_info['price']);
$total_money= array_sum($price);

$order_item['type'] = $order_info['type'];
$order_item['price'] = $order_info['price'];
$order_item['total_money'] = $total_money;
$r_data['total_money']=$total_money;
$r_data['paytime']=date("Y-m-d H:i:s",$order_info['paytime']);
$r_data['qgorderid']=$order_info['qgorderid'];
$r_data['goods'] = $order_list;
$r_data['addtime'] = date("Y-m-d H:i:s", $order_info['addtime']);
$r_data['fhtime'] = isset($order_info['fhtime'])? date("Y-m-d H:i:s",$order_info['fhtime']):" ";


//地址详情
$r_data['pid'] = $order_info['pid'];
$r_data['cid'] = $order_info['cid'];
$r_data['qid'] = $order_info['qid'];
$r_data['sname'] = $order_info['sname'];
$r_data['stel'] = $order_info['stel'];
$r_data['kuaidih'] = isset($order_info['kuaidih'])?$order_info['kuaidih']:" ";
$r_data['wlname'] = isset($order_info['wlname'])?$order_info['wlname']:" ";
$r_data['retime'] = isset($order_info['retime'])? date("Y-m-d H:i:s",$order_info['retime']):" ";

$sql = "select areaname from zj_area where id = ".$order_info['pid'];
$pinfo = dbLoad(dbQuery($sql, $con), true);
$pname = $pinfo['areaname'];

$sql = "select areaname from zj_area where id = ".$order_info['cid'];
$cinfo = dbLoad(dbQuery($sql, $con), true);
$cname = $cinfo['areaname'];

if($order_info['qid'] != 0){
    $sql = "select areaname from zj_area where id = ".$order_info['qid'];
    $ainfo = dbLoad(dbQuery($sql, $con), true);
    $aname = ' '.$ainfo['areaname'].' ';
}else{
    $aname = ' ';
}

$r_data['address'] = $pname." ".$cname.$aname.$order_info['address'];


forExit($lock_array, $con);
$return_list['data'] = json_encode($r_data);
toExit(0, $return_list, false);

?>
