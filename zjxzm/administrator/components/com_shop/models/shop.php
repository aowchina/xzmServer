<?php
defined('_JEXEC') or die;
class ShopModelShop extends JModelAdmin
{
	protected $text_prefix = 'COM_SHOP';
	public function getTable($type = 'Shop', $prefix = 'ShopTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_shop.shop', 'shop',array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_shop.edit.shop.data', array());
		if (empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}


}
