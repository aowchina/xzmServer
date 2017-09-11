<?php
/**
 * 我的提现记录
 * 接口参数: 8段 * sellerid(用户id) * 页码
 * author zq
 * date 2017-06-9
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$page = intval(trim($reqlist[9]));
if($page < 1){
    forExit($lock_array);
    toExit(28, $return_list);
}

//验证userid

$userid = trim($reqlist[8]);
if($userid < 1 || $userid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
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

//$userid =10;

//用户是否存在
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
//提现记录表是否有此用户记录
$condition = "userid = '$userid' and tid=2 order by paytime desc".$limit;
$count = dbCount('zj_txtowx', $con, $condition);
if($count>=1){
    $sql="select * from zj_txtowx where $condition";
    $result= dbLoad(dbQuery($sql, $con));
    foreach($result as $k => &$v){
        $v['paytime']=Date("m:d h:i:s",$v['paytime']);
    }
    $res=[
        "list"=>$result,
    ];


}else{
    $result=array();
    $res=[
        "list"=>$result,
    ];
 
}
$return_list['data'] = json_encode($res);
forExit($lock_array, $con);
toExit(0, $return_list);



?>
