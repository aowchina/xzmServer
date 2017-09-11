<?php
/**
 * 购物车商品数量改变
 * 接口参数: 8段 * userid * 购物车id * 操作类型(1加，2减)
 * author wangrui@min-fo.com
 * date 2015-11-13
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 11)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$id = intval(trim($reqlist[9]));
if($id < 0){
    forExit($lock_array);
    toExit(24, $return_list);
}

$type = intval(trim($reqlist[10]));
if(!($type == 1 || $type == 2)){
    forExit($lock_array);
    toExit(31, $return_list);
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

//验证购物车id是否存在
$condition = "id = $id and userid = $userid";
$count = dbCount('hd_cart', $con, $condition);
if($count == 0){
    forExit($lock_array, $con);
    toExit(30, $return_list);
}

//获取购物车信息
$sql = "select * from hd_cart where $condition";
$info = dbLoad(dbQuery($sql, $con), true);
//  +
if($type == 1){
    $data['amount'] = $info['amount'] + 1;
}
// -
else{
    $data['amount'] = $info['amount'] - 1;
}
//是否为秒杀产品
if($info['is_sk'] == 1)
{
    $time = time();
    $sql = "select sell_max,buy_max from hd_seckill where goods_num = $info[goods_num] and $time between stime and etime and state = 1";
    $sk_goods = dbLoad(dbQuery($sql, $con), true);
    if(empty($sk_goods))
    {
        //秒杀时间已过
        forExit($lock_array, $con);
        toExit(73, $return_list);
    }

    $sql = "select sum(sell_num) as all_sell_num from hd_sk_sell where goods_num = $info[goods_num]";
    $sell_sk_goods = dbLoad(dbQuery($sql, $con), true);
    if(empty($sell_sk_goods))
    {
        $sell_sk_goods['all_sell_num'] = 0;
    }

    //是否超出最大出售数
    if(($data['amount'] + $sell_sk_goods['all_sell_num']) > $sk_goods['sell_max'])
    {
        forExit($lock_array, $con);
        toExit(70, $return_list);
    }

    //是否超过每人限购数
    $sql = "select sell_num from hd_sk_sell where goods_num = $info[goods_num] and userid = $userid";
    $p_sk_sell =  dbLoad(dbQuery($sql, $con), true);
    if(empty($p_sk_sell))
    {
        $p_sk_sell['sell_num'] = 0;
    }
    if(($data['amount'] + $p_sk_sell['sell_num']) > $sk_goods['buy_max'])
    {
        forExit($lock_array, $con);
        toExit(71, $return_list);
    }

}
if($data['amount'] <= 0){
    forExit($lock_array, $con);
    toExit(32, $return_list);
}

if(dbUpdate($data, 'hd_cart', $con, $condition)){
    forExit($lock_array, $con);
    toExit(0, $return_list);
}else{
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

?>
