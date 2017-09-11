<?php
/**
 * 配件详情(比如纵梁前跳过来的界面)
 * 接口参数: 8段 * userid * epcid * id(oem的主键id)
 * author zq
 * date 2017-06-08
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

if(!(count($reqlist) == 11)){
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

$epcid= trim($reqlist[9]);
if(empty($epcid)){
    forExit($lock_array);
    toExit(21, $return_list);
}
if($epcid < 1 || $epcid > 4294967296){
    forExit($lock_array);
    toExit(19, $return_list);
}

$id= trim($reqlist[10]);
if(empty($id)){
    forExit($lock_array);
    toExit(26, $return_list);
}
if($id < 1 || $id > 4294967296){
    forExit($lock_array);
    toExit(19, $return_list);
}
//$epcid = "1";
//$id="1";

//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=1";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

//获取配件详情
    $sql="select a.position,a.name,a.oem from zj_oem a
where a.epcid='$epcid' and a.id='$id'";
    $res1 = dbLoad(dbQuery($sql, $con),true);

$sql="select e.bname,d.sname,c.cname,c.carid from zj_epc a
left join zj_type b on b.typeid=a.typeid
left join zj_car c on c.carid= b.carid
left join zj_serial d on d.serialid=c.serialid
left join zj_brand e on e.brandid=d.brandid where a.epcid='$epcid'";
$res2 = dbLoad(dbQuery($sql, $con));
     $result=array(
        "object"=>$res1,
        "list"=>$res2,
    );

    forExit($lock_array, $con);
    $return_list['data'] = json_encode($result);
    toExit(0, $return_list, false);




