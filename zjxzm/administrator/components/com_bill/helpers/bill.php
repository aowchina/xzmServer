<?php
defined('_JEXEC') or die;

class BillHelper{
	public static function getActions($categoryId = 0){
		$user  = JFactory::getUser();
    	$result  = new JObject;
       	if (empty($categoryId)){
        	$assetName = 'com_bill';
         	$level = 'component';
       	}
		else {
         	$assetName = 'com_bill.category.'.(int) $categoryId;
         	$level = 'category';
        }

        $actions = JAccess::getActions('com_bill', $level);
        foreach ($actions as $action){
         	$result->set($action->name,  $user->authorise($action->name, $assetName));
        }

		return $result;
	}

     public static function addSubmenu($vName = 'bill'){
        JHtmlSidebar::addEntry(
            JText::_('账单管理'),'index.php?option=com_bill&view=bills',$vName == 'bills');

    }
}

 