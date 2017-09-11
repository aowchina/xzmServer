<?php
/**
 * 商品详情
 * 接口参数: 8段 * userid * 求购配件id * 买家id * type(格式0,1,2) * price(格式1.00,2.000,3.00)
 * author pwj
 * date 2017-06-01
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");
include_once("../Jpush.php");

//验证参数个数
if(!(count($reqlist) == 13)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证货号
$bid = trim($reqlist[9]);
if($bid < 1 || $bid > 4294967296){
    forExit($lock_array);
    toExit(12, $return_list);
}

//验证买家id
$appuid = trim($reqlist[10]);
if($appuid < 1 || $appuid > 4294967296){
    forExit($lock_array);
    toExit(13, $return_list);
}

$type = rtrim($reqlist[11],',');
$price = rtrim($reqlist[12],',');

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
$lock_array[] = $user_path."lock";

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

//验证商品是否存在
$where = "bid = $bid";
$count = dbCount('zj_border', $con, $where);
if($count != 1)
{
    forExit($lock_array, $con);
    toExit(30, $return_list);
}

//验证appuid
$sql = "select appuid from zj_border where $where";
$pappuid = dbLoad(dbQuery($sql, $con),true);
if($appuid !=$pappuid['appuid'])
{
    forExit($lock_array, $con);
    toExit(31, $return_list);
}

/*
$bid = 2;
$userid = 2;
$type = '1,2,4';
$price = '1.0,22,23';
*/

//判断这个配件商是否对这个配件报价过
$data['type'] = $type;
$data['price'] = $price;

$count = dbCount('zj_setmoney',$con, "bid = $bid and sellerid = $userid");
if($count > 0)
{
        forExit($lock_array, $con);
        toExit(32, $return_list);
}
else
{
    $data['bid'] = $bid;
    $data['sellerid'] = $userid;
    if(!dbAdd($data,'zj_setmoney',$con))
    {
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }
    $sql = "select last_insert_id() as id from zj_setmoney";
}

$setMoneyId = dbLoad(dbQuery($sql, $con),true);

$r_list['setMoneyId'] = $setMoneyId['id'];

/**************************推送开始*******************************/
//给买家发送通知（报价推送）
$sql = "select deviceid from zj_user_login where is_app=1 and userid=".$appuid." group by deviceid";
$result = dbLoad(dbQuery($sql, $con),true);

if(count($result) > 0){
    //获取极光id
    $jpushid = @file_get_contents($t_path.'device/'.getSubPath($result['deviceid'], 4, true).'jpush');

    if($jpushid){
        $jp = new Jpush();
        $jp->push(array('registration_id'=>array($jpushid)),'您收到一条新的报价信息',array('page'=>1));
    }

}

/**************************推送结束*******************************/

forExit($lock_array, $con);
$return_list['data'] = json_encode($r_list);
toExit(0, $return_list, false);
?>