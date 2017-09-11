<?php
/**
 * 报价列表
 * 接口参数: 8段 * userid * page
 * author pwj
 * date 2017-06-14
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
/*测试数据
$userid = 8;
$page = 1;
$s_url = 'http://192.168.1.112/zjxzm/';
*/
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
$offset = ($page - 1) * 10;
//$sql = "select a.type as typelist, a.price as pricelist,a.id,b.bname,b.sname,b.cname,b.img,c.tel,c.name from zj_setmoney as a left join zj_border as b on a.bid = b.bid left join zj_seller as c on a.sellerid = c.sellerid where a.bid = $bid ";
$sql = "select a.type as typelist, a.price as pricelist,a.id,b.bname,b.sname,b.cname,b.img,b.vin,b.jname,b.picture from zj_setmoney as a left join zj_border as b on a.bid = b.bid  where a.sellerid = $userid limit $offset,10";

$setMoneyList = dbLoad(dbQuery($sql, $con));
foreach($setMoneyList as &$v)
{
    $date = [];
    if(!empty($v['typelist']) || $v['typelist'] == 0)
    {
        $typelist = explode(',',$v['typelist']);
        $pricelist = explode(',',$v['pricelist']);
        $v['typelist'] = $typelist[0];
        $v['pricelist'] = $pricelist[0];
        $v['picture'] = $s_url.json_decode($v['picture'])[0];

        foreach($typelist as $k=>$item)
        {
            $tmpDate['type'] = $item;
            $tmpDate['price'] = $pricelist[$k];
            $date[] = $tmpDate;

        }
         $v['tpdetail'] = $date;
    }
}
$r_list['setMoneyList'] = $setMoneyList;
forExit($lock_array, $con);
$return_list['data'] = json_encode($r_list);
toExit(0, $return_list, false);
?>