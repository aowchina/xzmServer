<?php
/**
 * 密码找回
 * 接口参数: 8段 * 用户名
 * author wangrui@min-fo.com
 * date 2015-11-13
 */
include_once("../functions_mut.php");
include_once("../functions_mcheck.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 9)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证用户名
$user = trim($reqlist[8]);
if(!isMobel($user)){
    forExit($lock_array);
    toExit(10, $return_list);
}

//用户名打锁
$user_lockname = $j_path.'lock/'.$user;
if(is_file($user_lockname)){
    forExit($lock_array);
    toExit(12, $return_list);
}
if(!file_put_contents($user_lockname, " ", LOCK_EX)){
    forExit($lock_array);
    toExit(12, $return_list);
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
$condition = "username = '$user'";
$count = dbCount('hd_users', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(13, $return_list);
}

//查询找回次数
$sql = "select id from hd_users where $condition";
$re = dbLoad(dbQuery($sql, $con), true);
$userid = $re['id'];

$user_path = $j_path.'user/'.getSubPath($userid, 4, true);
if(!is_dir($user_path)){
    mkdirs($user_path);
}

$get_psw_file = $user_path.'getpsw';
if(is_file($get_psw_file)){
    $get_record = json_decode(file_get_contents($get_psw_file), true);
}
else{
    $get_record = array();
}
$now_time = time();
$now_date = date("Y-m-d", $now_time);

if(isset($get_record[$now_date])){
    if(count($get_record[$now_date]) > 2){
        forExit($lock_array, $con);
        toExit(22, $return_list);
    }
}

$get_record[$now_date][] = $now_time;

//生成新密码
$new_psw = password_hash($user, PASSWORD_DEFAULT);

//发送短信
include "../duanxin/TopSdk.php";
$c = new TopClient;
$c->appkey = '23409451';
$c->secretKey = '516c90935138d7b965cf98de4cc5aa03';
$c->format = "json";

$req = new AlibabaAliqinFcSmsNumSendRequest;
$req ->setExtend("");
$req->setSmsType("normal");
$req->setSmsFreeSignName("恒都生鲜");
$req->setSmsParam("{psw:'$user'}");
$req->setRecNum($user);
$req->setSmsTemplateCode("SMS_13056600");
$resp = $c->execute($req);

$a = json_encode($resp);
$b = json_decode($a, true);

if(isset($b['result']) && $b['result']['err_code'] == 0){
    $data = array();
    $data['password'] = $new_psw;
    $data['lastResetTime'] = $now_date = date("Y-m-d H:i:s", $now_time);
    dbUpdate($data, 'hd_users', $con, $condition);

    file_put_contents($get_psw_file, json_encode($get_record));

    forExit($lock_array, $con);
    toExit(0, $return_list);
}
else{
    forExit($lock_array, $con);
    toExit(23, $return_list);
}

?>
