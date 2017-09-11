<?php
/**
 * 物流模板接口
 * param: 8段 * userid * 订单号(order_id)
 * author: zhangqin
 * date:2017-2-20
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");

//验证参数个数
if(!(count($reqlist)==10)){
    toExit(9,$return_list);
}

//验证userid
$userid=trim($reqlist[8]);
if($userid <1 || $userid > 4294967296){
	toExit(10,$return_list);
}

//验证order_id
$order_num=trim($reqlist[9]);
if(!preg_match('/^hondo_wx[0-9]+$/', $order_num)){
    forExit($lock_array);
    toExit(35, $return_list);
}

//打用户锁
 $user_path=$j_path.'user/'.getSubPath($userid,3,true);
if(!mkdirs($user_path)){
    toExit(11, $return_list);
}

if(is_file($user_path."lock")){
    toExit(11, $return_list);
}
if(!file_put_contents($user_path."lock", " ", LOCK_EX)){
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
$condition = "userid = '$userid' and deviceid ='$deviceid' and status = 1";
$count = dbCount('hd_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

//物流运费模板
$have_pt = false;  //包含普通商品
$have_by = false;  //包含包邮商品

$sql = "select goods_num,amount from hd_order_goods where order_id = '$order_num'";
$order_info = dbLoad(dbQuery($sql, $con));

$total_weight = 0;

if(count($order_info) > 0){
    foreach($order_info as $order_item){
        $sql = "select weight,is_baoyou from hd_goods where goods_num = '".$order_item['goods_num']."'";
        $goods_info = dbLoad(dbQuery($sql, $con), true);

        if($goods_info['is_baoyou'] == 0){
            $have_pt = true;

            $total_weight = $total_weight + $goods_info['weight'] * $order_item['amount'];
        }
        else{
            $have_by = true;
        }
    }
}

//如果全是包邮的商品
if($have_by && !$have_pt){
    //获取物流收费标准
    $sql = "select address,id,name from hd_wl where type=3";
    $re = dbLoad(dbQuery($sql, $con));
    $data['wl_price'] = 0;
    $data['name'] =$re[0]['name'];
    $data['id']=$re[0]['id'];
    $result_data[]=$data;
}
else{
    //获取物流收费标准
    $sql = "select s_money,z_money,t_money,address,id,name from hd_wl where type = 1";
    $re = dbLoad(dbQuery($sql, $con));

    if(count($re) > 0){

        foreach ($re as $k => $v) {
            if($v['t_money']!=0){
                 // 直接输出按运费金额
                $data['id'] = $v['id'];
                $data['name'] = $v['name'];
                $data['address'] = $v['address'];
                $data['wl_price'] = $v['t_money'];
                $result_data[]=$data;

            }else{
                $s_money=$v['s_money'];
                $z_money=$v['z_money'];
                //如果全是不包邮商品
                if($have_pt && !$have_by){
                    if($total_weight <= 1){
                        $data['id'] = $v['id'];
                        $data['name'] = $v['name'];
                        $data['wl_price'] = $s_money;
                        $data['address'] = $v['address'];
                        $result_data[]=$data;
                    }
                    else{
                        $data['id'] = $v['id'];
                        $data['name'] = $v['name'];
                        $data['wl_price'] = $s_money + ceil($total_weight - 1) * $z_money;
                        $data['address'] = $v['address'];
                        $result_data[]=$data;
                    }
                }
                //如果2者都有
                else{
                    $data['id'] = $v['id'];
                    $data['name'] = $v['name'];
                    $data['address'] = $v['address'];
                    $data['wl_price'] = ceil($total_weight) * $z_money;
                    $result_data[]=$data;
                }

            }
        }
    }
}

forExit($lock_array, $con);
$return_list['data'] = json_encode($result_data);

toExit(0, $return_list);


?>