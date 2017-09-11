<?php
/**
 * 用户登录(电话验证码)
 * 接口参数: 8段 * 用户名(手机号) * 验证码
 * author pwj
 * date 2015-11-13
 */

include_once("../functions_mut.php");
include_once("../functions_mcheck.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证用户名(用户名就是手机号)
$user = trim($reqlist[8]);
if(!isMobel($user)){
    forExit($lock_array);
    toExit(13, $return_list);
}

$code = trim($reqlist[9]);
if(!preg_match("/^[0-9]{4}$/", $code)){
    forExit($lock_array);
    toExit(50, $return_list);
}
//用户名打锁
$user_lockname = $j_path.'lock/'.$user;
if(is_file($user_lockname)){
    forExit($lock_array);
    toExit(15, $return_list);
}
if(!file_put_contents($user_lockname, " ", LOCK_EX)){
    forExit($lock_array);
    toExit(15, $return_list);
}
$lock_array[] = $user_lockname;

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

//用户名是否存在
$condition = "tel = '$user'";
$count = dbCount('zj_appuser', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(16, $return_list);
}

/***************pwj......(还发送个毛！！！！)开始*********************/
//发送验证码并验证
// $createCode = createCode();
// $result = sendMsg($user, $createCode);
// if($result === true)
// {
//     if($createCode != $code)
//     {
//         forExit($lock_array, $con);
//         toExit(49, $return_list);
//     }
// }
// else
// {
//     forExit($lock_array, $con);
//     toExit(51, $return_list);
// }
/***************pwj......(还发送个毛！！！！)结束*********************/

/******************************验证码验证开始mo_yu***************************************/
//获取存在文件里面的验证码
$filePath = $j_path . 'tel/' . getSubPath($user, 4, true).'/code';
$getCodeByFile = file_get_contents($filePath);

//判断是否从文件中获取到验证码
if(!$getCodeByFile){
    forExit($lock_array, $con);
    toExit(90, $return_list, true);//验证码失效，需重新请求发送验证码接口
}

//验证输入的验证码是否正确
if($code != $getCodeByFile){
    forExit($lock_array, $con);
    toExit(49, $return_list);// 验证码错误
}
/******************************验证码验证结束mo_yu***************************************/

$sql = "select appuid,name,picture from zj_appuser where ".$condition;
$now_userinfo = dbLoad(dbQuery($sql, $con), true);
$userid = $now_userinfo['appuid'];

//检查是否有其它设备登录此号
$condition = "userid = $userid and deviceid != '".$deviceid."' and status = 1 and is_app = 1";
$count = dbCount('zj_user_login', $con, $condition);
if($count > 0){
    $data_out['status'] = 0;
    dbUpdate($data_out, 'zj_user_login', $con, $condition);
}

//检查是否有其它人在此设备已登录
$condition = "userid != $userid and deviceid = '".$deviceid."' and status = 1 and is_app = 1";
$count = dbCount('zj_user_login', $con, $condition);
if($count > 0){
    $data_out['status'] = 0;
    dbUpdate($data_out, 'zj_user_login', $con, $condition);
}

//更新当前用户登录状态
$condition = "userid = $userid and deviceid = '$deviceid' and is_app = 1";
$count = dbCount('zj_user_login', $con, $condition);

if($count == 1){
    $data_in['status'] = 1;
    dbUpdate($data_in, 'zj_user_login', $con, $condition);
}else{
    $data_in['status'] = 1;
    $data_in['is_app'] = 1;
    $data_in['userid'] = $userid;
    $data_in['deviceid'] = $deviceid;
    dbAdd($data_in, 'zj_user_login', $con);
}

//更新登录时间
$data = array();
$data['lastvisitDate'] = time();
dbUpdate($data, 'zj_appuser', $con, "appuid = $userid");

$user_path = $j_path.'user/'.getSubPath($userid, 4, true);
if(!is_dir($user_path)){
    mkdirs($user_path);
}
/***********登录返回开始*************/
//返回参数
$data = array();
$data['userid'] = $userid;
$data['nickname'] = $now_userinfo['name'] ? $now_userinfo['name'] : " ";
$data['picture'] = empty($now_userinfo['picture']) ? '' : $s_url.$now_userinfo['picture'];
/***********登录返回结束*************/
forExit($lock_array, $con);
$return_list['data'] = json_encode($data);
toExit(0, $return_list, true);
?>