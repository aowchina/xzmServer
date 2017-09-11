<?php

defined('_JEXEC') or die;

class UstyleModelIn extends JModelAdmin{


	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_ustyle.in', 'in', array('control'=>'jform', 'load_data'=>$loadData));

		if(empty($form)){
			return false;
		}

		return $form;
	}


}
