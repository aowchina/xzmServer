<?php
/**
 * Created by PhpStorm.
 * User: min-fo026
 * Date: 17/3/17
 * Time: 下午3:21
 */

/* 物流鸟参数*/
define('EBusinessID','1282969');
define('AppKey','51fc5770-8746-4039-b199-05cf124b9c82');
define('ReqURL', 'http://api.kdniao.cc/api/dist');
//接受商派post数据
$body = file_get_contents('php://input');

//解析商派数据
if(!empty($body))
{
    $erp_data = explode('&',$body);
    foreach ($erp_data as $k=>$v)
    {
        $median = explode('=',$v);
        $key[] = $median[0];
        $value[] = $median[1];
        $data = array_combine ( $key , $value);
    }
    //判断是否为发货
    if($data['method'] == 'b2c.delivery.update')
    {
        include_once("../functions_mdb.php");
        $con = conDb();
        $orderid = $data['order_bn'];
        $condation = "order_id = '$orderid'";
        if(isset($data['logi_name']) && isset($data['logi_no']) && isset($data['delivery_bn']) && isset($data['logi_code']))
        {
            $u_data['order_sfid'] = $data['logi_no'];
            //转码
            $wl_name = urldecode($data['logi_name']);
            $u_data['wl_name'] = $wl_name;
            $u_data['send_num'] = $data['delivery_bn'];
            $u_data['wl_code'] = $data['logi_code'];
        }
        if(isset($data['status']) && $data['status'] == 'succ')
        {
            $u_data['status'] = 2;
            $sendTime = urldecode($data['date']);
            $u_data['send_time'] =  strtotime($sendTime);
        }
        dbUpdate($u_data,'hd_order',$con, $condation);

        $sql = "select  order_sfid,wl_code from hd_order where order_id = '$orderid' and status = 2";
        $send_order = dbLoad(dbQuery($sql, $con), true);
        if(!empty($send_order))
        {
            //请求参数
            $requestData =json_encode([
                'ShipperCode' => $send_order['wl_code'],
                'LogisticCode' => $send_order['order_sfid'],
                'CallBack' =>$orderid,]);
            $logisticResult = json_decode(orderTracesSubByJson($requestData),true);
            $logisticResult['Success'] == true ? $a_data['success'] = 1 :$a_data['success'] = 0;

            $a_data['order_id'] = $orderid;
            dbAdd($a_data,'hd_wlgj',$con);
        }
    }
}
//$requestData =json_encode([
//    'ShipperCode' => 'YTO',
//    'LogisticCode' => '884666987673317862',
//    'CallBack' =>'hondo_wx147792233316182277734',]);
//$logisticResult = orderTracesSubByJson($requestData);
//var_dump($logisticResult);die;
/**
 * Json方式  物流信息订阅
 */
function orderTracesSubByJson($requestData){
    $datas = array(
        'EBusinessID' => EBusinessID,
        'RequestType' => '1008',
        'RequestData' => urlencode($requestData) ,
        'DataType' => '2',
    );
    $datas['DataSign'] = encrypt($requestData, AppKey);
    $result=sendPost(ReqURL, $datas);
    return $result;
}

/**
 *  post提交数据
 * @param  string $url 请求Url
 * @param  array $datas 提交的数据
 * @return url响应返回的html
 */
function sendPost($url, $datas) {
    $temps = array();
    foreach ($datas as $key => $value) {
        $temps[] = sprintf('%s=%s', $key, $value);
    }
    $post_data = implode('&', $temps);
    $url_info = parse_url($url);
    if(empty($url_info['port']))
    {
        $url_info['port']=80;
    }
    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
    $httpheader.= "Host:" . $url_info['host'] . "\r\n";
    $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
    $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
    $httpheader.= "Connection:close\r\n\r\n";
    $httpheader.= $post_data;
    $fd = fsockopen($url_info['host'], $url_info['port']);
    fwrite($fd, $httpheader);
    $gets = "";
    $headerFlag = true;
    while (!feof($fd)) {
        if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
            break;
        }
    }
    while (!feof($fd)) {
        $gets.= fread($fd, 128);
    }
    fclose($fd);

    return $gets;
}

/**
 * 电商Sign签名生成
 * @param data 内容
 * @param appkey Appkey
 * @return DataSign签名
 */
function encrypt($data, $appkey) {
    return urlencode(base64_encode(md5($data.$appkey)));
}

