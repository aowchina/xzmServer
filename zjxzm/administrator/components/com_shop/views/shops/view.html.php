<?php
defined('_JEXEC') or die;

class ShopViewShops extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		ShopHelper::addSubmenu('shops');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = ShopHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('店铺管理'), 'pie');
		
	
//		if($canDo->get('core.create')){
//			JToolbarHelper::addNew('shop.add');
//		}
//
//		if($canDo->get('core.edit')){
//			JToolbarHelper::editList('shop.edit');
//		}
		
//		if($canDo->get('core.delete')){
//
//		JToolbarHelper::deleteList('', 'shops.delete', 'JTOOLBAR_DELETE');
//
//		}
		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_shop');
		}

//		if ($canDo->get('core.edit.state'))
//		{
//			if ($this->state->get('filter.published') != 2)
//			{
//				JToolbarHelper::publish('shops.publish', 'JTOOLBAR_PUBLISH', true);
//				JToolbarHelper::unpublish('shops.unpublish', 'JTOOLBAR_UNPUBLISH', true);
//			}
//		}
	}
}
