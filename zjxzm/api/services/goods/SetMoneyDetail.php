<?php
/**
 * 报价详情
 * 接口参数: 8段 * userid(卖家) * 报价id
 * author pwj
 * date 2017-06-14
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");
//验证参数个数
if(!(count($reqlist) == 10)){
   forExit($lock_array);
   toExit(9, $return_list);
}

//验证报价id
$bjid = trim($reqlist[9]);
if($bjid < 1 || $bjid > 4294967296){
   forExit($lock_array);
   toExit(10, $return_list);
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

$count = dbCount('zj_setmoney', $con, "id = $bjid");
if($count != 1)
{
    forExit($lock_array, $con);
    toExit(69, $return_list);
}

$sql = "select a.bid,a.sellerid,a.type,a.price,b.bname,b.sname,b.cname,b.img,b.picture,b.jname,b.vin from zj_setmoney as a left join zj_border as b on a.bid = b.bid where a.id = $bjid";
$SetMoneyDetail =  dbLoad(dbQuery($sql, $con),true);

$buyImg = json_decode($SetMoneyDetail['picture']);
$SetMoneyDetail['picture'] = $buyImg;
$typeArr = explode(',',$SetMoneyDetail['type']);
$priceArr = explode(',',$SetMoneyDetail['price']);

foreach($typeArr as $k=>$v)
{
    $tmpDate['type'] = $v;
    $tmpDate['price'] = $priceArr[$k];
    $date[] = $tmpDate;
}
$SetMoneyDetail['tpdetail'] = $date;

$smDetail['info'] = $SetMoneyDetail;
forExit($lock_array, $con);
$return_list['data'] = json_encode($smDetail);
toExit(0, $return_list, false);
?>