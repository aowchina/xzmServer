<?php
defined('_JEXEC') or die;

class EpcViewOems extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		EpcHelper::addSubmenu('oems');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = EpcHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('配件'), 'cart');
		
		//创建按钮
		if($canDo->get('core.create')){
			JToolbarHelper::addNew('oem.add');
		}

		//编辑按钮
		if($canDo->get('core.edit')){
			JToolbarHelper::editList('oem.edit');
			JToolbarHelper::custom('oems.toin', 'contract-2', 'contract-2', '导入配件数据', false);
		}

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'oems.delete', 'JTOOLBAR_DELETE');
			JToolbarHelper::divider();
		}
		
		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_epc');
		}

		JHtmlSidebar::setAction('index.php?option=com_epc&view=oems');
	}
}
