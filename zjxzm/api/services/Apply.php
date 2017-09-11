<?php
/**
 * 用户申请(注册)
 * 接口参数: 8段 * 手机号 * 密码 * 确认密码 * 短信验证码 * 用户类别
 * author pwj
 * date 2017-06-02
 */
include_once("../Easemob.class.php");
include_once ("../Common.php");

//验证参数个数
if(!(count($reqlist) == 13)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$tel = trim($reqlist[8]);
if(!isMobel($tel)){
    forExit($lock_array);
    toExit(13, $return_list);
}

$psw = trim($reqlist[9]);
if(!isPsw($psw)){
    forExit($lock_array);
    toExit(14, $return_list);
}

$psw2 = trim($reqlist[10]);
if($psw != $psw2){
    forExit($lock_array);
    toExit(18, $return_list);
}

$code = trim($reqlist[11]);
if(!preg_match("/^[0-9]{4}$/", $code)){
    forExit($lock_array);
    toExit(50, $return_list);
}

$tel_path = $j_path.'tel/'.getSubPath($tel, 4, true);
$code_file = $tel_path.'code';
if(!is_file($code_file)){
    forExit($lock_array);
    toExit(51, $return_list);
}

if($code != file_get_contents($code_file)){
    forExit($lock_array);
    toExit(51, $return_list);
}

$type = trim($reqlist[12]);
if(in_array($type,[1,2,3,4]))
{
    forExit($lock_array);
    toExit(52, $return_list);
}

//用户打锁
$user_lockname = $j_path.'lock/'.$tel;
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


$nowtime = time();

//手机号是否存在
$condition = "tel = '$tel'";
$count = dbCount('zj_seller', $con, $condition);
if($count > 0){
    forExit($lock_array, $con);
    toExit(19, $return_list);
}

$data['name'] = $tel;
$data['tel'] = $tel;
$data['password'] = password_hash($psw, PASSWORD_DEFAULT);
$data['addtime']  = $nowtime;
$data['type']  = $type;

if(!dbAdd($data, 'zj_seller', $con)){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

// $userid='1';$psw = '123';

$sql = "select sellerid from zj_seller where tel = '$tel'";
$result = dbLoad(dbQuery($sql, $con), true);
$userid = $result['sellerid'];

$sql = "select name from zj_seller where sellerid=".$userid;
$name = dbLoad(dbQuery($sql,$con),true)['name'];

$class = new Common($option=array());
$hx = $class->hx();

//注册到环信
$hx = new Easemob($hx);
$create_result = $hx->createUser($userid, $tel, $name);
// var_dump($create_result);exit;
if(isset($create_result['error'])){
    if($create_result['error'] != 'duplicate_unique_property_exists'){
        forExit($lock_array, $con);
        toExit(11, $return_list);
    }
    else{
        //重置环信登录密码与昵称
        $hx->resetPassword($userid, $tel);
        $hx->editNickname($userid, $name);
    }
}


$user_path = $j_path.'user/'.getSubPath($userid, 4, true);
if(!is_dir($user_path)){
    mkdirs($user_path);
}


//检查是否有其它设备登录此号
$condition = "userid = $userid and deviceid != '".$deviceid."' and status = 1 and is_app = 0";
$count = dbCount('zj_user_login', $con, $condition);
if($count > 0){
    $data_out['status'] = 0;
    dbUpdate($data_out, 'zj_user_login', $con, $condition);
}

//检查是否有其它人在此设备已登录
$condition = "userid != $userid and deviceid = '".$deviceid."' and status = 1";
$count = dbCount('zj_user_login', $con, $condition);
if($count > 0){
    $data_out['status'] = 0;
    dbUpdate($data_out, 'zj_user_login', $con, $condition);
}

//更新当前用户登录状态
$condition = "userid = $userid and deviceid = '$deviceid' and is_app = 0";
$count = dbCount('zj_user_login', $con, $condition);

if($count == 1){
    $data_in['status'] = 1;
    dbUpdate($data_in, 'zj_user_login', $con, $condition);
}else{
    $data_in['status'] = 1;
    $data_in['is_app'] = 0;
    $data_in['userid'] = $userid;
    $data_in['deviceid'] = $deviceid;
    dbAdd($data_in, 'zj_user_login', $con);
}

$return_list['data'] = $userid;
forExit($lock_array, $con);
toExit(0, $return_list);

?>
