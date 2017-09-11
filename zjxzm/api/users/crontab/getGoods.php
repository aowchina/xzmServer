<?php
/**
 * 7天自动收货
 * author peiweijain
 * date 2017-05-13
 */

//include_once("/Library/WebServer/Documents/zjxzm/api/functions_mdb.php");
include_once("../functions_mdb.php");
//连接db
$con = conDb();
if($con == ''){
    exit;
}


//取出7天还没有收货的订单(每周的0点执行)
$time = time();

//商城订单
$sql="select orderid from zj_order where status=2 and '$time'-fhtime>=300";
$order = dbLoad(dbQuery($sql, $con));

if(!empty($order))
{
    foreach($order as $v)
    {
            //更新订单表
            $data['status']= 3;
            $data['retime'] = $time;
            $data['ifreceive']=1;

           dbUpdate($data,'zj_order',$con,"orderid = $v[orderid]");
    }
}

//求购订单
$sql="select qgorderid from zj_qgorder where status=2 and '$time'-fhtime>=300";
$order = dbLoad(dbQuery($sql, $con));

if(!empty($order))
{
    foreach($order as $v)
    {
        $data=[];
        //更新订单表
        $data['status']= 3;
        $data['retime'] = $time;
        $data['ifreceive']=1;

        dbUpdate($data,'zj_qgorder',$con,"qgorderid = $v[qgorderid]");
    }
}


?>
