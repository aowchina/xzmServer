<?php
/**
 * 收货地址列表
 * 接口参数：8段 * userid
 * author pwj
 * date 2017-06-03
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
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


//验证userid是否存在
$count = dbCount("zj_seller", $con, "sellerid = $userid");
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

//获取地址列表
$sql = "select * from zj_user_address where userid = $userid and is_app = 0";
$list = dbLoad(dbQuery($sql, $con));

if(count($list) > 0){
    foreach($list as &$item){
        $sql = "select areaname from zj_area where id = ".$item['user_pid'];
        $pinfo = dbLoad(dbQuery($sql, $con), true);
        $item['pname'] = $pinfo['areaname'];

        $sql = "select areaname from zj_area where id = ".$item['user_cid'];
        $cinfo = dbLoad(dbQuery($sql, $con), true);
        $item['cname'] = $cinfo['areaname'];

        if($item['user_qid'] != 0){
            $sql = "select areaname from zj_area where id = ".$item['user_qid'];
            $ainfo = dbLoad(dbQuery($sql, $con), true);
            $item['aname'] = $ainfo['areaname'].' ';
        }else{
            $item['aname'] = ' ';
        }
    }
}else{
    $list = array();
}

forExit($lock_array, $con);
$return_list['data'] = json_encode($list);
toExit(0, $return_list, false);

?>
