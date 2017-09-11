<?php
defined('_JEXEC') or die;

class PcenterHelper{
    public static function getActions($categoryId = 0){
        $user  = JFactory::getUser();
        $result  = new JObject;
        if (empty($categoryId)){
            $assetName = 'com_pcenter';
            $level = 'component';
        }
        else {
            $assetName = 'com_pcenter.category.'.(int) $categoryId;
            $level = 'category';
        }

        $actions = JAccess::getActions('com_pcenter', $level);
        foreach ($actions as $action){
            $result->set($action->name,  $user->authorise($action->name, $assetName));
        }

        return $result;
    }

    public static function addSubmenu($vName = 'pcenter'){
        JHtmlSidebar::addEntry(
            JText::_('文本管理'),'index.php?option=com_ad&view=pcenters',$vName == 'pcenters');

    }
}



 