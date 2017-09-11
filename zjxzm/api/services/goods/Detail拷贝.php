<?php
/**
 * 商品详情
 * 接口参数: 8段 * userid * 商品id
 * author pwj
 * date 2017-06-01
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证货号
$goodid = trim($reqlist[9]);
if($goodid < 1 || $goodid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
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


//验证商品是否存在
$where = "goodid = $goodid ";
$count = dbCount('zj_good', $con, $where);
if($count != 1)
{
    forExit($lock_array, $con);
    toExit(30, $return_list);
}

//获取商品信息

$sql = "select a.name,a.price,a.img,a.carid,a.typeid,a.oem,a.num,a.tel,b.name as tname ,a.detail from zj_good as a left join zj_pt as b on a.typeid = b.id where a.goodid = $goodid  order by a.addtime desc ";
$goods = dbLoad(dbQuery($sql, $con),true);

$sql = "select GROUP_CONCAT(cname SEPARATOR ' ') as cname,serialid from zj_car where carid in ($goods[carid])";
$cname = dbLoad(dbQuery($sql, $con),true);

$sql = "select brandid,sname from zj_serial where serialid  = $cname[serialid]";
$sname = dbLoad(dbQuery($sql, $con),true);

$sql = "select bname from zj_brand where brandid  = $sname[brandid]";
$bname = dbLoad(dbQuery($sql, $con),true);
$goods['car_name'] = $bname['bname'].' '.$sname['sname'].' '.$cname['cname'];
if(!empty($goods['img']))
{
    $imgs = json_decode($goods['img']);
    $goods['img'] = $imgs;
}
else
{
    $goods['img'] = [];
}

$ginfo['info'] = $goods;
forExit($lock_array, $con);
$return_list['data'] = json_encode($ginfo);
toExit(0, $return_list, false);

?>
