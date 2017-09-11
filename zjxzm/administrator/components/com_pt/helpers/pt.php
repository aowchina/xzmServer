<?php
defined('_JEXEC') or die;

class PtHelper{
    public static function getActions($categoryId = 0){
        $user  = JFactory::getUser();
        $result  = new JObject;
        if (empty($categoryId)){
            $assetName = 'com_pt';
            $level = 'component';
        }
        else {
            $assetName = 'com_pt.category.'.(int) $categoryId;
            $level = 'category';
        }

        $actions = JAccess::getActions('com_pt', $level);
        foreach ($actions as $action){
            $result->set($action->name,  $user->authorise($action->name, $assetName));
        }

        return $result;
    }

     public static function addSubmenu($vName = 'pt'){
        JHtmlSidebar::addEntry(
            JText::_('配件分类'),'index.php?option=com_pt&view=pts',$vName == 'pts');

    }
}

 