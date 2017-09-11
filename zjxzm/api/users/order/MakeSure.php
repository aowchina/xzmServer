<?php
/**
 * 确认订单
 * 接口参数: 8段 * userid * 订单号(orderid)
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
    toExit(35, $return_list);
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

//$userid="10";
//$order_num ="zj149742842210103717390";

//查订单
$count = dbCount('zj_order', $con, "orderid = '".$order_num."' and appuid = $userid and status = 0");
if($count != 1){
    forExit($lock_array, $con);
    toExit(30, $return_list);
}

$sql = "select * from zj_order where orderid = '$order_num'";
$order_info = dbLoad(dbQuery($sql, $con), true);

//获取收货地址
//是否有默认地址
//先看有没有默认地址
$condition = "userid = $userid and isdefault = 1 and is_app= 1";
$count = dbCount('zj_user_address', $con, $condition);
if($count == 1){
    $sql = "select * from zj_user_address where $condition";
    $add_info = dbLoad(dbQuery($sql, $con), true);

    $r_data['address_id']=$add_info['id'];
    $r_data['user_name'] = $add_info['user_name'];
    $r_data['user_tel'] = $add_info['user_tel'];

    $sql = "select areaname from zj_area where id = ".$add_info['user_pid'];
    $pinfo = dbLoad(dbQuery($sql, $con), true);
    $pname = $pinfo['areaname'];

    $sql = "select areaname from zj_area where id = ".$add_info['user_cid'];
    $cinfo = dbLoad(dbQuery($sql, $con), true);
    $cname = $cinfo['areaname'];

    if($add_info['qid'] != 0){
        $sql = "select areaname from zj_area where id = ".$add_info['user_qid'];
        $ainfo = dbLoad(dbQuery($sql, $con), true);
        $aname = ' '.$ainfo['areaname'].' ';
    }else{
        $aname = ' ';
    }

    $r_data['user_address'] = $pname." ".$cname.$aname.$add_info['user_address'];

    $r_data['user_pid'] = $add_info['user_pid'];
    $r_data['user_cid'] = $add_info['user_cid'];
    $r_data['user_qid'] = $add_info['user_qid'];


     //写入订单表
        $data = array();
        $data['pid'] = $add_info['user_pid'];
        $data['cid'] = $add_info['user_cid'];
        $data['qid'] = $add_info['user_qid'];
        $data['sname'] = $add_info['user_name'];
        $data['stel'] = $add_info['user_tel'];
        $data['address'] = $add_info['user_address'];
        dbUpdate($data, 'zj_order', $con, "orderid = '$order_num'");

}
else{
    // 没有默认收货地址
//        forExit($lock_array, $con);
//        toExit(37, $return_list);

    $r_data['user_pid'] = 0;
    $r_data['user_cid'] = 0;
    $r_data['user_qid'] = 0;

    $r_data['user_name'] = '暂未设置';
    $r_data['user_tel'] = '暂未设置';
    $r_data['user_address'] = '暂未设置';
}


//取商品详情
$sql = "select * from zj_order_goods where orderid = '$order_num' order by id asc";
$order_list = dbLoad(dbQuery($sql, $con));

if(count($order_list) > 0){
    foreach($order_list as &$order_item){
//        $sql = "select img,name,oem from zj_goods where goodid = '".$order_item['goodid']."'";
        $sql = "select a.img,a.name,a.oem,d.bname,c.sname,b.cname from zj_good a left join zj_type e on a.typeid=e.typeid left join zj_car b on e.carid=b.carid left join zj_serial c on b.serialid=c.serialid left join zj_brand d on c.brandid= d.brandid where a.goodid = '".$order_item['goodid']."'";

        $ginfo = dbLoad(dbQuery($sql, $con), true);

        $imgs=$ginfo['img'];
        $imgs=json_decode($imgs);
        $img = trim($imgs[0]);
        $order_item['img'] = $s_url.$img;
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
$r_data['addtime'] = date("Y-m-d H:i:s", $order_info['addtime']);

forExit($lock_array, $con);
$return_list['data'] = json_encode($r_data);
toExit(0, $return_list, false);

?>
