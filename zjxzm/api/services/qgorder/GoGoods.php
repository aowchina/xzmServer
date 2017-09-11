<?php
/**
 * 发货(卖家端)
 * * 接口参数: 8段 * sellerid * 订单号(qgorderid) * 快递单号 * 快递id
 * author zq
 * date 2017-6-19
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 12)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$order_num = trim($reqlist[9]);
if(!preg_match('/^zjqg[0-9]+$/', $order_num)){
    forExit($lock_array);
    toExit(29, $return_list);
}

$kuaidih=trim($reqlist[10]);
if(empty($kuaidih)){
    forExit($lock_array);
    toExit(50, $return_list);
}

$kid=trim($reqlist[11]);
if(empty($kid)){
    forExit($lock_array);
    toExit(51, $return_list);
}
if($kid < 1 || $kid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
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

//$kuaidih="1223344";
//$wl_name="shentong";
//$order_num="zj149742825210106006595";
//$userid="12";

//卖家可以对他自己的商品进行发货
$sql="select b.sellerid from zj_qgorder a left join zj_setmoney b on a.bjid=b.id where a.qgorderid='$order_num'  and a.status=1";
$result = dbLoad(dbQuery($sql, $con), true);
if($userid!=$result['sellerid']){
    forExit($lock_array, $con);
    toExit(30, $return_list);
}

$sql = "select name from zj_kuaidi where id = '$kid'";
$result = dbLoad(dbQuery($sql, $con), true);


$data = array();
$data['status'] = 2;
$data['kuaidih']=$kuaidih;
$data['wlname']=$result['name'];
$data['fhtime']=time();


if(!dbUpdate($data, "zj_qgorder", $con, "qgorderid = '$order_num'")){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

forExit($lock_array, $con);
toExit(0, $return_list, false);

?>
