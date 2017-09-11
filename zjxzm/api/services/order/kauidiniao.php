<?php
/**
 * Created by PhpStorm.
 * User: min-fo026
 * Date: 17/3/17
 * Time: 下午3:21
 */
include_once("../functions_mdb.php");
$con = conDb();
//接受快递鸟post数据
define('EBusinessID','1282969');
$RequestData = json_decode($_POST['RequestData']);
$RequestType = json_decode($_POST['RequestType']);
$DataSign = json_decode($_POST['DataSign']);
$r_data = [
  'EBusinessID' => EBusinessID,
  'UpdateTime' => '2017-03-30 10:42:39',
  'Success' =>  true,
    "Reason" =>'',
];
//与快递鸟交互
echo json_encode($r_data);
if($RequestType == 101 && $RequestData['EBusinessID'] == EBusinessID)
{
    foreach($RequestData['Data'] as $k=>$v)
    {
        if($v['Success'] == true)
        {
            $u_data['traces'] = urlencode(json_encode($v['Traces']));
            dbUpdate($u_data,'hd_wlgj',$con,"order_id = '$v[CallBack]'");
        }
    }
}
