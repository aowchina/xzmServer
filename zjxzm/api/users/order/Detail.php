<?php
/**
 * 订单详情(买家端)
 * 接口参数: 8段 * appuid * 订单号(orderid)
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
if(!preg_match('/^zj[0-9]+$/', $order_num)){
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
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=1";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

$count = dbCount('zj_order', $con, "orderid = '".$order_num."'");
if($count != 1){
    forExit($lock_array, $con);
    toExit(30, $return_list);
}

$sql = "select * from zj_order where orderid = '$order_num'";
$order_info = dbLoad(dbQuery($sql, $con), true);

//取商品详情(名称,图片,oem号)
$sql = "select * from zj_order_goods where orderid = '$order_num' order by id asc";
$order_list = dbLoad(dbQuery($sql, $con));

if(count($order_list) > 0){
    foreach($order_list as &$order_item){
//        $sql = "select img,name,oem from zj_goods where goodid = '".$order_item['goodid']."'";
        $sql = "select a.img,a.name,a.oem,d.bname,c.sname,b.cname from zj_good a left join zj_type e on a.typeid=e.typeid left join zj_car b on e.carid=b.carid left join zj_serial c on b.serialid=c.serialid left join zj_brand d on c.brandid= d.brandid where a.goodid = '".$order_item['goodid']."'";

        $ginfo = dbLoad(dbQuery($sql, $con), true);

        $imgs=$ginfo['img'];
        if($imgs){
            $imgs=json_decode($imgs);
            $img = trim($imgs[0]);
            $order_item['img'] = $s_url.$img;
        }else{
            $order_item['img'] = " ";
        }

        $order_item['name'] = $ginfo['name'];
        $order_item['oem']= isset($order_item['oem'])?$ginfo['oem']:" ";
        $order_item['bname'] = $ginfo['bname'];
        $order_item['sname'] = $ginfo['sname'];
        $order_item['cname'] = $ginfo['cname'];
    }
}else{
    $order_list = array();
}

$r_data['goods'] = $order_list;
$r_data['money'] = $order_item['money'];  //现在直接取的是商品的价格(单价),待改
$r_data['paytime'] = Date("Y-m-d H:i:s",$order_info['paytime']);
$r_data['fhtime'] = isset($order_info['fhtime'])?date("Y-m-d H:i:s",$order_info['fhtime']):" ";

$r_data['total_money'] = $order_item['money']* $order_item['amount'];

if(empty($order_info['kuaidih']))
{
    $order_info['kuaidih'] = '';
}

if(empty($order_info['wlname']))
{
    $order_info['wlname'] = '';
}
// if(empty($order_info['retime']))
// {
//     $order_info['retime'] = '';
// }

//地址详情
$r_data['pid'] = $order_info['pid'];
$r_data['cid'] = $order_info['cid'];
$r_data['qid'] = $order_info['qid'];
$r_data['sname'] = $order_info['sname'];
$r_data['stel'] = $order_info['stel'];
$r_data['info'] = $order_info['info'];
$r_data['kuaidih'] = $order_info['kuaidih'];
$r_data['wlname'] = $order_info['wlname'];
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
$r_data['orderid'] = $order_info['orderid'];
$r_data['addtime'] = date("Y-m-d H:i:s", $order_info['addtime']);

forExit($lock_array, $con);
$return_list['data'] = json_encode($r_data);
toExit(0, $return_list, false);

?>
