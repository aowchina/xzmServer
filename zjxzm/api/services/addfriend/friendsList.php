<?php
/**
 * 好友列表
 * 接口参数: 8段 * userid(sid:卖家id) * page 分页
 * author mo_yu
 * date 2017-06-23
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
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

$page = trim($reqlist[9]);
if(empty($page)){
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
//取出好友id
$sql = "select aid from zj_friends where sid =".$userid;
$idList =  dbLoad(dbQuery($sql, $con));

if(empty($idList))
{
    forExit($lock_array, $con);
    toExit(0, $return_list);
}

$ids = array();//所有好友id
foreach($idList as $key=>$value){
    $ids[] = $value['aid'];
}

//查出所有好友id
$limit = " limit ".(($page - 1)*10).",10";
$sql = "select * from zj_appuser where appuid in (".implode(',',$ids).") order by appuid ".$limit;
$friendInfo = dbLoad(dbQuery($sql, $con));

if(empty($friendInfo))
{
    forExit($lock_array, $con);
    $return_list['data']=json_encode($friendInfo);
    toExit(0, $return_list,false);
}

//处理图片地址
foreach($friendInfo as $k=>$v){
    $friendInfo[$k]['picture'] = $s_url.$v['picture'];
}

forExit($lock_array, $con);
$return_list['data'] = json_encode($friendInfo);
toExit(0, $return_list, false);

?>
