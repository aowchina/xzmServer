<?php
/**
 * 待付款订单取消
 * 接口参数: 8段 * userid * 订单号(order_id)
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

$order_num = trim($reqlist[9]);
if(!preg_match('/^hondo_wx[0-9]+$/', $order_num)){
    forExit($lock_array);
    toExit(35, $return_list);
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

/*模拟参数
$order_num = 'hondo_wx147809522234209321710';
$userid = 2420;
/**/
$count = dbCount('hd_order', $con, "order_id = '".$order_num."' and userid = $userid ");

if($count != 1){
    forExit($lock_array, $con);
    toExit(34, $return_list);
}
//取出这个订单的状态
$sql = 'select status,wl_price + price as allPrice,create_time,pay_time,wl_id,user_pid,user_cid,user_qid,user_address,user_tel,user_name,user_info,order_dsfid,wl_price from hd_order where order_id = "'.$order_num.'" and userid = '.$userid;
$order_status = dbLoad(dbQuery($sql, $con),true);
$sql = "select name from hd_wl where id = $order_status[wl_id]";
$wl_name = dbLoad(dbQuery($sql, $con), true);
//判断订单是否可以取消
if(!in_array($order_status['status'],[0,1]))
{
    forExit($lock_array, $con);
    toExit(67, $return_list);
}
$time = time();
$datetime = date('Y-m-d H:i:s',$time);
if($order_status['status'] == 0)
{
    $u_data['status'] = 4;
    $u_data['intime'] = $time;
    $condition = "order_id = '$order_num' and userid = $userid";
    if(!dbUpdate($u_data,'hd_order',$con,$condition))
    {
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }
}
else
{
    $count = dbCount('hd_wallet', $con, "userid = $userid ");
    //开启事物
    dbQuery("START TRANSACTION", $con);
    if( $count == 0)
    {

        $sql = "insert into hd_wallet values(null,$userid,$order_status[allPrice],$time)";
    }
    else
    {
        $sql = "update hd_wallet set money = money + $order_status[allPrice] where userid = $userid";
    }
    $in_money = dbQuery($sql, $con);
    $sql = "update hd_order set status  = 4,intime = $time where order_id = '$order_num' and userid = $userid ";
    $u_money = dbQuery($sql, $con);
    if($in_money && $u_money)
    {
        dbQuery("COMMIT", $con);
    }
    else
    {
        dbQuery("ROLLBACK", $con);
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }

}

//当订单中存在秒杀产品时,更新卖出秒出商品中的数量
$sql = "select goods_num,amount,is_sk,price from hd_order_goods where order_id = '$order_num'";
$sk_goods = dbLoad(dbQuery($sql, $con));
$all_goods_num = 0;
foreach($sk_goods as $k=>$v)
{
    $sql = "select name,price from hd_goods where goods_num = '$v[goods_num]'";
    $goods_name = dbLoad(dbQuery($sql, $con), true);
    $all_goods_num += $v['amount'];
    $erp_goods['bn'] = $v['goods_num'];
    $erp_goods['name'] = $goods_name['name'];
    $erp_goods['sku_properties'] = '';
    $erp_goods['price'] = $goods_name['price'];
    $erp_goods['sale_price'] = $v['price'];
    $erp_goods['total_item_fee'] = $v['amount'] * $v['price'];
    $erp_goods['num'] = $v['amount'];
    $erp_goods['item_type'] = 'product';
    $erp_goods['item_status'] = 'normal';
    $erp_item[] = $erp_goods;
    //商品为秒杀产品
    if($v['is_sk'] == 1)
    {
        //以经售出的秒杀产品
        $sql = "select sell_num from hd_sk_sell where userid = $userid and goods_num = $v[goods_num]";
        $sell_goods = dbLoad(dbQuery($sql, $con),true);
            $u_data['sell_num'] = $sell_goods['sell_num'] - $v['amount'];
            $condition = "userid = $userid and goods_num = $v[goods_num]";
            dbUpdate($u_data, 'hd_sk_sell', $con, $condition);
    }
}
 if($order_status['status'] == 1)
 {
     include("../prism-php-master/src/PrismClient.php");
     //更新erp中订单状态为已取消
     $client = new PrismClient($url = $erp_url, $key = $erp_key, $secret = $erp_secret);
     //取出收货人信息
     $sql = "select areaname from hd_area where id = ".$order_status['user_pid'];
     $pinfo = dbLoad(dbQuery($sql, $con), true);

     $sql = "select areaname from hd_area where id = ".$order_status['user_cid'];
     $cinfo = dbLoad(dbQuery($sql, $con), true);

     $sql = "select areaname from hd_area where id = ".$order_status['user_qid'];
     $qinfo = dbLoad(dbQuery($sql, $con), true);

     if(empty($order_info['user_info']))
     {
         $order_info['user_info'] = '';
     }

     $orders = array(
         "order" => array(
             array(
                 "oid" => $order_num,
                 "type" => "goods",
                 "items_num" => $all_goods_num,
                 "total_order_fee" => $order_status['allPrice'],
                 "status" => "TRADE_CLOSED",
                 "ship_status" => "SHIP_NO",
                 "pay_status" => "PAY_FINISH",
                 "order_items" => array(
                     "item" =>$erp_item,
                 )
             ),
         )
     );

     $promotion_details = array(
         array(
             "promotion_name" => "",
             "promotion_fee" => ""
         )
     );
     $create_time = date('Y-m-d H:i:s',$order_status['create_time']);
     $pay_time = date('Y-m-d H:i:s',$order_status['pay_time']);
     $payment_lists = array(
         "payment_list" => array(
             array(
                 "payment_id" => $order_status['order_dsfid'],
                 "tid" => $order_num,
                 "seller_bank" => "",
                 "seller_account" => "",
                 "pay_fee" => $order_status['allPrice'],
                 "currency" => "CNY",
                 "currency_fee" => $order_status['allPrice'],
                 "pay_type" => "online",
                 "pay_time" => $pay_time,
                 "status" => "SUCC"
             )
         )
     );

     $params = array(
         "method" => "store.trade.update",
         "node_id" => $node_id,
         "tid" => $order_num,
         "title" => $order_num,
         "created" => $create_time,
         "lastmodify" => $datetime,
         "modified" => $datetime,
         "is_cod" => false,
         "total_trade_fee" => $order_status['allPrice'],
         "status" => "TRADE_CLOSED",
         "pay_status" => "PAY_FINISH",
         "ship_status" => "SHIP_NO",
         "has_invoice" => "false",
         "payed_fee" => $order_status['allPrice'],
         "shipping_tid" => $order_status['wl_id'],
         "shipping_type" => $wl_name['name'],
         "shipping_fee" => $order_status['wl_price'],
         "is_protect" => "0",
         "receiver_name" => $order_status['user_name'],
         "receiver_email" => "",
         "receiver_state" => $pinfo['areaname'],
         "receiver_city" => $cinfo['areaname'],
         "receiver_district" => $qinfo['areaname'],
         "receiver_address" => $order_status['user_address'],
         "receiver_zip" => '',
         "receiver_mobile" => $order_status['user_tel'],
         "buyer_memo" => $order_status['user_info'],
         "promotion_details" => json_encode($promotion_details),
         //基本参数完成
         "orders" => json_encode($orders),
         "payment_lists" => json_encode($payment_lists),
     );

     $headers = array(
     );

      $client->post('/oms', $params, $headers);

 }

forExit($lock_array, $con);
toExit(0, $return_list, false);

?>
