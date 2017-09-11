<?php
defined('_JEXEC') or die;

/*
 * 类名必须是 组件名 + Controller
 * author zhangqin
 * 2017-02-23
 */
class WalletController extends JControllerLegacy{
	protected $default_view = 'wallets';

	public function display($cachable = false, $urlparams = false){
		require_once JPATH_COMPONENT.'/helpers/wallet.php';
		
		$view = JRequest::getCmd('view', 'wallets');
		$layout = JRequest::getCmd('layout', 'default');
		$id = JRequest::getCmd('id');
		
		parent::display();
		return $this;
	}
}
?>