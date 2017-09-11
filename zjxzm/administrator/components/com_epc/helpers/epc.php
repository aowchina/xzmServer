<?php
defined('_JEXEC') or die;

class EpcHelper{
	public static function getActions($categoryId = 0){
		$user  = JFactory::getUser();
    	$result  = new JObject;
       	if (empty($categoryId)){
        	$assetName = 'com_epc';
         	$level = 'component';
       	}
		else {
         	$assetName = 'com_epc.category.'.(int) $categoryId;
         	$level = 'category';
        }

        $actions = JAccess::getActions('com_epc', $level);
        foreach ($actions as $action){
         	$result->set($action->name,  $user->authorise($action->name, $assetName));
        }

		return $result;
	}

     public static function addSubmenu($vName = 'epcs'){
        JHtmlSidebar::addEntry(
            JText::_('EPC结构图'),'index.php?option=com_epc&view=epcs',$vName == 'epcs');

        JHtmlSidebar::addEntry(JText::_('配件'),'index.php?option=com_epc&view=oems',$vName == 'oems');
        // if ($vName == 'ustype'){
        //     JToolbarHelper::title(
        //         JText::sprintf('COM_USERSTYLECON_USERSTYLECON_TITLE',JText::_('com_epc')),'epc-ustype');
        // }
       

    }
}

 