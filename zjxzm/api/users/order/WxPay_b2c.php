<?php
/** 
 * 获取支付信息
 * 参数：8段 * userid * order_id * 订单备注
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");

$wx_url = "http://192.168.1.104/hondo_wx/api/order/WxNotify.php";//微信回调url
$wx_ip = "127.0.0.1";//微信请求ip

$orderid = trim($reqlist[9]);
if(!preg_match('/^hondo_wx[0-9]+$/', $orderid)){
    forExit($lock_array);
    toExit(35, $return_list);
}

//验证订单备注
$user_info = getStrFromByte(trim($reqlist[10]));
$len = mb_strlen($user_info,'UTF8');
if($len > 256)
{
    forExit($lock_array);
    toExit(90, $return_list);
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
$count = dbCount("hd_order", $con, "order_id = '$orderid' and userid = $userid and status = 0");
if($count != 1){
    forExit($lock_array, $con);
    toExit(34, $return_list);
}

$sql = "select price,wl_price,user_pid,wl_id,cang_id from hd_order where order_id = '$orderid'";
$order_info = dbLoad(dbQuery($sql, $con), true);

if(empty($order_info['user_pid'])){
    forExit($lock_array, $con);
    toExit(42, $return_list);
}

if(empty($order_info['wl_id'])){
    forExit($lock_array, $con);
    toExit(43, $return_list);
}

//检查当前用户身份
$sql = "select group_id from hd_user_usergroup_map where user_id = $userid";
$re = dbLoad(dbQuery($sql, $con), true);
$group = $re['group_id'];
if(!($group == 9 || $group == 2 || $group == 3)){
    forExit($lock_array, $con);
    toExit(11, $return_list);
}

//如果是微商，获取微商等级
if($group == 9){
    $sql = "select level from hd_users where id = $userid";
    $re = dbLoad(dbQuery($sql, $con), true);
    $level = $re['level'];
}

//再次验证订单中的商品价格与数量
$sql = "select goods_num,price,amount,is_sk from hd_order_goods where order_id = '$orderid'";
$list = dbLoad(dbQuery($sql, $con));

foreach($list as $item){
    if($item['is_sk'] == 0)
    {
        $sql = "select price,ng_price,h_price,m_price,l_price from hd_goods where goods_num = '".$item['goods_num']."'";
        $goods_info = dbLoad(dbQuery($sql, $con), true);
        if($group == 9){
            switch ($level) {
                case 1:
                    $ng_price = $goods_info['l_price'];
                    break;
                case 2:
                    $ng_price = $goods_info['m_price'];
                    break;
                case 3:
                    $ng_price = $goods_info['h_price'];
                    break;
                default:
                    $ng_price = $goods_info['l_price'];
                    break;
            }
        }
        elseif($group == 2){
            $ng_price = $goods_info['price'];
        }
        else{
            $ng_price = $goods_info['ng_price'];
        }
    }
    else
    {
        //检查秒杀是否超时
        $time = time();
        $sql = "select sell_max, buy_max, sk_price from hd_seckill where goods_num = '$item[goods_num]' and state = 1 and $time between stime and etime";
        $sk_goods_info = dbLoad(dbQuery($sql, $con), true);
        if(empty($sk_goods_info))
        {
            forExit($lock_array, $con);
            toExit(81, $return_list);
        }


        //验证是否超出最大出售个数
        $sql = "select sum(sell_num) as all_sell_num from hd_sk_sell where goods_num = '$item[goods_num]'";
        $sell_sk_goods = dbLoad(dbQuery($sql, $con), true);
        if(empty($sell_sk_goods))
        {
            $sell_sk_goods['all_sell_num'] = 0;
        }
        if(($item['amount'] + $sell_sk_goods['all_sell_num']) > $sk_goods_info['sell_max'])
        {
            forExit($lock_array, $con);
            toExit(70, $return_list);
        }

        //是否超过每人限购数
        $sql = "select sell_num from hd_sk_sell where goods_num = '$item[goods_num]' and userid = $userid";
        $p_sk_sell =  dbLoad(dbQuery($sql, $con), true);
        if(empty($p_sk_sell))
        {
            $p_sk_sell['sell_num'] = 0;
        }
        if(($item['amount'] + $p_sk_sell['sell_num']) > $sk_goods_info['buy_max'])
        {
            forExit($lock_array, $con);
            toExit(71, $return_list);
        }
        $ng_price = $sk_goods_info['sk_price'];
    }

    if(bccomp($ng_price, $item['price']) != 0){
        forExit($lock_array, $con);
        toExit(41, $return_list);
    }
}

//更新订单详情和微信支付交易id
$data = [];
$now_time = time();
$data['user_info'] = $user_info;
$data['pay_id'] = $pay_id = $now_time.rand(100,999);
if(!dbUpdate($data, 'hd_order', $con, "order_id = '$orderid'"))
{
    forExit($lock_array, $con);
    toExit(91, $return_list);
}

$money = ($order_info['price'] + $order_info['wl_price']) * 100;

//拼接请求微信的数组
$req = array();
$req['appid'] = "wx107c247196f93722";
$req['body'] = "恒都牛肉内购平台";
$req['mch_id'] = "1362955002";
$req['nonce_str'] = md5($now_time);    //随机字符串,32位
$req['notify_url'] = $wx_url;
$req['out_trade_no'] = $pay_id;
$req['spbill_create_ip'] = $wx_ip;
$req['total_fee'] = $money;
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
    toExit(101, $return_list);
}

if($xml_array->result_code != "SUCCESS"){

    if($xml_array->err_code == "ORDERPAID"){
        forExit($lock_array, $con);
        toExit(102, $return_list);
    }
    else{
        $req = array();
        $now_time = time();
        $req['appid'] = "wx107c247196f93722";
        $req['mch_id'] = "1362955002";
        $req['nonce_str'] = md5($now_time);    //随机字符串,32位
        $req['out_trade_no'] = $pay_id;
        $req['sign'] = getSign($req);

        //拼接请求微信服务器的xml
        $post_xml = getXml($req);
        $data = file_get_contents_post("https://api.mch.weixin.qq.com/pay/orderquery", $post_xml);

        //解析微信返回的xml
        if(!$data){
            forExit($lock_array, $con);
            toExit(100, $return_list);
        }

        $xml_array = simplexml_load_string($data, null, LIBXML_NOCDATA);

        //file_put_contents($j_path.'lock/wx3', json_encode($xml_array));

        if($xml_array->return_code != "SUCCESS"){
            forExit($lock_array, $con);
            toExit(101, $return_list);
        }

        if($xml_array->result_code != "SUCCESS"){
            forExit($lock_array, $con);
            toExit(102, $return_list);
        }

        if($xml_array->trade_state != "SUCCESS" && $xml_array->trade_state != "NOTPAY"){
            forExit($lock_array, $con);
            toExit(103, $return_list);
        }
    }
}

$rs_list["pid"] = (String)$xml_array->prepay_id;
$rs_list["orderid"] = $orderid;//这不确定是返回的系统的orderid还是微信返回的
$rs_list["nonce_str"] = md5($now_time);
$rs_list["timestamp"] = $now_time;

$req2['partnerid'] = "1362955002";
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