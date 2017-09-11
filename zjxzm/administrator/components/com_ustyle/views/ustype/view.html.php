<?php

defined('_JEXEC') or die;

class UstyleViewUstype extends JViewLegacy{
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

		$serialid = $this->item->serialid;
		if(empty($serialid)){
			JToolbarHelper::title(JText::_('车系管理: 添加'), 'book');
		}else{
			JToolbarHelper::title(JText::_('车系管理: 修改'), 'book');
		}

		JToolbarHelper::save('ustype.save');
		
		if(empty($this->item->serialid)){
			JToolbarHelper::cancel('ustype.cancel');
		}else{
			JToolbarHelper::cancel('ustype.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
