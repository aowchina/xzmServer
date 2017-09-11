<?php
defined('_JEXEC') or die;

class ServiceViewServices extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		ServiceHelper::addSubmenu('services');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = ServiceHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('客服信息管理'), 'pie');
		
			if($canDo->get('core.create')){
				JToolbarHelper::addNew('service.add');
			}
		
		if($canDo->get('core.edit')){
			JToolbarHelper::editList('service.edit');
		}
		
		if($canDo->get('core.delete')){

		JToolbarHelper::deleteList('', 'services.delete', 'JTOOLBAR_DELETE');

		}

		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_service');
		}

		 if ($canDo->get('core.edit.state'))
		{
			if ($this->state->get('filter.published') != 2)
			{
				JToolbarHelper::publish('services.publish', 'JTOOLBAR_PUBLISH', true);
				JToolbarHelper::unpublish('services.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			}
		}
	}
}
