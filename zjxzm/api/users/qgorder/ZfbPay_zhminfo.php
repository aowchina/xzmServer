<?php
/** 
 * 获取支付信息
 * 参数：8段 * userid * orderid
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../alipay/aop/AopClient.php");
include_once("../alipay/aop/request/AlipayTradeAppPayRequest.php");

$orderid = trim($reqlist[9]);
if(!preg_match('/^zjqg[0-9]+$/', $orderid)){
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
//$userid="10";
//$orderid ="zj149742825210106006595";

//订单是否存在
$count = dbCount("zj_qgorder", $con, "qgorderid = '$orderid' and appuid = $userid and status = 0");
if($count != 1){
    forExit($lock_array, $con);
    toExit(30, $return_list);
}


$sql = "select price,pid from zj_qgorder where qgorderid = '$orderid'";
$order_info = dbLoad(dbQuery($sql, $con), true);

if(empty($order_info['pid'])){
    forExit($lock_array, $con);
    toExit(37, $return_list);
}


$price = explode(',',$order_info['price']);
$total_money= array_sum($price);

// //再次验证订单中的商品价格与数量
// $sql = "select goodid,money,amount from zj_order_goods where orderid = '$orderid'";
// $list = dbLoad(dbQuery($sql, $con),true);

// $price=$list['money'] * $list['amount'];  //不考虑议价,订单价格=价格*数量

//     if(bccomp($price, $order_info['money']) != 0){
//         forExit($lock_array, $con);
//         toExit(41, $return_list);
//     }


$aop = new AopClient;
$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
$aop->appId = "2017051507242969";
$aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAtv3c1d9CI6tkNe0556C5SABZmQPIim12VK8oE8bMpBPnv+hapEnJQNIdGDsWw4a/81dIJEeSbXyhDuKgrae2PJdDtj9AhXYwZ09/LVp//OLIrk1DxBEsuzUMU33h6A3iMIJDRgH1tEcZ9XTqbYZufiO0JQD00NjySSEKnurG7CP1W2wn8wIddmZlXJ+ZejU1sMumjfq2pCa9Ikj6Wx8hJqxgvBJie1Khay86++kuG9Lp8mkumE5htj1SlEBivp0poZ/koOdmOeD63O7IOxgdOEVt3ZT8RxRJrgcCQcIVg54WxyTsXsMciakK5vZN5rouoppDPWJp/YrPdBlrptmRnQIDAQABAoIBAQC2EIyIyjGu9ZalxNpo59OQnLCIemgruk8SYJc6XgA7e4aHvLF6ZeNzt9m0ww2aClau0Pd5CTDZ+DbybW86d/PsAwAesn4Ki5YLI+BACpvuyuCp8zvqNsBPnq8d3tBGJpIWe3RdkhOZg2iDfAjGYgtLO1C/xFzP8hnVwqcjiCnUxR6DdQbRzu02vSvDwxt6hsA3VRfWugFUCyFm5O28MROGmrWQ4RK2iFYbjk7fQ+fwvTFJ6rV/PgtLeC+DUFTEUQNBwaYn4JN3bSb92KwNPi559tWOloUpqT9flRBjx715S4l5ff4os+ohKG4eO7kRcSO4IccX0LT78ztXReY0thNNAoGBAObMD8nhqSMpDsDTfFnrnxi/Devu3kLqZ1K/psUK8FlKAJgvb6bOdtTn+/EGcyP7Ppv6rRri+Ahsat11OHOaQTcpbF21lxwY+ZUNVm5KzuDnprr6os0z33fKFPNd47A16P/R12kl0hBrLBJOsKgRpHMgLjxW82nIYwTKYVnWNLR3AoGBAMr5Ze4n0EeIiMqUZO490HKgcU2iX9PI5oyJxHn93IfMKDI9tAl00kuVm6x/S3njcB4mzO8XCiI1Fyv798EkPAGY/gTSk5LH1p6zJuld0Z/xSiEE7AlgSBC9YMELX1fGLdhSSUUV56wqWb/HJWnaXls/sD7hOHXjqDNmoxg3T1OLAoGAX4q2NoJ+PBnxC44A+lB6Cgp9PuAhjl3u6+h+py9CFBR4boekls79jmGCgGKFI64MWxHIu0qeFw6appayCdkfijBRtfFIXs9P8o4U6494WM5MzTaYUo5YwgQb7Cs/6GBI1i4OGG8ZqMZU9jcxFkJHa4k02rG8Dlxv8Tm+Vyw/oT0CgYBk9LlrRGhHPDT1teuhyCMm8ICKocnSGCn8GwYbu1X2QWh43NHwpid3Ktm8abBL1wFMLfZesXH747Y7zV7EtVYXYVZvZaG7LySj2O3wwxZh3G0HkWAppbcShG9cdWCd0te4sez5rNSHgKUVS2NjjBbEgiASlokzseFWd6WFhPUy0wKBgQDK/rWOY6JR96lyo/K5zHyJFmRLrnIuVsVQqZcofo3HgAFEn1egBavXJgodkQDoLjS/xLvV5oLvafEc2A4Y0AD3IhnhV5Z0zLAVLhat3cl94/EqtYJLe4lLlCOiUNFXWQV0i/BYti0pUUA3bu22YYd3vJfSbuWVYyEu+VDHUaRaSg==' ;
$aop->format = "json";
$aop->charset = "UTF-8";
$aop->signType = "RSA2";
$aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsfRMu6JdyjkOVIIYNF+mJythCsw7sJMiW4br8IuffMAUo3gJEYtL36WHh3QLA1CxxOxvFBbMwhAgYFf0+M42fMgtXnE+CfcMrPVkLa7qPqn3iUz5ZYLB4y5EboOXZMw1rOXz+jqk4SudwSVxYmn0lL918kVgstsboI9XR2ZJgYm0VQ0fVOqLCku0Z60ThMF1+y0jlwKS6/hAzidVazyKvwZsSnPi7F/YfjZgxhZ0IQHCCCcHFJCNNlZ3Wvk6ZEgWsrR33nzgP/5/+rHobcTeP+5zxnaf0IFb7ilmJrIZYtwMV/Y+qyeguy3r3BAkY0JJZABZyHcKXkD1Wh6ANvbABwIDAQAB';
//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
$request = new AlipayTradeAppPayRequest();
//SDK已经封装掉了公共参数，这里只需要传入业务参数
$res = json_encode([
    'subject' => 'App支付',
    'out_trade_no' => $orderid,
    'timeout_express' =>'60m',
    'total_amount' => $total_money,
    'product_code' => 'QUICK_MSECURITY_PAY',
]);
$request->setNotifyUrl("http://zjxzm.min-fo.com/api/users/order/ZfbNotify.php");
$request->setBizContent($res);
//这里和普通的接口调用不同，使用的是sdkExecute
$response = $aop->sdkExecute($request);
//htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
// $r_data['data']= htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。


$return_list['data'] = $response;
forExit($lock_array, $con);
toExit(0, $return_list);
?>
