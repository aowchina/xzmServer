<?php
defined('_JEXEC') or die;

class OrderHelper{
	public static function getActions($categoryId = 0){
        $user  = JFactory::getUser();
        $result  = new JObject;
        if (empty($categoryId)){
            $assetName = 'com_order';
            $level = 'component';
        }else {
            $assetName = 'com_order.category.'.(int) $categoryId;
            $level = 'category';
        }

        $actions = JAccess::getActions('com_order', $level);
        foreach ($actions as $action){
            $result->set($action->name,  $user->authorise($action->name, $assetName));
        }

	   return $result;
	}

    public static function addSubmenu($vName = 'orders'){
        JHtmlSidebar::addEntry(JText::_('采购订单'),'index.php?option=com_order&view=orders',$vName == 'orders');
    }
}
