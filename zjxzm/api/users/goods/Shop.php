<?php
/**
 * 店铺商品列表
 * 接口参数: 8段 * userid8 *  shopid9 * 页码10
 * author pwj
 * date 2017-06-03
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 11)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证页码
$page = intval(trim($reqlist[10]));
if($page < 1){
    forExit($lock_array);
    toExit(28, $return_list);
}

//验证userid
$userid = trim($reqlist[8]);
if($userid < 1 || $userid > 4294967296){
    forExit($lock_array);

    toExit(10, $return_list);
}

//shopid
$shopid = trim($reqlist[9]);
if($shopid < 1 || $shopid > 4294967296){
    forExit($lock_array);

    toExit(32, $return_list);
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
if ($con == '') {
    forExit($lock_array);
    toExit(300, $return_list);
}

//检查连接数
if (!checkDbCon($con)) {
    forExit($lock_array, $con);
    toExit(301, $return_list);
}

/*测试数据
$sellerid = 2;
$userid = 1;
$page = 1;
$s_url = 'http://192.168.118/hondo_wx/';
*/
$data = [];
//店铺信息
$sql = "select tel,shopname,picture,rate from zj_shop where shopid = $shopid";
$shopInfo = dbLoad(dbQuery($sql, $con), true);
$shopInfo['picture'] = $s_url . $shopInfo['picture'];
//$shopid = $shopInfo['shopid'];
$data['shopInfo'] = $shopInfo;

//店铺的全部商品
$offset = ($page - 1) * 10;
$where = " a.shopid = $shopid and a.state = 1 and a.is_sj = 1";
$sql="select a.goodid,a.name,a.price,a.img,b.tname from zj_good a left join zj_type b on a.typeid=b.typeid where $where limit $offset,10";
$allList = dbLoad(dbQuery($sql, $con));

if (empty($allList)) {

    $allList = [];

} else {
    foreach ($allList as $k => $v) {
        if($v['img'])
        {
          $allList[$k]['img'] = $s_url.json_decode($v['img'])[0];
        }
        
    }
    
}

$count = dbCount('zj_good', $con, "shopid = $shopid and state=1 and is_sj=1");
$allCount = intval($count);
$data['allGoods'] = $allList;
$data['allCount'] = $allCount;

//店铺的新品(默认添加时间倒叙前10个)
$sql="select a.goodid,a.name,a.price,a.img,b.tname from zj_good as a left join zj_type as b on a.typeid=b.typeid where $where order by a.addtime limit 10";
$newList = dbLoad(dbQuery($sql, $con));
if (!empty($newList)) {
    foreach ($newList as &$v) {
         if(!empty($v['img']))
        {
           $v['img'] = $s_url.json_decode($v['img'])[0];
        }
        
    }
} else {
    $newList = [];
}
$newCount = count($newList);
$data['newGoods'] = $newList;
$data['newCount'] = $newCount;

//销量
$sellCount = dbCount('zj_order_goods',$con ,"shopid = $shopid");
$data['sellCount'] = $sellCount;

$s_data['number']=$sellCount;
if(!dbUpdate($s_data, "zj_shop", $con, "shopid = '$shopid'"))
{
    forExit($lock_array);
    toExit(302, $return_list);
}

forExit($lock_array, $con);
$return_list['data'] = json_encode($data);
toExit(0, $return_list, false);
?>