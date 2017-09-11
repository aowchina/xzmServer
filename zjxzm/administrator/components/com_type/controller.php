<?php
defined('_JEXEC') or die;

/*
 * 类名必须是 组件名 + Controller
 * author zhangqin
 * 2016-09-22
 */
class TypeController extends JControllerLegacy{
	protected $default_view = 'types';

	public function display($cachable = false, $urlparams = false){
		require_once JPATH_COMPONENT.'/helpers/type.php';
		
		$view = JRequest::getCmd('view', 'types');
		$layout = JRequest::getCmd('layout', 'default');
		$id = JRequest::getCmd('id');
		
		parent::display();
		return $this;
	}
}
?>