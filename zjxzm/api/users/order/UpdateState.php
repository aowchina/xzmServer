<?php
/**
 * Created by PhpStorm.
 * User: min-fo026
 * Date: 17/7/6
 * Time: 下午2:53
 */
include_once("../functions_mdb.php");

class UpdateState {
    //修改订单状态
    function update($orderid){
        //连接db
        $con = conDb();
        if(!$con){
            return false;
        }
        $sql = "select count(*) as num from zj_order where orderid='$orderid'  and status=0 ";
        $res = dbLoad(dbQuery($sql,$con),true);

        if($res['num'] > 0){
            if(dbUpdate(array('status'=>1,'paytype'=>2,'paytime'=>time()), 'zj_order', $con, "orderid = "."'".$orderid."'")){
                mysql_close($con);
                file_put_contents("../WxPay/logs/".date('Y-m-d').'.log',date('Y-m-d H:i:s').'notify--success!!',0777);
                return 'success';
            }else{
                mysql_close($con);
                return false;
            }
        }else{
            $sql2 = "select count(*) as sum from zj_qgorder where qgorderid='$orderid' and status=0 ";
            $res2 = dbLoad(dbQuery($sql2,$con),true);


            if($res2['sum'] > 0){
                if(dbUpdate(array('status'=>1,'paytype'=>2,'paytime'=>time()), 'zj_qgorder', $con, "qgorderid = "."'".$orderid."'")){
                    mysql_close($con);
                    file_put_contents("../WxPay/logs/".date('Y-m-d').'.log',date('Y-m-d H:i:s').'qg--notify--success!!',0777);
                    return 'success';
                }else{
                    mysql_close($con);
                    return false;
                }
            }else{
                return false;
            }
            
        }
    }
}

$orderid = $_POST['orderid'];
$class = new UpdateState();
$class->update($orderid);