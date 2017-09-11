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
$aop->appId = '2017070707675380';
$aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAk2rWQcDtx9HYuYcex005Ia/zkA6fPqW9LH5kqcXYXBwSMP7odN02LWe+2/WaJ9IaWbB8qoky5MV84HA5K1FpQVI7ERaac+G7sOi1ZDmV6SbzjiGfeU/tdVs68PewxVePsXQSt7XYnt3PeaIrop4HPYx/+VNR+iUuDliWO5x5pg9lmt/UsrUfrwzRdLZ/8uApyyFcGGAEcBt11TK3unN8SJigISXAVDoWW+35fO6/uKbkCEAzM5tGqpaJPIiHa0v8M+Icae023thgCe0ulSAbi+tLfMVIDLBL1nqn46tCcMbMwuaqxM0KG7xbIkYLIb9cQB9b/mVJLGsIt4H5Lo0b/wIDAQABAoIBADkmXv/IyqX/rXRndMAxKOftbZA4ivXfjRI1wdKrd7Bl1YuXFwlPRRSfNrfRZDzxz/NbX0lBvTBBe6MK/q7Tdemz9mAukhxAs+HmpYPAa2SjDCLa2BdQrC8l+hi2/ZnT00opRKRh5CPau/dcrmvxtb7fvDEtYweF6G/5WeysQeDlV2AuFRjA+x9WaIfkVUHp8qH5WIUhYuIwLV8H2icEU8enC52ilVt8CJt08NYp6ZMqzg5+2hBKKR8YtuqJUW7ofliZKfbbarRt4NUr+4W4IHT/UeAu26Go2hlaA9vg2sDrgbrPij6BicW13deoqKa7qxEp5y5S0Aq+V7bxlW6oOrkCgYEA7ogoCBGwsvPxyOSNqpw/Ohxei34PZeuloPK6/LQUixpP29v3zm/Os/rsSfTnhBoV6VKRZOm61pBLWHR7ra/TvznJknJQ6vnS+GjD+iiAzVjTa91fnA0FDZ07rFj/wUnDtztrtIqXH8mzglpHrzBSs+G4jnSi/OdTelh6x/a6SPMCgYEAnjaF+I0/WadkRFIVNsU9klmMfYgg9bLuFSMP/JICQWAm5g7jX21oiCiqlgnCQWb2nQKiRpgartKvfI9rwZ5FMNg0i2CtyF1SfkzJZUFjzhzZB/ELnWIi7YNahKBQ5SNxSdcl80t5EZ08MDcTdxVxWugMszzRqPu5nQI5uCxPY8UCgYEAyN13eHQNLGUosldkxMsWo0TIiQb6yIUL0OEWfT7YHdtmbGpr3zdgBUfEOvM1EpU05mpVbXk1kQGpsS9GBxkNYvayu9PwOC9wbScsATiiXknyn96naK2+F5zUZ2n61TXcziwHot0iLe6Yb6i+vNQgkMH/vFaT+gyCy4A+xK0MaekCgYEAkUkUYZijshqrUqnl3TPnRLuC7cvSKI41X6ehCJi0BYDcd6r4VStNMKvXaoxvnEuG78v5ZMZMynmiuMmjdFt4wk/ogI05SKOaG208DAWuNxn6mvZZah8yX9Wdi+kEIEcZw2sU6IRIG0q+eLA66cIBJC+vafDRv1HZszrV8jCOftkCgYA8d5K1CuuGDKZBRcLU7ntpDGMgGIO2iaPp5HLzYn4eF4jCDl9dC/2Gr9o+ZRhan1+SRBGgu5bW/Fm8yN2bijOJC7nw+oyclZNXdDDpo/m/Hn9hNnE15tRWWxK2oPbxlKX0lBq/z9R7L1e3aRuAIgmSWYSwrko21/zJL7rBw+SEPQ==';
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