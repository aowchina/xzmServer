<?php
defined('_JEXEC') or die;

class TypeHelper{
	public static function getActions($categoryId = 0){
		$user  = JFactory::getUser();
    	$result  = new JObject;
       	if (empty($categoryId)){
        	$assetName = 'com_type';
         	$level = 'component';
       	}
		else {
         	$assetName = 'com_type.category.'.(int) $categoryId;
         	$level = 'category';
        }

        $actions = JAccess::getActions('com_type', $level);
        foreach ($actions as $action){
         	$result->set($action->name,  $user->authorise($action->name, $assetName));
        }

		return $result;
	}

     public static function addSubmenu($vName = 'types'){

        JHtmlSidebar::addEntry(
            JText::_('商品类别'),'index.php?option=com_type&view=types',$vName == 'types');


        if ($vName == 'type'){
            JToolbarHelper::title(
                JText::sprintf('COM_USERSTYLECON_USERSTYLECON_TITLE',JText::_('com_type')),'type-type');
        }
       

    }
}

 