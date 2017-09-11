<?php
defined('_JEXEC') or die;

class WborderHelper{
	public static function getActions($categoryId = 0){
        $user  = JFactory::getUser();
        $result  = new JObject;
        if (empty($categoryId)){
            $assetName = 'com_wborder';
            $level = 'component';
        }else {
            $assetName = 'com_wborder.category.'.(int) $categoryId;
            $level = 'category';
        }

        $actions = JAccess::getActions('com_wborder', $level);
        foreach ($actions as $action){
            $result->set($action->name,  $user->authorise($action->name, $assetName));
        }

	   return $result;
	}

    public static function addSubmenu($vName = 'wborders'){
        JHtmlSidebar::addEntry(JText::_('求购订单'),'index.php?option=com_wborder&view=wborders',$vName == 'wborders');
    }
}
