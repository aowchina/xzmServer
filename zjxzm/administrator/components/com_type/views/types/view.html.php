<?php
defined('_JEXEC') or die;

class TypeViewTypes extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		TypeHelper::addSubmenu('types');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = TypeHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('商品类别管理'), 'pie');
		
			if($canDo->get('core.create')){
				JToolbarHelper::addNew('type.add');
			}
		
		if($canDo->get('core.edit')){
			JToolbarHelper::editList('type.edit');
//			JToolbarHelper::custom('orders.out', 'pin', 'pin', '导出订单', false);
			JToolbarHelper::custom('types.toin', 'contract-2', 'contract-2', '导入商品类型', false);
		}
		
		if($canDo->get('core.delete')){

		JToolbarHelper::deleteList('', 'types.delete', 'JTOOLBAR_DELETE');

		}
		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_type');
		}
	}
}
