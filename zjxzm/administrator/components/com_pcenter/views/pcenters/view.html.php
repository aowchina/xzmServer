<?php
defined('_JEXEC') or die;

class PcenterViewPcenters extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		PcenterHelper::addSubmenu('pcenters');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = PcenterHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('文本管理'), 'pie');
		
			if($canDo->get('core.create')){
				JToolbarHelper::addNew('pcenter.add');
			}
		
		if($canDo->get('core.edit')){
			JToolbarHelper::editList('pcenter.edit');
		}
		
		if($canDo->get('core.delete')){

		JToolbarHelper::deleteList('', 'pcenters.delete', 'JTOOLBAR_DELETE');

		}

		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_pcenter');
		}
		// if ($canDo->get('core.edit.state'))
		// {
		// 	if ($this->state->get('filter.published') != 2)
		// 	{
		// 		JToolbarHelper::publish('ads.publish', 'JTOOLBAR_PUBLISH', true);
		// 		JToolbarHelper::unpublish('ads.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		// 	}
		// }
	}
}
