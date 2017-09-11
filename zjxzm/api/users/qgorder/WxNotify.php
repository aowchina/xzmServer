<?php
/** 
 * 更新支付结果
 * 参数：8段 * userid * 订单号
 */
require_once("../functions_mdb.php");
require_once("../prism-php-master/src/PrismClient.php");

if($postStr){
	$xml_array = simplexml_load_string($postStr, null, LIBXML_NOCDATA);
	if($xml_array->return_code == "SUCCESS"){
	    if($xml_array->result_code == "SUCCESS"){
	    	if($xml_array->appid == 'wxa5b09b46e7eb4d1b' && $xml_array->mch_id == '1328387001'){
				$pay_id = $xml_array->out_trade_no;
	    		//查询订单
	    		$con = conDb();
	    		if($con){

					//订单号存在，且金额相同，且未支付
					$sql = "select orderid from zj_order where pay_id ='$pay_id'";
					$pay_orderid = dbLoad(dbQuery($sql, $con), true);
					$orderid = $pay_orderid['orderid'];
					$wx_fee = $xml_array->total_fee / 100;
					$count = dbCount('zj_order', $con, "orderid = '$orderid' and status = 0");
					if($count == 1)
					{
						//支付的钱是否订单的金额
						  $sql="select money,addtime,appuid from zj_order where orderid='$orderid'";
						  $order_info = dbLoad(dbQuery($sql, $con), true);

						//相等时更新支付状态
						if(bccomp($wx_fee, $order_info['money']) == 0)
						{
							$data = array();
							$data['paytype'] = 2;  //支付方式为微信
							$data['status'] = 1;    //支付状态为已支付
							$data['paytime'] = $time = time(); // 支付时间
							$datetime = date('Y-m-d H:i:s',$time);

							dbUpdate($data, 'zj_order', $con, "orderid = '$orderid'");
						}

					}


			                closeDb($con);
			                $return_xml = "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
							echo $return_xml;
							exit;
			            }
	    			}
	    		}

	    		closeDb($con);
	    	}
	    }


$return_xml = "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[]]></return_msg></xml>";

echo $return_xml;
exit;

?>