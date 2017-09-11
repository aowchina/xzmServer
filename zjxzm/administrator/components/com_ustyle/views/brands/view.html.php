<?php
defined('_JEXEC') or die;

class UstyleViewBrands extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');
  		UstyleHelper::addSubmenu('brands');

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

		JToolbarHelper::title(JText::_('品牌管理'), 'pie');
		
		if($canDo->get('core.create')){
			JToolbarHelper::addNew('brand.add');
		}

		if($canDo->get('core.edit')){
			JToolbarHelper::editList('brand.edit');
			JToolbarHelper::custom('brands.toin', 'contract-2', 'contract-2', '导入品牌数据', false);
		}
		
		if($canDo->get('core.delete')){
			JToolbarHelper::deleteList('', 'brands.delete', 'JTOOLBAR_DELETE');
		}

		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_ustyle');  //???????
		}

	}
}
