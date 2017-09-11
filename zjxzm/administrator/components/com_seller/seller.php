<?php

defined('_JEXEC') or die;
if (!JFactory::getUser()->authorise('core.manage', 'com_seller')){
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
$controller	= JControllerLegacy::getInstance('Seller');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();