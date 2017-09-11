<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
require_once("../functions_mdb.php");

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if($verify_result) {//验证成功
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

	//商户订单号
	$out_trade_no = $_POST['out_trade_no'];

	//支付宝交易号
	$trade_no = $_POST['trade_no'];

	//交易状态
	$trade_status = $_POST['trade_status'];

    $total_fee = $_POST['total_fee'];

    if($_POST['trade_status'] == 'TRADE_FINISHED') {
		//判断该笔订单是否在商户网站中已经做过处理
    	//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
		//如果有做过处理，不执行商户的业务程序

        //连接db
        $con = conDb();
        if($con == ''){
            echo "fail";
            exit;
        }
        $count = dbCount("hd_order", $con, "order_id = '".$out_trade_no."' and status = 0");

        if($count > 0){

            $sql = "select price,wl_price,userid,create_time,wl_id,user_pid,user_cid,user_qid,user_address,user_tel,user_name,user_info from hd_order where order_id = '$out_trade_no'";
            $order_info = dbLoad(dbQuery($sql, $con), true);

            $sql = "select name from hd_wl where id = $order_info[wl_id]";
            $wl_name = dbLoad(dbQuery($sql, $con), true);

            $order_price = $order_info['price'] + $order_info['wl_price'];
            if(bccomp($total_fee, $order_price) == 0){
                $data['status'] = 1;
                $data['pay_type'] = 1;
                $data['order_dsfid'] = $trade_no;
                $data['intime'] = $data['pay_time'] = $time = time();
                $datetime = date('Y-m-d H:i:s',$time);

                 //绑定仓库
                $sql = "select type from hd_wl where id = $order_info[wl_id]";
                $wl = dbLoad(dbQuery($sql, $con),true);
                if($wl['type'] != 2)
                {
                    $sql = "select cang_id from hd_cang_area where pid = ".$order_info['user_pid'];
                    $cang_info = dbLoad(dbQuery($sql, $con), true);

                    if($cang_info['cang_id']){
                        $data['cang_id'] = $cang_info['cang_id'];
                    }
                }

                if(dbUpdate($data, "hd_order", $con, "order_id = '".$out_trade_no."'")){

                    //取出订单中的秒杀商品

                    $sql = "select goods_num,amount,is_sk,price from hd_order_goods where order_id = '$out_trade_no'";
                    $sk_goods = dbLoad(dbQuery($sql, $con));
                    $all_goods_num = 0;
                    foreach($sk_goods as $k=>$v)
                    {
                        $sql = "select name,price from hd_goods where goods_num = '$v[goods_num]'";
                        $goods_name = dbLoad(dbQuery($sql, $con), true);
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
                                "oid" => $out_trade_no,
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
                                "payment_id" => $trade_no,
                                "tid" => $out_trade_no,
                                "seller_bank" => "",
                                "seller_account" => "",
                                "pay_fee" => $order_price,
                                "currency" => "CNY",
                                "currency_fee" => $total_fee,
                                "pay_type" => "online",
                                "payment_name" => "支付宝支付",
                                "pay_time" => $pay_time,
                                "status" => "SUCC"
                            )
                        )
                    );
                    $cerate_time = date('Y-m-d H:i:s',$order_info['create_time']);
                    $params = array(
                        "method" => "store.trade.add",
                        "node_id" => $node_id,
                        "tid" => $out_trade_no,
                        "title" => $out_trade_no,
                        "created" => $cerate_time,
                        "modified" => $datetime,
                        "lastmodify" => $datetime,
                        "is_cod" => false,
                        "total_trade_fee" => $order_price,
                        "status" => "TRADE_ACTIVE",
                        "pay_status" => "PAY_FINISH",
                        "ship_status" => "SHIP_NO",
                        "has_invoice" => "false",
                        "payed_fee" => $total_fee,
                        "shipping_tid" => $order_info['wl_id'],
                        "shipping_type" => $wl_name['name'],
                        "shipping_fee" => $order_info['wl_price'],
                        "is_protect" => "0",
                        "payment_tid" =>$trade_no,
                        "payment_type" => "支付宝支付",
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
                    $client->post('/oms', $params, $headers);
                    /*---- 计算返点 ----*/

                    //首先，先查询下单者是否被推广
                    $sql = "select parentid from hd_tj_record where userid = ".$order_info['userid'];
                    $parent_info = dbLoad(dbQuery($sql, $con), true);
                    $parentid = $parent_info['parentid'];

                    if($parentid){

                        //获取返点比例
                        $sql = "select tj_back,is_oprice from hd_users where id = $parentid";
                        $re_info = dbLoad(dbQuery($sql, $con), true);
                        $money = round(($total_fee * $re_info['tj_back'] / 1000), 2);

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
                            $data['order_id'] = $out_trade_no;

                            dbAdd($data, 'hd_back_in', $con);
                        }
                    }
                    closeDb($con);
                    echo "success";
                    exit;
                }else{
                    closeDb($con);
                    echo "fail";
                    exit;
                }
            }
            else{
                closeDb($con);
                echo "fail";
                exit;
            }
        }
        else{
            closeDb($con);
            echo "success";
            exit;
        }

		//注意：
		//退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
		//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的

        //调试用，写文本函数记录程序运行情况是否正常
        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
    }
    else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
        //连接db
        $con = conDb();
        if($con == ''){
            echo "fail";
            exit;
        }

        $count = dbCount("hd_order", $con, "order_id = '".$out_trade_no."' and status = 0");

        if($count > 0){

            $sql = "select price,wl_price,userid,create_time,wl_id,user_pid,user_cid,user_qid,user_address,user_tel,user_name,user_info from hd_order where order_id = '$out_trade_no'";
            $order_info = dbLoad(dbQuery($sql, $con), true);

            $sql = "select name from hd_wl where id = $order_info[wl_id]";
            $wl_name = dbLoad(dbQuery($sql, $con), true);

            $order_price = $order_info['price'] + $order_info['wl_price'];

            if(bccomp($total_fee, $order_price) == 0){
                $data['status'] = 1;
                $data['pay_type'] = 1;
                $data['order_dsfid'] = $trade_no;
                $data['intime'] = $data['pay_time'] = $time = time();
                $datetime = date('Y-m-d H:i:s',$time);


                //绑定仓库
                $sql = "select type from hd_wl where id = $order_info[wl_id]";
                $wl = dbLoad(dbQuery($sql, $con),true);
                if($wl['type'] != 2)
                {
                    $sql = "select cang_id from hd_cang_area where pid = ".$order_info['user_pid'];
                    $cang_info = dbLoad(dbQuery($sql, $con), true);

                    if($cang_info['cang_id']){
                        $data['cang_id'] = $cang_info['cang_id'];
                    }
                }

                if(dbUpdate($data, "hd_order", $con, "order_id = '".$out_trade_no."'")){

                    //取出订单中的秒杀商品

                    $sql = "select goods_num,amount,is_sk,price from hd_order_goods where order_id = '$out_trade_no'";
                    $sk_goods = dbLoad(dbQuery($sql, $con));
                    $all_goods_num = 0;
                    foreach($sk_goods as $k=>$v)
                    {
                        $sql = "select name,price from hd_goods where goods_num = '$v[goods_num]'";
                        $goods_name = dbLoad(dbQuery($sql, $con), true);
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
                                "oid" => $out_trade_no,
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
                                "payment_id" => $trade_no,
                                "tid" => $out_trade_no,
                                "seller_bank" => "",
                                "seller_account" => "",
                                "pay_fee" => $order_price,
                                "currency" => "CNY",
                                "currency_fee" => $total_fee,
                                "pay_type" => "online",
                                "payment_name" => "支付宝支付",
                                "pay_time" => $pay_time,
                                "status" => "SUCC"
                            )
                        )
                    );
                    $cerate_time = date('Y-m-d H:i:s',$order_info['create_time']);
                    $params = array(
                        "method" => "store.trade.add",
                        "node_id" => $node_id,
                        "tid" => $out_trade_no,
                        "title" => $out_trade_no,
                        "created" => $cerate_time,
                        "modified" => $datetime,
                        "lastmodify" => $datetime,
                        "is_cod" => false,
                        "total_trade_fee" => $order_price,
                        "status" => "TRADE_ACTIVE",
                        "pay_status" => "PAY_FINISH",
                        "ship_status" => "SHIP_NO",
                        "has_invoice" => "false",
                        "payed_fee" => $total_fee,
                        "shipping_tid" => $order_info['wl_id'],
                        "shipping_type" => $wl_name['name'],
                        "shipping_fee" => $order_info['wl_price'],
                        "is_protect" => "0",
                        "payment_tid" =>$trade_no,
                        "payment_type" => "支付宝支付",
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
                    $client->post('/oms', $params, $headers);
                    /*---- 计算返点 ----*/

                    //首先，先查询下单者是否被推广
                    $sql = "select parentid from hd_tj_record where userid = ".$order_info['userid'];
                    $parent_info = dbLoad(dbQuery($sql, $con), true);
                    $parentid = $parent_info['parentid'];

                    if($parentid){

                        //获取返点比例
                        $sql = "select tj_back,is_oprice from hd_users where id = $parentid";
                        $re_info = dbLoad(dbQuery($sql, $con), true);
                        $money = round(($total_fee * $re_info['tj_back'] / 1000), 2);

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
                            $data['order_id'] = $out_trade_no;

                            dbAdd($data, 'hd_back_in', $con);
                        }
                    }
                    closeDb($con);
                    echo "success";
                    exit;
                }else{
                    closeDb($con);
                    echo "fail";
                    exit;
                }
            }
            else{
                closeDb($con);
                echo "fail";
                exit;
            }
        }
        else{
            closeDb($con);
            echo "success";
            exit;
        }
		//注意：
		//付款完成后，支付宝系统发送该交易状态通知
		//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的

        //调试用，写文本函数记录程序运行情况是否正常
        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
    }

	echo "success";		//请不要修改或删除

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    //验证失败
    echo "fail";

    //调试用，写文本函数记录程序运行情况是否正常
    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
}
?>