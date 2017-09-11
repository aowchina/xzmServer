<?php

defined('_JEXEC') or die;
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

//类别 组件名.Controller
class OrderControllerOrders extends JControllerAdmin{

	public function getModel($name = 'Order', $prefix = 'OrderModel', $config = array('ignore_request'=>true)){
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	 public function getOp(){
	 	$pid = $_GET['pid'];
	 	$db = JFactory::getDbo();

	 	$sql = "select areaname as name, id from #__area where parentid = $pid";
	 	$db->setQuery($sql);

	 	$re = $db->loadObjectList();
	 	$json_re = json_encode($re);
	 	$new_re = json_decode($json_re, true);
	 	array_unshift($new_re, array('name'=>'无', 'id'=>0));

	 	echo json_encode($new_re);
	 	exit;
	 }

	 public function cancel(){
	 	$this->setRedirect(JRoute::_('index.php?option=com_order&view=orders', false));
	 	return false;
	 }

 	public function save(){
 		$jFileInput = new JInput($_FILES);
         $files = $jFileInput->get('jform',array(),'array');

         $now_time = time();
 		$save_path = JPATH_ROOT.'/xls/order.xlsx';

 		if(!JFile::upload($files['tmp_name']['xls'], $save_path)){
 			$this->setRedirect(JRoute::_('index.php?option=com_order&view=orders', false));
 			return false;
 		}
		
 		include 'PHPExcel/Classes/PHPExcel.php';
 		$objReader = PHPExcel_IOFactory::createReaderForFile($save_path);
 		$objPHPExcel = $objReader->load($save_path);

 		$sheet = $objPHPExcel->getSheet(0);
     	$highestRow = $sheet->getHighestRow();           //取得总行数
 		$highestColumn = $sheet->getHighestColumn();     //取得总列数

 		$db = JFactory::getDbo();

 		for($j = 2; $j <= $highestRow; $j++){

 			$order_sfid = $objPHPExcel->getActiveSheet()->getCell("A$j")->getValue();
 			$order_id = $objPHPExcel->getActiveSheet()->getCell("B$j")->getValue();

 			if($order_sfid){
 				$sql = "select count(*) from #__order where order_id = '$order_id' and status = 1";
 				$db->setQuery($sql);
 				$count = $db->loadResult();
				
 				if($count == 1){
 					$sql = "update #__order set order_sfid = '$order_sfid',status = 2 where order_id = '$order_id'";
 					$db->setQuery($sql);
 					$db->query();
 				}
 			}
         }
    	
     	$this->setRedirect(JRoute::_('index.php?option=com_order&view=orders', false));
 		return false;
 	}

// 	//显示导入订单页面
// 	public function toin(){
// 		$this->setRedirect(JRoute::_('index.php?option=com_order&view=in&layout=edit', false));
// 		return false;
// 	}
//
// 	//导出订单
// 	public function out(){
// 		$ids = $this->input->get('cid', array(), 'array');
// 		if(count($ids) < 1){
// 			JError::raiseWarning(500, '请选择至少一条记录');
// 			$this->setRedirect(JRoute::_('index.php?option=com_order&view=orders', false));
// 			return false;
// 		}
//
// 		$db = JFactory::getDbo();
//
// 		//表格数组
// 		$data = array();
//
// 		foreach($ids as $id){
// 			$data_item = array();
//
// 			$field = "order_id,user_pid,user_cid,user_qid,user_name,user_tel,user_address,price,wl_price,wl_id";
// 			$sql = "select $field from #__order where id = $id";
// 			$db->setQuery($sql);
// 			$order_info = $db->loadObject();
//
// 			$data_item[] = $order_info->order_id;
// 			$data_item[] = $order_info->user_name;
//
//
// 			$sql = "select areaname from #__area where id = ".$order_info->user_pid;
// 			$db->setQuery($sql);
// 			$pname = $db->loadResult();
// 			$data_item[] = $pname;
//
// 			if($order_info->user_cid > 0){
// 				$sql = "select areaname from #__area where id = ".$order_info->user_cid;
// 				$db->setQuery($sql);
// 				$cname = $db->loadResult();
// 				$data_item[] = $cname;
// 			}
// 			else{
// 				$data_item[] = '';
// 			}
//
// 			if($order_info->user_qid > 0){
// 				$sql = "select areaname from #__area where id = ".$order_info->user_qid;
// 				$db->setQuery($sql);
// 				$qname = $db->loadResult();
// 				$data_item[] = $qname;
// 			}
// 			else{
// 				$data_item[] = '';
// 			}
//
// 			$data_item[] = $order_info->user_address;
// 			$data_item[] = $order_info->user_tel;
//
// 			$sql = 'select name from #__wl where id= '.$order_info->wl_id;
// 			$db->setQuery($sql);
// 			$wl_name = $db->loadResult();
// 			$data_item[] = $wl_name;
// //			if($order_info->wl_id == 1){
// //				$data_item[] = "顺丰快递";
// //			}
// //			else{
// //				$data_item[] = "自提";
// //			}
//
// 			$data_item[] = $order_info->price + $order_info->wl_price;
// 			$data_item[] = $order_info->wl_price;
//
// 			$sql = "select goods_num,price,amount from #__order_goods where order_id = '".$order_info->order_id."'";
// 			$db->setQuery($sql);
// 			$order_goods = $db->loadObjectList();
//
// 			$goods_num_list = '';
// 			$goods_name_list = '';
// 			$goods_price_list = '';
// 			$goods_amount_list = '';
//
// 			foreach($order_goods as $h => $order_goods_item){
// 				$sql = "select name from #__goods where goods_num = '".$order_goods_item->goods_num."'";
// 				$db->setQuery($sql);
// 				$name = $db->loadResult();
//
// 				if($h != count($order_goods) - 1){
// 					$goods_num_list .= $order_goods_item->goods_num."\n";
// 					$goods_name_list .= $name."\n";
// 					$goods_price_list .= $order_goods_item->price."\n";
// 					$goods_amount_list .= $order_goods_item->amount."\n";
// 				}
// 				else{
// 					$goods_num_list .= $order_goods_item->goods_num;
// 					$goods_name_list .= $name;
// 					$goods_price_list .= $order_goods_item->price;
// 					$goods_amount_list .= $order_goods_item->amount;
// 				}
// 			}
//
// 			$data_item[] = $goods_num_list;
// 			$data_item[] = $goods_name_list;
// 			$data_item[] = $goods_price_list;
// 			$data_item[] = $goods_amount_list;
//
// 			$data[] = $data_item;
// 		}
//
// 		include 'PHPExcel/Classes/PHPExcel.php';
// 		$excel = new PHPExcel();
//
// 		//Excel表格式,这里简略写了8列
// 		$letter = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N');
//
// 		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
// 		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
// 		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
// 		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
// 		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
// 		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
// 		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
// 		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
// 		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
// 		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
// 		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
// 		$excel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
// 		$excel->getActiveSheet()->getColumnDimension('M')->setWidth(10);
// 		$excel->getActiveSheet()->getColumnDimension('N')->setWidth(10);
//
// 		//表头数组
// 		$tableheader = array('我方单号','收件人','省','市','区/县','详细地址','电话',
// 			'快递方式','商品总价(含物流)','物流费用','商品编码','商品名称','出货价格','商品数量');
//
// 		//填充表头信息
// 		for($i = 0; $i < count($tableheader); $i++) {
// 			$excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
// 		}
//
// 		//填充表格信息
// 		for ($i = 2;$i <= count($data) + 1;$i++) {
// 			$j = 0;
// 			foreach ($data[$i - 2] as $key=>$value) {
// 				if($j == 10 || $j == 11 || $j == 12 || $j == 13){
// 					$excel->getActiveSheet()->getStyle("$letter[$j]$i")->getAlignment()->setWrapText(true);
// 				}
// 				$excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i", "$value", PHPExcel_Cell_DataType::TYPE_STRING);
// 				$j++;
// 			}
// 		}
//
// 		$write = new PHPExcel_Writer_Excel5($excel);
// 		header("Pragma: public");
// 		header("Expires: 0");
// 		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
// 		header("Content-Type:application/force-download");
// 		header("Content-Type:application/vnd.ms-execl");
// 		header("Content-Type:application/octet-stream");
// 		header("Content-Type:application/download");;
// 		header('Content-Disposition:attachment;filename="恒都内购平台'.date("Y-m-d H:m:s", time()).'订单导出.xls"');
// 		header("Content-Transfer-Encoding:binary");
// 		$write->save('php://output');
// 		exit;
// 	}
}