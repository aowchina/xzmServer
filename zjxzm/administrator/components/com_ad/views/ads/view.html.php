<?php
defined('_JEXEC') or die;

class AdViewAds extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		AdHelper::addSubmenu('ads');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = adHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('广告'), 'pie');
		
			if($canDo->get('core.create')){
				JToolbarHelper::addNew('ad.add');
			}
		
		if($canDo->get('core.edit')){
			JToolbarHelper::editList('ad.edit');
		}
		
		if($canDo->get('core.delete')){

		JToolbarHelper::deleteList('', 'ads.delete', 'JTOOLBAR_DELETE');

		}

		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_ad');
		}
		if ($canDo->get('core.edit.state'))
		{
			if ($this->state->get('filter.published') != 2)
			{
				JToolbarHelper::publish('ads.publish', 'JTOOLBAR_PUBLISH', true);
				JToolbarHelper::unpublish('ads.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			}
		}
	}
}
