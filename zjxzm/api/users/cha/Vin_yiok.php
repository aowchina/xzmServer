<?php
/**
 * 车架号查询
 * 接口参数: 8段 * userid * vin
 * author zq
 * date 2017-06-06
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");

if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
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
//
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
$vin= trim($reqlist[9]);
if(empty($vin)){
    forExit($lock_array);
    toExit(13, $return_list);
}



//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=1";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

$condition = "vin = '$vin'";
$count = dbCount('zj_car', $con, $condition);

if($count == 1){

    $sql="select cimage from zj_car where vin='$vin'";
    $res=dbLoad(dbQuery($sql, $con),true);
    $data['cimage']=$s_url.$res['cimage'];
    $data['biaoshi']=1;
}else{
    $data['biaoshi']=0;
}

    //md5签名方式--非简单签名
    header("Content-Type:text/html;charset=UTF-8");
    date_default_timezone_set("PRC");
    $showapi_appid = '41472';  //替换此值,在官网的"我的应用"中找到相关值
    $showapi_secret = 'dbc045771f6c49d695b31efe84f181ad';  //替换此值,在官网的"我的应用"中找到相关值
    $paramArr = array(
        'showapi_appid'=> $showapi_appid,
        'vin'=> $vin
    );

    //创建参数(包括签名的处理)
    function createParam ($paramArr,$showapi_secret) {
        $paraStr = "";
        $signStr = "";
        ksort($paramArr);
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $signStr .= $key.$val;
                $paraStr .= $key.'='.urlencode($val).'&';
            }
        }
        $signStr .= $showapi_secret;//排好序的参数加上secret,进行md5
        $sign = strtolower(md5($signStr));
        $paraStr .= 'showapi_sign='.$sign;//将md5后的值作为参数,便于服务器的效验
//        echo "排好序的参数:".$signStr."<br>\r\n";
        return $paraStr;
    }

    $param = createParam($paramArr,$showapi_secret);
    $url = 'http://route.showapi.com/1142-1?'.$param;
    $result = file_get_contents($url);

    $data['che'] = json_decode($result,true);

    if($data['che']['showapi_res_code']==0){
        forExit($lock_array, $con);
        $return_list['data'] = json_encode($data);
        toExit(0, $return_list, false);

    }elseif($data['che']['showapi_res_code']==-2){
        forExit($lock_array, $con);
        toExit(70, $return_list, false);
}
else{
        forExit($lock_array, $con);
        $return_list['data'] = $data;
        toExit(14, $return_list, false);
    }
