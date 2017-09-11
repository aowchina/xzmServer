<?php
defined('_JEXEC') or die;

class UstyleHelper{
	public static function getActions($categoryId = 0){
		$user  = JFactory::getUser();
    	$result  = new JObject;
       	if (empty($categoryId)){
        	$assetName = 'com_ustyle';
         	$level = 'component';
       	}
		else {
         	$assetName = 'com_ustyle.category.'.(int) $categoryId;
         	$level = 'category';
        }

        $actions = JAccess::getActions('com_ustyle', $level);
        foreach ($actions as $action){
         	$result->set($action->name,  $user->authorise($action->name, $assetName));
        }

		return $result;
	}

     public static function addSubmenu($vName = 'ustyles'){
        JHtmlSidebar::addEntry(
            JText::_('车款管理'),'index.php?option=com_ustyle&view=ustyles',$vName == 'ustyles');

        JHtmlSidebar::addEntry(
            JText::_('车系管理'),'index.php?option=com_ustyle&view=ustypes',$vName == 'ustypes');

		 JHtmlSidebar::addEntry(
			 JText::_('品牌管理'),'index.php?option=com_ustyle&view=brands',$vName == 'brands');


        if ($vName == 'ustype'){
            JToolbarHelper::title(
                JText::sprintf('COM_USERSTYLECON_USERSTYLECON_TITLE',JText::_('com_ustyle')),'ustyle-ustype');
        }


		 if ($vName == 'brand'){
			 JToolbarHelper::title(
				 JText::sprintf('COM_USERSTYLECON_USERSTYLECON_TITLE',JText::_('com_ustyle')),'ustyle-brand');
		 }
       

    }

	public static function addSubmenu2($vName = 'cimgs'){
		JHtmlSidebar::addEntry(JText::_('品牌管理'),'index.php?option=com_ustyle&view=brands',$vName == 'brands');
		JHtmlSidebar::addEntry(JText::_('车系管理'),'index.php?option=com_ustyle&view=ustypes',$vName == 'ustypes');
		JHtmlSidebar::addEntry(JText::_('车款管理'),'index.php?option=com_ustyle&view=ustyles',$vName == 'ustyles');
		JHtmlSidebar::addEntry(JText::_('图片详情'),'index.php?option=com_ustyle&view=cimgs',$vName == 'cimgs');

	}
}

 