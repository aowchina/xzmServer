<?php
/**
 * 获取支付信息
 * 接口参数: 8段 * userid * order_id * 订单备注
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

$orderid = trim($reqlist[9]);
if(!preg_match('/^hondo_wx[0-9]+$/', $orderid)){
    forExit($lock_array);
    toExit(35, $return_list);
}

//获取订单备注
$user_info = getStrFromByte(trim($reqlist[10]));
$len = mb_strlen($user_info,'UTF8');
if($len > 256)
{
    forExit($lock_array);
    toExit(90, $return_list);
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

//订单是否存在
$count = dbCount("hd_order", $con, "order_id = '$orderid' and userid = $userid and status = 0");
if($count != 1){
    forExit($lock_array, $con);
    toExit(34, $return_list);
}

//判断是否有地址
$sql = "select user_pid,wl_id,cang_id from hd_order where order_id = '$orderid'";
$order_info = dbLoad(dbQuery($sql, $con), true);
if(empty($order_info['user_pid'])){
    forExit($lock_array, $con);
    toExit(42, $return_list);
}

if($order_info['wl_id'] == 0){
    forExit($lock_array, $con);
    toExit(43, $return_list);
}

//检查当前用户身份
$sql = "select group_id from hd_user_usergroup_map where user_id = $userid";
$re = dbLoad(dbQuery($sql, $con), true);
$group = $re['group_id'];
if(!($group == 9 || $group == 2 || $group == 3)){
    forExit($lock_array, $con);
    toExit(11, $return_list);
}
//如果是微商，获取微商等级
if($group == 9){
    $sql = "select level from hd_users where id = $userid";
    $re = dbLoad(dbQuery($sql, $con), true);
    $level = $re['level'];
}

//再次验证订单中的商品价格与数量
$sql = "select goods_num,price,amount,is_sk from hd_order_goods where order_id = '$orderid'";
$list = dbLoad(dbQuery($sql, $con));

foreach($list as $item){
    if($item['is_sk'] == 0)
    {
        $sql = "select price,ng_price,h_price,m_price,l_price from hd_goods where goods_num = '".$item['goods_num']."'";
        $goods_info = dbLoad(dbQuery($sql, $con), true);
        if($group == 9){
            switch ($level) {
                case 1:
                    $ng_price = $goods_info['l_price'];
                    break;
                case 2:
                    $ng_price = $goods_info['m_price'];
                    break;
                case 3:
                    $ng_price = $goods_info['h_price'];
                    break;
                default:
                    $ng_price = $goods_info['l_price'];
                    break;
            }
        }
        elseif($group == 2){
            $ng_price = $goods_info['price'];
        }
        else{
            $ng_price = $goods_info['ng_price'];
        }
    }
    else
    {
        //检查秒杀是否超时
        $time = time();
        $sql = "select sell_max, buy_max, sk_price from hd_seckill where goods_num = '$item[goods_num]' and state = 1 and $time between stime and etime";
        $sk_goods_info = dbLoad(dbQuery($sql, $con), true);
        if(empty($sk_goods_info))
        {
            forExit($lock_array, $con);
            toExit(81, $return_list);
        }

        //验证是否超出最大出售个数
        $sql = "select sum(sell_num) as all_sell_num from hd_sk_sell where goods_num = '$item[goods_num]'";
        $sell_sk_goods = dbLoad(dbQuery($sql, $con), true);
        if(empty($sell_sk_goods))
        {
            $sell_sk_goods['all_sell_num'] = 0;
        }
        if(($item['amount'] + $sell_sk_goods['all_sell_num']) > $sk_goods_info['sell_max'])
        {
            forExit($lock_array, $con);
            toExit(70, $return_list);
        }

        //是否超过每人限购数
        $sql = "select sell_num from hd_sk_sell where goods_num = '$item[goods_num]' and userid = $userid";
        $p_sk_sell =  dbLoad(dbQuery($sql, $con), true);
        if(empty($p_sk_sell))
        {
            $p_sk_sell['sell_num'] = 0;
        }
        if(($item['amount'] + $p_sk_sell['sell_num']) > $sk_goods_info['buy_max'])
        {
            forExit($lock_array, $con);
            toExit(71, $return_list);
        }
        $ng_price = $sk_goods_info['sk_price'];
    }

    if(bccomp($ng_price, $item['price']) != 0){
        forExit($lock_array, $con);
        toExit(41, $return_list);
    }
}
//更新订单详情
$data= [];
$data['user_info'] = $user_info;
if(!dbUpdate($data, 'hd_order', $con, "order_id = '$orderid'"))
{
    forExit($lock_array, $con);
    toExit(91, $return_list);
}
forExit($lock_array, $con);
toExit(0, $return_list);
?>
