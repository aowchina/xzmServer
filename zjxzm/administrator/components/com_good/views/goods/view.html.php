<?php
defined('_JEXEC') or die;

class GoodViewGoods extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		GoodHelper::addSubmenu('goods');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = GoodHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('商品信息'), 'pie');
		
			// if($canDo->get('core.create')){
			// 	JToolbarHelper::addNew('good.add');
			// }
		
		// if($canDo->get('core.edit')){
		// 	JToolbarHelper::editList('good.edit');
		// }
		
		if($canDo->get('core.delete')){

		JToolbarHelper::deleteList('', 'goods.delete', 'JTOOLBAR_DELETE');

		}
		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_good');
		}

		if ($canDo->get('core.edit.state'))
		{
			if ($this->state->get('filter.published') != 2)
			{
				JToolbarHelper::publish('goods.publish', 'JTOOLBAR_PUBLISH', true);
				JToolbarHelper::unpublish('goods.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			}
		}
	}
}
