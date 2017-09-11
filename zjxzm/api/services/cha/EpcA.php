<?php
/**
 * 配件查询(左边的类别标题)
 * 接口参数: 8段 * userid  * carid(车款)
 * author zq
 * date 2017-06-08
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
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
$carid= trim($reqlist[9]);
if(empty($carid)){
    forExit($lock_array);
    toExit(19, $return_list);
}
if($carid < 1 || $carid > 4294967296){
    forExit($lock_array);
    toExit(19, $return_list);
}

//$carid = "3";

//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=0";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

$count =dbCount("zj_type", $con, $where = "carid='$carid'");
if($count>=1){
    //取出该车款下的商品类别
    $sql1="select a.typeid,a.tname from zj_type a
where a.carid='$carid' ";

    $result1 = dbLoad(dbQuery($sql1, $con));

    $sql2="select d.bname,c.sname,b.cname,b.cimage from zj_car b
left join zj_serial c on b.serialid=c.serialid
left join zj_brand d on c.brandid=d.brandid
where b.carid='$carid' ";
    $result2 = dbLoad(dbQuery($sql2, $con),true);
    $result2['cimage']=$s_url.$result2['cimage'];
    
$result=array(
    "list" =>$result1,
    "object"=>$result2,
);

    forExit($lock_array, $con);
    $return_list['data'] = json_encode($result);
    toExit(0, $return_list, false);


}else{

    forExit($lock_array, $con);
    toExit(20, $return_list);
}












