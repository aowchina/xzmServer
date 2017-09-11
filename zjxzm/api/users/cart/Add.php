<?php
/**
 * 添加到购物车
 * 接口参数: 8段 * userid * 平台货号 * 价格(单价) * 数量
 * author wangrui@min-fo.com
 * date 2015-11-13
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");

//验证参数个数
if(!(count($reqlist) == 12)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$goods_num = trim($reqlist[9]);
if(!isGoodsNum($goods_num)){
    forExit($lock_array);
    toExit(26, $return_list);
}

$price = trim($reqlist[10]);
if(!isPoint($price, 8, 2)){
    forExit($lock_array);
    toExit(27, $return_list);
}

$amount = intval(trim($reqlist[11]));
if($amount <= 0){
    forExit($lock_array);
    toExit(28, $return_list);
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

//验证货号是否存在
$condition = "goods_num = '".$goods_num."' and status = 1";
$count = dbCount('hd_goods', $con, $condition);
if($count == 0){
    forExit($lock_array, $con);
    toExit(29, $return_list);
}

//判断该商品是否为秒杀产品
$time = time();
$sql = "select sell_max,buy_max from hd_seckill where goods_num = '$goods_num' and $time between stime and etime and state = 1";
$sk_goods = dbLoad(dbQuery($sql, $con), true);
$sell_goods = 0;
$data['is_sk'] = 0;
if(!empty($sk_goods))
{
    //已经卖出的数量
    $sql = "select sum(sell_num) as all_sell_num from hd_sk_sell where goods_num = '$goods_num'";
    $sell_sk_goods = dbLoad(dbQuery($sql, $con), true);
    if(!empty($sell_sk_goods))
    {
        $sell_goods = $sell_sk_goods['all_sell_num'];
    }
    //是否超出最大出售数
    if(($amount +$sell_goods) > $sk_goods['sell_max'])
    {
        forExit($lock_array, $con);
        toExit(70, $return_list);
    }

    //判断是否超过每人限购数
    $sql = "select amount from hd_cart where userid = $userid and goods_num = '$goods_num' and is_sk = 1";
    $sk_amount = dbLoad(dbQuery($sql, $con), true);
    if(empty($sk_amount))
    {
        $sk_amount['amount'] = 0;
    }

    $sql = "select sell_num from hd_sk_sell where goods_num = '$goods_num' and userid = $userid";
    $p_sk_sell =  dbLoad(dbQuery($sql, $con), true);
    if(empty($p_sk_sell))
    {
        $p_sk_sell['sell_num'] = 0;
    }
    if(($amount + $sk_amount['amount'] + $p_sk_sell['sell_num']) > $sk_goods['buy_max'])
    {
        forExit($lock_array, $con);
        toExit(71, $return_list);
    }
    $data['is_sk'] = 1;
}
//构建数组
$data['price'] = $price;
$data['userid'] = $userid;
$data['goods_num'] = $goods_num;

//判断购物车里是否已经有相同商品
$condition = "userid = $userid and goods_num = '$goods_num' and is_sk = $data[is_sk] ";

$count = dbCount('hd_cart', $con, $condition);
if($count > 0){
    $sql = "select * from hd_cart where $condition";
    $cart_info = dbLoad(dbQuery($sql, $con), true);

    $data['amount'] = $amount + $cart_info['amount'];
    if(dbUpdate($data, 'hd_cart', $con, $condition)){
        forExit($lock_array, $con);
        toExit(0, $return_list);
    }else{
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }
}
else{
    $data['amount'] = $amount;
    if(dbAdd($data, 'hd_cart', $con)){
        forExit($lock_array, $con);
        toExit(0, $return_list);
    }else{
        forExit($lock_array, $con);
        toExit(303, $return_list);
    }
}

?>
