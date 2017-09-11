<?php
/**
 * 买家报价列表
 * 接口参数: 8段 * userid * page * 求购配件id
 * author pwj
 * date 2017-06-14
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 11)){
    forExit($lock_array);
    toExit(9, $return_list);
}

$page = trim($reqlist[9]);
if($page < 1){
    forExit($lock_array);
    toExit(25, $return_list);
}

$bid =  trim($reqlist[10]);
if($bid < 1 || $bid > 4294967296){
    forExit($lock_array);
    toExit(26, $return_list);
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
$bid = 30;
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
$sql = "select a.type , a.price,a.id,a.sellerid,b.bname,b.sname,b.cname,c.tel,c.name,c.picture from zj_setmoney as a left join zj_border as b on a.bid = b.bid left join zj_seller as c on a.sellerid = c.sellerid where a.bid = $bid limit $offset,10";
$setMoneyList = dbLoad(dbQuery($sql, $con));

if(!empty($setMoneyList))
{
    foreach($setMoneyList as &$v)
    {
        if(!empty($v['type']) || $v['type'] == 0)
        {
            $typelist = explode(',',$v['type']);
            $pricelist = explode(',',$v['price']);
             switch($typelist[0])
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
            $v['price'] = $pricelist[0];
            $v['picture'] = $s_url . $v['picture'];

        }
    }
}
else
{
    $setMoneyList = [];
}

$r_list['setMoneyList'] = $setMoneyList;

forExit($lock_array, $con);
$return_list['data'] = json_encode($r_list);
toExit(0, $return_list, false);
?>