<?php
defined('_JEXEC') or die;

class EpcViewEpcs extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		EpcHelper::addSubmenu('epcs');

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

		JToolbarHelper::title(JText::_('EPC结构图'), 'pie');
		
			if($canDo->get('core.create')){
				JToolbarHelper::addNew('epc.add');
			}
		
		if($canDo->get('core.edit')){
			JToolbarHelper::editList('epc.edit');
			JToolbarHelper::custom('epcs.toin', 'contract-2', 'contract-2', '导入epc数据', false);
		}
		
		if($canDo->get('core.delete')){

		JToolbarHelper::deleteList('', 'epcs.delete', 'JTOOLBAR_DELETE');

		}
		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_epc');
		}
	}
}
