<?php
defined('_JEXEC') or die;

/*
 * 类名必须是 组件名 + Controller
 * author wangrui
 * 2016-03-08
 */
class OrderController extends JControllerLegacy{
	protected $default_view = 'orders';

	public function display($cachable = false, $urlparams = false){
		require_once JPATH_COMPONENT.'/helpers/order.php';
		
		$view = JRequest::getCmd('view', 'orders');
		$layout = JRequest::getCmd('layout', 'default');
		$id = JRequest::getCmd('id');
		
		parent::display();
		return $this;
	}
}
?>