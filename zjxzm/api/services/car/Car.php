<?php
/**
 * 车款
 * 接口参数: 8段 * userid * serialid
 * author pwj
 * date 2017-06-01
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}
$serialid = trim($reqlist[9]);
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

/*测试数据
$serialid = 12;
$userid = 1;
$s_url = 'http://192.168.118/hondo_wx/';
*/

$sql = "select sname from zj_serial where serialid = $serialid";
$sname = dbLoad(dbQuery($sql, $con),true);

$where = "a.serialid =$serialid ";
$sql = "select  DISTINCT a.issuedate from zj_car as a  where $where";
$year = dbLoad(dbQuery($sql, $con));

foreach($year as &$v)
{
   $sql = "select a.carid,a.cname,a.cimage,a.vin, a.issuedate, a.price,b.sname,c.bname from zj_car as a left join zj_serial as b on a.serialid = b.serialid left join zj_brand as c on b.brandid = c.brandid where $where and a.issuedate = $v[issuedate]";
    $carinfo = dbLoad(dbQuery($sql, $con));
    foreach($carinfo as &$value)
    {
        $value['cimage'] = $s_url.$value['cimage'];
    }
    $v['info'] = $carinfo;
}


$r_list['sname'] =$sname['sname'];
$r_list['carinfo'] = $year;

forExit($lock_array, $con);
$return_list['data'] = json_encode($r_list);
toExit(0, $return_list, false);