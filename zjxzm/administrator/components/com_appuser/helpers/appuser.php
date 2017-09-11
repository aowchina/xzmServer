<?php
defined('_JEXEC') or die;

class AppuserHelper{
	public static function getActions($categoryId = 0){
        $user  = JFactory::getUser();
        $result  = new JObject;
        if (empty($categoryId)){
            $assetName = 'com_appuser';
            $level = 'component';
        }else {
            $assetName = 'com_appuser.category.'.(int) $categoryId;
            $level = 'category';
        }

        $actions = JAccess::getActions('com_appuser', $level);
        foreach ($actions as $action){
            $result->set($action->name,  $user->authorise($action->name, $assetName));
        }

	   return $result;
	}

    public static function addSubmenu($vName = 'appusers'){
        JHtmlSidebar::addEntry(JText::_('APP用户管理'),'index.php?option=com_appuser&view=appusers',$vName == 'appusers');
    }

    
}
