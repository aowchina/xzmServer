<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once "../WxPay/lib/WxPay.Api.php";
require_once '../WxPay/lib/WxPay.Notify.php';
require_once '../WxPay/example/log.php';

//初始化日志
$logHandler= new CLogFileHandler("../WxPay/logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);


class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}

	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();

		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";sftp://root:@218.240.21.181/data/pubout/minfo/zjxzm/api/users/order/UpdateState.php
			return false;
		}
		return true;
	}
	//修改订单状态
	public function updateState($data){
		if($data){
			//这里有一个彩蛋。。。。。
			$order_id = $data['out_trade_no'];
			$return = array();
			$return['orderid'] = $order_id;

			//修改订单状态(用curlpost方法请求至thinkphp目录下的Controller里面控制器里面的方法,修改状态)
			$url = "http://".$_SERVER['HTTP_HOST'].'/api/users/order/UpdateState.php';
			//$url = "http://".$_SERVER['HTTP_HOST'].'/zjxzm/api/users/order/UpdateState.php';
			header('content-type:text/html;charset=utf8');
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $return);
			$result = curl_exec($curl);
			curl_close($curl);

			//回调返回值记录
			Log::DEBUG($result.'returnValue');

		}
	}
}

Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle(false);

//接受参数,修改状态
$xml = file_get_contents("php://input");

$data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

$notify->updateState($data);
