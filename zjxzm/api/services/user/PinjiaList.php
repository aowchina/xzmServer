<?php
/**
* 评价列表
* 接口参数: 8段 * sellerid(卖家) *  goodid * 页码
* author zq
* date 2017-06-21
*/
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 11)){
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

$page = intval(trim($reqlist[10]));
if($page < 1){
    forExit($lock_array);
    toExit(28, $return_list);
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


//买家用户是否存在
$condition = "sellerid = '$userid'";
$count = dbCount('zj_seller', $con, $condition);

if($count != 1) {
forExit($lock_array, $con);
toExit(11, $return_list);
}

//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=0";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

$limit = " limit ".(($page - 1)*10).",10";
$condition = "goodid = $goodid";
$count = dbCount('zj_pinjia', $con, $condition);
if($count <1){
    forExit($lock_array, $con);
    toExit(67, $return_list);
}else{
    $sql="select a.content,b.name,b.picture,a.addtime from zj_pinjia a left join zj_appuser b on a.appuid=b.appuid where a.goodid=$goodid".$limit;
    $res = dbLoad(dbQuery($sql, $con));
 foreach($res as $k=> &$v){
        $v['addtime']=Date("Y-m-d H:i:s",$v['addtime']);
       $v['content']= file_get_contents($v['content']);
       $v['picture']= $s_url.$v['picture'];

    }
    $result=[
        "pinjia"=>$res,
    ];
    $return_list['data']=json_encode($result);
    forExit($lock_array, $con);
    toExit(0, $return_list);
}


?>
