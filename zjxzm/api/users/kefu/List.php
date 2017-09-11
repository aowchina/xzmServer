<?php
/**
 * 客服列表
 * 接口参数: 8段 $userid
 * author zq
 * date 2017-06-08
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

if(!(count($reqlist) == 9)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证userid
$userid = intval(trim($reqlist[8]));
if(!($userid >= 1)){
    forExit($lock_array);
    toExit(10, $return_list);
}

//userid打锁
$user_cpath = getSubPath($userid, 4, true);
$user_path = $j_path.'user/'.$user_cpath;
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

$sql = 'select picture,name,tel,sellerid from zj_seller where type=2';
$list = dbLoad(dbQuery($sql,$con));

if($list){
    //处理图片地址
    foreach ($list as $key => $value) {
        $list[$key]['picture'] = $s_url.$value['picture'];
    }
}

forExit($lock_array, $con);
$return_list['data'] = json_encode($list);
toExit(0, $return_list, false);




