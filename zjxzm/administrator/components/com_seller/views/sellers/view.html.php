<?php
defined('_JEXEC') or die;

class SellerViewSellers extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		SellerHelper::addSubmenu('sellers');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = SellerHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('配件商管理：配件商列表'), 'database');
		
//		if($canDo->get('core.create')){
//			JToolbarHelper::addNew('seller.add');
//		}
//		if($canDo->get('core.edit')){
//			JToolbarHelper::editList('seller.edit');
//		}

			JToolbarHelper::publish('sellers.publish', '审核通过', true);
			JToolbarHelper::unpublish('sellers.unpublish', '审核不通过', true);


//		if($canDo->get('core.delete')){
//			JToolbarHelper::deleteList('', 'sellers.delete', 'JTOOLBAR_DELETE');
//		}


		
		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_seller');
		}


	}
}
