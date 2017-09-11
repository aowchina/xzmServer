<?php
/**
 * 配件商列表
 * 接口参数: 8段 * userid * $page
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

//验证page
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

//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app = 1";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

//更新登录时间
$now_time = time();
$data = array();
$data['lastvisitDate'] = $now_time;
dbUpdate($data, 'zj_appuser', $con, "appuid = $userid");

//返回配件商列表
$host = $s_url;
$limit = " limit ".(($page - 1)*10).",10";
$sql = "SELECT a.name, a.picture,a.sellerid, b.major, b.skill, b.company FROM zj_seller AS a LEFT JOIN zj_sellercert AS b ON ( a.sellerid = b.sellerid ) where a.is_rz=1 and a.type!=2 ORDER BY a.sellerid".$limit;
$pjsList = dbLoad(dbQuery($sql,$con));
if(!$pjsList){
    forExit($lock_array, $con);
    $return_list['data'] = json_encode($pjsList);
    toExit(0, $return_list);//获取配件商列表有误
}
//处理图片地址
foreach ($pjsList as $key => $value) {
    $sql = 'select id from zj_friends where sid='.$value['sellerid'].' and aid='.$userid;

    $pjsList[$key]['is_friend'] = dbLoad(dbQuery($sql,$con),true) ? 1 : 2;

    $pjsList[$key]['picture'] = $host.$pjsList[$key]['picture'];
}

forExit($lock_array, $con);
$return_list['data'] = json_encode($pjsList);
toExit(0, $return_list);

?>
