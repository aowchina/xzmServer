<?php
/**
 * 配件商商品列表
 * 接口参数: 8段 * userid * 状态(2:审核中 1:审核通过出售中 0:审核不通过 3 :已下架) * page
 * author pwj
 * date 2017-06-01
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 11)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证页码
$state = intval(trim($reqlist[9]));
if(!in_array($state, [0,1,2,3]))
{
    forExit($lock_array);
    toExit(26, $return_list);
}
//验证页码
$page = intval(trim($reqlist[10]));
if($page < 1){
    forExit($lock_array);
    toExit(25, $return_list);
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
$state = 2;
*/
//取出店铺id
$sql = "select shopid from zj_shop where sellerid = $userid and state = 1";
$shopid =  dbLoad(dbQuery($sql, $con), true);
if(empty($shopid))
{
    forExit($lock_array, $con);
    toExit(56, $return_list);
}

if($state ==3)
{
    $where = "shopid = $shopid[shopid] and state = 1 and is_sj = 0";
}
elseif($state == 2){
    $where = "shopid = $shopid[shopid] and state = 2 and is_sj = 2";
}elseif ($state == 0) {
    $where = "shopid = $shopid[shopid] and state = 0 and is_sj = 0";
}else
{
    $where = "shopid = $shopid[shopid] and state = $state and is_sj = 1";
}
$offset = ($page - 1) * 10;
//取出自己店铺的产品
$sql = "select goodid,name,price,img,carid,addtime from zj_good where $where order by addtime desc  limit $offset,10";

$list = dbLoad(dbQuery($sql, $con));

foreach($list as &$v)
{

    $sql = "select GROUP_CONCAT(cname SEPARATOR ' ') as cname,serialid from zj_car where carid in ($v[carid])";
    $cname = dbLoad(dbQuery($sql, $con),true);

    $sql = "select brandid,sname from zj_serial where serialid  = $cname[serialid]";
    $sname = dbLoad(dbQuery($sql, $con),true);

    $sql = "select bname from zj_brand where brandid  = $sname[brandid]";
    $bname = dbLoad(dbQuery($sql, $con),true);
    $v['car_name'] = $bname['bname'].' '.$sname['sname'].' '.$cname['cname'];
    if(!empty($v['img']))
    {
        $v['img'] = $s_url.json_decode($v['img'])[0];
    }
    $v['addtime'] = date('m',$v['addtime']). '/'.date('d',$v['addtime']);
}

$r_list['goods'] = $list;

forExit($lock_array, $con);
$return_list['data'] = json_encode($r_list);
toExit(0, $return_list, false);

?>
