<?php
/**
 * 删除配件
 * 接口参数: 8段 * userid * 商品id
 * author pwj
 * date 2017-06-06
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证商品id
$goodid = trim($reqlist[9]);
if($goodid < 1 || $goodid > 4294967296){
    forExit($lock_array);
    toExit(50, $return_list);
}
//验证userid
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
$lock_array[] = $user_path."lock";
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
/*测试数据
$userid = 1;
$goodid = 5;
*/

//取出店铺id
$sql = "select shopid from zj_shop where sellerid = $userid and state = 1";
$shopid =  dbLoad(dbQuery($sql, $con), true);
if(empty($shopid))
{
    forExit($lock_array, $con);
    toExit(56, $return_list);
}

//取出这件商品的图片
$condition = "where goodid = $goodid and shopid = $shopid[shopid]";
$sql = "select img from zj_good $condition";
$goodImg = dbLoad(dbQuery($sql, $con), true);

//删除商品成功后删除图片
$sql = "delete from zj_good $condition";
if(!dbQuery($sql, $con))
{
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

$imgs = json_decode($goodImg['img']);
foreach($imgs as $v)
{
    if(is_file($s_path.$v))
    {
        unlink($s_path.$v);
    }
}


forExit($lock_array, $con);
toExit(0, $return_list);
?>