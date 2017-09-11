<?php
defined('_JEXEC') or die;

class UstyleViewUstypes extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');
  		UstyleHelper::addSubmenu('ustypes');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = UstyleHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('车系管理'), 'pie');
		
		if($canDo->get('core.create')){
			JToolbarHelper::addNew('ustype.add');
		}

		if($canDo->get('core.edit')){
			JToolbarHelper::editList('ustype.edit');
			JToolbarHelper::custom('ustypes.toin', 'contract-2', 'contract-2', '导入车系', false);
		}
		
		if($canDo->get('core.delete')){
			JToolbarHelper::deleteList('', 'ustypes.delete', 'JTOOLBAR_DELETE');
		}

		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_ustype');
		}

	}
}
