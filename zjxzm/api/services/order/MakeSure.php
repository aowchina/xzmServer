<?php
/**
 * 确认订单
 * 接口参数: 8段 * userid * 订单号(order_id)
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

$order_num = trim($reqlist[9]);
if(!preg_match('/^hondo_wx[0-9]+$/', $order_num)){
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


//查订单
$count = dbCount('hd_order', $con, "order_id = '".$order_num."' and userid = $userid and status = 0");
if($count != 1){
    forExit($lock_array, $con);
    toExit(34, $return_list);
}

$sql = "select * from hd_order where order_id = '$order_num'";
$order_info = dbLoad(dbQuery($sql, $con), true);

$sql = "select * from hd_order_goods where order_id = '$order_num' order by id asc";
$order_list = dbLoad(dbQuery($sql, $con));

$total_weight = 0;
if(count($order_list) > 0){
    foreach($order_list as &$order_item){
        $sql = "select simg,name,weight from hd_goods where goods_num = '".$order_item['goods_num']."'";
        $ginfo = dbLoad(dbQuery($sql, $con), true);
        $order_item['simg'] = $s_url.$ginfo['simg'];
        $order_item['name'] = $ginfo['name'];
        $total_weight = $total_weight + $ginfo['weight'] * $order_item['amount'];
    }
}else{
    $order_list = array();
}

$r_data['goods'] = $order_list;
$r_data['price'] = $order_info['price'];

//计算物流费用和物流名称
if(!empty($order_info['wl_id']))
{
    $sql = "select  name,type from hd_wl where id = $order_info[wl_id]";
    $wl = dbLoad(dbQuery($sql, $con),true);
    if(empty($wl['name']))
    {
        $r_data['wl_type'] = '';//物流名称返回
    }else{
        if($wl['type']==2){
           //自提时取仓库名称
            $sql = "select  a.name from hd_cang a left join hd_order b on a.id=b.cang_id where order_id = '$order_num'";
            $wl = dbLoad(dbQuery($sql, $con), true);
            $r_data['wl_type'] =$wl['name'] ;
        }
        $r_data['wl_type'] = $wl['name'];
    }

}else{
    $r_data['wl_type'] = " ";
}
//else
//{
//   if($order_info['wl_id']==0) {
//
//          //仓库名称不存在
//        if($order_info['cang_id']==0){
//            $r_data['wl_type'] =" " ;
//           }else{
//            //物流id等于0时为自提,取仓库名称
//           $sql = "select  a.name from hd_cang a left join hd_order b on a.id=b.cang_id where order_id = '$order_num'";
//           $wl = dbLoad(dbQuery($sql, $con), true);
//           $r_data['wl_type'] =$wl['name'] ;
//           }
//


//返回订单备注
if(empty($order_info['user_info']))
{
    $order_info['user_info'] = '';
}
$r_data['wl_price'] = $order_info['wl_price'];

//取出用户的余额
$sql = "select money from hd_wallet where userid = $userid";
$user_money = dbLoad(dbQuery($sql, $con), true);
if(empty($user_money))
{
    $r_data['user_money'] = 0;
}
else
{
    $r_data['user_money'] = $user_money['money'];
}

//获取收货地址
if($order_info['user_pid'] == 0){
    //先看有没有默认地址
    $condition = "userid = $userid and isdefault = 1";
    $count = dbCount('hd_user_address', $con, $condition);
    if($count == 1){
        $sql = "select * from hd_user_address where $condition";
        $add_info = dbLoad(dbQuery($sql, $con), true);

        $r_data['user_name'] = $add_info['user_name'];
        $r_data['user_tel'] = $add_info['user_tel'];

        $sql = "select areaname from hd_area where id = ".$add_info['user_pid'];
        $pinfo = dbLoad(dbQuery($sql, $con), true);
        $pname = $pinfo['areaname'];

        $sql = "select areaname from hd_area where id = ".$add_info['user_cid'];
        $cinfo = dbLoad(dbQuery($sql, $con), true);
        $cname = $cinfo['areaname'];

        if($add_info['user_qid'] != 0){
            $sql = "select areaname from hd_area where id = ".$add_info['user_qid'];
            $ainfo = dbLoad(dbQuery($sql, $con), true);
            $aname = ' '.$ainfo['areaname'].' ';
        }else{
            $aname = ' ';
        }

        $r_data['user_address'] = $pname." ".$cname.$aname.$add_info['user_address'];

        $r_data['user_pid'] = $add_info['user_pid'];
        $r_data['user_cid'] = $add_info['user_cid'];
        $r_data['user_qid'] = $add_info['user_qid'];

        $r_data['user_name'] = $add_info['user_name'];
        $r_data['user_tel'] = $add_info['user_tel'];

        $data = array();
        $data['user_pid'] = $add_info['user_pid'];
        $data['user_cid'] = $add_info['user_cid'];
        $data['user_qid'] = $add_info['user_qid'];
        $data['user_name'] = $add_info['user_name'];
        $data['user_tel'] = $add_info['user_tel'];
        $data['user_address'] = $add_info['user_address'];
        dbUpdate($data, 'hd_order', $con, "order_id = '$order_num'");
    }
    else{
        $r_data['user_pid'] = 0;
        $r_data['user_cid'] = 0;
        $r_data['user_qid'] = 0;

        $r_data['user_name'] = '暂未设置';
        $r_data['user_tel'] = '暂未设置';
        $r_data['user_address'] = '暂未设置';
    }
}
else{
    $r_data['user_pid'] = $order_info['user_pid'];
    $r_data['user_cid'] = $order_info['user_cid'];
    $r_data['user_qid'] = $order_info['user_qid'];
    $r_data['user_name'] = $order_info['user_name'];
    $r_data['user_tel'] = $order_info['user_tel'];

    $sql = "select areaname from hd_area where id = ".$order_info['user_pid'];
    $pinfo = dbLoad(dbQuery($sql, $con), true);
    $pname = $pinfo['areaname'];

    $sql = "select areaname from hd_area where id = ".$order_info['user_cid'];
    $cinfo = dbLoad(dbQuery($sql, $con), true);
    $cname = $cinfo['areaname'];

    if($order_info['user_qid'] != 0){
        $sql = "select areaname from hd_area where id = ".$order_info['user_qid'];
        $ainfo = dbLoad(dbQuery($sql, $con), true);
        $aname = ' '.$ainfo['areaname'].' ';
    }else{
        $aname = ' ';
    }
    $r_data['user_address'] = $pname." ".$cname.$aname.$order_info['user_address'];
}

forExit($lock_array, $con);
$return_list['data'] = json_encode($r_data);
toExit(0, $return_list, false);

?>
