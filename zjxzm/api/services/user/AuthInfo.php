<?php
/**
 * 获取认证详情
 * 接口参数 sellerid * 认证id
 * return 成功    * 
 * author moyu
 * date 2017-06-21 2-34
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
 if(!(count($reqlist) == 9)){
     forExit($lock_array);
     toExit(9, $return_list);
 }

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

//验证sellerid
 $userid = intval(trim($reqlist[8]));
 if(!($userid >= 1)){
     forExit($lock_array);
     toExit(10, $return_list);
 }
//userid打锁
$user_path = $j_path.'user/'.getSubPath($userid, 4, true);
if(!is_dir($user_path)){
    forExit($lock_array);
    toExit(11, $return_list);
}
if(is_file($user_path.'lock')){
    forExit($lock_array);
    toExit(11, $return_list);
}
if(!file_put_contents($user_path.'lock', " ", LOCK_EX)){
    forExit($lock_array);
    toExit(11, $return_list);
}
$lock_array[] = $user_path.'lock';

//$userid = $_POST["sellerId"];

//查询认证详情sname,shopid,major,skill,picture,type,number
$sql = "select * from zj_sellercert where sellerid = ".$userid;
$sellerDetalil = dbLoad(dbQuery($sql, $con),true);

//认证信息详情

if($sellerDetalil){//不为空,认证已申请提交
    $host = $s_url;//服务器地址

    //地址拼接
    $sellerDetalil['picture'] = $host.$sellerDetalil['picture'];
    $sellerDetalil['cardfront'] = $host.$sellerDetalil['cardfront'];
    $sellerDetalil['cardback'] = $host.$sellerDetalil['cardback'];
    $sellerDetalil['cardhand'] = $host.$sellerDetalil['cardhand'];
    $sellerDetalil['license'] = $host.$sellerDetalil['license'];

    //查看卖家用户认证是否通过
    $sql = "select is_rz from zj_seller WHERE sellerid = ".$userid;
    $is_rz = dbLoad(dbQuery($sql,$con),true)['is_rz'];

    $sellerDetalil['status'] = $is_rz;

    $sellerDetalil = json_encode($sellerDetalil);
}else{
    $sellerDetalil="";
}


forExit($lock_array, $con);
$return_list['data'] = $sellerDetalil;
toExit(0, $return_list, false);
?>