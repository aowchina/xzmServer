<?php
defined('_JEXEC') or die;

class ShopController extends JControllerLegacy{
	protected $default_view = 'shops';

	public function display($cachable = false, $urlparams = false){
		require_once JPATH_COMPONENT.'/helpers/shop.php';
		
		$view = JRequest::getCmd('view', 'shops');
		$layout = JRequest::getCmd('layout', 'default');
		$id = JRequest::getCmd('shopid');
		
		parent::display();
		return $this;
	}
}
?>