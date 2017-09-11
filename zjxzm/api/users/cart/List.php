<?php
/**
 * 购物车商品列表
 * 接口参数: 8段 * userid * 页码
 * author wangrui@min-fo.com
 * date 2015-11-13
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

//获取类别列表
//$sql = "select id,name from hd_goods_type order by id asc";
//$t_list = dbLoad(dbQuery($sql, $con));
//if(count($t_list) > 0){
//    foreach($t_list as &$t_item){
//        if($t_item['id'] == $tid){
//            $t_item['status'] = 1;
//        }else{
//            $t_item['status'] = 0;
//        }
//    }
//}else{
//    $t_list = array();
//}

//获取列表
$limit = " limit ".(($page - 1)*10).",10";
$sql = "select * from hd_cart where userid = ".$userid." order by id desc".$limit;

$g_list = dbLoad(dbQuery($sql, $con));
if(count($g_list) > 0){
    //检查当前用户身份
    $sql = "select group_id from hd_user_usergroup_map where user_id = $userid";
    $re = dbLoad(dbQuery($sql, $con), true);
    $group = $re['group_id'];
    if(!($group == 9 || $group == 2 || $group == 3)){
        forExit($lock_array, $con);
        toExit(11, $return_list);
    }

    //如果是微商，获取微商等级
    if($group == 9){
        $sql = "select level from hd_users where id = $userid";
        $re = dbLoad(dbQuery($sql, $con), true);
        $level = $re['level'];
    }

    foreach($g_list as &$g_item){
        $goods_num = $g_item['goods_num'];
        $sql = "select intro,simg,name,price,ng_price,h_price,m_price,l_price from hd_goods where goods_num = '$goods_num'";
        $ginfo = dbLoad(dbQuery($sql, $con), true);

        $g_item['simg'] = $s_url.$ginfo['simg'];
        $g_item['goods_name'] = $ginfo['name'];
        $g_item['intro'] = $ginfo['intro'];
        // 产品是否能购买
        $g_item['overtime'] = 1;
        if($group == 9){
            switch ($level) {
                case 1:
                    $g_item['ng_price'] = $ginfo['l_price'];
                    break;
                case 2:
                    $g_item['ng_price'] = $ginfo['m_price'];
                    break;
                case 3:
                    $g_item['ng_price'] = $ginfo['h_price'];
                    break;
                default:
                    $g_item['ng_price'] = $ginfo['l_price'];
                    break;
            }
        }
        elseif($group == 2){
            $g_item['ng_price'] = $ginfo['price'];
        }
        else{
            $g_item['ng_price'] = $ginfo['ng_price'];
        }
        //判断购物车中的商品是否为秒杀商品
        if($g_item['is_sk'] == 1)
        {
            $time = time();
            $sql = "select state from hd_seckill where goods_num = '$goods_num' and $time between stime and etime";
            $overtime = dbLoad(dbQuery($sql, $con), true);
            //判断购物车中的秒杀商品是否超过出售时间或下架,$g_item['overtime'] = 0 超时或下架 1 正常
            if(empty($overtime) || $overtime['state'] != 1)
            {
                $g_item['overtime'] = 0;
            }

            $sql = "select sk_price from hd_seckill where goods_num = '$goods_num'";
            $skinfo = dbLoad(dbQuery($sql, $con), true);
            $g_item['ng_price'] = $skinfo['sk_price'];
        }
    }
}else{
    $g_list = array();
}

forExit($lock_array, $con);
$return_list['data'] = json_encode($g_list);
toExit(0, $return_list, false);

?>