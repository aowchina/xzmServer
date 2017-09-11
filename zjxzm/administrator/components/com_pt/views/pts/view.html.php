<?php
defined('_JEXEC') or die;

class PtViewPts extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		PtHelper::addSubmenu('pts');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = ptHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('配件分类'), 'pie');
		
			if($canDo->get('core.create')){
				JToolbarHelper::addNew('pt.add');
			}
		
		if($canDo->get('core.edit')){
			JToolbarHelper::editList('pt.edit');
			JToolbarHelper::custom('pts.toin', 'contract-2', 'contract-2', '导入配件分类数据', false);
		}
		
		if($canDo->get('core.delete')){

		JToolbarHelper::deleteList('', 'pts.delete', 'JTOOLBAR_DELETE');

		}

		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_pt');
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
