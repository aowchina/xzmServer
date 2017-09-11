<?php

defined('_JEXEC') or die;

class WborderModelIn extends JModelAdmin{

	/*
	public function getTable($type = 'Wborder', $prefix = 'WborderTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	*/

	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_wborder.in', 'in', array('control'=>'jform', 'load_data'=>$loadData));

		if(empty($form)){
			return false;
		}

		return $form;
	}

	/*
	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_wborder.edit.wborder.data', array());

		if(empty($data)){
			$data = $this->getItem();

			$db = JFactory::getDbo();
			$sql = "select areaname from #__area where id = $data->user_pid";
			$db->setQuery($sql);
			$pname = $db->loadResult();

			$sql = "select areaname from #__area where id = $data->user_cid";
			$db->setQuery($sql);
			$cname = $db->loadResult();

			$sql = "select areaname from #__area where id = $data->user_qid";
			$db->setQuery($sql);
			$qname = $db->loadResult();

			$data->address = $pname.' '.$cname.' '.$qname.' '.$data->user_address;
		}

		return $data;
	}
	*/
}
