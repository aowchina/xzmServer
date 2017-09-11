<?php
/**
 * 收索产品
 * 接口参数: 8段 * userid * 商品名称(goodname)
 * author zhangqin@min-fo.com
 * date 2017-02-27
 */
//include_once("../functions_mut.php");
include_once("../functions_mdb.php");
//include_once("../functions_mcheck.php");

////验证参数个数
//if(!(count($reqlist) == 10)){
//    forExit($lock_array);
//    toExit(9, $return_list);
//}

//$goodname = trim($reqlist[9]);
$goodname="牛肉";


////验证userid
//$userid = trim($reqlist[8]);
//if($userid < 1 || $userid > 4294967296){
//    forExit($lock_array);
//    toExit(10, $return_list);
//}
//$user_path = $j_path.'user/'.getSubPath($userid, 4, true);
//if(!is_dir($user_path)){
//    forExit($lock_array);
//    toExit(11, $return_list);
//}
//if(is_file($user_path."lock")){
//    forExit($lock_array);
//    toExit(11, $return_list);
//}
//if(!file_put_contents($user_path."lock", " ", LOCK_EX)){
//    forExit($lock_array);
//    toExit(11, $return_list);
//}
//$lock_array[] = $user_path."lock";

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
//查出此类商品的平台货号和名称
$sql="select name,goods_num from hd_goods where name like '%$goodname%' and status =1";
$result = dbLoad(dbQuery($sql, $con));
 var_dump($result);die;
//$data=array();
foreach($result as $k=>$v){

      $data=$v['goods_num'];

    //此类商品是否是秒杀产品
    $count = dbCount('hd_seckill', $con,"state=1 and goods_num='$data'");
    // var_dump($count);
    if($count ==1){
        // echo "111";
//       var_dump($data);
        $sql="select b.goods_num,b.name,a.state from hd_seckill a left join hd_goods b on a.goods_num=b.goods_num where a.state=1 and a.goods_num='$data'";
        $res = dbLoad(dbQuery($sql, $con),true);

       // var_dump($resms);
    }else{
        $res=$result;
        // var_dump($res);die;
    }
//    echo "1111";


    if(count($res)<=0){
        forExit($lock_array, $con);
        toExit(54, $return_list);
    }
    return $res;
//    var_dump($res);
    
}
$return_list['data'] = json_encode($res);
    var_dump($return_list['data']);
forExit($lock_array, $con);

toExit(0, $return_list, false);
?>
