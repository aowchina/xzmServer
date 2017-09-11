<?php
/**
 * 确认收货
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
if(!preg_match('/^zjqg[0-9]+$/', $order_num)){
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

//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=1";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

$count = dbCount('zj_qgorder', $con, "qgorderid = '".$order_num."' and appuid = $userid and status = 2");
if($count != 1){
    forExit($lock_array, $con);
    toExit(30, $return_list);
}


//开启事务
 $sw = dbQuery("start transaction", $con);
$data = array();
$data['status'] = 4;
$data['ifreceive'] = 1;
$data['retime']=time();
$resource1 = dbUpdate($data, "zj_qgorder", $con, "qgorderid = '$order_num'");
  
//写入平台账单表,用于后期给平台打款
$b_data=array();
$b_data['state'] = 0;
$b_data['orderid'] = $order_num;
$resource2 = dbAdd($b_data, 'zj_bill', $con);
  
  if($resource1 && $resource2)
  {
       dbQuery("COMMIT", $con);
       forExit($lock_array, $con);
       toExit(0, $return_list);
  }else{
            dbQuery("ROLLBACK", $con);
            forExit($lock_array, $con);
            toExit(39, $return_list);
        }


?>
