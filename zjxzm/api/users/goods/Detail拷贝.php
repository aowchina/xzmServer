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
if($goodid  < 1 || $goodid  > 4294967296){
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
/*测试数据*/

$where = "goodid = $goodid and state = 1 and is_sj = 1";
$count = dbCount('zj_good', $con, $where);
if($count != 1)
{
    forExit($lock_array, $con);
    toExit(30, $return_list);
}

//获取商品信息
$field = "a.goodid, a.name, a.img, a.price, a.oem, a.num, b.cname, c.sname, d.bname, d.blogo, a.shopid";
$from = "zj_good as a left join zj_type e on a.typeid=e.typeid left join zj_car as b on e.carid = b.carid left join zj_serial as c on b.serialid = c.serialid left join zj_brand as d on c.brandid = d.brandid";
$where = $where;
$sql = "select $field from $from where $where";

$goods = dbLoad(dbQuery($sql, $con),true);
$goods['blogo'] = $surl. $goods['blogo'];
$imgList = json_decode($goods['img']);
$goods['img'] = $imgList;
$goods['all_goods'] = dbCount('zj_good', $con, "shopid = $goods[shopid]");
$goods['news'] = $goods['all_goods']>=10 ? 10 :$goods['all_goods'];

forExit($lock_array, $con);
$return_list['data'] = json_encode($goods);
toExit(0, $return_list, false);

?>
