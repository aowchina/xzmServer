<?php
/**
 * 我的收藏(列表)
 * 接口参数: 8段 * appuid(用户id)
 * author zq
 * date 2017-06-9
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 9)){
    forExit($lock_array);
    toExit(9, $return_list);
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

//$userid =1;
//$s_url = 'http://192.168.1.113/zjxzm/';
//用户是否存在
$condition = "appuid = '$userid'";
$count = dbCount('zj_appuser', $con, $condition);

if($count != 1) {
    forExit($lock_array, $con);
    toExit(11, $return_list);
}

//收藏中是否有此记录
$condition = "appuid = '$userid'";
$count = dbCount('zj_appuser', $con, $condition);

if($count < 1){
    //没有此用户记录收藏为空
    $collet=" ";

}else{
    //用户收藏
    $sql="select e.bname,d.sname,c.cname,b.name,b.goodid,b.price,b.img from zj_collect a
left join zj_good b on a.goodid=b.goodid
left join zj_type f on b.typeid=f.typeid
left join zj_car c on f.carid=c.carid
left join zj_serial d on c.serialid=d.serialid
left join zj_brand e on d.brandid=e.brandid
where a.appuid ='$userid'";
    $res= dbLoad(dbQuery($sql, $con));
    foreach($res as  $k => $v){
            $imgs=json_decode($v['img']);
            $img = trim($imgs[0]);
        $res[$k]['img'] = $s_url.$img;
    }

}
$result['list']=$res;
$return_list['data'] = json_encode($result);

forExit($lock_array, $con);
toExit(0, $return_list);


?>
