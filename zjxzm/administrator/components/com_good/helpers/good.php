<?php
defined('_JEXEC') or die;

class GoodHelper{
	public static function getActions($categoryId = 0){
		$user  = JFactory::getUser();
    	$result  = new JObject;
       	if (empty($categoryId)){
        	$assetName = 'com_good';
         	$level = 'component';
       	}
		else {
         	$assetName = 'com_good.category.'.(int) $categoryId;
         	$level = 'category';
        }

        $actions = JAccess::getActions('com_good', $level);
        foreach ($actions as $action){
         	$result->set($action->name,  $user->authorise($action->name, $assetName));
        }

		return $result;
	}

     public static function addSubmenu($vName = 'goods'){
        JHtmlSidebar::addEntry(
            JText::_('商品管理'),'index.php?option=com_good&view=goods',$vName == 'goods');

    }
}

 