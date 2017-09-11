<?php
/**
 * 下单
 * 接口参数: 8段 * userid * 购物车的id(如果只一个，形如1，如果多个，形如2,3,4,5)
 * author wangrui@min-fo.com
 * date 2015-11-13
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");


//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$ids = trim($reqlist[9]);
if(strpos($ids, ',') !== false){
    $id_list = explode(",", $ids);
}else{
    $id_list[] = $ids;
}
foreach($id_list as $id_item){
    if(intval($id_item) < 1){
        forExit($lock_array);
        toExit(24, $return_list);
    }
}
$id_list = array_values($id_list);

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
$userid = 712;
$id_list = [738,739];
/***********************/

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

$total_price = 0;
$cart_list = array();

$total_price = 0;
//验证购物车记录是否存在
foreach($id_list as $id_item){
    $count = dbCount("hd_cart", $con, "userid = ".$userid." and id = ".$id_item);
    if($count != 1){
        forExit($lock_array, $con);
        toExit(30, $return_list);
    }
    $condition = "id = $id_item and userid = $userid";
    $sql = "select * from hd_cart where $condition";
    $cart_info = dbLoad(dbQuery($sql, $con), true);
    //获取商品价格
    $goods_num = $cart_info['goods_num'];
    $sql = "select status,price,ng_price,h_price,m_price,l_price from hd_goods where goods_num = '$goods_num'";
    $goods_info = dbLoad(dbQuery($sql, $con), true);
    //检查商品是否处于上架状态
    if($goods_info['status'] != 1){
        $sql = "delete from hd_cart where $condition";
        dbQuery($sql, $con);

        forExit($lock_array, $con);
        toExit(33, $return_list);
    }
    if($cart_info['is_sk'] == 0)
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
        $cart_info['is_sk'] = 0;
    }
    //判断购物车中的商品是否为秒杀
    else
    {
        $time = time();
        //取出秒杀产品信息
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
        if(($cart_info['amount'] + $sell_sk_goods['all_sell_num']) > $sk_goods_info['sell_max'])
        {
            forExit($lock_array, $con);
            toExit(70, $return_list);
        }

        $sql = "select sell_num from hd_sk_sell where goods_num = '$goods_num' and userid = $userid";
        $p_sk_sell =  dbLoad(dbQuery($sql, $con), true);
        if(empty($p_sk_sell))
        {
            $p_sk_sell['sell_num'] = 0;
        }
        if(($cart_info['amount'] + $p_sk_sell['sell_num']) > $sk_goods_info['buy_max'])
        {
            forExit($lock_array, $con);
            toExit(71, $return_list);
        }
        $cart_info['is_sk'] = 1;
        $ng_price = $sk_goods_info['sk_price'];
    }
    $total_price = $total_price + $ng_price * $cart_info['amount'];

    $cart_info['ng_price'] = $ng_price;

    $cart_list[] = $cart_info;
}
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

//循环记录订单的产品
$all_goods_num = 0;
foreach($cart_list as $cart_item){
    $data = array();
    $data['order_id'] = $orderid;
    $data['goods_num'] = $cart_item['goods_num'];
    $data['price'] = $cart_item['ng_price'];
    $data['amount'] = $cart_item['amount'];
    $data['is_sk'] = $cart_item['is_sk'];
    dbAdd($data, 'hd_order_goods', $con);

    //删除购物车
    $sql = "delete from hd_cart where id = ".$cart_item['id'];
    dbQuery($sql, $con);
}

$r_data['order_id'] = $orderid;

$return_list['data'] = json_encode($r_data);
forExit($lock_array, $con);
toExit(0, $return_list);

?>
