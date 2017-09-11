<?php
/**
 * Created by PhpStorm.
 * User: min-fo026
 * Date: 17/5/17
 * Time: 下午5:44
 */
include_once("../functions_mdb.php");
include_once("../alipay/aop/AopClient.php");
$aop = new AopClient;
$aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAph/UQq2sj3YisThkJQ6KcLsbXxyuZsjj1yGmQZWg+DO2NoZM6WCxUSQ8sOVdrHGBw647lk1Hd14AFcfa+HW2s8jar+uPzAMDJ2A873/+nPOBaoFeqccIepqv/KzKxuFWy37M02De24ER+pxkFvKpvj4P+u+XwRVK8vmZ4rKvZ/CDIPHb8V7fXZ3McH5LC4SVaJfxlAcV+f65D5SsNoNXWMdV2FH6SA4XXMJwSsyMzIf2+Toc235yr5SIY8J85ptrqJnGk3SCJCzJNguXCSUfE2VIsAUozEJ8b24Prv8+69gMmDaLo1wqep4eYheBgZzfnTgtPHGfiM08f9KmWarIuwIDAQAB';
$flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");

if($flag) {//验证成功
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
    //商户订单号

    $out_trade_no = $_POST['out_trade_no'];
    //支付宝交易号
    $trade_no = $_POST['trade_no'];
    //交易状态
    $trade_status = $_POST['trade_status'];
    //付款时间
    $total_fee = $_POST['buyer_pay_amount'];

    if($_POST['trade_status'] == 'TRADE_FINISHED')
    {
        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //如果有做过处理，不执行商户的业务程序

        //连接db
        $con = conDb();
        if ($con == '')
        {
            echo "fail";
            exit;
        }

        //判断走是商城订单还是求购订单
        if(preg_match('/^zj[0-9]+$/', $out_trade_no))
        {
            //商城订单
            $count = dbCount("zj_order", $con, "orderid = '" . $out_trade_no . "' and status = 0");

            if ($count > 0)
            {
                $sql = "select money,appuid,addtime from zj_order where orderid = '$out_trade_no'";
                $order_info = dbLoad(dbQuery($sql, $con), true);

                $order_price = $order_info['money'];
                if (bccomp($total_fee, $order_price) == 0)
                {
                    $data['status'] = 1;
                    $data['paytype'] = 1;
                    $data['order_dsfid'] = $trade_no;
                    $data['paytime'] = $time = time();
                    $datetime = date('Y-m-d H:i:s', $time);

                    if (dbUpdate($data, "zj_order", $con, "orderid = '$out_trade_no ' "))
                    {
                        closeDb($con);
                        echo "success";
                        exit;
                    }
                } else
                {
                    closeDb($con);
                    echo "success";
                    exit;
                }
            }

        }else
        {

           //求购订单
            $count = dbCount("zj_qgorder", $con, "qgorderid = '" . $out_trade_no . "' and status = 0");

            if ($count > 0)
            {
                $sql = "select price,appuid,addtime from zj_qgorder where qgorderid = '$out_trade_no'";
                $order_info = dbLoad(dbQuery($sql, $con), true);

                $price = explode(',',$order_info['price']);
                $total_money= array_sum($price);

                if (bccomp($total_fee, $total_money) == 0)
                {
                    $data['status'] = 1;
                    $data['paytype'] = 1;
                    $data['order_dsfid'] = $trade_no;
                    $data['paytime'] = $time = time();
                    $datetime = date('Y-m-d H:i:s', $time);

                    if (dbUpdate($data, "zj_qgorder", $con, "qgorderid = '$out_trade_no ' "))
                    {
                        closeDb($con);
                        echo "success";
                        exit;
                    }
                } else
                {
                    closeDb($con);
                    echo "success";
                    exit;
                }
            }
        }


            //注意：
            //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");

    }
        elseif($_POST['trade_status'] == 'TRADE_SUCCESS')
        {
            //连接db
            $con = conDb();
            if ($con == '')
            {
                echo "fail";
                exit;
            }

            //判断是商城订单还是求购订单
            if(preg_match('/^zj[0-9]+$/', $out_trade_no)){
                //商城订单
                $count = dbCount("zj_order", $con, "orderid = '" . $out_trade_no . "' and status = 0");
                if ($count > 0)
                {

                    $sql = "select money,appuid,addtime from zj_order where orderid = '$out_trade_no'";
                    $order_info = dbLoad(dbQuery($sql, $con), true);


                    $order_price = $order_info['money'];

                    if (bccomp($total_fee, $order_price) == 0)
                    {
                        $data['status'] = 1;
                        $data['paytype'] = 1;
                        $data['order_dsfid'] = $trade_no;
                        $data['paytime'] = $time = time();
                        $datetime = date('Y-m-d H:i:s', $time);

                        if (dbUpdate($data, "zj_order", $con, "orderid = '" . $out_trade_no . "'"))
                        {
                            closeDb($con);
                            echo "success";
                            exit;
                        } else
                        {
                            closeDb($con);
                            echo "fail";
                            exit;
                        }
                    } else
                    {
                        closeDb($con);
                        echo "fail";
                        exit;
                    }
                } else {
                    closeDb($con);
                    echo "success";
                    exit;
                }

            }else{
                //求购订单
                $count = dbCount("zj_qgorder", $con, "qgorderid = '" . $out_trade_no . "' and status = 0");
                if ($count > 0)
                {
                    $sql = "select price,appuid,addtime from zj_qgorder where qgorderid = '$out_trade_no'";
                    $order_info = dbLoad(dbQuery($sql, $con), true);

                    $price = explode(',',$order_info['price']);
                    $total_money= array_sum($price);

                    if (bccomp($total_fee, $total_money) == 0)
                    {
                        $data['status'] = 1;
                        $data['paytype'] = 1;
                        $data['order_dsfid'] = $trade_no;
                        $data['paytime'] = $time = time();
                        $datetime = date('Y-m-d H:i:s', $time);

                        if (dbUpdate($data, "zj_qgorder", $con, "qgorderid = '" . $out_trade_no . "'"))
                        {
                            closeDb($con);
                            echo "success";
                            exit;
                        } else
                        {
                            closeDb($con);
                            echo "fail";
                            exit;
                        }
                    } else
                    {
                        closeDb($con);
                        echo "fail";
                        exit;
                    }
                } else {
                    closeDb($con);
                    echo "success";
                    exit;
                }
            }


            //注意：
            //付款完成后，支付宝系统发送该交易状态通知
            //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
        echo "success";        //请不要修改或删除

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    //验证失败
    echo "fail";

    //调试用，写文本函数记录程序运行情况是否正常
    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
}