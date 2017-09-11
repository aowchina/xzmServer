<?php
/**
* 求购配件列表
* 接口参数: 8段 * userid * page 
* author pwj
* date 2017-06-06
*/
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$page = trim($reqlist[9]);
if($page < 1){
    forExit($lock_array);
    toExit(25, $return_list);
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

/*测试数据
$userid = 1;
$state = 2;
*/

$offset = ($page - 1) * 10;
$sql = "select bid, appuid, bname, sname, cname, jname, picture, vin,img, type from  zj_border ORDER by bid desc limit $offset,10";
$Buylist = dbLoad(dbQuery($sql, $con));
foreach($Buylist as &$v)
{
    if(!empty($v['type']) ||  $v['type'] == 0 )
    {
        $type = substr($v['type'] ,0,1);
        switch($type)
        {
            case 0:
                $v['type'] = '原厂';
                break;
            case 1:
                $v['type'] = '拆车';
                break;
            case 2:
                $v['type'] = '品牌';
                break;
            case 1:
                $v['type'] = '其他';
                break;
        }
    }
    if(!empty($v['picture']))
    {
        $v['picture'] = $s_url.json_decode($v['picture'])[0];
    }
}

$r_list['wantBuy'] = $Buylist;

forExit($lock_array, $con);
$return_list['data'] = json_encode($r_list);
toExit(0, $return_list, false);
?>