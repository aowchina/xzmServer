<?php
/** 
 * 获取支付信息
 * 参数：8段 * userid * order_id
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

$wx_url = "http://zjxzm.min-fo.com/api/users/order/WxNotify.php";//微信回调url
$wx_ip = "127.0.0.1";//微信请求ip
$orderid = trim($reqlist[9]);
if(!preg_match('/^zj[0-9]+$/', $orderid)){
    forExit($lock_array);
    toExit(29, $return_list);
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

//订单是否存在
$count = dbCount("zj_order", $con, "orderid = '$orderid' and appuid = $userid and status = 0");
if($count != 1){
    forExit($lock_array, $con);
    toExit(30, $return_list);
}

$sql = "select money,pid from zj_order where orderid = '$orderid'";
$order_info = dbLoad(dbQuery($sql, $con), true);

if(empty($order_info['pid'])){
    forExit($lock_array, $con);
    toExit(37, $return_list);
}


//再次验证订单中的商品价格与数量
$sql = "select goodid,money,amount from zj_order_goods where orderid = '$orderid'";
$list = dbLoad(dbQuery($sql, $con),true);

$price=$list['money'] * $list['amount'] ; //不考虑议价,订单价格=价格*数量

    if(bccomp($price, $order_info['money']) != 0){
        forExit($lock_array, $con);
        toExit(41, $return_list);
    }


//更新订单详情和微信支付交易id
$data = [];
$now_time = time();
$data['pay_id'] = $pay_id = $now_time.rand(100,999);
if(!dbUpdate($data, 'zj_order', $con, "orderid = '$orderid'"))
{
    forExit($lock_array, $con);
    toExit(302, $return_list);
}


//拼接请求微信的数组
$req = array();
$req['appid'] = "wx4d9f692ef2985664";
$req['body'] = "浙江心之盟平台";
$req['mch_id'] = "1481528832";
$req['nonce_str'] = md5($now_time);    //随机字符串,32位
$req['notify_url'] = $wx_url;
$req['out_trade_no'] = $orderid;//之前这里传的是pay_id
$req['spbill_create_ip'] = $wx_ip;
$req['total_fee'] = $price*100;
$req['trade_type'] = "APP";
$req['sign'] = getSign($req);

//拼接请求微信服务器的xml
$post_xml = getXml($req);
$data = file_get_contents_post("https://api.mch.weixin.qq.com/pay/unifiedorder", $post_xml);

//解析微信返回的xml
if(!$data){
    forExit($lock_array, $con);
    toExit(100, $return_list);
}

$xml_array = simplexml_load_string($data, null, LIBXML_NOCDATA);

if($xml_array->return_code != "SUCCESS"){
    forExit($lock_array, $con);
    toExit(71, $return_list);
}

if($xml_array->result_code != "SUCCESS"){

    if($xml_array->err_code == "ORDERPAID"){
        forExit($lock_array, $con);
        toExit(72, $return_list);
    }
    else{
        $req = array();
        $now_time = time();
        $req['appid'] = "wx4d9f692ef2985664";
        $req['mch_id'] = "1481528832";
        $req['nonce_str'] = md5($now_time);    //随机字符串,32位
        $req['out_trade_no'] = $orderid;//之前这里传的是pay_id
        $req['sign'] = getSign($req);

        //拼接请求微信服务器的xml
        $post_xml = getXml($req);
        $data = file_get_contents_post("https://api.mch.weixin.qq.com/pay/orderquery", $post_xml);

        //解析微信返回的xml
        if(!$data){
            forExit($lock_array, $con);
            toExit(57, $return_list);
        }

        $xml_array = simplexml_load_string($data, null, LIBXML_NOCDATA);

        //file_put_contents($j_path.'lock/wx3', json_encode($xml_array));

        if($xml_array->return_code != "SUCCESS"){
            forExit($lock_array, $con);
            toExit(71, $return_list);
        }

        if($xml_array->result_code != "SUCCESS"){
            forExit($lock_array, $con);
            toExit(71, $return_list);
        }

        if($xml_array->trade_state != "SUCCESS" && $xml_array->trade_state != "NOTPAY"){
            forExit($lock_array, $con);
            toExit(71, $return_list);
        }
    }
}

$rs_list["pid"] = (String)$xml_array->prepay_id;
$rs_list["orderid"] = $orderid;//这不确定是返回的系统的orderid还是微信返回的
$rs_list["nonce_str"] = md5($now_time);
$rs_list["timestamp"] = $now_time;

$req2['partnerid'] = "1481528832";
$req2['pripayid'] = (String)$xml_array->prepay_id;
$req2['package'] = "Sign=WXPay";
$req2['noncestr'] = md5($now_time);
$req2['timestamp'] = $now_time;

$rs_list["sign"] = getSign($req2);

$return_list['data'] = json_encode($rs_list);
forExit($lock_array, $con);
toExit(0, $return_list, true);

function getSign($reqArray){
    $str = "";
    $key_list = array_keys($reqArray);

    for($i = 0; $i < count($reqArray); $i++){
        $str = $str.$key_list[$i]."=".$reqArray[$key_list[$i]]."&";
    }

    $new_str = $str."key=qazwsxedcrfvtgbyhnujmikolp123456";
    return strtoupper(md5($new_str));
}

//post提交
function file_get_contents_post($url, $post) {  
    $options = array(  
        'http' => array(  
            'method' => 'POST',  
            'content' => $post,  
        ),  
    );  
  
    $result = @file_get_contents($url, false, stream_context_create($options));

    return $result;  
}

function getXml($reqArray){
    $str = "<xml>";
    $key_list = array_keys($reqArray);

    for($i = 0; $i < count($reqArray); $i++){
        $str = $str."<".$key_list[$i].">".$reqArray[$key_list[$i]]."</".$key_list[$i].">";
    }

    return $str."</xml>";
}

?>