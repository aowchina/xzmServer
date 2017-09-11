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
	    	if($xml_array->appid == 'wx107c247196f93722' && $xml_array->mch_id == '1362955002'){
				$pay_id = $xml_array->out_trade_no;
	    		//查询订单
	    		$con = conDb();
	    		if($con){
	    			//订单号存在，且金额相同，且未支付
					$sql = "select order_id from hd_order where pay_id ='$pay_id'";
					$pay_orderid = dbLoad(dbQuery($sql, $con), true);
					$orderid = $pay_orderid['order_id'];
	    			$wx_fee = $xml_array->total_fee / 100;

	    			$count = dbCount('hd_order', $con, "order_id = '$orderid' and status = 0");
	    			if($count == 1){

	    				$sql = "select price,wl_price,userid,create_time,wl_id,user_pid,user_cid,user_qid,user_address,user_tel,user_name,user_info from hd_order where order_id = '$orderid'";
            			$order_info = dbLoad(dbQuery($sql, $con), true);

						$sql = "select name from hd_wl where id = $order_info[wl_id]";
						$wl_name = dbLoad(dbQuery($sql, $con), true);

            			$order_price = $order_info['price'] + $order_info['wl_price'];

			            if(bccomp($wx_fee, $order_price) == 0){

			            	$dsf_orderid = $xml_array->transaction_id;
		    				$data = array();
		    				$data['order_dsfid'] = $dsf_orderid;
		    				$data['pay_type'] = 2;  //支付方式为微信
		    				$data['status'] = 1;    //支付状态为已支付
							$data['pay_time'] = $time = time(); // 支付时间
							$datetime = date('Y-m-d H:i:s',$time);

		    				//绑定仓库
							$sql = "select  type from hd_wl where id = $order_info[wl_id]";
							$wl = dbLoad(dbQuery($sql, $con),true);
							if($wl['type'] != 2)
							{
								$sql = "select cang_id from hd_cang_area where pid = ".$order_info['user_pid'];
								$cang_info = dbLoad(dbQuery($sql, $con), true);
								if($cang_info['cang_id']){
									$data['cang_id'] = $cang_info['cang_id'];
								}
								dbUpdate($data, 'hd_order', $con, "order_id = '$orderid'");
							}

							//取出订单中的商品
							$sql = "select goods_num,amount,is_sk,price from hd_order_goods where order_id = '$orderid'";
							$sk_goods = dbLoad(dbQuery($sql, $con));
							$all_goods_num = 0;
							foreach($sk_goods as $k=>$v)
							{
								$sql = "select name,price from hd_goods where goods_num = '$v[goods_num]'";
								$goods_name = dbLoad(dbQuery($sql, $con), true);
								//商派对接商品信息
								$all_goods_num += $v['amount'];
								$erp_goods['bn'] = $v['goods_num'];
								$erp_goods['name'] = $goods_name['name'];
								$erp_goods['sku_properties'] = '';
								$erp_goods['price'] = $goods_name['price'];
								$erp_goods['sale_price'] = $v['price'];
								$erp_goods['total_item_fee'] = $v['amount'] * $v['price'];
								$erp_goods['num'] = $v['amount'];
								$erp_goods['item_type'] = 'product';
								$erp_goods['item_status'] = 'normal';
								$erp_item[] = $erp_goods;
								//商品为秒杀产品
								if($v['is_sk'] == 1)
								{
									//以经售出的秒杀产品
									$sql = "select sell_num from hd_sk_sell where userid = $order_info[userid] and goods_num = $v[goods_num]";
									$sell_goods = dbLoad(dbQuery($sql, $con),true);

									if(empty($sell_goods))
									{
										$n_data['userid'] = $order_info['userid'];
										$n_data['goods_num'] = $v['goods_num'];
										$n_data['sell_num'] = $v['amount'];
										 dbAdd($n_data, 'hd_sk_sell', $con);

									}
									else
									{
										$u_data['sell_num'] = $v['amount'] + $sell_goods['sell_num'];
										$condition = "userid = $order_info[userid] and goods_num = $v[goods_num]";
										dbUpdate($u_data, 'hd_sk_sell', $con, $condition);
									}
								}
							}
							/****** 商派对接 *****/
							//向erp中添加订单
							$client = new PrismClient($url = $erp_url, $key = $erp_key, $secret = $erp_secret);

							//取出收货地址
							$sql = "select areaname from hd_area where id = ".$order_info['user_pid'];
							$pinfo = dbLoad(dbQuery($sql, $con), true);

							$sql = "select areaname from hd_area where id = ".$order_info['user_cid'];
							$cinfo = dbLoad(dbQuery($sql, $con), true);

							$sql = "select areaname from hd_area where id = ".$order_info['user_qid'];
							$qinfo = dbLoad(dbQuery($sql, $con), true);

							if(empty($order_info['user_info']))
							{
								$order_info['user_info'] = '';
							}

							$promotion_details = array(
								array(
									"promotion_name" => "",
									"promotion_fee" => ""
								)
							);

							$orders = array(
								"order" => array(
									array(
										"oid" => $orderid,
										"type" => "goods",
										"items_num" => $all_goods_num,
										"total_order_fee" => $order_price,
										"status" => "TRADE_ACTIVE",
										"ship_status" => "SHIP_NO",
										"pay_status" => "PAY_FINISH",
										"order_items" => array(
											"item" =>$erp_item,
										)
									),
								)
							);

							$pay_time = date('Y-m-d H:i:s',$time);
							$payment_lists = array(
								"payment_list" => array(
									array(
										"payment_id" => $dsf_orderid,
										"tid" => $orderid,
										"seller_bank" => "",
										"seller_account" => "",
										"pay_fee" => $order_price,
										"currency" => "CNY",
										"currency_fee" => $wx_fee,
										"pay_type" => "online",
										"payment_name" => "微信支付",
										"pay_time" => $pay_time,
										"status" => "SUCC"
									)
								)
							);

							$cerate_time = date('Y-m-d H:i:s',$order_info['create_time']);
							$params = array(
								"method" => "store.trade.add",
								"node_id" => $node_id,
								"tid" => $orderid,
								"title" => $orderid,
								"created" => $cerate_time,
								"modified" => $datetime,
								"lastmodify" => $datetime,
								"is_cod" => false,
								"total_trade_fee" => $order_price,
								"status" => "TRADE_ACTIVE",
								"pay_status" => "PAY_FINISH",
								"ship_status" => "SHIP_NO",
								"has_invoice" => "false",
								"payed_fee" => $wx_fee,
								"shipping_tid" => $order_info['wl_id'],
								"shipping_type" => $wl_name['name'],
								"shipping_fee" => $order_info['wl_price'],
								"is_protect" => "0",
								"payment_tid" =>$dsf_orderid,
								"payment_type" => "微信支付",
								"receiver_name" => $order_info['user_name'],
								"receiver_email" => "",
								"receiver_state" => $pinfo['areaname'],
								"receiver_city" => $cinfo['areaname'],
								"receiver_district" => $qinfo['areaname'],
								"receiver_address" => $order_info['user_address'],
								"receiver_zip" => '',
								"receiver_mobile" => $order_info['user_tel'],
								"buyer_memo" => $order_info['user_info'],
								"promotion_details" => json_encode($promotion_details),

								//基本参数完成
								"orders" => json_encode($orders),
								"payment_lists" => json_encode($payment_lists),
							);

							$headers = array(
							);
							 $r = $client->post('/oms', $params, $headers);

		    				/*---- 计算返点 ----*/

		    				//首先，先查询下单者是否被推广
		    				$sql = "select parentid from hd_tj_record where userid = ".$order_info['userid'];
		    				$parent_info = dbLoad(dbQuery($sql, $con), true);
		    				$parentid = $parent_info['parentid'];

		    				if($parentid){
		    					//获取返点比例
		    					$sql = "select tj_back,is_oprice from hd_users where id = $parentid";
		    					$re_info = dbLoad(dbQuery($sql, $con), true);

		    					$money = round(($wx_fee * $re_info['tj_back'] / 1000), 2);
								//判断是否开差价
								$chajia = 0;
								if($re_info['is_oprice'] == 1)
								{
									//取出下单人的级别和推荐人的级别
									$sql = "select group_id from hd_user_usergroup_map where user_id = $order_info[userid]";
									$u_group = dbLoad(dbQuery($sql, $con), true);
									$sql = "select group_id from hd_user_usergroup_map where user_id = $parentid";
									$p_group = dbLoad(dbQuery($sql, $con), true);
									foreach($sk_goods as $k=>$v)
									{
										//非秒杀商品
										if($v['is_sk'] == 0)
										{
											//取出这个商品的所有级别价格
											$sql = "select price,ng_price,h_price,m_price,l_price from hd_goods where goods_num = '$v[goods_num]'";
											$goods_price = dbLoad(dbQuery($sql, $con), true);
											if($u_group['group_id'] == 9)
											{
												$sql = "select level from hd_users where id = $order_info[userid]";
												$re = dbLoad(dbQuery($sql, $con), true);
												$level = $re['level'];
												switch ($level) {
													case 1:
														$u_price = $goods_price['l_price'];
														break;
													case 2:
														$u_price = $goods_price['m_price'];
														break;
													case 3:
														$u_price = $goods_price['h_price'];
														break;
													default:
														$u_price = $goods_price['l_price'];
														break;
												}
											}
											elseif($u_group['group_id'] == 2)
											{
												$u_price =  $goods_price['price'];
											}
											else
											{
												$u_price =  $goods_price['ng_price'];
											}
											//计算推荐人的级别
											if($p_group['group_id'] == 9)
											{
												$sql = "select level from hd_users where id = $parentid";
												$re = dbLoad(dbQuery($sql, $con), true);
												$level = $re['level'];
												switch ($level) {
													case 1:
														$p_price = $goods_price['l_price'];
														break;
													case 2:
														$p_price = $goods_price['m_price'];
														break;
													case 3:
														$p_price = $goods_price['h_price'];
														break;
													default:
														$p_price = $goods_price['l_price'];
														break;
												}
											}
											elseif($p_group['group_id'] == 2)
											{
												$p_price =  $goods_price['price'];
											}
											else
											{
												$p_price =  $goods_price['ng_price'];
											}
											$chajia += ($u_price - $p_price);

										}
									}
								}
								if($money > 0 || $chajia > 0 ){
									$data = array();
									$data['userid'] = $parentid;
									$data['money'] = $money;
									$data['oprice'] = $chajia;
									$data['intime'] = time();
									$data['order_id'] = $orderid;
									dbAdd($data, 'hd_back_in', $con);
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
	}
}

$return_xml = "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[]]></return_msg></xml>";

echo $return_xml;
exit;

?>