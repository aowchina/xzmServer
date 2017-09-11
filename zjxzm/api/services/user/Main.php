<?php
/**
 * 个人中心首页
 * 接口参数: 8段 * userid
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
$userid = trim($reqlist[8]);
if($userid < 1 || $userid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
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

//获取当前用户信息
$condition = "sellerid = $userid";
$sql = "select name,picture from zj_seller where $condition";
$user_info = dbLoad(dbQuery($sql, $con), true);
$re_list = array();
$re_list['name'] = $user_info['name'];
//图片处理
if(empty($user_info['picture']))
{
    $img = '';
}
else
{
    if(substr($user_info['picture'],0,4) == 'http')
    {
        $img = $user_info['picture'];
    }
    else
    {
        $img = $s_url.$user_info['picture'];
    }
}
$re_list['img'] = $img;

$re_list['version'] = 1;
$re_list['down_url'] = 'http://zjxzm.min-fo.com/xzmps.apk';

$return_list['data'] = json_encode($re_list);
forExit($lock_array, $con);
toExit(0, $return_list);

?>
