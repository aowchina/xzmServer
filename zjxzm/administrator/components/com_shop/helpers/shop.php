<?php
defined('_JEXEC') or die;

class ShopHelper{
	public static function getActions($categoryId = 0){
		$user  = JFactory::getUser();

    	$result  = new JObject;
       	if (empty($categoryId)){
        	$assetName = 'com_shop';
         	$level = 'component';
       	}
		else {
         	$assetName = 'com_shop.category.'.(int) $categoryId;
         	$level = 'category';
        }

        $actions = JAccess::getActions('com_shop', $level);

        foreach ($actions as $action){
         	$result->set($action->name,  $user->authorise($action->name, $assetName));
        }

		return $result;
	}

    public static function addSubmenu($vName = 'shops'){
        JHtmlSidebar::addEntry(
            JText::_('店铺管理'),'index.php?option=com_shop&view=shops',$vName == 'shops');
   
    }
}

