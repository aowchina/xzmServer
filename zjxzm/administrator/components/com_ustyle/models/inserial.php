<?php

defined('_JEXEC') or die;

class UstyleModelInserial extends JModelAdmin{


	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_ustyle.inserial', 'inserial', array('control'=>'jform', 'load_data'=>$loadData));

		if(empty($form)){
			return false;
		}

		return $form;
	}

	
}
