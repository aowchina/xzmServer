<?php
defined('_JEXEC') or die;
class BillModelBill extends JModelAdmin
{
	protected $text_prefix = 'COM_BILL';
	public function getTable($type = 'Bill', $prefix = 'BillTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_bill.bill', 'bill',array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	//获取修改记录信息
	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_bill.edit.bill.data', array());
		if (empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}

}