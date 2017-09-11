<?php
/**
* 添加评价
* 接口参数: 8段 * appuid(评价的人) * goodid * 描述评分 * 物流评分 * 服务评分 * 评价内容 * 订单号
* author zq
* date 2017-06-21
*/
include_once("../functions_mut.php");
include_once("../functions_mdb.php");   

//验证参数个数
if(!(count($reqlist) == 15)){
forExit($lock_array);
toExit(9, $return_list);
}


$userid = trim($reqlist[8]);
if($userid < 1 || $userid > 4294967296) {
    forExit($lock_array);
    toExit(10, $return_list);
}



$goodid = trim($reqlist[9]);
if($goodid < 1 || $goodid > 4294967296){
    forExit($lock_array);
    toExit(19, $return_list);
}

$msfen = trim($reqlist[10]);
   if($msfen<1 || $msfen>5){
       forExit($lock_array);
       toExit(63, $return_list);
   }


$wlfen = trim($reqlist[11]);
    if($wlfen<1 || $wlfen>5){
        forExit($lock_array);
        toExit(64, $return_list);
    }


$fwfen = trim($reqlist[12]);
    if($fwfen<1 || $fwfen>5){
        forExit($lock_array);
        toExit(65, $return_list);
    }

$content= getStrFromByte(trim($reqlist[13]));
if(empty($content)){
    forExit($lock_array);
    toExit(66, $return_list);
}

$order_num = trim($reqlist[14]);
if(!preg_match('/^zj[0-9]+$/', $order_num)){
   forExit($lock_array);
   toExit(29, $return_list);
}


//打用户锁
$user_path = $j_path.'user/'.getSubPath($userid, 4, true);
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

//$goodid="3";
//$appuid= "1";
//$sellerid="1";
//$msfen="5";
//$wlfen="5";
//$fwfen="5";
//$content="非常满意";

//买家用户是否存在
$condition = "appuid = '$userid'";
$count = dbCount('zj_appuser', $con, $condition);

if($count != 1) {
forExit($lock_array, $con);
toExit(11, $return_list);
}


//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=1";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}
//$j_path = "/Library/WebServer/Documents/zjxzm/data/";
$dir=$j_path.'pinjia/';
if(!is_dir($dir)){
    mkdir($dir,0777);
}
$pinadd=$dir.time();
file_put_contents($pinadd,$content);

//写入评价
$data['goodid'] = $goodid;
$data['appuid'] = $userid;
$data['msfen'] = $msfen;
$data['wlfen'] = $fwfen;
$data['fwfen'] = $fwfen;
$data['content'] = $pinadd;
$data['addtime']=time();

if(!dbAdd($data, 'zj_pinjia', $con)){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

$o_data['status'] = 4;
if(!dbUpdate($o_data, 'zj_order', $con, "orderid='$order_num'")){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

//算好评率
$sql="select b.shopid from zj_pinjia a left join zj_good b on a.goodid=b.goodid where a.goodid='$goodid'";
$result= dbLoad(dbQuery($sql, $con), true);

$sql="select msfen,wlfen,fwfen from zj_pinjia a left join zj_good b on a.goodid=b.goodid where shopid='$result[shopid]'";
$res=dbLoad(dbQuery($sql, $con));

$ms=0;
$wl=0;
$fw=0;
foreach($res as $k=>$v){
    $ms += $v['msfen'];
    $wl += $v['wlfen'];
    $fw += $v['fwfen'];
}
$rate=($ms + $wl +$fw)/(($k+1)*15)*100;//一条评价满分15分

$r_data['rate']= $rate;
$r_data['addtime']= time();
if(!dbUpdate($r_data, "zj_shop", $con, "shopid = '$result[shopid]'"))
{
    forExit($lock_array);
    toExit(302, $return_list);
}


forExit($lock_array, $con);
toExit(0, $return_list);


?>
