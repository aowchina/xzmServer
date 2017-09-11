<?php
defined('_JEXEC') or die;

class WalletHelper{
	public static function getActions($categoryId = 0){
        $user  = JFactory::getUser();
        $result  = new JObject;
        if (empty($categoryId)){
            $assetName = 'com_wallet';
            $level = 'component';
        }else {
            $assetName = 'com_wallet.category.'.(int) $categoryId;
            $level = 'category';
        }

        $actions = JAccess::getActions('com_wallet', $level);
        foreach ($actions as $action){
            $result->set($action->name,  $user->authorise($action->name, $assetName));
        }

	   return $result;
	}

    public static function addSubmenu($vName = 'wallets'){
        JHtmlSidebar::addEntry(JText::_('钱包'),'index.php?option=com_wallet&view=wallets',$vName == 'wallets');
    }
}
