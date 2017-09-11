<?php
defined('_JEXEC') or die;

class WalletViewWallets extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		WalletHelper::addSubmenu('wallets');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = WalletHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('钱包管理：钱包列表'), 'support');
		
		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_wallet');
		}
	}
}
