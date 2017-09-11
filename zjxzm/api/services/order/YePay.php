<?php
/** 
 * 获取支付信息
 * 参数：8段 * userid * order_id * 备注(user_info)
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

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

////验证userid
//$userid =546;
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

//用户是否存在
$condition = "id = '$userid'";
$count = dbCount('hd_users', $con, $condition);
if($count != 1) {
    forExit($lock_array, $con);
    toExit(10, $return_list);
}


//订单是否存在
$count = dbCount("hd_order", $con, "order_id = '$orderid' and userid = $userid and status = 0");
if($count != 1){
    forExit($lock_array, $con);
    toExit(34, $return_list);
}


$sql = "select price,wl_price,user_pid,wl_id from hd_order where order_id = '$orderid'";
$order_info = dbLoad(dbQuery($sql, $con), true);
//判断是否有地址
if(empty($order_info['user_pid'])){
    forExit($lock_array, $con);
    toExit(42, $return_list);
}

if(empty($order_info['wl_id'])){
    forExit($lock_array, $con);
    toExit(43, $return_list);
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
$sql = "select is_sk,goods_num,price,amount from hd_order_goods where order_id = '$orderid'";
$list = dbLoad(dbQuery($sql, $con));

//把商品中是秒杀的商品添加到一个数组
$sk_goods = [];
$sk_num = [];

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

        //把卖出的秒杀产品放到数组中
        array_push($sk_goods,$item['goods_num']);
        array_push($sk_num,$item['amount']);

        $ng_price = $sk_goods_info['sk_price'];
    }

    if(bccomp($ng_price, $item['price']) != 0){
        forExit($lock_array, $con);
        toExit(41, $return_list);
    }
}

$money = ($order_info['price'] + $order_info['wl_price']);
//$money= '30.00';
//余额支付

//查出此用户的余额
$sql="select money from hd_wallet where userid=".$userid;
$result=dbLoad(dbQuery($sql, $con));

if($result){
    //if(余额>=支付款){用双精度做比较
    if(bccomp($result['0']['money'],$money)>=0) {
        //可用余额支付
        //用户钱包余额做减法
       //开启事务
        $sw = dbQuery("start transaction", $con);

        //更新用户钱包
        $where = "userid = $userid";
        $data['money'] = $result['0']['money'] - $money;
        $update['money']=$data['money'];
        $resource = dbUpdate($update,'hd_wallet', $con, $where);

        //支付成功后修改订单状态,支付方式为余额支付(3),用户备注
        $res['status']= "1";
        $res['pay_type']= "3";
        $res['user_info'] = $user_info;

        //绑定仓库
        $sql = "select  type from hd_wl where id = $order_info[wl_id]";
        $wl = dbLoad(dbQuery($sql, $con),true);
        if($wl['type'] != 2)
        {
            $sql = "select cang_id from hd_cang_area where pid = ".$order_info['user_pid'];
            $cang_info = dbLoad(dbQuery($sql, $con), true);
            if($cang_info['cang_id']){
                $res['cang_id'] = $cang_info['cang_id'];
            }
        }

        $resource2 = dbUpdate($res, 'hd_order', $con, "order_id = '$orderid'");

        if($resource && $resource2){
            dbQuery("COMMIT", $con);

            //更新卖出的秒杀产品
            if(!empty($sk_goods))
            {
                foreach($sk_goods as $k=>$v)
                {
                    $sql = "select sell_num from hd_sk_sell where userid = $userid and goods_num = '$v'";
                    $sell_goods = dbLoad(dbQuery($sql, $con),true);
                    if(empty($sell_goods))
                    {
                        $n_data['userid'] = $userid;
                        $n_data['goods_num'] = $v;
                        $n_data['sell_num'] = $sk_num[$k];
                        dbAdd($n_data, 'hd_sk_sell', $con);
                    }
                    else
                    {
                        $u_data['sell_num'] = $sk_num[$k] + $sell_goods['sell_num'];
                        $condition = "userid = $userid and goods_num = $v";
                        dbUpdate($u_data, 'hd_sk_sell', $con, $condition);
                    }
                }
            }
            forExit($lock_array, $con);
            toExit(0, $return_list);
        }else{
            dbQuery("ROLLBACK", $con);
            forExit($lock_array, $con);
            toExit(55, $return_list);
        }

    }else{

        //余额不足,请更换支付方式
        forExit($lock_array, $con);
        toExit(57, $return_list);
}

 }else{
    $money=0;

    forExit($lock_array, $con);
    toExit(58, $return_list);
}

?>