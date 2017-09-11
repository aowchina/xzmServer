<?php
/**
 * 下单
 * 接口参数: 8段 * userid * 平台货号(goods_num) * 数量 * is_sk
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

$amount = intval(trim($reqlist[10]));
if($amount <= 0){
    forExit($lock_array);
    toExit(28, $return_list);
}

$is_sk = intval(trim($reqlist[11]));
if(!in_array($is_sk,[0,1]))
{
    forExit($lock_array);
    toExit(80, $return_list);
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

//锁订单表
$order_lock = $j_path.'lock/order';
//锁表
if(!lockDb($order_lock, 3)){
    forExit($lock_array, $con);
    toExit(303, $return_list);
}
$lock_array[] = $j_path."lock/order";

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


/******* 模拟参数 *******
$userid = 1337;
$goods_num = '110027';
$amount = 2;
$is_sk = 1;
/***********************/
$total_price = 0;
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

//获取商品价格
$sql = "select status,price,ng_price,h_price,m_price,l_price,name from hd_goods where goods_num = '$goods_num'";
$goods_info = dbLoad(dbQuery($sql, $con), true);
$old_price = $goods_info['price'];
//检查商品是否处于上架状态
if($goods_info['status'] != 1){
    forExit($lock_array, $con);
    toExit(33, $return_list);
}
if($is_sk == 0)
{
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
//秒杀
else
{
    //检查秒杀是否超时
    $time = time();
    $sql = "select sell_max, buy_max, sk_price from hd_seckill where goods_num = '$goods_num' and state = 1 and $time between stime and etime";
    $sk_goods_info = dbLoad(dbQuery($sql, $con), true);
    if(empty($sk_goods_info))
    {
        forExit($lock_array, $con);
        toExit(81, $return_list);
    }

    //验证是否超出最大出售个数
    $sql = "select sum(sell_num) as all_sell_num from hd_sk_sell where goods_num = '$goods_num'";
    $sell_sk_goods = dbLoad(dbQuery($sql, $con), true);
    if(empty($sell_sk_goods))
    {
        $sell_sk_goods['all_sell_num'] = 0;
    }
    if(($amount + $sell_sk_goods['all_sell_num']) > $sk_goods_info['sell_max'])
    {
        forExit($lock_array, $con);
        toExit(70, $return_list);
    }

    //是否超过每人限购数
    $sql = "select sell_num from hd_sk_sell where goods_num = '$goods_num' and userid = $userid";
    $p_sk_sell =  dbLoad(dbQuery($sql, $con), true);
    if(empty($p_sk_sell))
    {
        $p_sk_sell['sell_num'] = 0;
    }
    if(($amount + $p_sk_sell['sell_num']) > $sk_goods_info['buy_max'])
    {
        forExit($lock_array, $con);
        toExit(71, $return_list);
    }
    $ng_price = $sk_goods_info['sk_price'];
}


$total_price = $total_price + $ng_price * $amount;


//生成订单号
$orderid = 'hondo_wx'.time().($userid + 1000).rand(1000000, 9999999);
$time = time();
$data['order_id'] = $orderid;
$data['userid'] = $userid;
$data['price'] = $total_price;
$data['status'] = 0;
$data['create_time'] = $data['intime'] = $time;

if(!dbAdd($data, 'hd_order', $con)){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

$data = array();
$data['order_id'] = $orderid;
$data['goods_num'] = $goods_num;
$data['price'] = $ng_price;
$data['amount'] = $amount;
$data['is_sk'] = $is_sk;
dbAdd($data, 'hd_order_goods', $con);

$r_data['order_id'] = $orderid;
$return_list['data'] = json_encode($r_data);
forExit($lock_array, $con);
toExit(0, $return_list);

?>
