<?php

defined('_JEXEC') or die;

class SellerModelSeller extends JModelAdmin{

	public function getTable($type = 'Seller', $prefix = 'SellerTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_seller.seller', 'seller', array('control'=>'jform', 'load_data'=>$loadData));

		if(empty($form)){
			return false;
		}

		return $form;
	}

	//获取修改记录信息
	protected function loadFormData(){
		
		$data = JFactory::getApplication()->getUserState('com_seller.edit.seller.data', array());
		if (empty($data)){
			$data = $this->getItem();

			$data=get_object_vars($data);
			$sellerid= $data['sellerid'];
			$db = JFactory::getDbo();
			$sql = "select * from #__sellercert where sellerid='$sellerid'";
			$db->setQuery($sql);
			$res = $db->loadAssoc();
			array_unshift($data, $res);
		}

		return $data;
	}


}
