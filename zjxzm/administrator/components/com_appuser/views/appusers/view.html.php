<?php
defined('_JEXEC') or die;

class AppuserViewAppusers extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		AppuserHelper::addSubmenu('appusers');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = AppuserHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('APP用户管理：APP用户列表'), 'database');
		
//		if($canDo->get('core.create')){
//			JToolbarHelper::addNew('appuser.add');
//		}
//		if($canDo->get('core.edit')){
//			JToolbarHelper::editList('appuser.edit');
//		}

//			JToolbarHelper::publish('appusers.publish', 'JTOOLBAR_PUBLISH', true);
//			JToolbarHelper::unpublish('appusers.unpublish', 'JTOOLBAR_UNPUBLISH', true);


//		if($canDo->get('core.delete')){
//			JToolbarHelper::deleteList('', 'appusers.delete', 'JTOOLBAR_DELETE');
//		}


		
		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_appuser');
		}


	}
}
