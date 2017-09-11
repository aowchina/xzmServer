<?php
defined('_JEXEC') or die;

class AdHelper{
    public static function getActions($categoryId = 0){
        $user  = JFactory::getUser();
        $result  = new JObject;
        if (empty($categoryId)){
            $assetName = 'com_ad';
            $level = 'component';
        }
        else {
            $assetName = 'com_ad.category.'.(int) $categoryId;
            $level = 'category';
        }

        $actions = JAccess::getActions('com_ad', $level);
        foreach ($actions as $action){
            $result->set($action->name,  $user->authorise($action->name, $assetName));
        }

        return $result;
    }

     public static function addSubmenu($vName = 'ad'){
        JHtmlSidebar::addEntry(
            JText::_('广告'),'index.php?option=com_ad&view=ads',$vName == 'ads');

    }
}

 