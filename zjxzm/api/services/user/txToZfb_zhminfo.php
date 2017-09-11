<?php
/** 
 * 钱包提现到支付宝
 * 参数：8段 * sellerid * money * payee_account(收款方账户)
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
$condition = "sellerid = '$userid'";
$count = dbCount('zj_seller', $con, $condition);
if($count != 1) {
    forExit($lock_array, $con);
    toExit(10, $return_list);
}
//用户是否登入
$condition = "userid = $userid and deviceid = '".$deviceid."' and status = 1 and is_app=0";
$count = dbCount('zj_user_login', $con, $condition);
if($count != 1){
    forExit($lock_array, $con);
    toExit(12, $return_list);
}

//提现的金额是否<钱包的余额
//钱包是否有钱
$condition = "userid = '$userid' and tid=2";
$count = dbCount('zj_wallet', $con, $condition);
if($count != 1) {
    forExit($lock_array, $con);
    toExit(38, $return_list);//此种情况余额为0
}
//查出此用户的余额
$sql="select money from zj_wallet where userid=".$userid ." and tid=2";
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
$aop->appId = '2017053107390885';
$aop->rsaPrivateKey = 'MIIEowIBAAKCAQEAgHFPtCd+z00Njdz9ePW+BA2qdM4F/uU0GigNbS1b/ciFcgvAqrW1e2tusT0xvZ6xr3f7h07eDsi1Eekv9h+RqPrV+ZzQRjxJCWk37WxTw3ppGYosLE8jpmFJrZpJFDWaPhSlqG19s3Jyz6sjBklQ0FS9Qz4eVPVEKRhrcV2JrgwC8KIJemVkmN3OMrynTEJ9oLYisV9AJCF2XvzOwIaDXE5tnvtJNzLvNEwQV9Bnf+vJhFTNdrX1/47aw+FHGQ3nUmVO/rV7kSCyfrMgsUjo/wGWlvorNwAy+Jd4BpiqeCtG8CcZwBk6ivj23p6C0t1HKGbcRXb1Rnv9U2napy4UCwIDAQABAoIBABlo0TC9o3uVLbpD3q0gSF/66B7FZzA+3ajTBZz0nT8+fQ/LvWjIG8f3v+U2SvHNRC+HV+4zrwBTumZ5sDBLnIBGZzBDUnyijnxbIWJJzOReAg64Y/a8DTxsAKxkWlKyK/peEUuZpYVrfcURgRNMS717FhoIXu3Fu585on/B1mvk0OLqqluOFUxaAhegz62t2sSAYR9qPK58v6kcr9Tgq5g3HjHD3mEc2kqJjL+KN8NegZ6vbByJ5pgYAD4oE8MrZejRcd+FYddx5pp1fBXrRtZvs7yzyVFfZ+X5pV6RwHAhTTcSa6T6qOZeEc81ewwZidiCESE0aypFdK8cC2P79xECgYEA4pyvlD/jFOujhTzaLoeb+ACEva52sat+G06Kf5HETswqNw0KiKO/6xv/PVLg8n8jnGWqpOcgTTE+eE0i3IC/I2F/b5xxD/v/jZ2H/MobTmebIrvqUXb/buSDpG3E2o0dpye/FTORIfEFsb/BmNna3f8EZvq/F4ZNni2f9Yd3FpMCgYEAkRl9V5YUbjvsEH7hcF3dYsSZxsuD6Xa76omeGxpK2rkyrbJhiRdxcriY0aeyrKx2pb22rltgmcebyTJHqzJ/MxBxt1Irb2CIWkixOJGN/H6/SLQxR33/1SGIedl9zP+Izy8wTTlgoJZ+09XWpksjlZF8BBXyj+uRxXAOhTg2P6kCgYEAwI2Q57BtfQ5I0OMHJXhXQCD4qdA/zPJCYmOXbZRnsOjsigCLzdUOM3GDrRDqUUNU+ASvPhWiLdVFFqW8lI7VWvPye9z7eTCQUj3kGhmFoFaKzLXOAdDSdOC5NxOKrIYyByHPzO0XSjbptsKQdxfOUMnrbLFthEi7VUfBXrjvqP8CgYBeWr34S77jZBIBNBDzWgaRqJj6/fK8yZovTOhEZZeRnebHrvzgAh+i40l05GE7CjdpVfHKdd9egL/cWNbkC8VNdn8MH+Hg3lwsoaKkz3oOXHmVFKALBnNrrA+sdPLqcK0NVXlKbmpYYyT8Kc0YfDoak+2aVo9SaXR0eIbouIIGaQKBgEiVgCK1HEKmNoUe5vm6PbGaf1M8djfG0KBk6hWese+LhNbLSkhVnAz+kjytIQNws0F23Y57R/YGwtA5PMkuOfNMdoHeRJAk2tXREZQETlscAmgG5yoV++uJ/4XVKZvDYi7W7dcnxEUEJSfcRvLX2XTyclFRUeI/2GqV+42gfgjj';
$aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsfRMu6JdyjkOVIIYNF+mJythCsw7sJMiW4br8IuffMAUo3gJEYtL36WHh3QLA1CxxOxvFBbMwhAgYFf0+M42fMgtXnE+CfcMrPVkLa7qPqn3iUz5ZYLB4y5EboOXZMw1rOXz+jqk4SudwSVxYmn0lL918kVgstsboI9XR2ZJgYm0VQ0fVOqLCku0Z60ThMF1+y0jlwKS6/hAzidVazyKvwZsSnPi7F/YfjZgxhZ0IQHCCCcHFJCNNlZ3Wvk6ZEgWsrR33nzgP/5/+rHobcTeP+5zxnaf0IFb7ilmJrIZYtwMV/Y+qyeguy3r3BAkY0JJZABZyHcKXkD1Wh6ANvbABwIDAQAB';
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
    if(!dbUpdate($res, 'zj_wallet', $con, "userid = '$userid' and tid = 2")){
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }

     // 提现记录写到txtowx表
    $data_in['userid'] = $userid;
    $data_in['tid'] = "2";
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
    $wr_in['tid'] = "2";
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