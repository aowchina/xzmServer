<?php

defined('_JEXEC') or die;

class UstyleViewUstyle extends JViewLegacy{
	protected $item;
	protected $form;

	public function display($tpl = null){
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar(){
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$carid = $this->item->carid;
		if(empty($carid)){
			JToolbarHelper::title(JText::_('车款详情: 添加'), 'book');
		}else{
			JToolbarHelper::title(JText::_('车款详情: 修改'), 'book');
		}

		JToolbarHelper::save('ustyle.save');
		// JToolbarHelper::save2new('we.save2new');

		if(empty($this->item->carid)){
			JToolbarHelper::cancel('ustyle.cancel');
		}else{
			JToolbarHelper::cancel('ustyle.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
