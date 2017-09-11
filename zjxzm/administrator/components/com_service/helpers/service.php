<?php
defined('_JEXEC') or die;

class ServiceHelper{
	public static function getActions($categoryId = 0){
		$user  = JFactory::getUser();
    	$result  = new JObject;
       	if (empty($categoryId)){
        	$assetName = 'com_service';
         	$level = 'component';
       	}
		else {
         	$assetName = 'com_service.category.'.(int) $categoryId;
         	$level = 'category';
        }

        $actions = JAccess::getActions('com_service', $level);
        foreach ($actions as $action){
         	$result->set($action->name,  $user->authorise($action->name, $assetName));
        }

		return $result;
	}

     public static function addSubmenu($vName = 'service'){
        JHtmlSidebar::addEntry(
            JText::_('客服管理'),'index.php?option=com_service&view=services',$vName == 'services');

    }
}

 