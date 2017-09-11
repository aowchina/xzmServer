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

//验证deviceid
$deviceid = trim($reqlist[1]);
if(empty($deviceid) || !preg_match("/^[0-9a-zA-Z-]+$/", $deviceid)){
    toExit(6, $return_list);
}

//获取当前用户登录状态
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=0";
$count = dbCount('zj_user_login', $con, $condition);

if($count != 1){
   forExit($lock_array, $con);
   toExit(12, $return_list);
}

//$s_url="http://sdfg";
//$vin="lfv2a2150a3043256";
// $vin= "LFV2B21K4C3270035";
$condition = "vin = '$vin'";
$count = dbCount('zj_car', $con, $condition);

if($count == 1){

    $sql="select cimage from zj_car where vin='$vin'";
    $res=dbLoad(dbQuery($sql, $con),true);
    $vin_data['cimage']=$s_url.$res['cimage'];
    $vin_data['biaoshi']=1;
}else{
    $vin_data['biaoshi']=0;
}

header("content-type:text/html;charset=utf-8");

$url = "http://service.vin114.net/req?wsdl";
$method = "LevelData";
$arr =[
    "appkey" => "752e8cf232113f26",
    "AppSecret" => "D2EED1EB456647988AA9EB4C79DFF36A",
    "Method" =>"level.vehicle.vin.get",
    "requestformat" => "json",
    "vin"=>"$vin",
];

//数组转XML
function arrayToXml($arr)
{
    $data = "<root>";
    foreach ($arr as $key=>$val)
    {
        if (is_numeric($val)){
            $data.="<".$key.">".$val."</".$key.">";
        }else{
//            $data.="<".$key."><![CDATA[".$val."]]></".$key.">";
            $data.="<".$key.">".$val."</".$key.">";
        }
    }
    $data.="</root>";
    return $data;
}

$data= arrayToXml($arr);
$client = new SoapClient($url);
$addResult = $client->__soapCall($method,array(array('xmlInput'=>$data)));

$result= json_decode($addResult->LevelDataResult);//字符串转对象

$res = object_to_array($result);//对象转数组


$res = deep_object_to_array($res,'Result');//数组内部(隐藏的对象)转数组--彻底转数组

$vin_data['che'] = $result;

$return_list= array();
if(!empty($vin_data)){
    forExit($lock_array, $con);
    $return_list['data'] = json_encode($vin_data);
    toExit(0, $return_list, false);

}
else{
    forExit($lock_array, $con);
    $return_list['data'] = $vin_data;
    toExit(14, $return_list, false);
}

/*************************************************华丽分割线**********************************/
function object_to_array($obj){
    if(is_array($obj)){
        return $obj;
    }

    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;

    foreach ($_arr as $key => $val){
        $val = is_object($val) ? object_to_array($val) : $val;
        $arr[$key] = $val;
    }

    return $arr;
}

function deep_object_to_array($arr,$field){

    if(is_object($arr[$field][0])){
        foreach($arr[$field] as $k => $v){
            $v = (is_array($v)) || is_object($v) ? object_to_array($v) : $v;
            $arr[$field][$k] = $v;
        }
    }

    return $arr;
}

