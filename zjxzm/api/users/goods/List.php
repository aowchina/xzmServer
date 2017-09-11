<?php
/**
 * 产品列表
 * 接口参数: 8段 * userid * 页码
 * author pwj
 * date 2017-06-01
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证页码
$page = intval(trim($reqlist[9]));
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
$page = 1;
$userid = 1;
*/
$offset = ($page - 1) * 10;
//取出已经上架的产品
$sql = "select a.goodid,a.img,a.name,a.price,b.sellerid from zj_good as a left join zj_shop as b on a.shopid = b.shopid where a.state = 1 and a.is_sj = 1 order by a.addtime desc limit $offset,10";

$list = dbLoad(dbQuery($sql, $con));


/***********pwj************/
//取出每个用户的订单
// $sql = "select GROUP_CONCAT(orderid) as orders from zj_order where status != 0 group by appuid ";
// $orders = dbResult(dbQuery($sql, $con));
/***********pwj************/

foreach($list as $key=>&$item)
{
    $amount= 0;

    /************pwj***********/
    // foreach($orders as $v)
    // {

    //     $count = dbCount('zj_order_goods', $con,"orderid in ($v) and goodid = $item[goodid]");
    //     if($count >0)
    //     {
    //         $amount++;
    //     }
    // }
    /************pwj***********/

    //计算这个商品的销量
    $sql = "select sum(amount) as sum from zj_order_goods where goodid=".$item['goodid'];
    $sum = dbLoad(dbQuery($sql,$con),true);

    $list[$key]['amount'] = $sum['sum'];

    //处理图片
    if(!empty($item['img']))
    {
        $list[$key]['img'] = $s_url.json_decode($item['img'])[0];
    }
}

//取出广告信息images/picture/123.jpg
$sql = "select img,gid,name,url from zj_ad where state = 1";
$ad = dbLoad(dbQuery($sql, $con));
foreach($ad as &$item)
{
    $item['img'] = $s_url.$item['img'];
    $item['url'] = isset($item['url'])?$item['url']:" ";
}
$r_list['ad'] = $ad;
$r_list['goods'] = $list;

forExit($lock_array, $con);
$return_list['data'] = json_encode($r_list);
toExit(0, $return_list, false);

?>
