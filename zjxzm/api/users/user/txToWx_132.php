<?php
/** 
 * 提现到微信(企业付款给用户)
 * 参数：8段 * userid * money * openid
 * author:zhangqin
 * date:2017-3-21
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");



 $money = trim($reqlist[9]);

 $openid = trim($reqlist[10]);

 //验证deviceid
$deviceid = trim($reqlist[1]);
if(empty($deviceid) || !preg_match("/^[0-9a-zA-Z-]+$/", $deviceid)){
    toExit(6, $return_list);
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

// $money=1.51;
// $openid="oTelMwHoQ29EpxFd-k58oAGhtZSE";
// $userid=1;
// $tid=1;


//$money<2w
if($money>20000 || $money <1 ){
    forExit($lock_array);
    toExit(55, $return_list);
}
// //可以保留两位小数
// if(is_float($money*100) ){
//     forExit($lock_array);
//     toExit(108, $return_list);
// }

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


//用户是否存在
$condition = "appuid = '$userid'";
$count = dbCount('zj_appuser', $con, $condition);
if($count != 1) {
    forExit($lock_array, $con);
    toExit(10, $return_list);
}
//用户是否登入
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=1";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

//提现的金额是否<钱包的余额
//钱包是否有钱
$condition = "userid = '$userid' and tid =1";
$count = dbCount('zj_wallet', $con, $condition);
if($count != 1) {
    forExit($lock_array, $con);
    toExit(57, $return_list);//此种情况余额为0
}

//查出此用户的余额
$sql="select money from zj_wallet where userid=".$userid ." and tid=1";
$result=dbLoad(dbQuery($sql, $con),true);
$wallet=$result['money'];
//余额不足
if($money>$wallet){
    forExit($lock_array, $con);
    toExit(57, $return_list);
}

$daytime=date('Y-m-d',time());
$time=time();
//今日用户是否提款并限10次
$condition = "userid='$userid' and tid=1 and time='$daytime";
$count = dbCount('zj_txtowx', $con, $condition);
if($count > 1 && $count <10 ) {
    //是否大于2w
    $sql="select sum(money) as money from zj_txtowx where userid='$userid' and tid=1 and time='$daytime'";
    $result=dbLoad(dbQuery($sql, $con),true);
    if($result['money']>=20000){
        forExit($lock_array, $con);
        toExit(55, $return_list);
    }

    //此用户距上次提现是否<15秒
     //查出上次提现的时间
    $sql="select paytime from zj_txtowx where userid='$userid' and tid=1 order by time desc limit 1";
    $result=dbLoad(dbQuery($sql, $con),true);
    //判断
    if($time-$result['paytime']<=15){
        forExit($lock_array, $con);
        toExit(54, $return_list);
    }
}else{
    if($count >10){
        forExit($lock_array, $con);
        toExit(56, $return_list);
    }
}


function curl_post_ssl($url, $vars, $second=30,$aHeader=array())
{
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_TIMEOUT,$second);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
    //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
//   curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
    curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/apiclient_cert.pem');
//    curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
    curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'/apiclient_key.pem');
//   curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');

    if( count($aHeader) >= 1 ){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
    }

    curl_setopt($ch,CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
    $data = curl_exec($ch);

    if($data){
//        var_dump($data);
        curl_close($ch);
        return $data;
    }
    else {
        $error = curl_errno($ch);
        echo "call faild, errorCode:$error\n";
         var_dump(curl_strerror($error));

        curl_close($ch);
        return false;
    }
}

$time = time();
$req = array();

//拼接企业转账参数
 $req['mch_appid']="wxa5b09b46e7eb4d1b";
 $req['mchid']='1328387001';

// $req['mch_appid']="wx9b8bd56c56ab2c7b";
// $req['mchid']='1259272501';

$req['nonce_str']= md5($time);
$req['openid']=$openid;
$req['partner_trade_no']= $time;
$req['check_name']= 'NO_CHECK';
$req['amount']= $money*100;
$req['desc']= "提现";
$req['spbill_create_ip']= "127.0.0.1";
$req['sign']= getSign($req);



$post_xml = getXml($req);

$data = curl_post_ssl('https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', $post_xml);
//print_r(simplexml_load_string($data));






//解析返回值
//解析微信返回的xml
if(!$data){
    forExit($lock_array, $con);
    toExit(57, $return_list);
}

$xml_array = simplexml_load_string($data, null, LIBXML_NOCDATA);


//是否成功
if($xml_array->return_code!="SUCCESS"){
    forExit($lock_array, $con);
    toExit(58, $return_list);
}

if($xml_array->return_msg != ""){
//    var_dump($xml_array->return_msg);
    if($xml_array->err_code=="AMOUNT_LIMIT"){
        forExit($lock_array, $con);
        toExit(59, $return_list);
    }
    if($xml_array->err_code=="OPENID_ERROR"){
        forExit($lock_array, $con);
        toExit(60, $return_list);
    }
    if($xml_array->err_code=="SYSTEMERROR"){
        forExit($lock_array, $con);
        toExit(61, $return_list);
    }
    if($xml_array->err_code=="V2_ACCOUNT_SIMPLE_BAN"){
        forExit($lock_array, $con);
        toExit(62, $return_list);
    }

       forExit($lock_array, $con);
        toExit(58, $return_list);

}



//返回数据
$rs_list["mch_appid"] = (String)$xml_array->mch_appid;
$rs_list["mchid"] = $xml_array->mchid;
$rs_list["nonce_str"] = md5($time);
$rs_list["result_code"]=$xml_array->result_code;

if($rs_list["result_code"]!="SUCCESS"){
    forExit($lock_array, $con);
    toExit(58, $return_list);

}

if($xml_array->return_code=="SUCCESS" && $rs_list["result_code"]=="SUCCESS"){

    $shorderid= $xml_array->partner_trade_no;
    $wxorderid= $xml_array->payment_no;
    $paytime= $xml_array->payment_time;

    $rs_list['result']="成功";
    $rs_list["shorderid"]=$shorderid;
    $rs_list["orderid"]=$wxorderid;
    $rs_list["paytime"]=$paytime;
}
//var_dump($rs_list);

//提现成功,余额钱减少
$res['money'] = $wallet-$money;
$res['addtime'] =time();
if(!dbUpdate($res, 'zj_wallet', $con, "userid = '$userid' and tid=1")){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

// 提现记录写到txtowx表
$data_in['userid'] = $userid;
$data_in['tid'] = "1";
$data_in['paytime'] = strtotime($paytime);
$data_in['txmoney'] = $money;
$data_in['txorderid'] = $wxorderid;
$data_in['type'] = "2";

if(!dbAdd($data_in, 'zj_txtowx', $con)){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

//提现钱减少写到钱包记录表
$wr_in['userid'] = $userid;
$wr_in['tid'] = "1";
$wr_in['addtime'] = strtotime($paytime);
$wr_in['money'] = $money;
$wr_in['type'] = 3;
if(!dbAdd($wr_in, 'zj_wrecord', $con)){
    forExit($lock_array, $con);
    toExit(302, $return_list);
}



$return_list['data'] = json_encode($rs_list);
forExit($lock_array, $con);
toExit(0, $return_list, true);




function getSign($reqArray){
    ksort($reqArray);
    $str = "";
    $key_list = array_keys($reqArray);

    for($i = 0; $i < count($reqArray); $i++){
        $str = $str.$key_list[$i]."=".$reqArray[$key_list[$i]]."&";
    }

    $new_str = $str."key=qazwsxedcrfvtgbyhnujmikolp123456";
    return strtoupper(md5($new_str));
}




function file_get_contents_post($url, $post) {
    $options = array(
        'http' => array(
            'method' => 'POST',
            'content' => $post,
        ),
    );

    $result = file_get_contents($url, false, stream_context_create($options));

    return $result;
}


//把请求的参数拼成xml文件
function getXml($reqArray){
    $str = "<xml>";
    $key_list = array_keys($reqArray);

    for($i = 0; $i < count($reqArray); $i++){
        $str = $str."<".$key_list[$i].">".$reqArray[$key_list[$i]]."</".$key_list[$i].">";
    }

    return $str."</xml>";
}

























?>