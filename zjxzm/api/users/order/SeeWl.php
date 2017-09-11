<?php
/**
 * 查看物流
 * 接口参数: 8段 * userid * 订单号(orderid)
 * author zq
 * date 2017-6-20
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$order_num = trim($reqlist[9]);
if(!preg_match('/^zj[0-9]+$/', $order_num)){
    forExit($lock_array);
    toExit(29, $return_list);
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

//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=1";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
  forExit($lock_array, $con);
  toExit(12, $return_list);
}

$sql="select wlname,kuaidih from zj_order where orderid='$order_num' ";
$res= dbLoad(dbQuery($sql, $con),true);



$sql="select com from zj_kuaidi where name='$res[wlname]' ";
$result= dbLoad(dbQuery($sql, $con),true);

if(count($result['com'])!=1){
    forExit($lock_array, $con);
  toExit(52, $return_list);
}

//快递单号
$typeCom = $result['com'];
$typeNu = $res['kuaidih'];



$AppKey='b4bdf62d3e870532';
$url ='http://api.kuaidi100.com/api?id='.$AppKey.'&com='.$typeCom.'&nu='.$typeNu.'&show=0&muti=1&order=asc';

//请勿删除变量$powered 的信息，否者本站将不再为你提供快递接口服务。
$powered = '查询数据由：<a href="http://kuaidi100.com" target="_blank">KuaiDi100.Com （快递100）</a> 网站提供 ';


//优先使用curl模式发送数据
if (function_exists('curl_init') == 1){
  $curl = curl_init();
  curl_setopt ($curl, CURLOPT_URL, $url);
  curl_setopt ($curl, CURLOPT_HEADER,0);
  curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
  curl_setopt ($curl, CURLOPT_TIMEOUT,5);
  $get_content = curl_exec($curl);
  curl_close ($curl);
}else{
  include("snoopy.php");
  $snoopy = new snoopy();
  $snoopy->referer = 'http://www.google.com/';//伪装来源
  $snoopy->fetch($url);
  $get_content = $snoopy->results;
}

$data=json_decode($get_content,true);
$data=isset($data)?$data:"";

$result=[
    "list"=>$data,
    "word"=>$powered,
];
forExit($lock_array, $con);
$return_list['data'] = json_encode($result);
toExit(0, $return_list, false);
?>
