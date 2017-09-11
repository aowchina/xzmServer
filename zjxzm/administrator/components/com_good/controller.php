<?php
defined('_JEXEC') or die;

/*
 * 类名必须是 组件名 + Controller
 * author zhangqin
 * 2016-09-22
 */
class GoodController extends JControllerLegacy{
	protected $default_view = 'goods';

	public function display($cachable = false, $urlparams = false){
		require_once JPATH_COMPONENT.'/helpers/good.php';
		
		$view = JRequest::getCmd('view', 'goods');
		$layout = JRequest::getCmd('layout', 'default');
		$id = JRequest::getCmd('id');
		
		parent::display();
		return $this;
	}
}
?>