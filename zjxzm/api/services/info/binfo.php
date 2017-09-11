<?php
/**
 * 企业信息管理接口
 * param: 8段 * userid
 * author: zhangqin
 * date:2017-2-9
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");

//验证参数个数
if(!(count($reqlist)==9)){
    toExit(9,$return_list);
}

//验证userid
$userid=trim($reqlist[8]);
if($userid <1 || $userid > 4294967296){
	toExit(10,$return_list);

}

//打用户锁
 $user_path=$j_path.'user/'.getSubPath($userid,3,true);
if(!mkdirs($user_path)){
    toExit(11, $return_list);
}

if(is_file($user_path."lock")){
    toExit(11, $return_list);
}
if(!file_put_contents($user_path."lock", " ", LOCK_EX)){
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
$condition = "userid = '$userid' and deviceid ='$deviceid' and status = 1";
$count = dbCount('hd_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

//获取企业信息
$sql = "select infoadd,imageadd from hd_info";
$add = dbLoad(dbQuery($sql, $con));

if($add){
    $info_result = $add['0']['infoadd'];
    $image_result = $add['0']['imageadd'];
    $infoadd['info'] = file_get_contents($info_result);
    $infoadd['image'] = $s_url.$image_result;

}else{
    forExit($lock_array, $con);
    toExit(302, $return_list);
}


$result['introduce']=$infoadd;


forExit($lock_array, $con);
$return_list['data'] = json_encode($result);
toExit(0, $return_list);


?>