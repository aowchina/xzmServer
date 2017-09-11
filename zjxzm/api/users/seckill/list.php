<?php
/**
 * 秒杀列表
 * 8段 * userid * 场次信息(1:0-8,2:8-12，3:12-16,4:16-20,5:20-24) * 页码
 * User: min-fo026
 * Date: 17/2/21
 * Time: 下午2:57
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 11)){
    forExit($lock_array);
    toExit(9, $return_list);
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

//验证场次信息
$number_id = $s_sk = trim($reqlist[9]);
if(!in_array($number_id,[1,2,3,4,5]))
{
    forExit($lock_array);
    toExit(60, $return_list);
}

//验证页码
$page = intval(trim($reqlist[10]));
if($page < 1){
    forExit($lock_array);
    toExit(25, $return_list);
}

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

// 当前时间所在场次
$hour = date('H');
switch($hour)
{
    case $hour >= 0 && $hour < 8:
        $sk_type = 1;
        break;
    case $hour >= 8 && $hour < 12:
        $sk_type = 2;
        break;
    case $hour >= 12 && $hour < 16:
        $sk_type = 3;
        break;
    case $hour >= 16 && $hour < 20:
        $sk_type = 4;
        break;
    case $hour >= 20 && $hour < 24:
        $sk_type = 5;
        break;
}

//转化场次类型.
switch($number_id)
{
    case 1:
        $number_id = 'sk_one';
        break;
    case 2:
        $number_id = 'sk_two';
        break;
    case 3:
        $number_id = 'sk_three';
        break;
    case 4:
        $number_id = 'sk_four';
        break;
    case 5:
        $number_id = 'sk_five';
        break;
}

//查询秒杀数据列表
$time = time();
$limit = " limit ".(($page - 1)*10).",10";
$order = " order by ttime desc, intime desc";
$field = " goods_num, sk_price, sell_max ";
$where = " $time between stime and etime and state = 1 and $number_id = 1";
$sql = 'select'.$field.'from hd_seckill where'.$where.$order.$limit;
$sk_goods_list = dbLoad(dbQuery($sql, $con));
//关联产品表取出产品的详细信息
foreach($sk_goods_list as $k =>&$v)
{
    $sql = 'select name,price,simg from hd_goods where goods_num ='.$v['goods_num'];
    $goods_info =  dbLoad(dbQuery($sql, $con),true);
    $v['goods_name'] = $goods_info['name'];
    $v['goods_price'] = $goods_info['price'];
    $v['goods_simg'] = $s_url.$goods_info['simg'];
    //秒杀产品是否出售过
    $count = dbCount('hd_sk_sell',$con,'goods_num = '.$v['goods_num']);
    if($count > 0)
    {
        $sql = 'select sum(sell_num) as sell_num from hd_sk_sell where goods_num ='.$v['goods_num'];
        $sell_count = dbLoad(dbQuery($sql, $con),true);
        $sell_num = intval($sell_count['sell_num']);
    }
    else
    {
        $sell_num = 0;
    }
    if( $s_sk <= $sk_type)
    {
        $v['seckilling'] = 1;
    }
    else
    {
        $v['seckilling'] = 0;
    }
    $v['sell_num'] = $sell_num;
    $v['is_sk'] = 1;
}
$r_list['list'] = $sk_goods_list;
forExit($lock_array, $con);
$return_list['data'] = json_encode($r_list);
toExit(0, $return_list, false);
?>