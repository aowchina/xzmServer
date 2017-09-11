<?php

defined('_JEXEC') or die;

class AppuserModelAppuser extends JModelAdmin{

	public function getTable($type = 'Appuser', $prefix = 'AppuserTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_appuser.appuser', 'appuser', array('control'=>'jform', 'load_data'=>$loadData));

		if(empty($form)){
			return false;
		}

		return $form;
	}

	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_appuser.edit.appuser.data', array());

		if(empty($data)){

	}}


}
