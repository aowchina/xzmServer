<?php
defined('_JEXEC') or die;

class UstyleViewCimgs extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		UstyleHelper::addSubmenu2('cimgs');

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

		JToolbarHelper::title(JText::_('车款图片'), 'pie');
		
			if($canDo->get('core.create')){
				JToolbarHelper::addNew('cimg.add');
			}
		
		if($canDo->get('core.edit')){
			JToolbarHelper::editList('cimg.edit');
		}
		
		if($canDo->get('core.delete')){

		JToolbarHelper::deleteList('', 'cimgs.delete', 'JTOOLBAR_DELETE');

		}

		if($canDo->get('core.create')){
			JToolbarHelper::custom('ustyles.addimage', 'box-add.png', 'box-add.png', '图片详情', false);
		}
		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_ustyle');
		}
	}
}
