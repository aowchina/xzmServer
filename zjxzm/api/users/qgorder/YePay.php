<?php
/** 
 * 获取支付信息(余额支付)
 * 参数：8段 * userid * qgorderid
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}


$orderid = trim($reqlist[9]);
if(!preg_match('/^zjqg[0-9]+$/', $orderid)){
    forExit($lock_array);
    toExit(29, $return_list);
}

////验证userid
//$userid =10;
//$orderid ="zj149742842210103717390";

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

//用户是否存在
$condition = "appuid = '$userid'";
$count = dbCount('zj_appuser', $con, $condition);
if($count != 1) {
    forExit($lock_array, $con);
    toExit(11, $return_list);
}


//订单是否存在
$count = dbCount("zj_qgorder", $con, "qgorderid = '$orderid' and appuid = $userid and status = 0");
if($count != 1){
    forExit($lock_array, $con);
    toExit(30, $return_list);
}


$sql = "select price,pid from zj_qgorder where qgorderid = '$orderid'";
$order_info = dbLoad(dbQuery($sql, $con), true);
//判断是否有地址
if(empty($order_info['pid'])){
    forExit($lock_array, $con);
    toExit(37, $return_list);
}

//算总价
$prices = $order_info['price'];
$price = explode(',',$prices);
$money= array_sum($price);
//$money= '30.00';
//余额支付

//查出此用户的余额
$sql="select money from zj_wallet where userid=".$userid ." and tid= 1";
$result=dbLoad(dbQuery($sql, $con),true);
if($result){
    //if(余额>=支付款){用双精度做比较
    if(bccomp($result['money'],$money)>=0) {
        //可用余额支付
        //用户钱包余额做减法
       //开启事务
        $sw = dbQuery("start transaction", $con);
        $time=time();
        //更新用户钱包
        $where = "userid = $userid";
        $data['money'] = $result['money'] - $money;
        $update['money']=$data['money'];
        $resource = dbUpdate($update,'zj_wallet', $con, $where);

        //支付成功后修改订单状态,支付方式为余额支付(3)
        $res['status']= "1";
        $res['paytype']= "3";
        $res['paytime']= $time;

        $resource2 = dbUpdate($res, 'zj_qgorder', $con, "qgorderid = '$orderid'");
        
          //提现钱减少写到钱包记录表
        $wr_in['userid'] = $userid;
        $wr_in['tid'] = "1";
        $wr_in['addtime'] = $time;
        $wr_in['money'] = $money;
        $wr_in['type'] = 2;
        $resource3= dbAdd($wr_in, 'zj_wrecord', $con);


        if($resource && $resource2 && $resource3){
            dbQuery("COMMIT", $con);

            forExit($lock_array, $con);
            toExit(0, $return_list);
        }else{
            dbQuery("ROLLBACK", $con);
            forExit($lock_array, $con);
            toExit(39, $return_list);
        }

    }else{

        //余额不足,请更换支付方式
        forExit($lock_array, $con);
        toExit(40, $return_list);
}

 }else{
    //该用户钱包没有记录,0元
    forExit($lock_array, $con);
    toExit(38, $return_list);
}

?>