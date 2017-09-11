<?php
/**
 * 配件查询
 * 接口参数: 8段 * userid * 配件名
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
$name= getStrFromByte(trim($reqlist[9]));
if(empty($name)){
    forExit($lock_array);
    toExit(17, $return_list);
}
//$name = "气缸";

//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=1";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}


$count =dbCount("zj_good", $con, $where = "name like '%$name%' and is_sj=1");
if($count>=1){
    $sql="select a.name,d.bname,a.oem,a.price,a.goodid,a.img from zj_good a
left join zj_type e on a.typeid=e.typeid
left join zj_car b on b.carid=e.carid
left join zj_serial c on b.serialid=c.serialid
left join zj_brand d on c.brandid=d.brandid
where a.name like '%$name%' and a.is_sj=1";

    $result = dbLoad(dbQuery($sql, $con));
    
    foreach($result as $k=> &$v){
        $imgs= $v['img'];
        if($imgs){
            $imgs=json_decode($imgs);
            $img = trim($imgs[0]);
            $v['img'] = $s_url.$img;
        }else{
            $v['img'] = " ";
        }
    }

    forExit($lock_array, $con);
    $return_list['data'] = json_encode($result);
    toExit(0, $return_list, false);

}else{
    forExit($lock_array, $con);
    toExit(18, $return_list);
}



