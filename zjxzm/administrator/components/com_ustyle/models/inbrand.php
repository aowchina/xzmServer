<?php

defined('_JEXEC') or die;

class UstyleModelInbrand extends JModelAdmin{


	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_ustyle.inbrand', 'inbrand', array('control'=>'jform', 'load_data'=>$loadData));

		if(empty($form)){
			return false;
		}

		return $form;
	}



	
}
