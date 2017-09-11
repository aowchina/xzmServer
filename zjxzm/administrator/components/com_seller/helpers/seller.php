<?php
defined('_JEXEC') or die;

class SellerHelper{
	public static function getActions($categoryId = 0){
        $user  = JFactory::getUser();
        $result  = new JObject;
        if (empty($categoryId)){
            $assetName = 'com_seller';
            $level = 'component';
        }else {
            $assetName = 'com_seller.category.'.(int) $categoryId;
            $level = 'category';
        }

        $actions = JAccess::getActions('com_seller', $level);
        foreach ($actions as $action){
            $result->set($action->name,  $user->authorise($action->name, $assetName));
        }

	   return $result;
	}

    public static function addSubmenu($vName = 'sellers'){
        JHtmlSidebar::addEntry(JText::_('配件商管理'),'index.php?option=com_seller&view=sellers',$vName == 'sellers');
    }

    public static function addSubmenu2($vName = 'areas'){
        JHtmlSidebar::addEntry(JText::_('配件商管理'),'index.php?option=com_seller&view=sellers',$vName == 'sellers');
        // JHtmlSidebar::addEntry(JText::_('负责区域'),'index.php?option=com_seller&view=areas',$vName == 'areas');
    }
}
