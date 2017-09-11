<?php
/**
 * 收索产品
 * 接口参数: 8段 * userid * 商品名称(goodname)
 * author zhangqin@min-fo.com
 * date 2017-02-27
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$goodname = trim($reqlist[9]);


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

//产品是否存在
$sql="select goods_num,name from hd_goods where name like '%$goodname%' and status = 1";
$result = dbLoad(dbQuery($sql, $con));

if(count($result)<=0){
    forExit($lock_array, $con);
    toExit(54, $return_list);
}
else
{
    //从取出的产品中取出秒杀产品
    foreach($result as $k=>&$v)
    {
        $count = dbCount('hd_seckill',$con,"goods_num = '$v[goods_num]'");
        if($count == 1)
        {
            unset($result[$k]);
        }
        $v['is_sk'] = 0;
    }
}

$new_result = array_merge($result);
$result_data['data'] = $new_result;
forExit($lock_array, $con);
$return_list['data'] = json_encode($result_data);
toExit(0, $return_list, false);

?>
