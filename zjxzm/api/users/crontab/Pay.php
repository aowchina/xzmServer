<?php
/**
 * 7天自动转账
 * author peiweijain
 * date 2017-05-13
 */
include_once("../functions_mdb.php");
//include_once("/Library/WebServer/Documents/zjxzm/api/functions_mdb.php");

//连接db
$con = conDb();
if($con == ''){
    exit;
}
//商城订单
//取出7天收货还没有转账的订单(每周的0点执行)
$time = time();

$sql = "select a.orderid,a.money,a.shopid from zj_order as a left join zj_bill as b on a.orderid = b.orderid where a.status = 3 and b.state != 1 and '$time'-retime>=300";
$order = dbLoad(dbQuery($sql, $con));

if(!empty($order))
{
    foreach($order as $v)
    {
        //取出每个订单的配件商
        $sql = "select sellerid from zj_seller where shopid = $v[shopid]";
        $sellerid = dbLoad(dbQuery($sql, $con),true);

        //更新配件商钱包
        $sql = "select money from zj_wallet where userid = $sellerid[sellerid] and tid = 2";
        $before_money = dbLoad(dbQuery($sql, $con),true);
        if(empty($before_money))
        {
            $a_data['userid'] = $sellerid['sellerid'];
            $a_data['money'] = $v['money'];
            $a_data['addtime'] = $time;
            $a_data['tid'] = 2;
            $success = dbAdd($a_data,'zj_wallet',$con);
        }
        else
        {
            $u_data['money'] = $v['money'] + $before_money['money'];
            $success = dbUpdate($u_data,'zj_wallet',$con,"userid = $sellerid[sellerid] and tid = 2");
        }
        if($success)
        {
            //更新账单
            $data['state'] = 1;
            $data['paytime'] = $time;
            dbUpdate($data,'zj_bill',$con,"orderid = $v[orderid]");
        }
    }
}


//求购订单
$sql = "select a.qgorderid,a.price,b.sellerid from zj_qgorder as a left join zj_setmoney b on a.bjid=b.id left join zj_bill as c on a.qgorderid = c.orderid where a.status = 3 and c.state != 1 and '$time'-retime>=300";
$qgorder = dbLoad(dbQuery($sql, $con));

if(!empty($qgorder))
{
    foreach($qgorder as $v)
    {
        //更新配件商钱包
        $sql = "select money from zj_wallet where userid = $v[sellerid] and tid = 2";
        $before_money = dbLoad(dbQuery($sql, $con),true);
        if(empty($before_money))
        {
            $a_data['userid'] = $sellerid['sellerid'];
            $a_data['money'] = $v['money'];
            $a_data['addtime'] = $time;
            $a_data['tid'] = 2;
            $success = dbAdd($a_data,'zj_wallet',$con);
        }
        else
        {
            $u_data['money'] = $v['money'] + $before_money['money'];
            $success = dbUpdate($u_data,'zj_wallet',$con,"userid = $sellerid[sellerid] and tid = 2");
        }
        if($success)
        {
            //更新账单
            $data['state'] = 1;
            $data['paytime'] = $time;
            dbUpdate($data,'zj_bill',$con,"orderid = $v[orderid]");
        }
    }
}

?>
