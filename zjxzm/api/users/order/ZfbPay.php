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

$price=$list['money'] * $list['amount'];  //不考虑议价,订单价格=价格*数量

    if(bccomp($price, $order_info['money']) != 0){
        forExit($lock_array, $con);
        toExit(41, $return_list);
    }


$aop = new AopClient;
$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
$aop->appId = "2017070707675274";
$aop->rsaPrivateKey = 'MIIEogIBAAKCAQEAkD8QLAvdSVKALHU8MY94o1Jk8nMLm2Jv4QvWSvv5QDK+5QzM28tu1ZvA6LdCW7y5NWkKLydahwfyh4wd+wai+XAf7aFAUDR6Rf1AL/8oMTkSneJNLNmdjVzL8easjXvOmNye4cUSP6eoJ4c3pYL6nAB94hL5CSD8IOlS4AbcxBbAWYyHOY0TpCoy6h4H1NdgYWqkgwsKOaBV4CPLrZkc1AqyaEysw0tumA0nKJFmN4EL1m8mLNFLLLGykW3YD6PO6C9Hw7Cl6jDCYA1LDEY7ty2XLdm7Gjw+Uowrx/qdi2714RAx4YAgGt/6II24RbnE9XrHzpvWiwYuTm8L2fwMDwIDAQABAoIBAGxHGnGl2k9O4a39ttiRFQKsN+CTIXRbeRYal5qj+J8LOKahbCnVVHZ+O1m1LtfEG9cO6TkqNldETPcY4+xqN+48D4uGTsumCN7+0q70vwvsBqCDnmD7Xbwem20TNhXiiNvQSGe7Ug58YMqDQu84Gbz+1X/dtBj5LKnb230VSaTbNvJQrf3m3XpkZ2WaSihFXKCPaFbSYobeOqCMtPQe6ANifs+S/WyWfNv6ZY+HUAdWJ0OuBrGlpoVUhLkg7RQV8ux6KakqZGB74Ijsmfivmrg7ggpluVdHJ1YhPDxP7UxgMtg4oOba5xlUdhzYVO2nRiiL9RK3vN0zEEzYFRA9DnkCgYEA4edN2pDhioSLsILFSc6rpQclTp9eZvGgxy6jTc8L4K+YLxRiOY15QBDkMr6YFZsPALPl0QnqVMIeB4fOfquCS1uGoPK3lIXB9m0Z5guYIW18RLDrhKM7L+gEZiAmuJ6nnyMhfsGbj4XrV9RIZxarPAR8d4pEVsBe9oRiLrd0EhUCgYEAo3a/f2YH++mRcEdlph7G3L7la4ExulfUVmETZEyCIR4clk+SWvEnAu74B/xbXeiaFAjEDNIzPnEjesI9IxxFkiDTWmb7ZtAiVmGLxwOt/xqqN7cNgnilXMyZC/dZ3hs8RZfhQfiW9gXtQLG4q6bQxwX+ygAtrwwnT8TNh3FLgpMCgYBOoAzu16JIbd2Yr8su5ynCpHwNo1ZeChdlfAwGltuRdkxHhpaxZVYgQEDMkJ2qV2+fVP3WBddzbKS7Bj+Owu70f4SaHCBdJzjdJhfvg3WNnOe1mMZAQfflsqFlUidn9oBs2PNdhNE54OiKhy40AyXSG9WeXZdvgkELGAR8MgnKCQKBgF3Wb3w9IFw26yzfb7T7egGC/MTIN7nXbafgtncfjJxiYtrO9x+JzHFcqbo73l98heb2WUVa7dvsqwdvthUPx8hpW6tL7wpIdU3NVaZ7sEkAD7NIPFMn2xX4xesepdJhJCp3U/LBODgnktlOrMqtSrDV+jwP+inA+lhKjAoyubxNAoGAVR3QJFzyTFf0UuBt4QfCC7rF4Pa9LIYiZS53ZrxcpXZIcXoNuamRQhJlnjWfhQfXt9Ft99d0kUDsOkxKlUYvUV1jso+p+ubeA/OBAvDPdclfeZWC+D7jQ7kAc75nUoWX3+ARkgRH624TtvQgnXwX81ltkq2NoJ3rUvZNgEhUZps=' ;
$aop->format = "json";
$aop->charset = "UTF-8";
$aop->signType = "RSA2";
$aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAph/UQq2sj3YisThkJQ6KcLsbXxyuZsjj1yGmQZWg+DO2NoZM6WCxUSQ8sOVdrHGBw647lk1Hd14AFcfa+HW2s8jar+uPzAMDJ2A873/+nPOBaoFeqccIepqv/KzKxuFWy37M02De24ER+pxkFvKpvj4P+u+XwRVK8vmZ4rKvZ/CDIPHb8V7fXZ3McH5LC4SVaJfxlAcV+f65D5SsNoNXWMdV2FH6SA4XXMJwSsyMzIf2+Toc235yr5SIY8J85ptrqJnGk3SCJCzJNguXCSUfE2VIsAUozEJ8b24Prv8+69gMmDaLo1wqep4eYheBgZzfnTgtPHGfiM08f9KmWarIuwIDAQAB';
//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
$request = new AlipayTradeAppPayRequest();
//SDK已经封装掉了公共参数，这里只需要传入业务参数
$res = json_encode([
    'subject' => 'App支付',
    'out_trade_no' => $orderid,
    'timeout_express' =>'60m',
    'total_amount' => $price,
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
