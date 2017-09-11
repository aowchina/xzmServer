<?php
/**
 * 说明文档(1:帮助中心 2:法律中心 3:关于我们)
 * 接口参数: 8段 * sellerid(用户id) * type
 * author zq
 * date 2017-06-9
 * return
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证userid

$userid = trim($reqlist[8]);
if($userid < 1 || $userid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
}

$type=trim($reqlist[9]);
if($type>4 ||$type <0){
     forExit($lock_array);
     toExit(19, $return_list);
 }

//打用户锁
$user_path = $j_path.'user/'.getSubPath($userid, 4, true);
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
$condition = "sellerid = '$userid'";
$count = dbCount('zj_seller', $con, $condition);

if($count != 1) {
    forExit($lock_array, $con);
    toExit(11, $return_list);
}

//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=0";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}


//取帮助中心
if($type==1){
    $condition = "type =1";
    $count = dbCount('zj_pcenter', $con, $condition);
    if($count >=1 ){
        $sql="select name,url from zj_pcenter where type=1";
        $data= dbLoad(dbQuery($sql, $con), true);
        $res=array($data);

    }else{
        forExit($lock_array, $con);
        toExit(23, $return_list);
    }

}

//2取法律中心
if($type==2){
    $condition = "type =2";
    $count = dbCount('zj_pcenter', $con, $condition);
    if($count >=1 ){
        $sql="select url from zj_pcenter where type=2";
        $res= dbLoad(dbQuery($sql, $con), true);

    }else{
        forExit($lock_array, $con);
        toExit(24, $return_list);
    }
}

//取关于我们
if($type==3){
    $condition = "type =3";
    $count = dbCount('zj_pcenter', $con, $condition);
    if($count >=1 ){
        $sql="select url from zj_pcenter where type=3";
        $res= dbLoad(dbQuery($sql, $con), true);

    }else{
        forExit($lock_array, $con);
        toExit(25, $return_list);
    }
}

$return_list['data'] = json_encode($res);
forExit($lock_array, $con);
toExit(0, $return_list);

?>
