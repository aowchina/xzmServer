<?php
/**
 * 待付款订单取消
 * 接口参数: 8段 * userid * 订单号(orderid)
 * author zq
 * date 2017-6-19
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

////模拟参数
//$order_num = 'zj149742829110109192175';
//$userid = 10;
///**/
$count = dbCount('zj_order', $con, "orderid = '".$order_num."' and appuid = $userid ");

if($count != 1){
    forExit($lock_array, $con);
    toExit(30, $return_list);
}
//取出这个订单的状态
$sql = 'select status,money,addtime,paytime,stel,sname,order_dsfid from zj_order where orderid = "'.$order_num.'" and appuid = '.$userid;
$order_status = dbLoad(dbQuery($sql, $con),true);

//$sql = "select name from hd_wl where id = $order_status[wl_id]";
//$wl_name = dbLoad(dbQuery($sql, $con), true);
//判断订单是否可以取消
if(!in_array($order_status['status'],[0,1]))
{
    forExit($lock_array, $con);
    toExit(49, $return_list);
}
$time = time();
$datetime = date('Y-m-d H:i:s',$time);
if($order_status['status'] == 0)
{

    $sql = "delete from zj_order where orderid= '$order_num' and appuid ='$userid'";
    if(!dbQuery($sql, $con)){
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }

}
else
{
    $count = dbCount('zj_wallet', $con, "userid = $userid and tid=1 ");

    //开启事物
    dbQuery("START TRANSACTION", $con);
    if( $count == 0)
    {

        $sql = "insert into zj_wallet values(null,$userid,'1',$order_status[money],$time)";
    }
    else
    {
        $sql="select money from zj_wallet where userid=$userid and tid=1";
        $result  = dbLoad(dbQuery($sql, $con),true);

        $sql = "update zj_wallet set money = $result[money] + $order_status[money] where userid = $userid and tid=1";
    }
    $in_money = dbQuery($sql, $con);


    $sql = "update zj_order set status = 4,retime = $time where orderid = '$order_num' and appuid = $userid ";
    $u_money = dbQuery($sql, $con);

    //提现钱减少写到钱包记录表
    $wr_in['userid'] = $userid;
    $wr_in['tid'] = "1";
    $wr_in['addtime'] = $time;
    $wr_in['money'] = $order_status['money'];
    $wr_in['type'] = 1;
    $resource= dbAdd($wr_in, 'zj_wrecord', $con);

    if($in_money && $u_money && $resource)
    {
        dbQuery("COMMIT", $con);
    }
    else
    {
        dbQuery("ROLLBACK", $con);
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }

}


forExit($lock_array, $con);
toExit(0, $return_list, false);

?>
