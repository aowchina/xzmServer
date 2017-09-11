<?php
/** 
 * 钱包提现到支付宝
 * 参数：8段 * appuid * money * payee_account(收款方账户)
 * author:zhangqin
 * date:2017.6
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../alipay/aop/AopClient.php");
include_once("../alipay/aop/request/AlipayFundTransToaccountTransferRequest.php");
include_once("../alipay/aop/SignData.php");


//验证deviceid
$deviceid = trim($reqlist[1]);
if(empty($deviceid) || !preg_match("/^[0-9a-zA-Z-]+$/", $deviceid)){
    toExit(6, $return_list);
}
$money = trim($reqlist[9]);//只能保留两位小数

$payee_account = trim($reqlist[10]);

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

//$userid="10";
//$tid="1";
//$money="1";
//$payee_account="1402494823@qq.com";
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
$condition = "userid = '$userid' and tid=1";
$count = dbCount('zj_wallet', $con, $condition);
if($count != 1) {
    forExit($lock_array, $con);
    toExit(38, $return_list);//此种情况余额为0
}
//查出此用户的余额
$sql="select money from zj_wallet where userid=".$userid ." and tid=1";
$result=dbLoad(dbQuery($sql, $con),true);
$wallet=$result['money'];

//余额不足
if($money>$wallet){
    forExit($lock_array, $con);
    toExit(40, $return_list);
}

$orderid="zj".time();
$a = [
    'out_biz_no' => $orderid,
    'payee_type' => 'ALIPAY_LOGONID',
    'payee_account' =>$payee_account,
    'amount' => $money,
];
$b = json_encode($a);

//开启事务

//拼接请求支付宝接口的参数
$time=time();
$aop = new AopClient();
$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
$aop->appId = '2017070707675274';
$aop->rsaPrivateKey = 'MIIEogIBAAKCAQEAkD8QLAvdSVKALHU8MY94o1Jk8nMLm2Jv4QvWSvv5QDK+5QzM28tu1ZvA6LdCW7y5NWkKLydahwfyh4wd+wai+XAf7aFAUDR6Rf1AL/8oMTkSneJNLNmdjVzL8easjXvOmNye4cUSP6eoJ4c3pYL6nAB94hL5CSD8IOlS4AbcxBbAWYyHOY0TpCoy6h4H1NdgYWqkgwsKOaBV4CPLrZkc1AqyaEysw0tumA0nKJFmN4EL1m8mLNFLLLGykW3YD6PO6C9Hw7Cl6jDCYA1LDEY7ty2XLdm7Gjw+Uowrx/qdi2714RAx4YAgGt/6II24RbnE9XrHzpvWiwYuTm8L2fwMDwIDAQABAoIBAGxHGnGl2k9O4a39ttiRFQKsN+CTIXRbeRYal5qj+J8LOKahbCnVVHZ+O1m1LtfEG9cO6TkqNldETPcY4+xqN+48D4uGTsumCN7+0q70vwvsBqCDnmD7Xbwem20TNhXiiNvQSGe7Ug58YMqDQu84Gbz+1X/dtBj5LKnb230VSaTbNvJQrf3m3XpkZ2WaSihFXKCPaFbSYobeOqCMtPQe6ANifs+S/WyWfNv6ZY+HUAdWJ0OuBrGlpoVUhLkg7RQV8ux6KakqZGB74Ijsmfivmrg7ggpluVdHJ1YhPDxP7UxgMtg4oOba5xlUdhzYVO2nRiiL9RK3vN0zEEzYFRA9DnkCgYEA4edN2pDhioSLsILFSc6rpQclTp9eZvGgxy6jTc8L4K+YLxRiOY15QBDkMr6YFZsPALPl0QnqVMIeB4fOfquCS1uGoPK3lIXB9m0Z5guYIW18RLDrhKM7L+gEZiAmuJ6nnyMhfsGbj4XrV9RIZxarPAR8d4pEVsBe9oRiLrd0EhUCgYEAo3a/f2YH++mRcEdlph7G3L7la4ExulfUVmETZEyCIR4clk+SWvEnAu74B/xbXeiaFAjEDNIzPnEjesI9IxxFkiDTWmb7ZtAiVmGLxwOt/xqqN7cNgnilXMyZC/dZ3hs8RZfhQfiW9gXtQLG4q6bQxwX+ygAtrwwnT8TNh3FLgpMCgYBOoAzu16JIbd2Yr8su5ynCpHwNo1ZeChdlfAwGltuRdkxHhpaxZVYgQEDMkJ2qV2+fVP3WBddzbKS7Bj+Owu70f4SaHCBdJzjdJhfvg3WNnOe1mMZAQfflsqFlUidn9oBs2PNdhNE54OiKhy40AyXSG9WeXZdvgkELGAR8MgnKCQKBgF3Wb3w9IFw26yzfb7T7egGC/MTIN7nXbafgtncfjJxiYtrO9x+JzHFcqbo73l98heb2WUVa7dvsqwdvthUPx8hpW6tL7wpIdU3NVaZ7sEkAD7NIPFMn2xX4xesepdJhJCp3U/LBODgnktlOrMqtSrDV+jwP+inA+lhKjAoyubxNAoGAVR3QJFzyTFf0UuBt4QfCC7rF4Pa9LIYiZS53ZrxcpXZIcXoNuamRQhJlnjWfhQfXt9Ft99d0kUDsOkxKlUYvUV1jso+p+ubeA/OBAvDPdclfeZWC+D7jQ7kAc75nUoWX3+ARkgRH624TtvQgnXwX81ltkq2NoJ3rUvZNgEhUZps=';
$aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAph/UQq2sj3YisThkJQ6KcLsbXxyuZsjj1yGmQZWg+DO2NoZM6WCxUSQ8sOVdrHGBw647lk1Hd14AFcfa+HW2s8jar+uPzAMDJ2A873/+nPOBaoFeqccIepqv/KzKxuFWy37M02De24ER+pxkFvKpvj4P+u+XwRVK8vmZ4rKvZ/CDIPHb8V7fXZ3McH5LC4SVaJfxlAcV+f65D5SsNoNXWMdV2FH6SA4XXMJwSsyMzIf2+Toc235yr5SIY8J85ptrqJnGk3SCJCzJNguXCSUfE2VIsAUozEJ8b24Prv8+69gMmDaLo1wqep4eYheBgZzfnTgtPHGfiM08f9KmWarIuwIDAQAB';
$aop->apiVersion = '1.0';

$aop->signType = 'RSA2';
$aop->timestamp =date("Y-m-d h:i:s",$time);
$aop->postCharset='utf-8';
$aop->format='json';

$request = new AlipayFundTransToaccountTransferRequest ();
$request->setBizContent($b);
$result = $aop->execute ($request);

$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
$resultCode = $result->$responseNode->code;


if(!empty($resultCode)&&$resultCode == 10000){
    $rs_list['result']="成功";
    $rs_list['order_id']=$result->$responseNode->order_id;
    $rs_list['out_biz_no']=$result->$responseNode->out_biz_no;
    $rs_list['pay_date']=$result->$responseNode->pay_date;

    //提现成功,余额钱减少
    $res['money'] = $wallet-$money;
    $res['addtime'] =time();
    if(!dbUpdate($res, 'zj_wallet', $con, "userid = '$userid' and tid = 1")){
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }

     // 提现记录写到txtowx表
    $data_in['userid'] = $userid;
    $data_in['tid'] = "1";
    $data_in['paytime'] = strtotime($rs_list['pay_date']);
    $data_in['txmoney'] = $money;
    $data_in['txorderid'] = $rs_list['order_id'];
    $data_in['type'] = "1";
     if(!dbAdd($data_in, 'zj_txtowx', $con)){
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }

     //提现钱减少写到钱包记录表
    $wr_in['userid'] = $userid;
    $wr_in['tid'] = "1";
    $wr_in['addtime'] = strtotime($rs_list['pay_date']);
    $wr_in['money'] = $money;
    $wr_in['type'] = 3;
    if(!dbAdd($wr_in, 'zj_wrecord', $con)){
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }

    $return_list['data'] = json_encode($rs_list);
    forExit($lock_array, $con);
    toExit(0, $return_list, true);


} else {
    $rs_list['result']="失败";
    $sub_code=$result->$responseNode->sub_code;
    if($sub_code=='PAYEE_ACC_OCUPIED'){
        //"该手机号对应多个支付宝账户，请传入收款方姓名确定正确的收款账号"
        forExit($lock_array, $con);
        toExit(43, $return_list);
    }
    if($sub_code=="EXCEED_LIMIT_SM_AMOUNT"){
        //转账金额单笔额度超限
        forExit($lock_array, $con);
        toExit(44, $return_list);
    }
    if($sub_code=="PAYEE_NOT_EXIST"){
        //收款账号不存在
        forExit($lock_array, $con);
        toExit(45, $return_list);
    }
    if($sub_code=="PERM_AML_NOT_REALNAME_REV"){
        //请用户支付宝站内或手机客户端补充身份信息
        forExit($lock_array, $con);
        toExit(46, $return_list);
    }
    if($sub_code=="PAYEE_USER_INFO_ERROR"){
        //请确认用户姓名正确性
        forExit($lock_array, $con);
        toExit(47, $return_list);
    }
    if($sub_code=="MEMO_REQUIRED_IN_TRANSFER_ERROR"){
        //remark暂时为空(单笔提现达到50000),后期有需求可改
        forExit($lock_array, $con);
        toExit(48, $return_list);
    }
    if($sub_code=="PAYER_BALANCE_NOT_ENOUGH"){
        //企业余额不足
        forExit($lock_array, $con);
        toExit(68, $return_list);
    }

    forExit($lock_array, $con);
    toExit(301, $return_list);
}


































?>