<?php

defined('_JEXEC') or die;

class UstyleViewIn extends JViewLegacy{
	protected $item;
	protected $form;

	public function display($tpl = null){
		//$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = UstyleHelper::getActions();
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('车款管理：导入车款数据'), 'list-2');

		if($canDo->get('core.edit')){
			JToolbarHelper::save('ustyles.save');
		}

		if(empty($this->item->carid)){
			JToolbarHelper::cancel('ustyle.cancel');
		}else{
			JToolbarHelper::cancel('ustyle.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
