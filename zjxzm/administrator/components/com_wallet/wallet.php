<?php

defined('_JEXEC') or die;
if (!JFactory::getUser()->authorise('core.manage', 'com_wallet')){
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
$controller	= JControllerLegacy::getInstance('Wallet');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();