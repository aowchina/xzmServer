<?php

defined('_JEXEC') or die;
if (!JFactory::getUser()->authorise('core.manage', 'com_appuser')){
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
$controller	= JControllerLegacy::getInstance('Appuser');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();